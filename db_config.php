<?php
// ============================================================
// Tijarat PRO — Database Configuration (Local App — SQLite Only)
// Cloud MySQL is handled separately by cloud_sync_api.php
// ============================================================

$db_config_file = __DIR__ . '/db_config.json';
$db_name = 'general_store.db';

$global_config = [
    'db_name' => 'general_store.db',
    // Shop settings
    'shop_name' => 'TijaratPro',
    'shop_phone' => '03001234567',
    'shop_address' => 'Saddar, Karachi, Pakistan',
    'shop_currency' => 'PKR',
    'gemini_api_key' => '',
    'whatsapp_mode' => 'link',
    'receipt_template' => 'default',
    'tax_number' => '',
    'tax_enabled' => '0',
    'stop_negative_stock' => '0',
    'enable_passcode' => '0',
    'decimal_places' => 2,
    'font_scale' => 100,
    // Package & item types
    'active_package' => 'O1',
    'active_item_types' => '[1]',
    'store_setup_complete' => '0',
    // Cloud sync (hybrid packages only)
    'cloud_sync_enabled' => '0',
    'cloud_server_url' => '',
    'cloud_api_key' => '',
    'cloud_branch_slug' => 'main-store',
];

if (file_exists($db_config_file)) {
    $db_config_data = json_decode(file_get_contents($db_config_file), true);
    if ($db_config_data) {
        $db_name = $db_config_data['db_name'] ?? 'general_store.db';
        $global_config = array_merge($global_config, $db_config_data);
    }
} else {
    // Auto-create default configuration file
    file_put_contents($db_config_file, json_encode($global_config, JSON_PRETTY_PRINT));
}

$db_path = __DIR__ . '/' . $db_name;
$db_exists = file_exists($db_path);

// ============================================================
// Establish SQLite Connection
// ============================================================
try {
    $conn = new PDO("sqlite:" . $db_path);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("PRAGMA foreign_keys = ON;");
    $conn->exec("PRAGMA journal_mode = WAL;"); // Better performance for concurrent reads
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// ============================================================
// Auto-run schema migration on first run
// ============================================================
if (!$db_exists) {
    $schema_file = __DIR__ . '/schema.sql';
    if (file_exists($schema_file)) {
        try {
            $schema_sql = file_get_contents($schema_file);
            $conn->exec($schema_sql);
            error_log("TijaratPro: Database schema created successfully.");
        } catch (PDOException $e) {
            die("Database migration failed: " . $e->getMessage());
        }
    } else {
        die("Database file not found and schema.sql is missing!");
    }
}

// ============================================================
// Incremental Migrations (add new tables/columns without breaking existing data)
// ============================================================
try {
    // --- Customer date fields (v1.1) ---
    $cols = $conn->query("PRAGMA table_info(customers)")->fetchAll();
    $colNames = array_column($cols, 'name');
    if (!in_array('birthdate', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN birthdate DATE;");
    }
    if (!in_array('anniversary', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN anniversary DATE;");
    }
    if (!in_array('email', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN email TEXT;");
    }
    if (!in_array('loyalty_points', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN loyalty_points INTEGER NOT NULL DEFAULT 0;");
    }
    if (!in_array('source', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN source TEXT DEFAULT 'walk_in';");
    }
    if (!in_array('city', $colNames)) {
        $conn->exec("ALTER TABLE customers ADD COLUMN city TEXT;");
    }

    // --- Customer & Supplier Payments (v1.1) ---
    $conn->exec("CREATE TABLE IF NOT EXISTS customer_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        payment_method TEXT CHECK(payment_method IN ('cash', 'online', 'cheque', 'easypaisa', 'jazzcash', 'bank_transfer')) NOT NULL DEFAULT 'cash',
        reference_no TEXT,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
    );");

    $conn->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        supplier_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        payment_method TEXT CHECK(payment_method IN ('cash', 'online', 'cheque', 'bank_transfer')) NOT NULL DEFAULT 'cash',
        reference_no TEXT,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
    );");

    // --- Item Type System (v2.0) ---
    $conn->exec("CREATE TABLE IF NOT EXISTS item_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        name_ur TEXT,
        icon TEXT,
        color_hex TEXT,
        description TEXT,
        is_system INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Seed item types if empty
    $typeCount = $conn->query("SELECT COUNT(*) as cnt FROM item_types")->fetch();
    if ((int)($typeCount['cnt'] ?? 0) === 0) {
        $conn->exec("INSERT INTO item_types (id, name, slug, name_ur, icon, color_hex, is_system, sort_order) VALUES
            (1,  'General / Retail',     'general',      'جنرل',         '🛒', '#6366F1', 1, 1),
            (2,  'Perfume & Fragrance',  'perfume',       'عطر',          '🧴', '#EC4899', 1, 2),
            (3,  'Garment & Clothing',   'garment',       'کپڑے',        '👔', '#8B5CF6', 1, 3),
            (4,  'Electronics & Mobile', 'electronics',   'الیکٹرانکس',  '📱', '#3B82F6', 1, 4),
            (5,  'Shoes & Footwear',     'footwear',      'جوتے',        '👟', '#F59E0B', 1, 5),
            (6,  'Cosmetics & Beauty',   'cosmetics',     'کاسمیٹکس',    '💄', '#F43F5E', 1, 6),
            (7,  'Grocery & FMCG',       'grocery',       'گروسری',       '🧃', '#10B981', 1, 7),
            (8,  'Hardware & Auto',      'hardware',      'ہارڈویئر',     '🔧', '#64748B', 1, 8),
            (9,  'Jewelry & Accessories','jewelry',       'زیورات',       '💍', '#D97706', 1, 9),
            (10, 'Stationery & Books',   'stationery',    'سٹیشنری',      '📚', '#0EA5E9', 1, 10),
            (11, 'Medical & Pharmacy',   'pharmacy',      'دوائیں',       '💊', '#EF4444', 1, 11);
        ");
    }

    // Type Attributes
    $conn->exec("CREATE TABLE IF NOT EXISTS type_attributes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_type_id INTEGER NOT NULL,
        attribute_name TEXT NOT NULL,
        attribute_key TEXT NOT NULL,
        field_type TEXT CHECK(field_type IN ('text', 'number', 'select', 'multi_select', 'color', 'date', 'boolean', 'textarea')) NOT NULL DEFAULT 'text',
        options TEXT,
        default_value TEXT,
        placeholder TEXT,
        is_variant INTEGER NOT NULL DEFAULT 0,
        is_required INTEGER NOT NULL DEFAULT 0,
        is_filterable INTEGER NOT NULL DEFAULT 0,
        is_visible_pos INTEGER NOT NULL DEFAULT 1,
        is_visible_store INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(item_type_id) REFERENCES item_types(id) ON DELETE CASCADE,
        UNIQUE(item_type_id, attribute_key)
    );");

    // Seed type attributes if empty
    $attrCount = $conn->query("SELECT COUNT(*) as cnt FROM type_attributes")->fetch();
    if ((int)($attrCount['cnt'] ?? 0) === 0) {
        // General (1)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (1, 'Manufacturer',  'manufacturer', 'text',   NULL, 0, 1, 1),
            (1, 'Weight / Size', 'weight_size',  'text',   NULL, 0, 0, 2),
            (1, 'Pack Size',     'pack_size',    'select', '[\"Single\",\"3-Pack\",\"6-Pack\",\"12-Pack\",\"Carton\",\"Box\"]', 1, 1, 3),
            (1, 'Expiry Date',   'expiry_date',  'date',   NULL, 0, 0, 4),
            (1, 'Material',      'material',     'text',   NULL, 0, 0, 5);
        ");
        // Perfume (2)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
            (2, 'Fragrance Family',  'fragrance_family', 'select', '[\"Woody\",\"Floral\",\"Oriental\",\"Fresh\",\"Citrus\",\"Aquatic\",\"Gourmand\",\"Aromatic\",\"Spicy\",\"Musky\",\"Oud\",\"Amber\"]', 0, 0, 1, 1),
            (2, 'Concentration',     'concentration',    'select', '[\"Parfum\",\"EDP\",\"EDT\",\"EDC\",\"Body Spray\",\"Attar\",\"Oil\"]', 0, 1, 1, 2),
            (2, 'Gender',            'gender',           'select', '[\"Men\",\"Women\",\"Unisex\"]', 0, 0, 1, 3),
            (2, 'Volume (ml)',       'volume_ml',        'select', '[\"5ml\",\"10ml\",\"15ml\",\"30ml\",\"50ml\",\"75ml\",\"100ml\",\"125ml\",\"150ml\",\"200ml\",\"250ml\"]', 1, 0, 1, 4),
            (2, 'Top Notes',         'top_notes',        'text',   NULL, 0, 0, 0, 5),
            (2, 'Middle Notes',      'middle_notes',     'text',   NULL, 0, 0, 0, 6),
            (2, 'Base Notes',        'base_notes',       'text',   NULL, 0, 0, 0, 7),
            (2, 'Country of Origin', 'country_origin',   'text',   NULL, 0, 0, 1, 8);
        ");
        // Garment (3)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (3, 'Size',        'size',        'select', '[\"XS\",\"S\",\"M\",\"L\",\"XL\",\"XXL\",\"3XL\",\"Free Size\"]', 1, 1, 1),
            (3, 'Color',       'color',       'color',  NULL, 1, 1, 2),
            (3, 'Fabric',      'fabric',      'select', '[\"Cotton\",\"Polyester\",\"Silk\",\"Linen\",\"Wool\",\"Denim\",\"Chiffon\",\"Lawn\",\"Khaddar\",\"Karandi\",\"Viscose\",\"Blend\"]', 0, 1, 3),
            (3, 'Pattern',     'pattern',     'select', '[\"Solid\",\"Printed\",\"Striped\",\"Checkered\",\"Embroidered\",\"Floral\",\"Abstract\"]', 0, 1, 4),
            (3, 'Sleeve Type', 'sleeve_type', 'select', '[\"Full Sleeve\",\"Half Sleeve\",\"Sleeveless\",\"3/4 Sleeve\",\"Cap Sleeve\"]', 0, 0, 5),
            (3, 'Season',      'season',      'select', '[\"Summer\",\"Winter\",\"All Season\",\"Spring\",\"Autumn\"]', 0, 1, 6),
            (3, 'Gender',      'gender',      'select', '[\"Men\",\"Women\",\"Kids\",\"Unisex\"]', 0, 1, 7),
            (3, 'Wash Care',   'wash_care',   'text',   NULL, 0, 0, 8);
        ");
        // Electronics (4)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (4, 'Model',     'model',     'text',     NULL, 0, 1, 1),
            (4, 'Color',     'color',     'color',    NULL, 1, 1, 2),
            (4, 'Storage',   'storage',   'select',   '[\"16GB\",\"32GB\",\"64GB\",\"128GB\",\"256GB\",\"512GB\",\"1TB\"]', 1, 1, 3),
            (4, 'RAM',       'ram',       'select',   '[\"2GB\",\"3GB\",\"4GB\",\"6GB\",\"8GB\",\"12GB\",\"16GB\"]', 0, 1, 4),
            (4, 'Warranty',  'warranty',  'select',   '[\"No Warranty\",\"3 Months\",\"6 Months\",\"1 Year\",\"2 Years\"]', 0, 1, 5),
            (4, 'Condition', 'condition', 'select',   '[\"New\",\"Refurbished\",\"Open Box\",\"Used\"]', 0, 1, 6),
            (4, 'Specs',     'specs',     'textarea', NULL, 0, 0, 7);
        ");
        // Footwear (5)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (5, 'Shoe Size', 'shoe_size', 'select', '[\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\",\"13\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\",\"44\",\"45\"]', 1, 1, 1),
            (5, 'Color',     'color',     'color',  NULL, 1, 1, 2),
            (5, 'Material',  'material',  'select', '[\"Leather\",\"Synthetic\",\"Canvas\",\"Suede\",\"Rubber\",\"Mesh\",\"Fabric\"]', 0, 1, 3),
            (5, 'Style',     'style',     'select', '[\"Casual\",\"Formal\",\"Sports\",\"Sandals\",\"Boots\",\"Sneakers\",\"Loafers\",\"Chappal\",\"Peshawari\"]', 0, 1, 4),
            (5, 'Gender',    'gender',    'select', '[\"Men\",\"Women\",\"Kids\",\"Unisex\"]', 0, 1, 5);
        ");
        // Cosmetics (6)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (6, 'Shade / Color',   'shade',       'color',    NULL, 1, 1, 1),
            (6, 'Skin Type',       'skin_type',   'select',   '[\"All\",\"Oily\",\"Dry\",\"Combination\",\"Sensitive\",\"Normal\"]', 0, 1, 2),
            (6, 'Volume / Weight', 'volume',      'text',     NULL, 0, 0, 3),
            (6, 'SPF',             'spf',         'select',   '[\"None\",\"SPF 15\",\"SPF 30\",\"SPF 50\",\"SPF 50+\"]', 0, 1, 4),
            (6, 'Ingredients',     'ingredients', 'textarea', NULL, 0, 0, 5),
            (6, 'Gender',          'gender',      'select',   '[\"Men\",\"Women\",\"Unisex\"]', 0, 1, 6);
        ");
        // Grocery (7)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (7, 'MFG Date',     'mfg_date',     'date',   NULL, 0, 0, 1),
            (7, 'Expiry Date',  'expiry_date',  'date',   NULL, 0, 0, 2),
            (7, 'Batch No',     'batch_no',     'text',   NULL, 0, 0, 3),
            (7, 'Manufacturer', 'manufacturer', 'text',   NULL, 0, 1, 4),
            (7, 'Pack Size',    'pack_size',    'select', '[\"Single\",\"3-Pack\",\"6-Pack\",\"12-Pack\",\"24-Pack\",\"Carton\"]', 1, 1, 5),
            (7, 'Weight',       'weight',       'text',   NULL, 0, 0, 6),
            (7, 'Diet Type',    'diet_type',    'select', '[\"Regular\",\"Halal\",\"Organic\",\"Sugar Free\",\"Gluten Free\"]', 0, 1, 7);
        ");
        // Hardware (8)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (8, 'Material',      'material',      'select', '[\"Steel\",\"Iron\",\"Brass\",\"Copper\",\"Aluminum\",\"Plastic\",\"Wood\",\"Rubber\"]', 0, 1, 1),
            (8, 'Size / Spec',   'size_spec',     'text',   NULL, 1, 1, 2),
            (8, 'Grade',         'grade',         'text',   NULL, 0, 0, 3),
            (8, 'Compatibility', 'compatibility', 'text',   NULL, 0, 0, 4),
            (8, 'Weight',        'weight',        'text',   NULL, 0, 0, 5);
        ");
        // Jewelry (9)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (9, 'Material',       'material',   'select', '[\"Gold\",\"Silver\",\"Platinum\",\"Artificial\",\"Rose Gold\",\"Steel\",\"Pearl\"]', 0, 1, 1),
            (9, 'Karat',          'karat',      'select', '[\"24K\",\"22K\",\"18K\",\"14K\",\"N/A\"]', 0, 1, 2),
            (9, 'Stone Type',     'stone_type', 'select', '[\"None\",\"Diamond\",\"Ruby\",\"Emerald\",\"Sapphire\",\"Zircon\",\"Pearl\",\"Crystal\"]', 0, 1, 3),
            (9, 'Size',           'size',       'text',   NULL, 1, 0, 4),
            (9, 'Weight (grams)', 'weight_g',   'number', NULL, 0, 0, 5),
            (9, 'Gender',         'gender',     'select', '[\"Men\",\"Women\",\"Unisex\"]', 0, 1, 6);
        ");
        // Stationery (10)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_filterable, sort_order) VALUES
            (10, 'Type',       'type',       'select', '[\"Notebook\",\"Pen\",\"Pencil\",\"Eraser\",\"Ruler\",\"Marker\",\"File\",\"Book\",\"Art Supply\",\"Other\"]', 0, 1, 1),
            (10, 'Paper Size', 'paper_size', 'select', '[\"A4\",\"A5\",\"B5\",\"Legal\",\"Letter\",\"N/A\"]', 0, 1, 2),
            (10, 'Color',      'color',      'color',  NULL, 1, 1, 3),
            (10, 'Pack Count', 'pack_count', 'select', '[\"Single\",\"3-Pack\",\"6-Pack\",\"12-Pack\",\"Box\"]', 1, 1, 4);
        ");
        // Pharmacy (11)
        $conn->exec("INSERT INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
            (11, 'Generic Name',     'generic_name', 'text',    NULL, 0, 0, 1, 1),
            (11, 'Dosage Form',      'dosage_form',  'select',  '[\"Tablet\",\"Capsule\",\"Syrup\",\"Injection\",\"Cream\",\"Ointment\",\"Drops\",\"Inhaler\",\"Sachet\",\"Suppository\"]', 0, 0, 1, 2),
            (11, 'Strength',         'strength',     'text',    NULL, 1, 0, 1, 3),
            (11, 'Pack Size',        'pack_size',    'select',  '[\"Strip\",\"Bottle\",\"Box\",\"Tube\",\"Single\"]', 1, 0, 1, 4),
            (11, 'Manufacturer',     'manufacturer', 'text',    NULL, 0, 0, 1, 5),
            (11, 'Expiry Date',      'expiry_date',  'date',    NULL, 0, 1, 0, 6),
            (11, 'Batch No',         'batch_no',     'text',    NULL, 0, 0, 0, 7),
            (11, 'Prescription Req', 'rx_required',  'boolean', NULL, 0, 0, 1, 8);
        ");
    }

    // Product Attributes (EAV values)
    $conn->exec("CREATE TABLE IF NOT EXISTS product_attributes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        attribute_id INTEGER NOT NULL,
        value_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY(attribute_id) REFERENCES type_attributes(id) ON DELETE CASCADE,
        UNIQUE(product_id, attribute_id)
    );");

    // Product Variants (generic — size, color, volume, etc.)
    $conn->exec("CREATE TABLE IF NOT EXISTS product_variants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        variant_label TEXT NOT NULL,
        attribute_values TEXT,
        barcode TEXT UNIQUE,
        sku TEXT UNIQUE,
        purchase_price REAL NOT NULL DEFAULT 0.0,
        sale_price REAL NOT NULL DEFAULT 0.0,
        compare_price REAL,
        stock_qty REAL NOT NULL DEFAULT 0.0,
        min_stock_threshold REAL NOT NULL DEFAULT 3.0,
        image_url TEXT,
        weight_grams INTEGER,
        is_active INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    );");

    // Brands (universal — works for any item type)
    $conn->exec("CREATE TABLE IF NOT EXISTS brands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE,
        logo_url TEXT,
        country TEXT,
        description TEXT,
        is_local INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Add item_type_id to products table if missing
    $prodCols = $conn->query("PRAGMA table_info(products)")->fetchAll();
    $prodColNames = array_column($prodCols, 'name');
    if (!in_array('item_type_id', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN item_type_id INTEGER;");
    }
    if (!in_array('brand_id', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN brand_id INTEGER;");
    }
    if (!in_array('has_variants', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN has_variants INTEGER NOT NULL DEFAULT 0;");
    }
    if (!in_array('slug', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN slug TEXT;");
    }
    if (!in_array('sku', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN sku TEXT;");
    }
    if (!in_array('image_url', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN image_url TEXT;");
    }
    if (!in_array('is_featured', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN is_featured INTEGER NOT NULL DEFAULT 0;");
    }
    if (!in_array('is_online', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN is_online INTEGER NOT NULL DEFAULT 1;");
    }
    if (!in_array('is_active', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN is_active INTEGER NOT NULL DEFAULT 1;");
    }
    if (!in_array('description', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN description TEXT;");
    }
    if (!in_array('tags', $prodColNames)) {
        $conn->exec("ALTER TABLE products ADD COLUMN tags TEXT;");
    }

    // Sync tracking columns on key tables
    $syncTables = ['sales', 'products', 'customers', 'purchases'];
    foreach ($syncTables as $tbl) {
        $tblCols = $conn->query("PRAGMA table_info({$tbl})")->fetchAll();
        $tblColNames = array_column($tblCols, 'name');
        if (!in_array('sync_status', $tblColNames)) {
            $conn->exec("ALTER TABLE {$tbl} ADD COLUMN sync_status TEXT DEFAULT 'pending';");
        }
        if (!in_array('sync_cursor', $tblColNames)) {
            $conn->exec("ALTER TABLE {$tbl} ADD COLUMN sync_cursor INTEGER DEFAULT 0;");
        }
    }

    // Sync logs table
    $conn->exec("CREATE TABLE IF NOT EXISTS sync_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        action TEXT NOT NULL,
        record_count INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL DEFAULT 'synced',
        error_message TEXT,
        synced_at TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // E-Commerce Online Orders
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_no TEXT UNIQUE NOT NULL,
        customer_id INTEGER,
        shipping_name TEXT NOT NULL,
        shipping_phone TEXT NOT NULL,
        shipping_address TEXT NOT NULL,
        shipping_city TEXT DEFAULT 'Karachi',
        subtotal REAL NOT NULL DEFAULT 0.0,
        shipping_fee REAL NOT NULL DEFAULT 0.0,
        discount REAL NOT NULL DEFAULT 0.0,
        total REAL NOT NULL DEFAULT 0.0,
        payment_method TEXT CHECK(payment_method IN ('cod', 'easypaisa', 'jazzcash', 'bank_transfer')) DEFAULT 'cod',
        payment_status TEXT CHECK(payment_status IN ('pending', 'paid', 'failed')) DEFAULT 'pending',
        order_status TEXT CHECK(order_status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled')) DEFAULT 'pending',
        order_channel TEXT DEFAULT 'website',
        customer_notes TEXT,
        tracking_no TEXT,
        sale_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
        FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE SET NULL
    );");

    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        variant_id INTEGER,
        product_name TEXT NOT NULL,
        variant_label TEXT,
        quantity REAL NOT NULL DEFAULT 1.0,
        price REAL NOT NULL,
        total REAL NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
    );");

} catch (PDOException $e) {
    error_log("TijaratPro incremental migration: " . $e->getMessage());
}

// ============================================================
// Database Helper Functions
// ============================================================

/**
 * Executes a SELECT query and returns all matching rows.
 */
function dbQuery($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB Query Error: " . $e->getMessage() . " SQL: " . $sql);
        return [];
    }
}

/**
 * Executes a single SELECT query and returns the first row.
 */
function dbQueryFirst($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("DB Query First Error: " . $e->getMessage() . " SQL: " . $sql);
        return null;
    }
}

/**
 * Executes an INSERT, UPDATE, or DELETE query.
 */
function dbExecute($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return [
            'insertId' => $conn->lastInsertId(),
            'affectedRows' => $stmt->rowCount(),
            'success' => true
        ];
    } catch (PDOException $e) {
        error_log("DB Execute Error: " . $e->getMessage() . " SQL: " . $sql);
        return [
            'insertId' => 0,
            'affectedRows' => 0,
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// ============================================================
// Package & Feature Gating
// ============================================================

/**
 * Get the active package code (O1, O2, O3, H1, H2, H3).
 */
function getActivePackage() {
    global $global_config;
    return $global_config['active_package'] ?? 'O1';
}

/**
 * Check if a feature is active based on the current package.
 */
function isFeatureActive($feature) {
    $pkg = getActivePackage();
    
    $packageFeatures = [
        'O1' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports'],
        'O2' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports',
                  'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing'],
        'O3' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports',
                  'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing',
                  'expenses', 'a4_templates', 'loyalty', 'variants', 'advanced_reports', 'lan_multi_terminal', 'brands'],
        'H1' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports',
                  'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing',
                  'cloud_sync', 'ecommerce_catalog'],
        'H2' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports',
                  'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing',
                  'expenses', 'a4_templates', 'loyalty', 'variants', 'advanced_reports', 'lan_multi_terminal', 'brands',
                  'cloud_sync', 'ecommerce_catalog', 'multi_branch', 'stock_transfers', 'cloud_dashboard'],
        'H3' => ['pos', 'products', 'categories', 'customers_basic', 'thermal_print', 'barcode', 'basic_reports',
                  'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing',
                  'expenses', 'a4_templates', 'loyalty', 'variants', 'advanced_reports', 'lan_multi_terminal', 'brands',
                  'cloud_sync', 'ecommerce_catalog', 'multi_branch', 'stock_transfers', 'cloud_dashboard',
                  'ecommerce_full', 'evolution_api', 'coupons', 'crm_advanced', 'customer_portal', 'api_access'],
    ];

    $activeFeatures = $packageFeatures[$pkg] ?? $packageFeatures['O1'];
    return in_array($feature, $activeFeatures);
}

/**
 * Check if current package is a hybrid (cloud) package.
 */
function isHybridPackage() {
    return in_array(getActivePackage(), ['H1', 'H2', 'H3']);
}

/**
 * Check if current package is offline-only.
 */
function isOfflinePackage() {
    return in_array(getActivePackage(), ['O1', 'O2', 'O3']);
}

/**
 * Get active item type IDs for this store.
 */
function getActiveItemTypes() {
    global $global_config;
    $json = $global_config['active_item_types'] ?? '[1]';
    return json_decode($json, true) ?: [1];
}

/**
 * Get a config value.
 */
function getConfig($key, $default = '') {
    global $global_config;
    return $global_config[$key] ?? $default;
}

/**
 * Save a config value to db_config.json.
 */
function setConfig($key, $value) {
    global $global_config, $db_config_file;
    $global_config[$key] = $value;
    file_put_contents($db_config_file, json_encode($global_config, JSON_PRETTY_PRINT));
}

/**
 * Get type attributes for a specific item type.
 */
function getTypeAttributes($itemTypeId) {
    return dbQuery("SELECT * FROM type_attributes WHERE item_type_id = ? ORDER BY sort_order ASC", [$itemTypeId]);
}

/**
 * Get product's attribute values as key-value pairs.
 */
function getProductAttributes($productId) {
    $rows = dbQuery("SELECT ta.attribute_key, pa.value_text 
                     FROM product_attributes pa 
                     JOIN type_attributes ta ON pa.attribute_id = ta.id 
                     WHERE pa.product_id = ?", [$productId]);
    $attrs = [];
    foreach ($rows as $row) {
        $attrs[$row['attribute_key']] = $row['value_text'];
    }
    return $attrs;
}

/**
 * Save product attribute values.
 */
function saveProductAttributes($productId, $itemTypeId, $attributeValues) {
    $attributes = getTypeAttributes($itemTypeId);
    foreach ($attributes as $attr) {
        $key = $attr['attribute_key'];
        $value = $attributeValues[$key] ?? null;
        if ($value !== null && $value !== '') {
            dbExecute("INSERT OR REPLACE INTO product_attributes (product_id, attribute_id, value_text) VALUES (?, ?, ?)",
                [$productId, $attr['id'], $value]);
        } else {
            // Remove empty attribute values
            dbExecute("DELETE FROM product_attributes WHERE product_id = ? AND attribute_id = ?",
                [$productId, $attr['id']]);
        }
    }
}

// ================= WHATSAPP ENGINE =================
if (file_exists(__DIR__ . '/classes/WhatsAppEngine.php')) {
    require_once __DIR__ . '/classes/WhatsAppEngine.php';
}

// ================= LICENSE VERIFICATION SYSTEM =================
require_once __DIR__ . '/license_manager.php';
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page !== 'login.php') {
    $license_status = check_license();
    if ($license_status['status'] === 'invalid') {
        header("Location: login.php?license_status=invalid&message=" . urlencode($license_status['message']));
        exit();
    }
}
?>
