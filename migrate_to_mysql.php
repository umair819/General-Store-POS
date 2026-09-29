<?php
// ============================================================
// Tijarat PRO — SQLite to MySQL Migration Script
// Migrates all existing data from general_store.db to MySQL
// ============================================================
// Usage: php migrate_to_mysql.php
//
// Prerequisites:
// 1. MySQL database 'tijarat_pro' must exist
// 2. schema_mysql.sql must be imported first:
//    mysql -u root -p tijarat_pro < schema_mysql.sql
// 3. db_config.json must have MySQL credentials configured
// ============================================================

echo "============================================\n";
echo "  Tijarat PRO — SQLite to MySQL Migrator\n";
echo "============================================\n\n";

// Load config
$config_file = __DIR__ . '/db_config.json';
if (!file_exists($config_file)) {
    die("ERROR: db_config.json not found!\n");
}

$config = json_decode(file_get_contents($config_file), true);
if (!$config) {
    die("ERROR: Invalid db_config.json!\n");
}

// Connect to SQLite source
$sqlite_db = $config['db_name'] ?? 'general_store.db';
$sqlite_path = __DIR__ . '/' . $sqlite_db;

if (!file_exists($sqlite_path)) {
    die("ERROR: SQLite database '{$sqlite_db}' not found!\n");
}

echo "📂 Source: SQLite ({$sqlite_db})\n";

try {
    $sqlite = new PDO("sqlite:" . $sqlite_path);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ERROR: Cannot connect to SQLite: " . $e->getMessage() . "\n");
}

// Connect to MySQL destination
$mysql_host = $config['mysql_host'] ?? 'localhost';
$mysql_port = $config['mysql_port'] ?? 3306;
$mysql_db   = $config['mysql_database'] ?? 'tijarat_pro';
$mysql_user = $config['mysql_username'] ?? 'root';
$mysql_pass = $config['mysql_password'] ?? '';

echo "🎯 Destination: MySQL ({$mysql_host}:{$mysql_port}/{$mysql_db})\n\n";

try {
    $mysql = new PDO("mysql:host={$mysql_host};port={$mysql_port};dbname={$mysql_db};charset=utf8mb4", $mysql_user, $mysql_pass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $mysql->exec("SET FOREIGN_KEY_CHECKS = 0");
    $mysql->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("ERROR: Cannot connect to MySQL: " . $e->getMessage() . "\n");
}

$stats = [];

// ============================================================
// Migration Functions
// ============================================================

function migrateTable($sqlite, $mysql, $table, $columns, $idColumn = 'id') {
    global $stats;
    
    try {
        $rows = $sqlite->query("SELECT * FROM {$table}")->fetchAll();
    } catch (PDOException $e) {
        echo "  ⚠️  Table '{$table}' not found in SQLite, skipping...\n";
        $stats[$table] = 0;
        return;
    }
    
    if (empty($rows)) {
        echo "  ⏭️  {$table}: 0 rows (empty)\n";
        $stats[$table] = 0;
        return;
    }
    
    $migrated = 0;
    
    foreach ($rows as $row) {
        $cols = [];
        $placeholders = [];
        $values = [];
        
        foreach ($columns as $col) {
            if (isset($row[$col])) {
                $cols[] = $col;
                $placeholders[] = '?';
                $values[] = $row[$col];
            }
        }
        
        if (empty($cols)) continue;
        
        $colStr = implode(', ', $cols);
        $phStr = implode(', ', $placeholders);
        
        // Use REPLACE INTO to handle duplicate keys gracefully
        $sql = "REPLACE INTO {$table} ({$colStr}) VALUES ({$phStr})";
        
        try {
            $stmt = $mysql->prepare($sql);
            $stmt->execute($values);
            $migrated++;
        } catch (PDOException $e) {
            echo "  ❌ Error migrating row in {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "  ✅ {$table}: {$migrated}/" . count($rows) . " rows migrated\n";
    $stats[$table] = $migrated;
}

// ============================================================
// Run Migrations
// ============================================================

echo "🔄 Starting migration...\n\n";

// 1. Users
echo "👤 Migrating Users...\n";
migrateTable($sqlite, $mysql, 'users', [
    'id', 'username', 'password', 'role', 'pin', 'name', 'phone', 'created_at'
]);

// 2. Categories
echo "\n📁 Migrating Categories...\n";
migrateTable($sqlite, $mysql, 'categories', [
    'id', 'name', 'description', 'created_at'
]);

// 3. Products (map to new schema with additional default values)
echo "\n📦 Migrating Products...\n";
try {
    $products = $sqlite->query("SELECT * FROM products")->fetchAll();
    $migrated = 0;
    
    foreach ($products as $prod) {
        // Generate slug from name
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $prod['name']), '-'));
        
        $sql = "REPLACE INTO products (id, barcode, name, slug, category_id, purchase_price, sale_price, unit, stock_qty, min_stock_threshold, expiry_date, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        
        try {
            $stmt = $mysql->prepare($sql);
            $stmt->execute([
                $prod['id'],
                $prod['barcode'] ?? null,
                $prod['name'],
                $slug,
                $prod['category_id'] ?? null,
                $prod['purchase_price'] ?? 0,
                $prod['sale_price'] ?? 0,
                $prod['unit'] ?? 'Piece',
                $prod['stock_qty'] ?? 0,
                $prod['min_stock_threshold'] ?? 5,
                $prod['expiry_date'] ?? null,
                $prod['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $migrated++;
        } catch (PDOException $e) {
            echo "  ❌ Product '{$prod['name']}': " . $e->getMessage() . "\n";
        }
    }
    echo "  ✅ products: {$migrated}/" . count($products) . " rows migrated\n";
    $stats['products'] = $migrated;
} catch (PDOException $e) {
    echo "  ⚠️  Products table not found, skipping...\n";
    $stats['products'] = 0;
}

// 4. Customers (map birthdate/anniversary)
echo "\n👥 Migrating Customers...\n";
try {
    $customers = $sqlite->query("SELECT * FROM customers")->fetchAll();
    $migrated = 0;
    
    foreach ($customers as $cust) {
        $sql = "REPLACE INTO customers (id, name, phone, address, balance, date_of_birth, anniversary, source, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'walk_in', ?)";
        
        try {
            $stmt = $mysql->prepare($sql);
            $stmt->execute([
                $cust['id'],
                $cust['name'],
                $cust['phone'],
                $cust['address'] ?? '',
                $cust['balance'] ?? 0,
                $cust['birthdate'] ?? null,
                $cust['anniversary'] ?? null,
                $cust['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $migrated++;
        } catch (PDOException $e) {
            echo "  ❌ Customer '{$cust['name']}': " . $e->getMessage() . "\n";
        }
    }
    echo "  ✅ customers: {$migrated}/" . count($customers) . " rows migrated\n";
    $stats['customers'] = $migrated;
} catch (PDOException $e) {
    echo "  ⚠️  Customers table not found, skipping...\n";
    $stats['customers'] = 0;
}

// 5. Suppliers
echo "\n🏭 Migrating Suppliers...\n";
migrateTable($sqlite, $mysql, 'suppliers', [
    'id', 'name', 'contact_person', 'phone', 'address', 'email', 'balance', 'created_at'
]);

// 6. Sales
echo "\n💰 Migrating Sales...\n";
try {
    $sales = $sqlite->query("SELECT * FROM sales")->fetchAll();
    $migrated = 0;
    
    foreach ($sales as $sale) {
        $sql = "REPLACE INTO sales (id, invoice_no, customer_id, user_id, subtotal, discount, total, paid_amount, balance_amount, payment_method, status, sale_channel, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pos', ?)";
        
        try {
            $stmt = $mysql->prepare($sql);
            $stmt->execute([
                $sale['id'],
                $sale['invoice_no'],
                $sale['customer_id'] ?? null,
                $sale['user_id'] ?? null,
                $sale['subtotal'] ?? 0,
                $sale['discount'] ?? 0,
                $sale['total'] ?? 0,
                $sale['paid_amount'] ?? 0,
                $sale['balance_amount'] ?? 0,
                $sale['payment_method'] ?? 'cash',
                $sale['status'] ?? 'completed',
                $sale['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $migrated++;
        } catch (PDOException $e) {
            echo "  ❌ Sale '{$sale['invoice_no']}': " . $e->getMessage() . "\n";
        }
    }
    echo "  ✅ sales: {$migrated}/" . count($sales) . " rows migrated\n";
    $stats['sales'] = $migrated;
} catch (PDOException $e) {
    echo "  ⚠️  Sales table not found, skipping...\n";
    $stats['sales'] = 0;
}

// 7. Sale Items
echo "\n🧾 Migrating Sale Items...\n";
migrateTable($sqlite, $mysql, 'sale_items', [
    'id', 'sale_id', 'product_id', 'quantity', 'purchase_price', 'sale_price', 'discount', 'total', 'created_at'
]);

// 8. Purchases
echo "\n📥 Migrating Purchases...\n";
try {
    $purchases = $sqlite->query("SELECT * FROM purchases")->fetchAll();
    $migrated = 0;
    
    foreach ($purchases as $pur) {
        $sql = "REPLACE INTO purchases (id, purchase_no, supplier_id, user_id, subtotal, discount, total, paid_amount, balance_amount, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $mysql->prepare($sql);
            $stmt->execute([
                $pur['id'],
                $pur['purchase_no'],
                $pur['supplier_id'] ?? null,
                $pur['user_id'] ?? null,
                $pur['subtotal'] ?? 0,
                $pur['discount'] ?? 0,
                $pur['total'] ?? 0,
                $pur['paid_amount'] ?? 0,
                $pur['balance_amount'] ?? 0,
                $pur['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            $migrated++;
        } catch (PDOException $e) {
            echo "  ❌ Purchase '{$pur['purchase_no']}': " . $e->getMessage() . "\n";
        }
    }
    echo "  ✅ purchases: {$migrated}/" . count($purchases) . " rows migrated\n";
    $stats['purchases'] = $migrated;
} catch (PDOException $e) {
    echo "  ⚠️  Purchases table not found, skipping...\n";
    $stats['purchases'] = 0;
}

// 9. Purchase Items
echo "\n📋 Migrating Purchase Items...\n";
migrateTable($sqlite, $mysql, 'purchase_items', [
    'id', 'purchase_id', 'product_id', 'quantity', 'purchase_price', 'total', 'expiry_date', 'created_at'
]);

// 10. Customer Payments
echo "\n💳 Migrating Customer Payments...\n";
migrateTable($sqlite, $mysql, 'customer_payments', [
    'id', 'customer_id', 'amount', 'payment_method', 'note', 'created_at'
]);

// 11. Supplier Payments
echo "\n💵 Migrating Supplier Payments...\n";
migrateTable($sqlite, $mysql, 'supplier_payments', [
    'id', 'supplier_id', 'amount', 'payment_method', 'note', 'created_at'
]);

// ============================================================
// Post-Migration: Sync product stock to main branch
// ============================================================
echo "\n🏬 Syncing product stock to Main Branch (HQ)...\n";
try {
    $products = $mysql->query("SELECT id, stock_qty, min_stock_threshold FROM products")->fetchAll();
    $synced = 0;
    
    foreach ($products as $prod) {
        $stmt = $mysql->prepare("INSERT INTO branch_stock (branch_id, product_id, stock_qty, min_threshold) 
                                  VALUES (1, ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE stock_qty = VALUES(stock_qty)");
        $stmt->execute([$prod['id'], $prod['stock_qty'], $prod['min_stock_threshold']]);
        $synced++;
    }
    echo "  ✅ branch_stock: {$synced} products synced to branch #1 (HQ)\n";
} catch (PDOException $e) {
    echo "  ⚠️  Branch stock sync failed: " . $e->getMessage() . "\n";
}

// ============================================================
// Post-Migration: Update settings from config
// ============================================================
echo "\n⚙️  Migrating Settings...\n";
$settings_map = [
    'shop_name' => 'general',
    'shop_phone' => 'general',
    'shop_address' => 'general',
    'shop_currency' => 'general',
    'tax_enabled' => 'general',
    'tax_number' => 'general',
    'gemini_api_key' => 'ai',
    'whatsapp_mode' => 'whatsapp',
    'receipt_template' => 'general',
    'decimal_places' => 'general',
];

foreach ($settings_map as $key => $group) {
    if (isset($config[$key]) && $config[$key] !== '') {
        $stmt = $mysql->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $config[$key], $group]);
    }
}
echo "  ✅ Settings migrated from db_config.json\n";

// Re-enable FK checks
$mysql->exec("SET FOREIGN_KEY_CHECKS = 1");

// ============================================================
// Migration Summary
// ============================================================
echo "\n============================================\n";
echo "  ✅ Migration Complete!\n";
echo "============================================\n";
echo "\n📊 Summary:\n";

$total = 0;
foreach ($stats as $table => $count) {
    echo "  • {$table}: {$count} rows\n";
    $total += $count;
}
echo "\n  📦 Total records migrated: {$total}\n";

echo "\n⚠️  Next Steps:\n";
echo "  1. Update db_config.json: set 'db_type' to 'mysql'\n";
echo "  2. Verify MySQL credentials in db_config.json\n";
echo "  3. Test the application with MySQL backend\n";
echo "  4. Keep SQLite as backup (don't delete general_store.db)\n";
echo "\n";
?>
