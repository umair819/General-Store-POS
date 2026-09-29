<?php
// ============================================================
// Tijarat PRO — Hybrid Cloud Sync Engine API
// Bridges Local Desktop (SQLite) <---> Central Cloud (MySQL)
// Used in Hybrid Packages: H1, H2, H3
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'sync_status';

// Read JSON input if sent via body
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true) ?? [];
if (!empty($data['action'])) {
    $action = $data['action'];
}

// -------------------------------------------------------------
// Package Check: Cloud Sync is only available on Hybrid tiers
// -------------------------------------------------------------
if (!isHybridPackage() || !hasFeature('cloud_sync')) {
    if ($action === 'sync_status') {
        echo json_encode([
            'status' => 'offline_package',
            'enabled' => false,
            'package' => getActivePackage(),
            'message' => 'Cloud Sync is disabled. Currently operating in Offline Package (' . getActivePackage() . '). Upgrade to H1, H2, or H3 to enable Cloud Sync.',
            'pending_records' => 0,
            'last_synced_at' => null
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'package' => getActivePackage(),
            'message' => 'Feature locked. Cloud Sync requires a Hybrid package (H1 Starter, H2 Multi-Store, or H3 ERP).'
        ]);
        exit;
    }
}

// Configuration
$cloudEnabled = getConfig('cloud_sync_enabled', '0') === '1';
$cloudServerUrl = rtrim(getConfig('cloud_server_url', 'http://127.0.0.1:8000'), '/');
$cloudApiKey = getConfig('cloud_api_key', '');
$branchSlug = getConfig('cloud_branch_slug', 'main-store');

/**
 * Helper to call remote Cloud Core API endpoint via cURL
 */
function callCloudApi($url, $method = 'GET', $payload = null, $token = '') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'TijaratPRO-SyncEngine/2.0');
    
    $headers = ['Content-Type: application/json'];
    if (!empty($token)) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($payload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
    }

    $startTime = microtime(true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    $latencyMs = round((microtime(true) - $startTime) * 1000);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
        'latency_ms' => $latencyMs,
        'error' => $curlErr,
        'data' => json_decode($response, true)
    ];
}

// -------------------------------------------------------------
// 1. TEST PING CONNECTION
// -------------------------------------------------------------
if ($action === 'test_connection') {
    $target = trim($_GET['target_url'] ?? $_POST['target_url'] ?? $data['target_url'] ?? '');
    if (empty($target)) {
        $target = $cloudServerUrl;
    }

    $res = callCloudApi($target . '/api/ping', 'GET', null, $cloudApiKey);
    $isSuccess = ($res['http_code'] >= 200 && $res['http_code'] < 400);

    echo json_encode([
        'status' => $isSuccess ? 'success' : 'failed',
        'http_code' => $res['http_code'],
        'latency_ms' => $res['latency_ms'],
        'cloud_url' => $target,
        'error' => $res['error'] ?: ($isSuccess ? null : 'HTTP ' . $res['http_code'])
    ]);
    exit;
}

// -------------------------------------------------------------
// 2. SYNC STATUS DASHBOARD
// -------------------------------------------------------------
if ($action === 'sync_status') {
    // Count pending records
    $salesCount = dbQueryFirst("SELECT COUNT(*) as cnt FROM sales WHERE sync_status = 'pending'")['cnt'] ?? 0;
    $prodCount = dbQueryFirst("SELECT COUNT(*) as cnt FROM products WHERE sync_status = 'pending'")['cnt'] ?? 0;
    $custCount = dbQueryFirst("SELECT COUNT(*) as cnt FROM customers WHERE sync_status = 'pending'")['cnt'] ?? 0;

    $totalPending = (int)$salesCount + (int)$prodCount + (int)$custCount;

    // Last sync log
    $lastLog = dbQueryFirst("SELECT * FROM sync_logs ORDER BY id DESC LIMIT 1");

    echo json_encode([
        'status' => 'success',
        'package' => getActivePackage(),
        'cloud_enabled' => $cloudEnabled,
        'branch_slug' => $branchSlug,
        'cloud_server_url' => $cloudServerUrl,
        'pending_records' => $totalPending,
        'breakdown' => [
            'sales' => (int)$salesCount,
            'products' => (int)$prodCount,
            'customers' => (int)$custCount
        ],
        'last_synced_at' => $lastLog['synced_at'] ?? 'Never',
        'last_sync_status' => $lastLog['status'] ?? 'None',
        'last_sync_action' => $lastLog['action'] ?? 'None'
    ]);
    exit;
}

// -------------------------------------------------------------
// 3. PUSH CHANGES (Local SQLite -> Cloud MySQL)
// -------------------------------------------------------------
if ($action === 'push_changes' || $action === 'auto_sync') {
    $pushedSales = 0;
    $pushedProducts = 0;
    $pushedCustomers = 0;

    // --- A. PUSH PENDING SALES ---
    $pendingSales = dbQuery("SELECT * FROM sales WHERE sync_status = 'pending' ORDER BY id ASC LIMIT 50");
    if (!empty($pendingSales)) {
        $saleIds = [];
        $payloadSales = [];

        foreach ($pendingSales as $s) {
            $sid = (int)$s['id'];
            $saleIds[] = $sid;
            // Fetch sale items
            $items = dbQuery("SELECT si.*, p.barcode as product_barcode, p.name as product_name 
                              FROM sale_items si 
                              LEFT JOIN products p ON si.product_id = p.id 
                              WHERE si.sale_id = ?", [$sid]);

            $payloadSales[] = [
                'local_id' => $sid,
                'invoice_no' => $s['invoice_no'],
                'customer_id' => $s['customer_id'],
                'subtotal' => (float)$s['subtotal'],
                'discount' => (float)$s['discount'],
                'total' => (float)$s['total'],
                'paid_amount' => (float)$s['paid_amount'],
                'balance_amount' => (float)$s['balance_amount'],
                'payment_method' => $s['payment_method'],
                'status' => $s['status'],
                'created_at' => $s['created_at'],
                'items' => $items
            ];
        }

        $pushUrl = $cloudServerUrl . "/api/sync/{$branchSlug}/push-sales";
        $cloudRes = callCloudApi($pushUrl, 'POST', [
            'branch' => $branchSlug,
            'sales' => $payloadSales
        ], $cloudApiKey);

        if ($cloudRes['http_code'] === 200 || $cloudRes['http_code'] === 201) {
            $idList = implode(',', $saleIds);
            dbExecute("UPDATE sales SET sync_status = 'synced', sync_cursor = ? WHERE id IN ($idList)", [time()]);
            $pushedSales = count($saleIds);

            dbExecute("INSERT INTO sync_logs (entity_type, action, record_count, status, synced_at) VALUES ('sales', 'push', ?, 'synced', datetime('now'))", [$pushedSales]);
        }
    }

    // --- B. PUSH PENDING PRODUCTS (Updated or created locally) ---
    $pendingProducts = dbQuery("SELECT * FROM products WHERE sync_status = 'pending' ORDER BY id ASC LIMIT 50");
    if (!empty($pendingProducts)) {
        $prodIds = [];
        $payloadProducts = [];

        foreach ($pendingProducts as $p) {
            $pid = (int)$p['id'];
            $prodIds[] = $pid;
            $attrs = getProductAttributes($pid);
            $variants = dbQuery("SELECT * FROM product_variants WHERE product_id = ?", [$pid]);

            $payloadProducts[] = [
                'local_id' => $pid,
                'barcode' => $p['barcode'],
                'name' => $p['name'],
                'item_type_id' => $p['item_type_id'],
                'brand_id' => $p['brand_id'],
                'category_id' => $p['category_id'],
                'purchase_price' => (float)$p['purchase_price'],
                'sale_price' => (float)$p['sale_price'],
                'unit' => $p['unit'],
                'stock_qty' => (float)$p['stock_qty'],
                'min_stock_threshold' => (float)$p['min_stock_threshold'],
                'expiry_date' => $p['expiry_date'],
                'sku' => $p['sku'],
                'has_variants' => $p['has_variants'],
                'attributes' => $attrs,
                'variants' => $variants
            ];
        }

        $pushUrl = $cloudServerUrl . "/api/sync/{$branchSlug}/push-products";
        $cloudRes = callCloudApi($pushUrl, 'POST', [
            'branch' => $branchSlug,
            'products' => $payloadProducts
        ], $cloudApiKey);

        if ($cloudRes['http_code'] === 200 || $cloudRes['http_code'] === 201) {
            $idList = implode(',', $prodIds);
            dbExecute("UPDATE products SET sync_status = 'synced', sync_cursor = ? WHERE id IN ($idList)", [time()]);
            $pushedProducts = count($prodIds);

            dbExecute("INSERT INTO sync_logs (entity_type, action, record_count, status, synced_at) VALUES ('products', 'push', ?, 'synced', datetime('now'))", [$pushedProducts]);
        }
    }

    if ($action === 'push_changes') {
        echo json_encode([
            'status' => 'success',
            'pushed' => [
                'sales' => $pushedSales,
                'products' => $pushedProducts
            ],
            'message' => "Successfully pushed {$pushedSales} sale(s) and {$pushedProducts} product(s) to cloud."
        ]);
        exit;
    }
}

// -------------------------------------------------------------
// 4. PULL CHANGES (Cloud MySQL -> Local SQLite)
// -------------------------------------------------------------
if ($action === 'pull_changes' || $action === 'auto_sync') {
    $pulledProducts = 0;
    $pulledOrders = 0;

    // --- PULL UPDATED PRODUCTS / PRICES FROM HQ ---
    $pullUrl = $cloudServerUrl . "/api/sync/{$branchSlug}/pull-catalog";
    $cloudRes = callCloudApi($pullUrl, 'POST', [
        'branch' => $branchSlug,
        'client_timestamp' => getConfig('last_pull_timestamp', '0')
    ], $cloudApiKey);

    if ($cloudRes['http_code'] === 200 && !empty($cloudRes['data']['products'])) {
        foreach ($cloudRes['data']['products'] as $p) {
            $barcode = trim($p['barcode'] ?? '');
            $name = trim($p['name'] ?? '');
            $salePrice = (float)($p['sale_price'] ?? 0);
            $purchasePrice = (float)($p['purchase_price'] ?? 0);
            
            if (!empty($name)) {
                // If barcode exists, update prices/catalog from HQ
                $exists = null;
                if (!empty($barcode)) {
                    $exists = dbQueryFirst("SELECT id FROM products WHERE barcode = ?", [$barcode]);
                }
                if ($exists) {
                    dbExecute("UPDATE products SET sale_price = ?, purchase_price = ?, name = ? WHERE id = ?", [
                        $salePrice, $purchasePrice, $name, $exists['id']
                    ]);
                } else {
                    dbExecute("INSERT INTO products (barcode, name, purchase_price, sale_price, unit, stock_qty) VALUES (?, ?, ?, ?, ?, ?)", [
                        $barcode, $name, $purchasePrice, $salePrice, $p['unit'] ?? 'Piece', (float)($p['stock_qty'] ?? 0)
                    ]);
                }
                $pulledProducts++;
            }
        }

        setConfig('last_pull_timestamp', strval(time()));
        dbExecute("INSERT INTO sync_logs (entity_type, action, record_count, status, synced_at) VALUES ('catalog', 'pull', ?, 'synced', datetime('now'))", [$pulledProducts]);
    }

    echo json_encode([
        'status' => 'success',
        'pulled' => [
            'products' => $pulledProducts,
            'orders' => $pulledOrders
        ],
        'pushed' => [
            'sales' => $pushedSales ?? 0,
            'products' => $pushedProducts ?? 0
        ],
        'message' => 'Cloud synchronization cycle complete.'
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action: ' . $action]);
exit;
