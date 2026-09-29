<?php
require_once __DIR__ . '/init.php';

$page_title = 'Shopping Bag — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';

$cart = $_SESSION['store_cart'] ?? [];
$subtotal = getStoreCartSubtotal();
$shipping = ($subtotal >= $store_settings['free_shipping_min'] || $subtotal == 0) ? 0.0 : $store_settings['shipping_flat'];
$grand_total = $subtotal + $shipping;
?>

<main class="store-container">
    <div class="product-breadcrumb" style="margin-bottom: 24px;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 700;">Shopping Bag</span>
    </div>

    <div class="section-header" style="margin-bottom: 30px;">
        <div>
            <h1 class="section-title">Your Curated Selections</h1>
            <p class="section-subtitle">Review items in your shopping bag before proceeding to secure checkout</p>
        </div>
        <?php if (!empty($cart)): ?>
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearCart()">
                <i class="fa-solid fa-trash-can"></i> Clear Bag
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($cart)): ?>
        <div style="text-align: center; padding: 80px 20px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); margin-bottom: 40px;">
            <div style="width: 84px; height: 84px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 20px;">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h2 style="font-family: var(--font-heading); font-size: 22px; font-weight: 700; margin-bottom: 8px;">Your Shopping Bag is Empty</h2>
            <p style="font-size: 14px; color: var(--text-muted); max-width: 440px; margin: 0 auto 24px;">
                Explore our signature perfume anthologies and luxury offerings to discover your next signature scent.
            </p>
            <a href="catalog.php" class="btn btn-primary" style="padding: 12px 28px;">
                <i class="fa-solid fa-sparkles"></i> Explore Collections
            </a>
        </div>
    <?php else: ?>
        <div class="cart-grid">
            <!-- Cart Items Table -->
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $key => $item): 
                            $lineTotal = (float)$item['price'] * (int)$item['qty'];
                        ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-thumb">
                                    <?php else: ?>
                                        <div class="cart-item-thumb" style="display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                            <i class="fa-solid fa-wine-bottle"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <a href="product.php?id=<?php echo $item['product_id']; ?>" class="cart-item-name">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <?php if (!empty($item['variant_label'])): ?>
                                            <div class="cart-item-variant">Edition: <strong><?php echo htmlspecialchars($item['variant_label']); ?></strong></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: var(--text-primary);">
                                <?php echo formatStorePrice($item['price']); ?>
                            </td>
                            <td>
                                <div class="qty-control">
                                    <button type="button" class="qty-btn" onclick="updateCartItem('<?php echo $key; ?>', <?php echo $item['qty'] - 1; ?>)">-</button>
                                    <input type="text" class="qty-input" value="<?php echo $item['qty']; ?>" readonly>
                                    <button type="button" class="qty-btn" onclick="updateCartItem('<?php echo $key; ?>', <?php echo $item['qty'] + 1; ?>)">+</button>
                                </div>
                            </td>
                            <td style="font-family: var(--font-heading); font-weight: 800; color: var(--primary); font-size: 15px;">
                                <?php echo formatStorePrice($lineTotal); ?>
                            </td>
                            <td>
                                <button type="button" class="icon-btn" style="width: 32px; height: 32px; font-size: 12px; color: var(--danger);" title="Remove item" onclick="removeCartItem('<?php echo $key; ?>')">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary Card -->
            <div class="order-summary-card">
                <h3 class="summary-title">Order Summary</h3>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong style="color: var(--text-primary);"><?php echo formatStorePrice($subtotal); ?></strong>
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

                <?php if ($shipping > 0): ?>
                    <div style="font-size: 11.5px; color: var(--primary-hover); background: var(--primary-light); padding: 8px 12px; border-radius: var(--radius-sm); margin-bottom: 14px;">
                        <i class="fa-solid fa-circle-info"></i> Add <strong><?php echo formatStorePrice($store_settings['free_shipping_min'] - $subtotal); ?></strong> more for <strong>FREE Delivery</strong>!
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span style="color: var(--primary);"><?php echo formatStorePrice($grand_total); ?></span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 24px;">
                    <a href="checkout.php" class="btn btn-primary" style="padding: 14px; font-size: 15px;">
                        <i class="fa-solid fa-lock"></i> Proceed to Checkout
                    </a>
                    <a href="catalog.php" class="btn btn-secondary" style="padding: 12px;">
                        <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
async function clearCart() {
    if (!confirm('Are you sure you wish to empty your shopping bag?')) return;
    const res = await fetch('api.php?action=clear_cart');
    const data = await res.json();
    if (data.success) {
        window.location.reload();
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
