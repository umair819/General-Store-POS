<?php
// migrate_vyapar.php - Command line utility to migrate data from Vyapar backup (.vyb) to TijaratPro db

require_once __DIR__ . '/db_config.php';

echo "=============================================\n";
echo "🚀 VYAPAR TO TIJARATPRO MIGRATION TOOL\n";
echo "=============================================\n\n";

// 1. Locate the .vyb backup file
$backup_file = '';
$parent_dirs = [__DIR__, dirname(__DIR__)];

foreach ($parent_dirs as $dir) {
    $files = glob($dir . '/*Backup.vyb');
    if (!empty($files)) {
        // Get the latest backup file by modification time
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $backup_file = $files[0];
        break;
    }
}

if (empty($backup_file)) {
    die("❌ Error: No Vyapar backup file (*Backup.vyb) found in the project or parent directory!\n");
}

echo "📂 Found Vyapar Backup: " . basename($backup_file) . "\n";

// 2. Create temporary extraction folder
$temp_dir = __DIR__ . '/temp_migration';
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

// 3. Extract the .vyb Zip Archive
$zip = new ZipArchive();
if ($zip->open($backup_file) === TRUE) {
    echo "📦 Extracting backup container...\n";
    $zip->extractTo($temp_dir);
    $zip->close();
} else {
    die("❌ Error: Failed to open ZIP archive: " . $backup_file . "\n");
}

// 4. Find the .vyp SQLite database file inside the extraction folder
$vyp_files = glob($temp_dir . '/*.vyp');
if (empty($vyp_files)) {
    // Clean up
    rrmdir($temp_dir);
    die("❌ Error: No database file (.vyp) found inside the backup archive!\n");
}

$vyp_db_path = $vyp_files[0];
echo "💾 Found Vyapar SQLite database: " . basename($vyp_db_path) . "\n";

// 5. Connect to Vyapar SQLite database
try {
    $vyapar_conn = new PDO("sqlite:" . $vyp_db_path);
    $vyapar_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $vyapar_conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    rrmdir($temp_dir);
    die("❌ Error: Failed to connect to Vyapar database: " . $e->getMessage() . "\n");
}

// 6. Migrate Categories
echo "🗂️ Migrating Categories...\n";
$categories = $vyapar_conn->query("SELECT * FROM kb_item_categories")->fetchAll();
$category_mapping = []; // Maps Vyapar category_id to TijaratPro category_id

foreach ($categories as $cat) {
    $cat_name = trim($cat['item_category_name']);
    
    // Check if category already exists in our system
    $existing = dbQueryFirst("SELECT id FROM categories WHERE name = ?", [$cat_name]);
    if ($existing) {
        $category_mapping[$cat['item_category_id']] = $existing['id'];
    } else {
        // Insert new category
        $exec = dbExecute("INSERT INTO categories (name) VALUES (?)", [$cat_name]);
        if ($exec['success']) {
            $category_mapping[$cat['item_category_id']] = $exec['insertId'];
        }
    }
}
echo "✅ Migrated " . count($categories) . " categories.\n";

// 7. Migrate Products
echo "📦 Migrating Products (Inventory)...\n";
$products = $vyapar_conn->query("SELECT * FROM kb_items")->fetchAll();
$migrated_products = 0;
$skipped_products = 0;

foreach ($products as $prod) {
    // Check if product is active
    if (isset($prod['item_is_active']) && $prod['item_is_active'] == 0) {
        $skipped_products++;
        continue;
    }

    $name = trim($prod['item_name']);
    $barcode = !empty($prod['item_code']) ? trim($prod['item_code']) : null;
    $purchase_price = (float)($prod['item_purchase_unit_price'] ?? 0.0);
    $sale_price = (float)($prod['item_sale_unit_price'] ?? 0.0);
    $stock_qty = (float)($prod['item_stock_quantity'] ?? 0.0);
    $min_stock = (float)($prod['item_min_stock_quantity'] ?? 5.0);
    
    // Map Category
    $cat_id = null;
    if (!empty($prod['category_id']) && isset($category_mapping[$prod['category_id']])) {
        $cat_id = $category_mapping[$prod['category_id']];
    }

    // Default Unit mapping
    $unit = 'Piece';
    // Vyapar stores unit ids, but for simplicity of TijaratPro we support basic standard unit strings.
    // If needed we could query kb_item_units table, but let's default to standard Piece.

    // Insert or update product in local POS database
    // If barcode is present, check by barcode. Otherwise check by name.
    $existing = null;
    if ($barcode) {
        $existing = dbQueryFirst("SELECT id FROM products WHERE barcode = ?", [$barcode]);
    } else {
        $existing = dbQueryFirst("SELECT id FROM products WHERE name = ?", [$name]);
    }

    if ($existing) {
        // Update stock and prices of existing product
        dbExecute(
            "UPDATE products SET purchase_price = ?, sale_price = ?, stock_qty = stock_qty + ? WHERE id = ?",
            [$purchase_price, $sale_price, $stock_qty, $existing['id']]
        );
    } else {
        // Insert new product
        dbExecute(
            "INSERT INTO products (barcode, name, category_id, purchase_price, sale_price, unit, stock_qty, min_stock_threshold) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$barcode, $name, $cat_id, $purchase_price, $sale_price, $unit, $stock_qty, $min_stock]
        );
    }
    $migrated_products++;
}
echo "✅ Migrated $migrated_products products. (Skipped $skipped_products inactive products).\n";

// 8. Migrate Customers / Parties
echo "👥 Migrating Customers (Khata/Udhaar)...\n";
// name_type = 1 represents customers in Vyapar
$customers = $vyapar_conn->query("SELECT * FROM kb_names WHERE name_type = 1")->fetchAll();
$migrated_customers = 0;

foreach ($customers as $cust) {
    $name = trim($cust['full_name']);
    $phone = !empty($cust['phone_number']) ? trim($cust['phone_number']) : '';
    $address = trim($cust['address'] ?? '');
    
    // In Vyapar, amount represents outstanding balance
    // Positive typically means customer owes us money (credit/Udhaar), which matches our schema
    $balance = (float)($cust['amount'] ?? 0.0);

    // Kiryana store requires unique phone. If phone is empty, generate a unique dummy phone based on name_id
    if (empty($phone)) {
        $phone = '0000-' . str_pad($cust['name_id'], 6, '0', STR_PAD_LEFT);
    }

    // Check if customer exists by phone
    $existing = dbQueryFirst("SELECT id FROM customers WHERE phone = ?", [$phone]);
    if ($existing) {
        // Update balance
        dbExecute("UPDATE customers SET balance = ? WHERE id = ?", [$balance, $existing['id']]);
    } else {
        // Insert new customer
        dbExecute(
            "INSERT INTO customers (name, phone, address, balance) VALUES (?, ?, ?, ?)",
            [$name, $phone, $address, $balance]
        );
    }
    $migrated_customers++;
}
echo "✅ Migrated $migrated_customers customers.\n";

// 9. Clean up temporary files
echo "🧹 Cleaning up temporary extraction files...\n";
unset($vyapar_conn); // close connection
rrmdir($temp_dir);

echo "\n✨ MIGRATION COMPLETED SUCCESSFULLY! ✨\n";
echo "---------------------------------------------\n";

// Recursive directory removal helper
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
