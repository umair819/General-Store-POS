<?php
require_once __DIR__ . '/init.php';

$order_no = trim($_GET['order_no'] ?? '');
if (empty($order_no)) {
    header("Location: index.php");
    exit;
}

$orders = dbQuery("SELECT * FROM orders WHERE order_no = ? LIMIT 1", [$order_no]);
if (empty($orders)) {
    header("Location: index.php");
    exit;
}

$order = $orders[0];
$items = dbQuery("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);

$page_title = 'Order Confirmed #' . $order['order_no'] . ' — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';
?>

<main class="store-container">
    <div class="order-success-hero">
        <div class="success-check-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 style="font-family: var(--font-heading); font-size: 28px; font-weight: 800; margin-bottom: 8px; color: var(--text-primary);">
            Order Confirmed!
        </h1>
        <p style="font-size: 14.5px; color: var(--text-muted); margin-bottom: 24px;">
            Thank you, <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($order['shipping_name']); ?></strong>! We've received your order and our dispatch team is now carefully packing your selection.
        </p>

        <!-- Order Key Pill -->
        <div style="background: var(--bg-muted); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 14px 20px; display: inline-flex; align-items: center; gap: 14px; margin-bottom: 30px;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">Order Reference:</span>
            <code style="font-family: monospace; font-size: 16px; font-weight: 800; color: var(--primary);"><?php echo htmlspecialchars($order['order_no']); ?></code>
        </div>

        <!-- Details Box -->
        <div style="text-align: left; background: var(--bg-page); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; margin-bottom: 30px;">
            <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                Order Summary
            </h4>

            <div style="margin-bottom: 16px;">
                <?php foreach ($items as $item): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 13.5px; margin-bottom: 8px;">
                        <span>
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            <?php if (!empty($item['variant_label'])): ?>
                                <small style="color: var(--text-muted);">(<?php echo htmlspecialchars($item['variant_label']); ?>)</small>
                            <?php endif; ?>
                            &times; <?php echo $item['quantity']; ?>
                        </span>
                        <strong style="color: var(--text-primary);"><?php echo formatStorePrice($item['total']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 12px; display: flex; justify-content: space-between; font-size: 15px;">
                <strong>Grand Total (<?php echo strtoupper($order['payment_method']); ?>):</strong>
                <strong style="color: var(--primary);"><?php echo formatStorePrice($order['total']); ?></strong>
            </div>

            <div style="margin-top: 16px; font-size: 12.5px; color: var(--text-muted);">
                <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Shipping to: <?php echo htmlspecialchars($order['shipping_address'] . ', ' . $order['shipping_city']); ?> (<?php echo htmlspecialchars($order['shipping_phone']); ?>)
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="track.php?order=<?php echo urlencode($order['order_no']); ?>" class="btn btn-primary" style="padding: 12px 24px;">
                <i class="fa-solid fa-truck-fast"></i> Track Order Status
            </a>
            <button type="button" class="btn btn-whatsapp" style="padding: 12px 20px;" onclick="orderOnWhatsApp('<?php echo htmlspecialchars($store_settings['phone']); ?>', 'Status Inquiry for Order #<?php echo $order['order_no']; ?>', '', '', '')">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp Support
            </button>
            <a href="index.php" class="btn btn-secondary" style="padding: 12px 20px;">
                <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
