<?php
require_once __DIR__ . '/init.php';

$search_order = trim($_GET['order'] ?? $_GET['q'] ?? '');
$orders_found = [];

if (!empty($search_order)) {
    $orders_found = dbQuery("
        SELECT * FROM orders 
        WHERE order_no = ? OR shipping_phone = ? 
        ORDER BY id DESC 
        LIMIT 5
    ", [$search_order, $search_order]);
}

$page_title = 'Track Order — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';
?>

<main class="store-container">
    <div class="product-breadcrumb" style="margin-bottom: 24px;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 700;">Track Order</span>
    </div>

    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 36px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px;">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <h1 class="section-title">Track Your Consignment</h1>
            <p class="section-subtitle">Enter your Order Number or WhatsApp Phone Number to check real-time status</p>
        </div>

        <!-- Search Form -->
        <form action="track.php" method="GET" style="display: flex; gap: 12px; margin-bottom: 40px;">
            <input type="text" name="order" required class="form-input" placeholder="e.g. ORD-20260929-ABCD or 03001234567" value="<?php echo htmlspecialchars($search_order); ?>" style="flex: 1; padding: 14px 20px; font-size: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px;">
                <i class="fa-solid fa-magnifying-glass"></i> Track Status
            </button>
        </form>

        <?php if (!empty($search_order) && empty($orders_found)): ?>
            <div style="text-align: center; padding: 48px 20px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <i class="fa-solid fa-magnifying-glass-chart" style="font-size: 42px; color: var(--text-muted); margin-bottom: 14px;"></i>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">No Order Found</h3>
                <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 20px;">
                    We could not locate an order matching "<strong><?php echo htmlspecialchars($search_order); ?></strong>". Please verify your order reference number.
                </p>
                <button type="button" class="btn btn-whatsapp" onclick="orderOnWhatsApp('<?php echo htmlspecialchars($store_settings['phone']); ?>', 'Tracking help for: <?php echo addslashes($search_order); ?>')">
                    <i class="fa-brands fa-whatsapp"></i> Contact Support on WhatsApp
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($orders_found)): ?>
            <?php foreach ($orders_found as $ord): 
                $items = dbQuery("SELECT * FROM order_items WHERE order_id = ?", [$ord['id']]);
                $status = strtolower($ord['order_status']);

                $step1 = true; // Placed
                $step2 = in_array($status, ['confirmed', 'processing', 'shipped', 'delivered']);
                $step3 = in_array($status, ['processing', 'shipped', 'delivered']);
                $step4 = in_array($status, ['shipped', 'delivered']);
                $step5 = ($status === 'delivered');
            ?>
            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Consignment Reference</div>
                        <div style="font-family: monospace; font-size: 18px; font-weight: 800; color: var(--primary);"><?php echo htmlspecialchars($ord['order_no']); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Placed On</div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);"><?php echo date('M d, Y - h:i A', strtotime($ord['created_at'])); ?></div>
                    </div>
                </div>

                <!-- Tracking Visual Timeline -->
                <div class="tracking-timeline">
                    <!-- Step 1: Placed -->
                    <div class="timeline-step <?php echo $step1 ? ($step2 ? 'completed' : 'active') : ''; ?>">
                        <div class="timeline-icon"><i class="fa-solid fa-file-invoice"></i></div>
                        <div class="timeline-label">Placed</div>
                    </div>

                    <!-- Step 2: Confirmed -->
                    <div class="timeline-step <?php echo $step2 ? ($step3 ? 'completed' : 'active') : ''; ?>">
                        <div class="timeline-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="timeline-label">Confirmed</div>
                    </div>

                    <!-- Step 3: Packing -->
                    <div class="timeline-step <?php echo $step3 ? ($step4 ? 'completed' : 'active') : ''; ?>">
                        <div class="timeline-icon"><i class="fa-solid fa-box-open"></i></div>
                        <div class="timeline-label">Processing</div>
                    </div>

                    <!-- Step 4: Shipped -->
                    <div class="timeline-step <?php echo $step4 ? ($step5 ? 'completed' : 'active') : ''; ?>">
                        <div class="timeline-icon"><i class="fa-solid fa-truck"></i></div>
                        <div class="timeline-label">Shipped</div>
                    </div>

                    <!-- Step 5: Delivered -->
                    <div class="timeline-step <?php echo $step5 ? 'completed' : ''; ?>">
                        <div class="timeline-icon"><i class="fa-solid fa-house-chimney-check"></i></div>
                        <div class="timeline-label">Delivered</div>
                    </div>
                </div>

                <?php if (!empty($ord['tracking_no'])): ?>
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6; border-radius: var(--radius-sm); padding: 12px 18px; margin-bottom: 20px; font-size: 13.5px; color: #1e40af; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa-solid fa-barcode"></i> Courier Tracking No: <strong><?php echo htmlspecialchars($ord['tracking_no']); ?></strong></span>
                    </div>
                <?php endif; ?>

                <!-- Order Items Breakdown -->
                <div style="margin-top: 20px;">
                    <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--text-secondary);">Items in this package:</h4>
                    <?php foreach ($items as $it): ?>
                        <div style="display: flex; justify-content: space-between; font-size: 13.5px; padding: 6px 0; border-bottom: 1px dashed var(--border-color);">
                            <span><?php echo htmlspecialchars($it['product_name']); ?> <?php if (!empty($it['variant_label'])): ?>(<?php echo htmlspecialchars($it['variant_label']); ?>)<?php endif; ?> &times; <?php echo $it['quantity']; ?></span>
                            <strong style="color: var(--text-primary);"><?php echo formatStorePrice($it['total']); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 18px; display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 700;">
                    <span>Total Amount (<?php echo strtoupper($ord['payment_method']); ?>):</span>
                    <span style="color: var(--primary); font-size: 16px;"><?php echo formatStorePrice($ord['total']); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
