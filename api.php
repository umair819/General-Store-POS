<?php
// Central API Handler for TijaratPro
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

// Check auth (except for login-request)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$action = $input['action'] ?? '';
$data = $input['data'] ?? [];

if (!isset($_SESSION['user_id']) && $action !== 'login-request') {
    echo json_encode([
        ['channel' => 'auth-error', 'data' => ['success' => false, 'msg' => 'Unauthorized access']]
    ]);
    exit;
}

$replies = [];

function reply($channel, $responseData) {
    global $replies;
    $replies[] = [
        'channel' => $channel,
        'data' => $responseData
    ];
}

switch ($action) {
    // -----------------------------------------
    // CATEGORIES ENDPOINTS
    // -----------------------------------------
    case 'get-categories':
        $rows = dbQuery("SELECT * FROM categories ORDER BY name ASC");
        reply('categories-list', $rows);
        break;

    case 'save-category':
        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if (empty($name)) {
            reply('category-saved', ['success' => false, 'msg' => 'Category name is required']);
            break;
        }

        if ($id) {
            // Update
            $res = dbExecute("UPDATE categories SET name = ?, description = ? WHERE id = ?", [$name, $description, $id]);
            $msg = 'Category updated successfully';
        } else {
            // Insert
            $res = dbExecute("INSERT INTO categories (name, description) VALUES (?, ?)", [$name, $description]);
            $msg = 'Category added successfully';
        }

        if (isset($res['error'])) {
            reply('category-saved', ['success' => false, 'msg' => 'Database error: ' . $res['error']]);
        } else {
            reply('category-saved', ['success' => true, 'msg' => $msg]);
        }
        break;

    case 'delete-category':
        $id = $data['id'] ?? null;
        if ($id) {
            $res = dbExecute("DELETE FROM categories WHERE id = ?", [$id]);
            if (isset($res['error'])) {
                reply('category-deleted', ['success' => false, 'msg' => 'Cannot delete: category might be in use']);
            } else {
                reply('category-deleted', ['success' => true, 'msg' => 'Category deleted successfully']);
            }
        }
        break;

    // -----------------------------------------
    // PRODUCTS ENDPOINTS
    // -----------------------------------------
    // PRODUCTS ENDPOINTS (Dynamic Item Types & Attributes Support)
    // -----------------------------------------
    case 'get-products':
        $type_id = !empty($data['item_type_id']) ? (int)$data['item_type_id'] : null;
        $sql = "SELECT p.*, c.name as category_name, it.name as item_type_name, it.slug as item_type_slug, it.icon as item_type_icon, b.name as brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN item_types it ON p.item_type_id = it.id 
                LEFT JOIN brands b ON p.brand_id = b.id";
        $params = [];
        if ($type_id) {
            $sql .= " WHERE p.item_type_id = ?";
            $params[] = $type_id;
        }
        $sql .= " ORDER BY p.name ASC";
        $rows = dbQuery($sql, $params);
        reply('products-list', $rows);
        break;

    case 'get-product-details':
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            reply('product-details', ['success' => false, 'msg' => 'Product ID is required']);
            break;
        }
        $product = dbQueryFirst("SELECT p.*, c.name as category_name, it.name as item_type_name, it.slug as item_type_slug, it.icon as item_type_icon, b.name as brand_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id 
                                 LEFT JOIN item_types it ON p.item_type_id = it.id 
                                 LEFT JOIN brands b ON p.brand_id = b.id 
                                 WHERE p.id = ?", [$id]);
        if (!$product) {
            reply('product-details', ['success' => false, 'msg' => 'Product not found']);
            break;
        }
        $attrs = getProductAttributes($id);
        $variants = dbQuery("SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC", [$id]);
        reply('product-details', [
            'success' => true,
            'product' => $product,
            'attributes' => $attrs,
            'variants' => $variants
        ]);
        break;

    case 'save-product':
        $id = $data['id'] ?? null;
        $barcode = !empty($data['barcode']) ? trim($data['barcode']) : null;
        $name = trim($data['name'] ?? '');
        $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $item_type_id = !empty($data['item_type_id']) ? (int)$data['item_type_id'] : null;
        $brand_id = !empty($data['brand_id']) ? (int)$data['brand_id'] : null;
        $purchase_price = (float)($data['purchase_price'] ?? 0.0);
        $sale_price = (float)($data['sale_price'] ?? 0.0);
        $unit = trim($data['unit'] ?? 'Piece');
        $stock_qty = (float)($data['stock_qty'] ?? 0.0);
        $min_stock = (float)($data['min_stock_threshold'] ?? 5.0);
        $expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $sku = !empty($data['sku']) ? trim($data['sku']) : null;
        $description = trim($data['description'] ?? '');
        $image_url = trim($data['image_url'] ?? '');
        $has_variants = !empty($data['has_variants']) ? 1 : 0;
        $is_online = isset($data['is_online']) ? (int)$data['is_online'] : 1;
        $is_featured = !empty($data['is_featured']) ? 1 : 0;
        $attributes = $data['attributes'] ?? [];

        if (empty($name)) {
            reply('product-saved', ['success' => false, 'msg' => 'Product name is required']);
            break;
        }

        if ($id) {
            // Update
            $res = dbExecute("UPDATE products SET barcode = ?, name = ?, category_id = ?, item_type_id = ?, brand_id = ?, purchase_price = ?, sale_price = ?, unit = ?, min_stock_threshold = ?, expiry_date = ?, sku = ?, description = ?, image_url = ?, has_variants = ?, is_online = ?, is_featured = ? WHERE id = ?", [
                $barcode, $name, $category_id, $item_type_id, $brand_id, $purchase_price, $sale_price, $unit, $min_stock, $expiry_date, $sku, $description, $image_url, $has_variants, $is_online, $is_featured, $id
            ]);
            $productId = $id;
            $msg = 'Product updated successfully';
        } else {
            // Insert
            $res = dbExecute("INSERT INTO products (barcode, name, category_id, item_type_id, brand_id, purchase_price, sale_price, unit, stock_qty, min_stock_threshold, expiry_date, sku, description, image_url, has_variants, is_online, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $barcode, $name, $category_id, $item_type_id, $brand_id, $purchase_price, $sale_price, $unit, $stock_qty, $min_stock, $expiry_date, $sku, $description, $image_url, $has_variants, $is_online, $is_featured
            ]);
            $productId = $res['insertId'] ?? null;
            $msg = 'Product added successfully';
        }

        if (isset($res['error']) || !$productId) {
            reply('product-saved', ['success' => false, 'msg' => 'Database error: ' . ($res['error'] ?? 'Unknown error')]);
            break;
        }

        // Save dynamic EAV attributes if provided
        if ($item_type_id && !empty($attributes) && is_array($attributes)) {
            saveProductAttributes($productId, $item_type_id, $attributes);
        }

        // Save variants if has_variants is true and variants array provided
        if ($has_variants && isset($data['variants']) && is_array($data['variants'])) {
            dbExecute("DELETE FROM product_variants WHERE product_id = ?", [$productId]);
            foreach ($data['variants'] as $idx => $v) {
                $vLabel = trim($v['variant_label'] ?? '');
                if (empty($vLabel)) continue;
                $vBarcode = !empty($v['barcode']) ? trim($v['barcode']) : null;
                $vSku = !empty($v['sku']) ? trim($v['sku']) : null;
                $vPurchase = (float)($v['purchase_price'] ?? $purchase_price);
                $vSale = (float)($v['sale_price'] ?? $sale_price);
                $vStock = (float)($v['stock_qty'] ?? 0.0);
                $vMinStock = (float)($v['min_stock_threshold'] ?? 3.0);
                $vAttrs = isset($v['attribute_values']) ? (is_array($v['attribute_values']) ? json_encode($v['attribute_values']) : $v['attribute_values']) : null;

                dbExecute("INSERT INTO product_variants (product_id, variant_label, attribute_values, barcode, sku, purchase_price, sale_price, stock_qty, min_stock_threshold, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $productId, $vLabel, $vAttrs, $vBarcode, $vSku, $vPurchase, $vSale, $vStock, $vMinStock, $idx
                ]);
            }
        }

        reply('product-saved', ['success' => true, 'msg' => $msg, 'product_id' => $productId]);
        break;

    case 'delete-product':
        $id = $data['id'] ?? null;
        if ($id) {
            dbExecute("DELETE FROM product_attributes WHERE product_id = ?", [$id]);
            dbExecute("DELETE FROM product_variants WHERE product_id = ?", [$id]);
            $res = dbExecute("DELETE FROM products WHERE id = ?", [$id]);
            if (isset($res['error'])) {
                reply('product-deleted', ['success' => false, 'msg' => 'Cannot delete: product is referenced by transactions']);
            } else {
                reply('product-deleted', ['success' => true, 'msg' => 'Product deleted successfully']);
            }
        }
        break;

    // -----------------------------------------
    // ITEM TYPES & ATTRIBUTES ENDPOINTS
    // -----------------------------------------
    case 'get-item-types':
        $activeIds = getActiveItemTypes();
        $types = dbQuery("SELECT * FROM item_types ORDER BY sort_order ASC, name ASC");
        foreach ($types as &$t) {
            $t['is_store_active'] = in_array((int)$t['id'], $activeIds);
            // Count products in this type
            $cnt = dbQueryFirst("SELECT COUNT(*) as count FROM products WHERE item_type_id = ?", [$t['id']]);
            $t['product_count'] = (int)($cnt['count'] ?? 0);
        }
        reply('item-types-list', [
            'success' => true,
            'types' => $types,
            'active_ids' => $activeIds
        ]);
        break;

    case 'set-active-item-types':
        $ids = $data['item_type_ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            reply('active-item-types-saved', ['success' => false, 'msg' => 'Please select at least one item type']);
            break;
        }
        $cleanIds = array_values(array_unique(array_map('intval', $ids)));
        setConfig('active_item_types', json_encode($cleanIds));
        reply('active-item-types-saved', ['success' => true, 'msg' => 'Active item types updated successfully', 'active_ids' => $cleanIds]);
        break;

    case 'get-type-attributes':
        $type_id = (int)($data['item_type_id'] ?? 0);
        if (!$type_id) {
            reply('type-attributes-list', ['success' => false, 'msg' => 'Item type ID is required']);
            break;
        }
        $attrs = getTypeAttributes($type_id);
        // Decode JSON options for select fields
        foreach ($attrs as &$a) {
            if ($a['options']) {
                $a['options_array'] = json_decode($a['options'], true) ?: [];
            } else {
                $a['options_array'] = [];
            }
        }
        reply('type-attributes-list', [
            'success' => true,
            'item_type_id' => $type_id,
            'attributes' => $attrs
        ]);
        break;

    // -----------------------------------------
    // BRANDS ENDPOINTS
    // -----------------------------------------
    case 'get-brands':
        $brands = dbQuery("SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id = b.id) as product_count FROM brands b ORDER BY b.name ASC");
        reply('brands-list', ['success' => true, 'brands' => $brands]);
        break;

    case 'save-brand':
        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');
        $country = trim($data['country'] ?? '');
        $description = trim($data['description'] ?? '');
        $is_local = !empty($data['is_local']) ? 1 : 0;

        if (empty($name)) {
            reply('brand-saved', ['success' => false, 'msg' => 'Brand name is required']);
            break;
        }

        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

        if ($id) {
            $res = dbExecute("UPDATE brands SET name = ?, slug = ?, country = ?, description = ?, is_local = ? WHERE id = ?", [
                $name, $slug, $country, $description, $is_local, $id
            ]);
            $msg = 'Brand updated successfully';
        } else {
            $res = dbExecute("INSERT INTO brands (name, slug, country, description, is_local) VALUES (?, ?, ?, ?, ?)", [
                $name, $slug, $country, $description, $is_local
            ]);
            $msg = 'Brand added successfully';
        }

        if (isset($res['error'])) {
            reply('brand-saved', ['success' => false, 'msg' => 'Database error: ' . $res['error']]);
        } else {
            reply('brand-saved', ['success' => true, 'msg' => $msg]);
        }
        break;

    case 'delete-brand':
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            $prodCount = dbQueryFirst("SELECT COUNT(*) as cnt FROM products WHERE brand_id = ?", [$id]);
            if (($prodCount['cnt'] ?? 0) > 0) {
                reply('brand-deleted', ['success' => false, 'msg' => 'Cannot delete brand: assigned to ' . $prodCount['cnt'] . ' product(s)']);
                break;
            }
            $res = dbExecute("DELETE FROM brands WHERE id = ?", [$id]);
            reply('brand-deleted', ['success' => true, 'msg' => 'Brand deleted successfully']);
        }
        break;

    // -----------------------------------------
    // PACKAGE & SETUP WIZARD ENDPOINTS
    // -----------------------------------------
    case 'get-packages':
        $packages = [
            'O1' => [
                'code' => 'O1',
                'name' => 'Offline Solo',
                'name_ur' => 'سولو آف لائن',
                'tier' => 'offline',
                'description' => 'Fast, single-terminal POS for small single-counter shops',
                'price' => 'One-time / Local',
                'badge' => 'Offline Starter',
                'features' => [
                    'Single PC Core POS', 'Standard Inventory', 'Basic Customer Udhaar', 
                    '58mm/80mm Thermal Receipt', 'Barcode Scanner Support', 'Daily Sales Report'
                ]
            ],
            'O2' => [
                'code' => 'O2',
                'name' => 'Offline Pro',
                'name_ur' => 'پرو آف لائن',
                'tier' => 'offline',
                'description' => 'Full retail workflow with supplier purchasing, WhatsApp receipts & AI bill scanner',
                'price' => 'One-time / Local',
                'badge' => 'Most Popular Offline',
                'features' => [
                    'Everything in O1', 'Supplier Khata & Purchases', 'Baileys WhatsApp Bot', 
                    'Gemini AI Invoice OCR Scanner', 'Marketing & Birthday Alerts', 'Low Stock & Expiry Alerts'
                ]
            ],
            'O3' => [
                'code' => 'O3',
                'name' => 'Offline Enterprise',
                'name_ur' => 'انٹرپرائز آف لائن',
                'tier' => 'offline',
                'description' => 'Multi-counter LAN setup with dynamic variants & advanced loyalty engine',
                'price' => 'One-time / Local',
                'badge' => 'Offline Flagship',
                'features' => [
                    'Everything in O2', 'LAN Multi-Terminal (Counter + Kitchen/Warehouse)', 
                    'Multi-Variant Matrix (Size/Color/Volume)', 'Customer Loyalty Points', 
                    'Custom A4 / Invoice Templates', 'Expense Tracking & Balance Sheet'
                ]
            ],
            'H1' => [
                'code' => 'H1',
                'name' => 'Hybrid Starter',
                'name_ur' => 'ہائبرڈ سٹارٹر',
                'tier' => 'hybrid',
                'description' => 'Offline-first desktop POS that seamlessly syncs with your online web catalog',
                'price' => 'Annual / Cloud',
                'badge' => 'Web Enabled',
                'features' => [
                    'Full O2 Retail Suite Locally', 'Auto Cloud Sync (Works 100% Offline)', 
                    'Online E-Commerce Web Storefront', 'Customer Web Catalog & Inquiries', 
                    'Remote Sales Monitoring'
                ]
            ],
            'H2' => [
                'code' => 'H2',
                'name' => 'Hybrid Multi-Store',
                'name_ur' => 'ہائبرڈ ملٹی اسٹور',
                'tier' => 'hybrid',
                'description' => 'Connect multiple retail branches with central warehouse & inter-store stock transfers',
                'price' => 'Annual / Cloud',
                'badge' => 'Retail Chain Ready',
                'features' => [
                    'Full O3 Suite on Each Branch', 'Multi-Branch Cloud Sync Engine', 
                    'Inter-Branch Stock Transfers', 'Centralized HQ Cloud Dashboard', 
                    'Consolidated Profit & Stock Reports', 'Brand & Dynamic Item Matrix'
                ]
            ],
            'H3' => [
                'code' => 'H3',
                'name' => 'Hybrid Omni-Channel ERP',
                'name_ur' => 'اومنی چینل ای آر پی',
                'tier' => 'hybrid',
                'description' => 'Ultimate retail powerhouse: Evolution API WhatsApp + full web store + unlimited branches',
                'price' => 'Custom / Cloud',
                'badge' => 'Ultimate ERP',
                'features' => [
                    'Everything in H2', 'Full E-Commerce Cart & Checkout (Order Web → Bill in POS)', 
                    'Evolution API WhatsApp (Multi-Device Auto-Responder)', 'Customer Online Portal & Khata Self-View', 
                    'REST API Access for Mobile App', 'Automated Daily Cloud Backups'
                ]
            ]
        ];

        reply('packages-list', [
            'success' => true,
            'packages' => $packages,
            'active_package' => getActivePackage(),
            'is_hybrid' => isHybridPackage(),
            'is_offline' => isOfflinePackage()
        ]);
        break;

    case 'set-active-package':
        $pkg = strtoupper(trim($data['package'] ?? ''));
        $validPackages = ['O1', 'O2', 'O3', 'H1', 'H2', 'H3'];
        if (!in_array($pkg, $validPackages)) {
            reply('package-saved', ['success' => false, 'msg' => 'Invalid package code']);
            break;
        }
        setConfig('active_package', $pkg);
        reply('package-saved', ['success' => true, 'msg' => "Package successfully switched to $pkg", 'active_package' => $pkg]);
        break;

    case 'get-store-setup':
        reply('store-setup-data', [
            'success' => true,
            'shop_name' => getConfig('shop_name', 'TijaratPro'),
            'shop_phone' => getConfig('shop_phone', ''),
            'shop_address' => getConfig('shop_address', ''),
            'shop_currency' => getConfig('shop_currency', 'PKR'),
            'active_package' => getActivePackage(),
            'active_item_types' => getActiveItemTypes(),
            'store_setup_complete' => getConfig('store_setup_complete', '0'),
            'tax_enabled' => getConfig('tax_enabled', '0'),
            'tax_number' => getConfig('tax_number', '')
        ]);
        break;

    case 'complete-store-setup':
        $shop_name = trim($data['shop_name'] ?? '');
        $shop_phone = trim($data['shop_phone'] ?? '');
        $shop_address = trim($data['shop_address'] ?? '');
        $shop_currency = trim($data['shop_currency'] ?? 'PKR');
        $active_package = strtoupper(trim($data['active_package'] ?? 'O1'));
        $item_types = $data['active_item_types'] ?? [1];

        if (!empty($shop_name)) setConfig('shop_name', $shop_name);
        if (!empty($shop_phone)) setConfig('shop_phone', $shop_phone);
        if (!empty($shop_address)) setConfig('shop_address', $shop_address);
        if (!empty($shop_currency)) setConfig('shop_currency', $shop_currency);
        
        $validPackages = ['O1', 'O2', 'O3', 'H1', 'H2', 'H3'];
        if (in_array($active_package, $validPackages)) {
            setConfig('active_package', $active_package);
        }

        if (is_array($item_types) && !empty($item_types)) {
            $cleanTypes = array_values(array_unique(array_map('intval', $item_types)));
            setConfig('active_item_types', json_encode($cleanTypes));
        }

        setConfig('store_setup_complete', '1');

        reply('store-setup-completed', [
            'success' => true,
            'msg' => 'Store setup completed successfully!'
        ]);
        break;

    // -----------------------------------------
    // STOCK ADJUSTMENT ENDPOINTS
    // -----------------------------------------
    case 'adjust-stock':
        $id = $data['id'] ?? null;
        $adjustment = (float)($data['adjustment'] ?? 0.0);
        
        if ($id && $adjustment != 0) {
            $res = dbExecute("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?", [$adjustment, $id]);
            if (isset($res['error'])) {
                reply('stock-adjusted', ['success' => false, 'msg' => 'Stock adjustment failed: ' . $res['error']]);
            } else {
                reply('stock-adjusted', ['success' => true, 'msg' => 'Stock adjusted successfully']);
            }
        }
        break;

    // -----------------------------------------
    // BACKUP & RESTORE ENDPOINTS
    // -----------------------------------------
    case 'restore-vyapar-backup':
        $base64_data = $data['file_data'] ?? '';
        
        if (empty($base64_data)) {
            reply('backup-restored', ['success' => false, 'msg' => 'No file data received']);
            break;
        }

        // Clean base64 string
        if (strpos($base64_data, 'data:') === 0) {
            $parts = explode(';base64,', $base64_data);
            $base64_data = array_pop($parts);
        }

        $temp_zip = __DIR__ . '/temp_upload_' . time() . '.zip';
        $temp_dir = __DIR__ . '/temp_extract_' . time();
        
        try {
            // Write base64 data to temp zip file
            file_put_contents($temp_zip, base64_decode($base64_data));
            
            // Extract Zip
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            
            $zip = new ZipArchive();
            if ($zip->open($temp_zip) === TRUE) {
                $zip->extractTo($temp_dir);
                $zip->close();
            } else {
                throw new Exception("Uploaded file is not a valid zip container");
            }
            
            // Find .vyp database inside
            $vyp_files = glob($temp_dir . '/*.vyp');
            if (empty($vyp_files)) {
                throw new Exception("No .vyp database file found in backup");
            }
            
            $vyp_db_path = $vyp_files[0];
            $vyapar_conn = new PDO("sqlite:" . $vyp_db_path);
            $vyapar_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $vyapar_conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Migrate Categories
            $categories = $vyapar_conn->query("SELECT * FROM kb_item_categories")->fetchAll();
            $category_mapping = [];
            foreach ($categories as $cat) {
                $cat_name = trim($cat['item_category_name']);
                $existing = dbQueryFirst("SELECT id FROM categories WHERE name = ?", [$cat_name]);
                if ($existing) {
                    $category_mapping[$cat['item_category_id']] = $existing['id'];
                } else {
                    $exec = dbExecute("INSERT INTO categories (name) VALUES (?)", [$cat_name]);
                    if ($exec['success']) {
                        $category_mapping[$cat['item_category_id']] = $exec['insertId'];
                    }
                }
            }

            // Migrate Products
            $products = $vyapar_conn->query("SELECT * FROM kb_items")->fetchAll();
            $migrated_products = 0;
            foreach ($products as $prod) {
                if (isset($prod['item_is_active']) && $prod['item_is_active'] == 0) continue;
                
                $name = trim($prod['item_name']);
                $barcode = !empty($prod['item_code']) ? trim($prod['item_code']) : null;
                $purchase_price = (float)($prod['item_purchase_unit_price'] ?? 0.0);
                $sale_price = (float)($prod['item_sale_unit_price'] ?? 0.0);
                $stock_qty = (float)($prod['item_stock_quantity'] ?? 0.0);
                $min_stock = (float)($prod['item_min_stock_quantity'] ?? 5.0);
                
                $cat_id = null;
                if (!empty($prod['category_id']) && isset($category_mapping[$prod['category_id']])) {
                    $cat_id = $category_mapping[$prod['category_id']];
                }

                $existing = $barcode ? dbQueryFirst("SELECT id FROM products WHERE barcode = ?", [$barcode]) : dbQueryFirst("SELECT id FROM products WHERE name = ?", [$name]);
                if ($existing) {
                    dbExecute(
                        "UPDATE products SET purchase_price = ?, sale_price = ?, stock_qty = stock_qty + ? WHERE id = ?",
                        [$purchase_price, $sale_price, $stock_qty, $existing['id']]
                    );
                } else {
                    dbExecute(
                        "INSERT INTO products (barcode, name, category_id, purchase_price, sale_price, unit, stock_qty, min_stock_threshold) VALUES (?, ?, ?, ?, ?, 'Piece', ?, ?)",
                        [$barcode, $name, $cat_id, $purchase_price, $sale_price, $stock_qty, $min_stock]
                    );
                }
                $migrated_products++;
            }

            // Migrate Customers
            $customers = $vyapar_conn->query("SELECT * FROM kb_names WHERE name_type = 1")->fetchAll();
            $migrated_customers = 0;
            foreach ($customers as $cust) {
                $name = trim($cust['full_name']);
                $phone = !empty($cust['phone_number']) ? trim($cust['phone_number']) : '';
                $address = trim($cust['address'] ?? '');
                $balance = (float)($cust['amount'] ?? 0.0);

                if (empty($phone)) {
                    $phone = '0000-' . str_pad($cust['name_id'], 6, '0', STR_PAD_LEFT);
                }

                $existing = dbQueryFirst("SELECT id FROM customers WHERE phone = ?", [$phone]);
                if ($existing) {
                    dbExecute("UPDATE customers SET balance = ? WHERE id = ?", [$balance, $existing['id']]);
                } else {
                    dbExecute("INSERT INTO customers (name, phone, address, balance) VALUES (?, ?, ?, ?)", [$name, $phone, $address, $balance]);
                }
                $migrated_customers++;
            }
            
            // Clean up connections & temp files
            unset($vyapar_conn);
            unlink($temp_zip);
            rrmdir($temp_dir);
            
            reply('backup-restored', [
                'success' => true,
                'msg' => "Vyapar backup migrated successfully! Imported " . count($categories) . " categories, " . $migrated_products . " products, and " . $migrated_customers . " customers."
            ]);
            
        } catch (Exception $e) {
            // Clean up on failure
            if (file_exists($temp_zip)) unlink($temp_zip);
            if (is_dir($temp_dir)) rrmdir($temp_dir);
            reply('backup-restored', ['success' => false, 'msg' => 'Migration failed: ' . $e->getMessage()]);
        }
        break;

    case 'get-customers':
        $rows = dbQuery("SELECT * FROM customers ORDER BY name ASC");
        reply('customers-list', $rows);
        break;

    case 'save-customer':
        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        $birthdate = !empty($data['birthdate']) ? $data['birthdate'] : null;
        $anniversary = !empty($data['anniversary']) ? $data['anniversary'] : null;
        $initial_balance = (float)($data['balance'] ?? 0.0);
        
        if (empty($name) || empty($phone)) {
            reply('customer-saved', ['success' => false, 'msg' => 'Name and Phone are required']);
            break;
        }
        
        if ($id) {
            // Update
            $res = dbExecute("UPDATE customers SET name = ?, phone = ?, address = ?, birthdate = ?, anniversary = ? WHERE id = ?", [
                $name, $phone, $address, $birthdate, $anniversary, $id
            ]);
            $msg = 'Customer updated successfully';
        } else {
            // Insert
            $res = dbExecute("INSERT INTO customers (name, phone, address, balance, birthdate, anniversary) VALUES (?, ?, ?, ?, ?, ?)", [
                $name, $phone, $address, $initial_balance, $birthdate, $anniversary
            ]);
            $msg = 'Customer added successfully';
        }
        
        if (isset($res['error'])) {
            reply('customer-saved', ['success' => false, 'msg' => 'Database error: ' . $res['error']]);
        } else {
            reply('customer-saved', ['success' => true, 'msg' => $msg]);
        }
        break;

    case 'delete-customer':
        $id = $data['id'] ?? null;
        if ($id) {
            $res = dbExecute("DELETE FROM customers WHERE id = ?", [$id]);
            if (isset($res['error'])) {
                reply('customer-deleted', ['success' => false, 'msg' => 'Cannot delete customer: they may have sales or payments records']);
            } else {
                reply('customer-deleted', ['success' => true, 'msg' => 'Customer deleted successfully']);
            }
        }
        break;

    case 'get-customer-ledger':
        $cust_id = (int)($data['customer_id'] ?? 0);
        if (!$cust_id) {
            reply('customer-ledger', ['success' => false, 'msg' => 'Invalid customer ID']);
            break;
        }
        // Fetch sales
        $sales = dbQuery("SELECT id, invoice_no as reference, total as debit, paid_amount as credit, created_at, 'sale' as type, payment_method FROM sales WHERE customer_id = ? AND status = 'completed'", [$cust_id]);
        // Fetch payments (cross-DB compatible concat)
        $concatRef = dbConcat("'PAY-'", "id");
        $payments = dbQuery("SELECT id, {$concatRef} as reference, 0.0 as debit, amount as credit, created_at, 'payment' as type, payment_method FROM customer_payments WHERE customer_id = ?", [$cust_id]);
        
        // Merge
        $ledger = array_merge($sales, $payments);
        
        // Sort by date
        usort($ledger, function($a, $b) {
            return strcmp($a['created_at'], $b['created_at']);
        });
        
        // Compute running balance
        $running_balance = 0.0;
        foreach ($ledger as &$entry) {
            $entry['debit'] = (float)$entry['debit'];
            $entry['credit'] = (float)$entry['credit'];
            $running_balance += ($entry['debit'] - $entry['credit']);
            $entry['balance'] = $running_balance;
        }
        
        reply('customer-ledger', [
            'success' => true, 
            'ledger' => $ledger, 
            'customer' => dbQueryFirst("SELECT * FROM customers WHERE id = ?", [$cust_id])
        ]);
        break;

    case 'collect-customer-payment':
        $customer_id = (int)($data['customer_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0.0);
        $payment_method = trim($data['payment_method'] ?? 'cash');
        $note = trim($data['note'] ?? '');
        
        if (!$customer_id || $amount <= 0) {
            reply('customer-payment-collected', ['success' => false, 'msg' => 'Invalid customer or amount']);
            break;
        }
        
        try {
            global $conn;
            $conn->beginTransaction();
            
            // 1. Insert payment record
            $res = dbExecute("INSERT INTO customer_payments (customer_id, amount, payment_method, note) VALUES (?, ?, ?, ?)", [
                $customer_id, $amount, $payment_method, $note
            ]);
            
            // 2. Reduce customer outstanding balance
            dbExecute("UPDATE customers SET balance = balance - ? WHERE id = ?", [$amount, $customer_id]);
            
            $conn->commit();
            reply('customer-payment-collected', ['success' => true, 'msg' => 'Payment recorded successfully!']);
        } catch (Exception $e) {
            $conn->rollBack();
            reply('customer-payment-collected', ['success' => false, 'msg' => 'Transaction failed: ' . $e->getMessage()]);
        }
        break;

    case 'get-suppliers':
        $rows = dbQuery("SELECT * FROM suppliers ORDER BY name ASC");
        reply('suppliers-list', $rows);
        break;

    case 'save-supplier':
        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');
        $contact_person = trim($data['contact_person'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        $email = trim($data['email'] ?? '');
        $initial_balance = (float)($data['balance'] ?? 0.0);
        
        if (empty($name) || empty($phone)) {
            reply('supplier-saved', ['success' => false, 'msg' => 'Supplier Name and Phone are required']);
            break;
        }
        
        if ($id) {
            $res = dbExecute("UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, address = ?, email = ? WHERE id = ?", [
                $name, $contact_person, $phone, $address, $email, $id
            ]);
            $msg = 'Supplier updated successfully';
        } else {
            $res = dbExecute("INSERT INTO suppliers (name, contact_person, phone, address, email, balance) VALUES (?, ?, ?, ?, ?, ?)", [
                $name, $contact_person, $phone, $address, $email, $initial_balance
            ]);
            $msg = 'Supplier added successfully';
        }
        
        if (isset($res['error'])) {
            reply('supplier-saved', ['success' => false, 'msg' => 'Database error: ' . $res['error']]);
        } else {
            reply('supplier-saved', ['success' => true, 'msg' => $msg]);
        }
        break;

    case 'delete-supplier':
        $id = $data['id'] ?? null;
        if ($id) {
            $res = dbExecute("DELETE FROM suppliers WHERE id = ?", [$id]);
            if (isset($res['error'])) {
                reply('supplier-deleted', ['success' => false, 'msg' => 'Cannot delete supplier: they may have purchase transactions']);
            } else {
                reply('supplier-deleted', ['success' => true, 'msg' => 'Supplier deleted successfully']);
            }
        }
        break;

    case 'get-purchases':
        $rows = dbQuery("SELECT p.*, s.name as supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.created_at DESC");
        reply('purchases-list', $rows);
        break;

    case 'process-purchase':
        global $conn;
        $purchase_no = trim($data['purchase_no'] ?? '');
        $supplier_id = !empty($data['supplier_id']) ? (int)$data['supplier_id'] : null;
        $subtotal = (float)($data['subtotal'] ?? 0.0);
        $discount = (float)($data['discount'] ?? 0.0);
        $total = (float)($data['total'] ?? 0.0);
        $paid_amount = (float)($data['paid_amount'] ?? 0.0);
        $balance_amount = (float)($data['balance_amount'] ?? 0.0); // positive means we owe supplier
        $items = $data['items'] ?? [];
        $user_id = $_SESSION['user_id'];
        
        if (empty($purchase_no)) {
            $purchase_no = 'PUR-' . time();
        }
        
        if (empty($items)) {
            reply('purchase-processed', ['success' => false, 'msg' => 'Purchase items list is empty']);
            break;
        }
        
        try {
            $conn->beginTransaction();
            
            // 1. Insert Purchases table entry
            $stmt = $conn->prepare("INSERT INTO purchases (purchase_no, supplier_id, user_id, subtotal, discount, total, paid_amount, balance_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$purchase_no, $supplier_id, $user_id, $subtotal, $discount, $total, $paid_amount, $balance_amount]);
            $purchase_id = $conn->lastInsertId();
            
            // 2. Insert items, update inventory stock, and update purchase price
            foreach ($items as $item) {
                $product_id = (int)$item['product_id'];
                $qty = (float)$item['qty'];
                $price = (float)$item['price'];
                $expiry = !empty($item['expiry']) ? $item['expiry'] : null;
                $item_total = $qty * $price;
                
                // Insert purchase item
                $stmtItem = $conn->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, purchase_price, total, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtItem->execute([$purchase_id, $product_id, $qty, $price, $item_total, $expiry]);
                
                // Update product stock (add stock) and set new purchase price
                if ($expiry) {
                    $stmtProd = $conn->prepare("UPDATE products SET stock_qty = stock_qty + ?, purchase_price = ?, expiry_date = ? WHERE id = ?");
                    $stmtProd->execute([$qty, $price, $expiry, $product_id]);
                } else {
                    $stmtProd = $conn->prepare("UPDATE products SET stock_qty = stock_qty + ?, purchase_price = ? WHERE id = ?");
                    $stmtProd->execute([$qty, $price, $product_id]);
                }
            }
            
            // 3. Update supplier balance if split/credit (negative balance represents money we owe supplier)
            if ($supplier_id && $balance_amount > 0) {
                $stmtSupp = $conn->prepare("UPDATE suppliers SET balance = balance - ? WHERE id = ?");
                $stmtSupp->execute([$balance_amount, $supplier_id]);
            }
            
            $conn->commit();
            reply('purchase-processed', ['success' => true, 'msg' => 'Purchase recorded successfully!', 'purchase_no' => $purchase_no]);
        } catch (Exception $e) {
            $conn->rollBack();
            reply('purchase-processed', ['success' => false, 'msg' => 'Purchase processing failed: ' . $e->getMessage()]);
        }
        break;

    case 'scan-invoice':
        $config = [];
        if (file_exists(__DIR__ . '/db_config.json')) {
            $config = json_decode(file_get_contents(__DIR__ . '/db_config.json'), true) ?: [];
        }
        $gemini_api_key = $config['gemini_api_key'] ?? '';
        if (empty($gemini_api_key)) {
            reply('invoice-scanned', ['success' => false, 'msg' => 'Gemini API Key is not set in Settings. Please configure it to enable AI OCR scanning.']);
            break;
        }

        $base64_image = $data['image_data'] ?? '';
        if (empty($base64_image)) {
            reply('invoice-scanned', ['success' => false, 'msg' => 'No image data received']);
            break;
        }

        // Extract base64 part
        if (preg_match('/^data:([^;]+);base64,(.*)$/', $base64_image, $matches)) {
            $mime_type = $matches[1];
            $base64_data = $matches[2];
        } else {
            $mime_type = 'image/jpeg';
            $base64_data = $base64_image;
        }

        // Call Gemini 1.5 Flash
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($gemini_api_key);
        $post_data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "You are an expert AI bill scanning assistant for Pakistani retail and wholesale businesses. Scan this purchase invoice image and extract the following details:
1. Supplier name (e.g. \"Korangi Whole Sellers\", \"Nestle Pakistan\", \"Unilever\", etc.)
2. Invoice or Bill Number (if present)
3. Purchase Items. For each item, extract:
   - Product Name (clean, readable title)
   - Quantity purchased
   - Unit Purchase Price
   - Expiry Date (format YYYY-MM-DD, or null if not found)
4. Grand Total invoice amount.

Format the output strictly as a JSON object, matching this structure:
{
  \"supplier\": \"Supplier Name or null\",
  \"invoice_no\": \"Invoice Number or null\",
  \"total\": 0.0,
  \"items\": [
    {
      \"name\": \"Product Name\",
      \"qty\": 1.0,
      \"price\": 0.0,
      \"expiry\": \"YYYY-MM-DD or null\"
    }
  ]
}
Do not include any explanation, notes, or markdown formatting (e.g. do not wrap in ```json block), just return the raw JSON string."
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $mime_type,
                                'data' => $base64_data
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code !== 200) {
            reply('invoice-scanned', ['success' => false, 'msg' => 'Gemini API Error (HTTP ' . $http_code . '): ' . ($curl_error ?: $response)]);
            break;
        }

        $res_json = json_decode($response, true);
        $text_content = $res_json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean markdown response wrappers if any
        $text_content = trim($text_content);
        if (strpos($text_content, '```') === 0) {
            $text_content = preg_replace('/^```(?:json)?\s*/i', '', $text_content);
            $text_content = preg_replace('/\s*```$/', '', $text_content);
        }
        $text_content = trim($text_content);

        $extracted_data = json_decode($text_content, true);
        if (!$extracted_data) {
            reply('invoice-scanned', ['success' => false, 'msg' => 'Failed to parse Gemini OCR response as structured JSON. Response was: ' . $text_content]);
        } else {
            reply('invoice-scanned', ['success' => true, 'data' => $extracted_data]);
        }
        break;

    case 'get-marketing-campaign':
        $campaign_type = trim($data['campaign_type'] ?? 'promotion');
        
        $customers = [];
        $template = '';
        
        // Load config to get shop name/phone
        $config = [
            'shop_name' => 'TijaratPro',
            'shop_phone' => '',
        ];
        if (file_exists(__DIR__ . '/db_config.json')) {
            $json = json_decode(file_get_contents(__DIR__ . '/db_config.json'), true);
            if ($json) $config = array_merge($config, $json);
        }
        $shop_name = $config['shop_name'];
        
        if ($campaign_type === 'udhaar_reminder') {
            $customers = dbQuery("SELECT * FROM customers WHERE balance > 0 ORDER BY balance DESC");
            $template = "Assalam-o-Alaikum {name},\n\nAap ke khate mein Rs. {balance} ka outstanding udhaar baaqi hai. Baraye meharbani jald az jald payment jama karwain.\n\nShukriya!\n*{shop_name}*";
        } elseif ($campaign_type === 'birthday_wishes') {
            $customers = dbQuery("SELECT * FROM customers WHERE STRFTIME('%m-%d', birthdate) = STRFTIME('%m-%d', 'now') ORDER BY name ASC");
            $template = "Assalam-o-Alaikum {name},\n\n*{shop_name}* ki taraf se aap ko janam din mubarak ho! 🎂 Aap ke liye hamari shop par special 5% discount hazir hai.\n\nHave a great day!";
        } elseif ($campaign_type === 'anniversary_wishes') {
            $customers = dbQuery("SELECT * FROM customers WHERE STRFTIME('%m-%d', anniversary) = STRFTIME('%m-%d', 'now') ORDER BY name ASC");
            $template = "Assalam-o-Alaikum {name},\n\n*{shop_name}* ki taraf se aap ko shadi ki salgirah mubarak ho! 🎉 Aap ke liye hamari shop par special check-out reward hazir hai.\n\nStay blessed!";
        } else { // default promotion
            $customers = dbQuery("SELECT * FROM customers ORDER BY name ASC");
            $template = "Assalam-o-Alaikum {name},\n\nBara Dhamaka Sale! Hamari shop par taza stock aur discount offers lag chuki hain. Aaj hi tashreef layen aur discount payen!\n\nContact: {shop_phone}\n*{shop_name}*";
        }
        
        reply('marketing-campaign-data', [
            'success' => true,
            'customers' => $customers,
            'template' => $template,
            'shop_name' => $shop_name,
            'shop_phone' => $config['shop_phone']
        ]);
        break;

    case 'search-products':
        $query = trim($data['query'] ?? '');
        if ($query === '') {
            $rows = dbQuery("SELECT p.*, c.name as category_name, b.name as brand_name, it.name as item_type_name, it.icon as item_type_icon 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             LEFT JOIN brands b ON p.brand_id = b.id 
                             LEFT JOIN item_types it ON p.item_type_id = it.id 
                             ORDER BY p.name ASC LIMIT 30");
        } else {
            $param = '%' . $query . '%';
            $rows = dbQuery("SELECT DISTINCT p.*, c.name as category_name, b.name as brand_name, it.name as item_type_name, it.icon as item_type_icon 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             LEFT JOIN brands b ON p.brand_id = b.id 
                             LEFT JOIN item_types it ON p.item_type_id = it.id 
                             LEFT JOIN product_variants pv ON pv.product_id = p.id 
                             WHERE p.name LIKE ? OR p.barcode = ? OR p.sku = ? OR b.name LIKE ? OR c.name LIKE ? OR pv.barcode = ?
                             ORDER BY p.name ASC LIMIT 30", 
                             [$param, $query, $query, $param, $param, $query]);
        }
        reply('search-results', $rows);
        break;

    case 'process-sale':
        global $conn;
        $invoice_no = trim($data['invoice_no'] ?? '');
        $customer_id = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $subtotal = (float)($data['subtotal'] ?? 0.0);
        $discount = (float)($data['discount'] ?? 0.0);
        $total = (float)($data['total'] ?? 0.0);
        $paid_amount = (float)($data['paid_amount'] ?? 0.0);
        $balance_amount = (float)($data['balance_amount'] ?? 0.0);
        $payment_method = trim($data['payment_method'] ?? 'cash');
        $cart = $data['cart'] ?? [];
        $user_id = $_SESSION['user_id'];

        if (empty($invoice_no)) {
            $invoice_no = 'INV-' . time();
        }

        if (empty($cart)) {
            reply('sale-processed', ['success' => false, 'msg' => 'Cart is empty']);
            break;
        }

        try {
            $conn->beginTransaction();

            // 1. Insert Sales entry
            $stmt = $conn->prepare("INSERT INTO sales (invoice_no, customer_id, user_id, subtotal, discount, total, paid_amount, balance_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$invoice_no, $customer_id, $user_id, $subtotal, $discount, $total, $paid_amount, $balance_amount, $payment_method]);
            $sale_id = $conn->lastInsertId();

            // 2. Insert items & update inventory
            foreach ($cart as $item) {
                $product_id = !empty($item['productId']) ? (int)$item['productId'] : (int)$item['id'];
                $variant_id = !empty($item['variantId']) ? (int)$item['variantId'] : null;
                $qty = (float)$item['qty'];
                $item_discount = (float)($item['discount'] ?? 0.0);
                
                // Fetch product purchase price & current stock
                $prod = dbQueryFirst("SELECT purchase_price, sale_price FROM products WHERE id = ?", [$product_id]);
                if (!$prod) {
                    throw new Exception("Product ID $product_id not found in catalog");
                }
                $purchase_price = (float)$prod['purchase_price'];
                $sale_price = (float)($item['price'] ?? $prod['sale_price']);
                $item_total = ($sale_price * $qty) - $item_discount;

                // Insert sale item
                $stmtItem = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, purchase_price, sale_price, discount, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtItem->execute([$sale_id, $product_id, $qty, $purchase_price, $sale_price, $item_discount, $item_total]);

                // Update product stock (deduct)
                $stmtStock = $conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
                $stmtStock->execute([$qty, $product_id]);

                // Update variant stock if applicable
                if ($variant_id) {
                    $stmtVar = $conn->prepare("UPDATE product_variants SET stock_qty = stock_qty - ? WHERE id = ?");
                    $stmtVar->execute([$qty, $variant_id]);
                }
            }

            // 3. Update customer outstanding credit balance (Udhaar)
            if (($payment_method === 'credit' || $payment_method === 'split') && $customer_id && $balance_amount > 0) {
                $stmtCust = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
                $stmtCust->execute([$balance_amount, $customer_id]);
            }

            $conn->commit();

            // WhatsApp Digital Receipt auto-send (if customer phone available)
            $waSent = false;
            if ($customer_id && class_exists('WhatsAppEngine')) {
                $cust = dbQueryFirst("SELECT phone FROM customers WHERE id = ?", [$customer_id]);
                if ($cust && !empty($cust['phone'])) {
                    WhatsAppEngine::sendReceipt($cust['phone'], [
                        'invoice_no' => $invoice_no,
                        'total' => $total,
                        'paid_amount' => $paid_amount,
                        'balance_amount' => $balance_amount,
                        'items' => $cart
                    ]);
                    $waSent = true;
                }
            }

            reply('sale-processed', [
                'success' => true,
                'msg' => 'Checkout completed successfully!' . ($waSent ? ' WhatsApp receipt sent.' : ''),
                'invoice_no' => $invoice_no,
                'sale_id' => $sale_id
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            reply('sale-processed', ['success' => false, 'msg' => 'Checkout transaction failed: ' . $e->getMessage()]);
        }
        break;

    default:
        reply('error-response', ['msg' => 'Action not found: ' . $action]);
        break;
}

echo json_encode($replies);

// Recursive directory helper
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}
?>
