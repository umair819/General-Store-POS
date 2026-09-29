<?php
require_once __DIR__ . '/init.php';

$store_settings = getStoreSettings();
$cart_count = getStoreCartCount();
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($page_title)) {
    $page_title = $store_settings['name'] . ' — ' . $store_settings['tagline'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="Shop luxury perfumes, artisanal fragrances and signature retail items with nationwide cash on delivery.">
    
    <!-- Design System Stylesheet -->
    <link rel="stylesheet" href="assets/css/store.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="top-announcement-bar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-truck-fast" style="color: var(--primary);"></i>
            <span>Free Express Delivery on orders over <?php echo formatStorePrice($store_settings['free_shipping_min']); ?></span>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <span><i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> 100% Original & Authentic</span>
            <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $store_settings['phone']); ?>" target="_blank" style="color: #fff; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <i class="fa-brands fa-whatsapp" style="color: var(--whatsapp);"></i> <?php echo htmlspecialchars($store_settings['phone']); ?>
            </a>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="store-header">
        <div class="header-container">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo">
                <div class="brand-logo-icon">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <div>
                    <div class="brand-logo-text">
                        <?php 
                        $parts = explode(' ', $store_settings['name'], 2);
                        echo htmlspecialchars($parts[0]); 
                        if (!empty($parts[1])): ?>
                            <span><?php echo htmlspecialchars($parts[1]); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="brand-subtitle"><?php echo htmlspecialchars($store_settings['tagline']); ?></div>
                </div>
            </a>

            <!-- Search Bar -->
            <form action="catalog.php" method="GET" style="flex: 1; max-width: 420px; display: flex; align-items: center; position: relative;">
                <input type="text" name="q" placeholder="Search fragrances, brands, notes..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="form-input" style="padding-left: 38px; border-radius: var(--radius-full); font-size: 13.5px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; color: var(--text-muted); font-size: 14px;"></i>
            </form>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="catalog.php" class="nav-link <?php echo ($current_page === 'catalog.php') ? 'active' : ''; ?>">Catalog</a></li>
                <li><a href="track.php" class="nav-link <?php echo ($current_page === 'track.php') ? 'active' : ''; ?>">Track Order</a></li>
            </ul>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Theme Toggle -->
                <button type="button" class="icon-btn" onclick="toggleStoreTheme()" title="Toggle Dark/Light Mode">
                    <i id="storeThemeIcon" class="fa-solid fa-moon"></i>
                </button>

                <!-- Shopping Bag -->
                <a href="cart.php" class="icon-btn" title="View Shopping Bag">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-count-badge" style="<?php echo $cart_count > 0 ? '' : 'display: none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
            </div>
        </div>
    </header>
