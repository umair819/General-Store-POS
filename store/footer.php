<?php
$store_settings = getStoreSettings();
?>
    <!-- Store Footer -->
    <footer class="store-footer">
        <div class="footer-container">
            <!-- Brand & Mission -->
            <div class="footer-col">
                <div class="brand-logo" style="margin-bottom: 16px;">
                    <div class="brand-logo-icon">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                    <div class="brand-logo-text">
                        <?php echo htmlspecialchars($store_settings['name']); ?>
                    </div>
                </div>
                <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.7; margin-bottom: 20px;">
                    Premium retail destination curating the finest designer fragrances, artisanal perfumes, and luxury lifestyle essentials. Authentic products delivered nationwide with care.
                </p>
                <div style="display: flex; gap: 12px;">
                    <a href="#" class="icon-btn" style="width: 36px; height: 36px; font-size: 14px;"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="icon-btn" style="width: 36px; height: 36px; font-size: 14px;"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon-btn" style="width: 36px; height: 36px; font-size: 14px;"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="footer-col">
                <h4>Collections</h4>
                <ul class="footer-links">
                    <li><a href="catalog.php">All Products</a></li>
                    <li><a href="catalog.php?featured=1">Featured Arrivals</a></li>
                    <li><a href="catalog.php?sort=price_desc">Haute Parfumerie</a></li>
                    <li><a href="catalog.php?sort=price_asc">Value Editions</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-col">
                <h4>Customer Support</h4>
                <ul class="footer-links">
                    <li><a href="track.php">Track My Order</a></li>
                    <li><a href="cart.php">Shopping Bag</a></li>
                    <li><a href="checkout.php">Easy Checkout</a></li>
                    <li><a href="https://wa.me/<?php echo preg_replace('/\D/', '', $store_settings['phone']); ?>" target="_blank">Direct WhatsApp Helpline</a></li>
                </ul>
            </div>

            <!-- Boutique Info -->
            <div class="footer-col">
                <h4>Flagship Boutique</h4>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px; display: flex; gap: 8px; align-items: flex-start;">
                    <i class="fa-solid fa-location-dot" style="color: var(--primary); margin-top: 3px;"></i>
                    <span><?php echo htmlspecialchars($store_settings['address']); ?></span>
                </p>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px; display: flex; gap: 8px; align-items: center;">
                    <i class="fa-solid fa-phone" style="color: var(--primary);"></i>
                    <span><?php echo htmlspecialchars($store_settings['phone']); ?></span>
                </p>
                <p style="font-size: 13px; color: var(--text-muted); display: flex; gap: 8px; align-items: center;">
                    <i class="fa-solid fa-clock" style="color: var(--primary);"></i>
                    <span>Mon – Sat: 11:00 AM – 10:00 PM</span>
                </p>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div>
                &copy; <?php echo date('Y'); ?> <strong><?php echo htmlspecialchars($store_settings['name']); ?></strong>. Powered by <strong style="color: var(--primary);">Tijarat PRO Omni-Channel ERP</strong>.
            </div>
            <div style="display: flex; gap: 16px; align-items: center;">
                <span><i class="fa-solid fa-shield-check" style="color: var(--success);"></i> 100% Genuine</span>
                <span><i class="fa-solid fa-money-bill-wave" style="color: var(--primary);"></i> Cash on Delivery</span>
                <span><i class="fa-solid fa-truck" style="color: #3b82f6;"></i> Nationwide Delivery</span>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $store_settings['phone']); ?>?text=Hello!%20I%20have%20an%20inquiry%20regarding%20an%20order%20from%20your%20online%20store." target="_blank" class="floating-whatsapp-btn" title="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- Toast Notification Container -->
    <div id="storeToastContainer" class="toast-container"></div>

    <!-- Scripts -->
    <script src="assets/js/store.js"></script>
</body>
</html>
