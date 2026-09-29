<?php
require_once __DIR__ . '/init.php';

$cart = $_SESSION['store_cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$subtotal = getStoreCartSubtotal();
$shipping = ($subtotal >= $store_settings['free_shipping_min']) ? 0.0 : $store_settings['shipping_flat'];
$grand_total = $subtotal + $shipping;

$error_msg = '';

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_name    = trim($_POST['shipping_name'] ?? '');
    $shipping_phone   = trim($_POST['shipping_phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $shipping_city    = trim($_POST['shipping_city'] ?? 'Karachi');
    $customer_notes   = trim($_POST['customer_notes'] ?? '');
    $payment_method   = trim($_POST['payment_method'] ?? 'cod');

    if (empty($shipping_name) || empty($shipping_phone) || empty($shipping_address)) {
        $error_msg = 'Please fill in all required delivery details (Name, WhatsApp Number, and Address).';
    } else {
        try {
            // Generate unique Order No
            $order_no = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Customer lookup or create in local customers table
            $customer = dbQuery("SELECT id FROM customers WHERE phone = ? LIMIT 1", [$shipping_phone]);
            $customerId = null;
            // Customer lookup or create in local customers table
            $customer = dbQuery("SELECT id FROM customers WHERE phone = ? LIMIT 1", [$shipping_phone]);
            $customerId = null;
            if (!empty($customer)) {
                $customerId = $customer[0]['id'];
            } else {
                $resCust = dbExecute("INSERT INTO customers (name, phone, address, city) VALUES (?, ?, ?, ?)", [
                    $shipping_name,
                    $shipping_phone,
                    $shipping_address,
                    $shipping_city
                ]);
                $customerId = $resCust['insertId'] ?? null;
            }

            // Insert into orders table
            $resOrder = dbExecute("
                INSERT INTO orders (
                    order_no, customer_id, shipping_name, shipping_phone, shipping_address, shipping_city,
                    subtotal, shipping_fee, discount, total, payment_method, payment_status,
                    order_status, order_channel, customer_notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'website', ?)
            ", [
                $order_no,
                $customerId,
                $shipping_name,
                $shipping_phone,
                $shipping_address,
                $shipping_city,
                $subtotal,
                $shipping,
                0.0,
                $grand_total,
                $payment_method,
                ($payment_method === 'cod' ? 'pending' : 'pending'),
                $customer_notes
            ]);
            $orderId = $resOrder['insertId'] ?? 0;

            // Insert order items
            foreach ($cart as $item) {
                $lineTotal = (float)$item['price'] * (int)$item['qty'];
                dbExecute("
                    INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_label, quantity, price, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['name'],
                    $item['variant_label'],
                    $item['qty'],
                    $item['price'],
                    $lineTotal
                ]);
            }

            // Trigger WhatsApp confirmation if WhatsApp engine available
            if (class_exists('WhatsAppEngine')) {
                try {
                    $waEngine = new WhatsAppEngine();
                    $msgText = "✨ *Order Confirmed!* ✨\n\n" .
                               "Dear *{$shipping_name}*,\n" .
                               "Thank you for shopping with *{$store_settings['name']}*!\n\n" .
                               "📋 *Order Number:* `{$order_no}`\n" .
                               "💰 *Total Amount:* " . formatStorePrice($grand_total) . "\n" .
                               "🚚 *Payment Mode:* " . strtoupper($payment_method) . "\n" .
                               "📍 *Delivery to:* {$shipping_address}, {$shipping_city}\n\n" .
                               "Your order is currently being prepared with care and will be dispatched within 24-48 hours. You can track your order status anytime at:\n" .
                               "https://{$_SERVER['HTTP_HOST']}/store/track.php?order={$order_no}\n\n" .
                               "For any questions, reply directly to this message. Thank you!";
                    
                    $waEngine->sendMessage($shipping_phone, $msgText);
                } catch (Exception $waEx) {
                    error_log("WhatsApp confirmation error: " . $waEx->getMessage());
                }
            }

            // Clear Cart
            $_SESSION['store_cart'] = [];

            // Redirect to success page
            header("Location: order_success.php?order_no=" . urlencode($order_no));
            exit;

        } catch (Exception $e) {
            $error_msg = "An error occurred while creating your order: " . $e->getMessage();
        }
    }
}

$page_title = 'Secure Checkout — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';
?>

<main class="store-container">
    <div class="product-breadcrumb" style="margin-bottom: 24px;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <span>/</span>
        <a href="cart.php">Shopping Bag</a>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 700;">Checkout</span>
    </div>

    <div class="section-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="section-title">Express Checkout</h1>
            <p class="section-subtitle">Complete your delivery details for immediate dispatch</p>
        </div>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error_msg); ?></span>
        </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST">
        <div class="checkout-grid">
            <!-- Delivery & Payment Form -->
            <div>
                <!-- Step 1: Customer & Delivery Info -->
                <div class="checkout-form-card">
                    <h3 style="font-family: var(--font-heading); font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px;">1</span>
                        <span>Recipient & Shipping Information</span>
                    </h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="shipping_name" required class="form-input" placeholder="e.g. Muhammad Umair" value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">WhatsApp Number *</label>
                            <input type="text" name="shipping_phone" required class="form-input" placeholder="e.g. 03001234567" value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Complete Street Address *</label>
                        <input type="text" name="shipping_address" required class="form-input" placeholder="House/Apartment #, Street, Sector or Landmark" value="<?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <select name="shipping_city" class="form-select">
                                <?php 
                                $cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Peshawar', 'Quetta', 'Sialkot', 'Hyderabad', 'Gujranwala', 'Bahawalpur', 'Abbottabad', 'Other City'];
                                $selected_city = $_POST['shipping_city'] ?? 'Karachi';
                                foreach ($cities as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo ($selected_city === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Special Delivery Note (Optional)</label>
                            <input type="text" name="customer_notes" class="form-input" placeholder="e.g. Please call before delivery" value="<?php echo htmlspecialchars($_POST['customer_notes'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Payment Method -->
                <div class="checkout-form-card">
                    <h3 style="font-family: var(--font-heading); font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px;">2</span>
                        <span>Select Payment Method</span>
                    </h3>

                    <div class="payment-method-selector">
                        <!-- COD -->
                        <label class="payment-card-option active" id="option_cod">
                            <input type="radio" name="payment_method" value="cod" checked onchange="selectPaymentCard('cod')">
                            <div style="font-size: 24px; color: var(--success);"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">Cash on Delivery (COD)</div>
                                <div style="font-size: 12.5px; color: var(--text-muted);">Pay in cash when courier arrives at your doorstep</div>
                            </div>
                        </label>

                        <!-- Bank Transfer -->
                        <label class="payment-card-option" id="option_bank">
                            <input type="radio" name="payment_method" value="bank_transfer" onchange="selectPaymentCard('bank')">
                            <div style="font-size: 24px; color: #3b82f6;"><i class="fa-solid fa-building-columns"></i></div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">Direct Bank Transfer / Raast ID</div>
                                <div style="font-size: 12.5px; color: var(--text-muted);">Instant transfer via online banking or 1-Link Raast</div>
                            </div>
                        </label>

                        <!-- Mobile Wallet -->
                        <label class="payment-card-option" id="option_wallet">
                            <input type="radio" name="payment_method" value="easypaisa" onchange="selectPaymentCard('wallet')">
                            <div style="font-size: 24px; color: #f59e0b;"><i class="fa-solid fa-mobile-screen-button"></i></div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">JazzCash / EasyPaisa</div>
                                <div style="font-size: 12.5px; color: var(--text-muted);">Send payment to our verified merchant account</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div>
                <div class="order-summary-card">
                    <h3 class="summary-title">Order Review</h3>

                    <!-- Order items list -->
                    <div style="max-height: 240px; overflow-y: auto; margin-bottom: 16px;">
                        <?php foreach ($cart as $item): 
                            $lineTotal = (float)$item['price'] * (int)$item['qty'];
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px;">
                            <div>
                                <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($item['name']); ?></strong>
                                <?php if (!empty($item['variant_label'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($item['variant_label']); ?> &times; <?php echo $item['qty']; ?></div>
                                <?php else: ?>
                                    <div style="font-size: 11px; color: var(--text-muted);">Qty: <?php echo $item['qty']; ?></div>
                                <?php endif; ?>
                            </div>
                            <span style="font-weight: 700; color: var(--text-primary);"><?php echo formatStorePrice($lineTotal); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row" style="border-top: 1px solid var(--border-color); padding-top: 12px;">
                        <span>Subtotal</span>
                        <strong><?php echo formatStorePrice($subtotal); ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>
                            <?php if ($shipping == 0): ?>
                                <strong style="color: var(--success);"><i class="fa-solid fa-gift"></i> FREE</strong>
                            <?php else: ?>
                                <?php echo formatStorePrice($shipping); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="summary-row total">
                        <span>Payable Amount</span>
                        <span style="color: var(--primary);"><?php echo formatStorePrice($grand_total); ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-top: 24px;">
                        <i class="fa-solid fa-circle-check"></i> Confirm & Place Order
                    </button>

                    <div style="font-size: 11.5px; color: var(--text-muted); text-align: center; margin-top: 14px; line-height: 1.5;">
                        <i class="fa-solid fa-lock"></i> Your information is strictly confidential. You will receive an instant WhatsApp order receipt.
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
function selectPaymentCard(type) {
    document.querySelectorAll('.payment-card-option').forEach(el => el.classList.remove('active'));
    const target = document.getElementById('option_' + type);
    if (target) target.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
