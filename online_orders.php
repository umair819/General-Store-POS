<?php
// ============================================================
// Tijarat PRO — Online Orders & E-Commerce Fulfillment Module
// ============================================================

session_start();
require_once __DIR__ . '/db_config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';
$success_msg = '';
$error_msg = '';

// ----------------------------------------------------
// HANDLE POST ACTIONS (Status update, Convert to POS Sale, etc.)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_status') {
        $order_id       = (int)($_POST['order_id'] ?? 0);
        $new_status     = trim($_POST['order_status'] ?? 'pending');
        $tracking_no    = trim($_POST['tracking_no'] ?? '');
        $payment_status = trim($_POST['payment_status'] ?? 'pending');
        $notify_wa      = isset($_POST['notify_whatsapp']) ? 1 : 0;

        if ($order_id > 0) {
            $order = dbFind('orders', $order_id);
            if ($order) {
                dbExecute("
                    UPDATE orders 
                    SET order_status = ?, tracking_no = ?, payment_status = ? 
                    WHERE id = ?
                ", [$new_status, $tracking_no, $payment_status, $order_id]);

                $success_msg = "Order #{$order['order_no']} updated to status: " . strtoupper($new_status);

                // Send WhatsApp notification if requested
                if ($notify_wa && class_exists('WhatsAppEngine') && !empty($order['shipping_phone'])) {
                    try {
                        $wa = new WhatsAppEngine();
                        $statusEmoji = [
                            'confirmed'  => '✅',
                            'processing' => '📦',
                            'shipped'    => '🚚',
                            'delivered'  => '🎉',
                            'cancelled'  => '❌'
                        ][$new_status] ?? 'ℹ️';

                        $msg = "{$statusEmoji} *Order Update from {$global_config['shop_name']}*\n\n" .
                               "Dear *{$order['shipping_name']}*,\n" .
                               "Your order *#{$order['order_no']}* status has been updated to: *" . strtoupper($new_status) . "*\n";

                        if (!empty($tracking_no)) {
                            $msg .= "🔖 *Courier Tracking Number:* `{$tracking_no}`\n";
                        }
                        $msg .= "💰 *Amount:* PKR " . number_format($order['total'], 0) . " (" . strtoupper($order['payment_method']) . ")\n\n" .
                                "Track live anytime: https://{$_SERVER['HTTP_HOST']}/store/track.php?order={$order['order_no']}\n" .
                                "Thank you for shopping with us!";

                        $wa->sendMessage($order['shipping_phone'], $msg);
                    } catch (Exception $e) {
                        error_log("WhatsApp status notification error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    // ----------------------------------------------------
    // CONVERT TO POS SALE
    // ----------------------------------------------------
    elseif ($action === 'convert_to_sale') {
        $order_id = (int)($_POST['order_id'] ?? 0);
        if ($order_id > 0) {
            $order = dbFind('orders', $order_id);
            if ($order) {
                if (!empty($order['sale_id'])) {
                    $error_msg = "This online order is already converted to POS Sale #{$order['sale_id']}.";
                } else {
                    try {
                        $conn->beginTransaction();

                        // 1. Generate Invoice No
                        $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                        // 2. Insert into sales
                        $stmt = $conn->prepare("
                            INSERT INTO sales (invoice_no, customer_id, user_id, subtotal, discount, total, paid_amount, balance_amount, payment_method, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0.0, ?, 'completed')
                        ");
                        $stmt->execute([
                            $invoice_no,
                            $order['customer_id'],
                            $user_id,
                            $order['subtotal'],
                            $order['discount'] ?? 0.0,
                            $order['total'],
                            ($order['payment_status'] === 'paid' ? $order['total'] : 0.0),
                            ($order['payment_method'] === 'cod' ? 'cash' : 'online')
                        ]);
                        $sale_id = $conn->lastInsertId();

                        // 3. Insert items and deduct stock
                        $items = dbQuery("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);
                        foreach ($items as $it) {
                            $stmtItem = $conn->prepare("
                                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price)
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmtItem->execute([
                                $sale_id,
                                $it['product_id'],
                                $it['quantity'],
                                $it['price'],
                                $it['total']
                            ]);

                            // Deduct inventory
                            if (!empty($it['variant_id'])) {
                                dbExecute("UPDATE product_variants SET stock_qty = MAX(0, stock_qty - ?) WHERE id = ?", [$it['quantity'], $it['variant_id']]);
                            }
                            dbExecute("UPDATE products SET stock_qty = MAX(0, stock_qty - ?) WHERE id = ?", [$it['quantity'], $it['product_id']]);
                        }

                        // 4. Update order with sale_id
                        dbExecute("UPDATE orders SET sale_id = ?, order_status = 'delivered', payment_status = 'paid' WHERE id = ?", [$sale_id, $order_id]);

                        $conn->commit();
                        $success_msg = "Online Order #{$order['order_no']} successfully converted to POS Invoice #{$invoice_no} and inventory deducted!";
                    } catch (Exception $e) {
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }
                        $error_msg = "Conversion failed: " . $e->getMessage();
                    }
                }
            }
        }
    }
}

// ----------------------------------------------------
// STATS & QUERY
// ----------------------------------------------------
$total_orders = (int)(dbQuery("SELECT COUNT(*) as cnt FROM orders")[0]['cnt'] ?? 0);
$pending_orders = (int)(dbQuery("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'pending'")[0]['cnt'] ?? 0);
$shipped_orders = (int)(dbQuery("SELECT COUNT(*) as cnt FROM orders WHERE order_status IN ('shipped', 'processing')")[0]['cnt'] ?? 0);
$delivered_orders = (int)(dbQuery("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'delivered'")[0]['cnt'] ?? 0);
$online_revenue = (float)(dbQuery("SELECT SUM(total) as rev FROM orders WHERE order_status != 'cancelled'")[0]['rev'] ?? 0.0);

// Filters
$filter_status = trim($_GET['status'] ?? 'all');
$search_q = trim($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if ($filter_status !== 'all' && $filter_status !== '') {
    $where[] = "o.order_status = ?";
    $params[] = $filter_status;
}

if ($search_q !== '') {
    $where[] = "(o.order_no LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ? OR o.shipping_city LIKE ?)";
    $like = '%' . $search_q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = implode(' AND ', $where);
$orders = dbQuery("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
           s.invoice_no as pos_invoice_no
    FROM orders o
    LEFT JOIN sales s ON o.sale_id = s.id
    WHERE {$whereSql}
    ORDER BY o.id DESC
", $params);
$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Orders & E-Commerce Fulfillment — Tijarat PRO</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .content-panel { flex-grow: 1; padding: 30px 40px; display: flex; flex-direction: column; gap: 20px; height: 100vh; overflow-y: auto; background-color: var(--bg-app); }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px 26px; box-shadow: var(--shadow-sm); }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 8px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-sm);
        }
        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .order-badge {
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-pending { background: rgba(245, 158, 11, 0.15); color: #d97706; }
        .badge-confirmed { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
        .badge-processing { background: rgba(139, 92, 246, 0.15); color: #7c3aed; }
        .badge-shipped { background: rgba(14, 165, 233, 0.15); color: #0284c7; }
        .badge-delivered { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .badge-cancelled { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content-panel">
            <!-- Header Nav -->
            <div class="header-nav">
                <div>
                    <h1 style="font-size: 20px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-globe" style="color: var(--accent);"></i>
                        <span>Online Orders & Web Storefront Fulfillment</span>
                    </h1>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Manage incoming e-commerce orders, customer WhatsApp dispatch, and 1-click POS sales conversion</p>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="store/index.php" target="_blank" style="padding: 10px 18px; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-main); font-weight: 600;">
                        <i class="fa-solid fa-arrow-up-right-from-square" style="color: var(--accent);"></i> <span>Open Storefront</span>
                    </a>
                </div>
            </div>

            <div>
                <?php if (!empty($success_msg)): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #065f46; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($success_msg); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #991b1b; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($error_msg); ?></span>
                    </div>
                <?php endif; ?>

                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.15); color: #6366f1;">
                            <i class="fa-solid fa-cart-flatbed"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Orders</div>
                            <div style="font-size: 22px; font-weight: 800; color: var(--text-primary);"><?php echo $total_orders; ?></div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pending Action</div>
                            <div style="font-size: 22px; font-weight: 800; color: #d97706;"><?php echo $pending_orders; ?></div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.15); color: #0ea5e9;">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">In Dispatch</div>
                            <div style="font-size: 22px; font-weight: 800; color: #0284c7;"><?php echo $shipped_orders; ?></div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="fa-solid fa-circle-dollar-to-slot"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Online Revenue</div>
                            <div style="font-size: 22px; font-weight: 800; color: #059669;">PKR <?php echo number_format($online_revenue, 0); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Toolbar -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                    <!-- Filter Status Pills -->
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <?php 
                        $statusTabs = [
                            'all' => 'All Orders',
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered'
                        ];
                        foreach ($statusTabs as $k => $label): ?>
                            <a href="online_orders.php?status=<?php echo $k; ?>&q=<?php echo urlencode($search_q); ?>" 
                               style="padding: 6px 14px; border-radius: var(--radius-full); font-size: 12.5px; font-weight: 600; text-decoration: none; border: 1px solid var(--border-color); <?php echo ($filter_status === $k) ? 'background: var(--accent); color: #fff; border-color: var(--accent);' : 'background: var(--bg-input); color: var(--text-secondary);'; ?>">
                                <?php echo $label; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Search Box -->
                    <form action="online_orders.php" method="GET" style="display: flex; gap: 8px;">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                        <input type="text" name="q" placeholder="Search Order #, Name, Phone..." value="<?php echo htmlspecialchars($search_q); ?>" style="padding: 7px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 13px; outline: none; background: var(--bg-input); color: var(--text-primary);">
                        <button type="submit" style="padding: 7px 14px; border-radius: var(--radius-sm); background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>

                <!-- Orders Table -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                        <thead>
                            <tr style="background: var(--bg-input); border-bottom: 1px solid var(--border-color); color: var(--text-secondary); text-transform: uppercase; font-size: 11px;">
                                <th style="padding: 12px 16px;">Order No</th>
                                <th style="padding: 12px 16px;">Date</th>
                                <th style="padding: 12px 16px;">Recipient</th>
                                <th style="padding: 12px 16px;">City / Address</th>
                                <th style="padding: 12px 16px;">Total Amount</th>
                                <th style="padding: 12px 16px;">Payment</th>
                                <th style="padding: 12px 16px;">Status</th>
                                <th style="padding: 12px 16px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" style="padding: 48px; text-align: center; color: var(--text-muted);">
                                        <i class="fa-solid fa-inbox" style="font-size: 36px; margin-bottom: 10px; display: block;"></i>
                                        No online orders matching current filter criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $ord): 
                                    $st = strtolower($ord['order_status']);
                                    $cleanPhone = preg_replace('/\D/', '', $ord['shipping_phone']);
                                    if (str_starts_with($cleanPhone, '03')) {
                                        $cleanPhone = '92' . substr($cleanPhone, 1);
                                    }
                                ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 14px 16px; font-weight: 700; font-family: monospace;">
                                        <?php echo htmlspecialchars($ord['order_no']); ?>
                                        <?php if (!empty($ord['pos_invoice_no'])): ?>
                                            <div style="font-size: 10px; color: #10b981; font-weight: 700;">POS: <?php echo htmlspecialchars($ord['pos_invoice_no']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 14px 16px; color: var(--text-muted);">
                                        <?php echo date('d M, h:i A', strtotime($ord['created_at'])); ?>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <div style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($ord['shipping_name']); ?></div>
                                        <div style="font-size: 11.5px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                            <span><?php echo htmlspecialchars($ord['shipping_phone']); ?></span>
                                            <a href="https://wa.me/<?php echo $cleanPhone; ?>?text=Hello%20<?php echo urlencode($ord['shipping_name']); ?>,%20regarding%20your%20order%20<?php echo $ord['order_no']; ?>%20with%20<?php echo urlencode($global_config['shop_name']); ?>:" target="_blank" style="color: #25D366; text-decoration: none;" title="Chat on WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <span style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($ord['shipping_city']); ?></span>
                                        <div style="font-size: 11px; color: var(--text-muted); max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($ord['shipping_address']); ?>
                                        </div>
                                    </td>
                                    <td style="padding: 14px 16px; font-weight: 800; color: var(--accent);">
                                        PKR <?php echo number_format($ord['total'], 0); ?>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                            <?php echo htmlspecialchars($ord['payment_method']); ?>
                                        </span>
                                        <div style="font-size: 10px; color: <?php echo ($ord['payment_status'] === 'paid') ? '#10b981' : '#f59e0b'; ?>; font-weight: 700;">
                                            ● <?php echo strtoupper($ord['payment_status']); ?>
                                        </div>
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <span class="order-badge badge-<?php echo $st; ?>">
                                            <?php echo htmlspecialchars($st); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 14px 16px; text-align: right;">
                                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($ord)); ?>)" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>

                                            <?php if (empty($ord['sale_id'])): ?>
                                                <form action="online_orders.php" method="POST" style="display: inline;" onsubmit="return confirm('Convert order #<?php echo $ord['order_no']; ?> into official POS Sale invoice and deduct inventory?');">
                                                    <input type="hidden" name="action" value="convert_to_sale">
                                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 5px 10px; font-size: 12px; background: #10b981; border-color: #10b981;" title="Convert to POS Sale">
                                                        <i class="fa-solid fa-receipt"></i> POS Sale
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800;" id="modalOrderNo">Order Details</h3>
                <button type="button" onclick="closeOrderModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <form action="online_orders.php" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modalOrderId" value="">

                <div style="margin-bottom: 16px; font-size: 13.5px;" id="modalRecipientInfo"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">Order Status</label>
                        <select name="order_status" id="modalStatusSelect" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary); font-size: 13px;">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">Payment Status</label>
                        <select name="payment_status" id="modalPaymentStatusSelect" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary); font-size: 13px;">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">Courier Tracking Number</label>
                    <input type="text" name="tracking_no" id="modalTrackingInput" placeholder="e.g. TRAX-998811 or Leopard #12345" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary); font-size: 13px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: var(--text-primary);">
                        <input type="checkbox" name="notify_whatsapp" value="1" checked>
                        <span><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Send status update to customer on WhatsApp</span>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeOrderModal()" class="btn btn-secondary">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openOrderModal(order) {
        document.getElementById('modalOrderNo').textContent = 'Order #' + order.order_no;
        document.getElementById('modalOrderId').value = order.id;
        document.getElementById('modalStatusSelect').value = order.order_status;
        document.getElementById('modalPaymentStatusSelect').value = order.payment_status;
        document.getElementById('modalTrackingInput').value = order.tracking_no || '';

        document.getElementById('modalRecipientInfo').innerHTML = `
            <div style="background: var(--bg-input); padding: 12px; border-radius: var(--radius-sm); margin-bottom: 12px;">
                <div><strong>Recipient:</strong> ${order.shipping_name} (${order.shipping_phone})</div>
                <div><strong>Address:</strong> ${order.shipping_address}, ${order.shipping_city}</div>
                <div><strong>Payment:</strong> ${order.payment_method.toUpperCase()} — PKR ${parseFloat(order.total).toLocaleString()}</div>
                ${order.customer_notes ? `<div style="margin-top: 4px; color: var(--text-muted);"><strong>Note:</strong> ${order.customer_notes}</div>` : ''}
            </div>
        `;

        document.getElementById('orderDetailModal').style.display = 'flex';
    }

    function closeOrderModal() {
        document.getElementById('orderDetailModal').style.display = 'none';
    }
    </script>
</body>
</html>
