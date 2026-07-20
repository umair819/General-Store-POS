<?php
// Central Database Configuration for TijaratPro
// Supports SQLite locally

$db_config_file = __DIR__ . '/db_config.json';
$db_name = 'general_store.db';

if (file_exists($db_config_file)) {
    $db_config_data = json_decode(file_get_contents($db_config_file), true);
    if ($db_config_data) {
        $db_name = $db_config_data['db_name'] ?? 'general_store.db';
    }
} else {
    // Auto-create default configuration file
    $default_config = [
        'db_type' => 'sqlite',
        'db_name' => 'general_store.db'
    ];
    file_put_contents($db_config_file, json_encode($default_config, JSON_PRETTY_PRINT));
}

$db_path = __DIR__ . '/' . $db_name;
$db_exists = file_exists($db_path);

// Establish database connection
try {
    $conn = new PDO("sqlite:" . $db_path);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable Foreign Key support
    $conn->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Auto-run schema migration on first run
if (!$db_exists) {
    $schema_file = __DIR__ . '/schema.sql';
    if (file_exists($schema_file)) {
        try {
            $schema_sql = file_get_contents($schema_file);
            // Execute the schema SQL
            $conn->exec($schema_sql);
            error_log("Database schema migrated successfully.");
        } catch (PDOException $e) {
            die("Database migration failed: " . $e->getMessage());
        }
    } else {
        die("Database file not found and schema.sql is missing!");
    }
}

// Run incremental migrations for customer payments, supplier payments, and customer dates
try {
    $cols = $conn->query("PRAGMA table_info(customers)")->fetchAll();
    $has_birthdate = false;
    $has_anniversary = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'birthdate') $has_birthdate = true;
        if ($col['name'] === 'anniversary') $has_anniversary = true;
    }
    if (!$has_birthdate) {
        $conn->exec("ALTER TABLE customers ADD COLUMN birthdate DATE;");
    }
    if (!$has_anniversary) {
        $conn->exec("ALTER TABLE customers ADD COLUMN anniversary DATE;");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS customer_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        payment_method TEXT CHECK(payment_method IN ('cash', 'online', 'cheque')) NOT NULL DEFAULT 'cash',
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
    );");

    $conn->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        supplier_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        payment_method TEXT CHECK(payment_method IN ('cash', 'online', 'cheque')) NOT NULL DEFAULT 'cash',
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
    );");
} catch (PDOException $e) {
    error_log("Incremental migration failed: " . $e->getMessage());
}


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
        error_log("Database Select Query Error: " . $e->getMessage() . " SQL: " . $sql);
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
        error_log("Database Select First Query Error: " . $e->getMessage() . " SQL: " . $sql);
        return null;
    }
}

/**
 * Executes an INSERT, UPDATE, or DELETE query.
 * Returns array with lastInsertId and affectedRows.
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
        error_log("Database Execute Query Error: " . $e->getMessage() . " SQL: " . $sql);
        return [
            'insertId' => 0,
            'affectedRows' => 0,
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
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
