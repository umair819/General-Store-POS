<?php
require_once __DIR__ . '/init.php';

$page_title = $store_settings['name'] . ' — Luxury Artisanal Fragrances & Retail';
require_once __DIR__ . '/header.php';

// Fetch active item types for pills
$item_types = dbQuery("SELECT * FROM item_types WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");

// Fetch featured & popular products
$featured_products = dbQuery("
    SELECT p.*, 
           b.name as brand_name, 
           it.name as item_type_name,
           (SELECT MIN(sale_price) FROM product_variants WHERE product_id = p.id AND is_active = 1) as min_variant_price,
           (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id AND is_active = 1) as variant_count
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN item_types it ON p.item_type_id = it.id 
    WHERE (p.is_active = 1 OR p.is_active IS NULL)
    ORDER BY p.is_featured DESC, p.id DESC 
    LIMIT 12
");
?>

<main class="store-container">
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fa-solid fa-crown"></i> Haute Parfumerie & Luxury Essentials
            </div>
            <h1 class="hero-title">
                Signature Fragrances, <span>Crafted to Captivate</span>
            </h1>
            <p class="hero-desc">
                Immerse yourself in our curated anthology of artisanal extrait de parfum, designer creations, and luxury retail collections. Delivered nationwide with Cash on Delivery.
            </p>
            <div class="hero-actions">
                <a href="catalog.php" class="btn btn-primary">
                    <i class="fa-solid fa-bag-shopping"></i> Explore Collection
                </a>
                <button type="button" class="btn btn-whatsapp" onclick="orderOnWhatsApp('<?php echo htmlspecialchars($store_settings['phone']); ?>', 'General Inquiry / Order Request', '', '', window.location.href)">
                    <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                </button>
            </div>
        </div>
        <div style="z-index: 1; display: flex; align-items: center; justify-content: center; position: relative;">
            <div style="width: 280px; height: 280px; border-radius: 50%; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); box-shadow: 0 20px 50px rgba(0,0,0,0.4);">
                <i class="fa-solid fa-gem" style="font-size: 110px; color: var(--primary); filter: drop-shadow(0 10px 20px rgba(245, 158, 11, 0.5));"></i>
            </div>
        </div>
    </section>

    <!-- Category / Item Type Pills Bar -->
    <?php if (!empty($item_types)): ?>
    <div class="category-pills-bar">
        <a href="catalog.php" class="cat-pill active">
            <i class="fa-solid fa-sparkles"></i> All Collections
        </a>
        <?php foreach ($item_types as $type): ?>
            <a href="catalog.php?type=<?php echo urlencode($type['slug']); ?>" class="cat-pill">
                <span><?php echo htmlspecialchars($type['icon'] ?? '🛍️'); ?></span>
                <span><?php echo htmlspecialchars($type['name']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Trust Badges Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 48px;">
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: rgba(245, 158, 11, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-certificate"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 2px;">100% Authentic</h4>
                <p style="font-size: 12px; color: var(--text-muted);">Guaranteed original formulations</p>
            </div>
        </div>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 2px;">Cash on Delivery</h4>
                <p style="font-size: 12px; color: var(--text-muted);">Pay securely at your doorstep</p>
            </div>
        </div>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 2px;">Express Dispatch</h4>
                <p style="font-size: 12px; color: var(--text-muted);">Orders shipped in 24-48 hours</p>
            </div>
        </div>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: rgba(37, 211, 102, 0.15); color: var(--whatsapp); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 2px;">WhatsApp Concierge</h4>
                <p style="font-size: 12px; color: var(--text-muted);">Instant scent consultation</p>
            </div>
        </div>
    </div>

    <!-- Featured Products Showcase -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Curated Masterpieces</h2>
            <p class="section-subtitle">Exquisite aromas and signature retail highlights</p>
        </div>
        <a href="catalog.php" class="section-link">
            <span>View All</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($featured_products)): ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <i class="fa-solid fa-boxes-stacked" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Catalog is being curated</h3>
            <p style="font-size: 13.5px; color: var(--text-muted);">Check back shortly as new luxury items are added to Tijarat PRO.</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($featured_products as $prod): 
                $price_display = formatStorePrice($prod['sale_price']);
                if (!empty($prod['has_variants']) && !empty($prod['min_variant_price'])) {
                    $price_display = '<small>From </small>' . formatStorePrice($prod['min_variant_price']);
                }

                // Get sample attributes for pills (e.g. concentration, gender)
                $attrs = getProductAttributes($prod['id']);
                $badge_pill = $attrs['concentration'] ?? $attrs['fabric'] ?? $prod['category'] ?? null;
                $fragrance_family = $attrs['fragrance_family'] ?? null;
            ?>
            <div class="product-card">
                <div class="product-thumb-wrap">
                    <?php if (!empty($prod['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-thumb-img">
                    <?php else: ?>
                        <div class="product-placeholder-icon">
                            <i class="fa-solid fa-wine-bottle" style="color: var(--primary);"></i>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($prod['is_featured'])): ?>
                        <div class="product-card-badge">Featured</div>
                    <?php endif; ?>

                    <?php if (!empty($prod['brand_name'])): ?>
                        <div class="product-brand-tag"><?php echo htmlspecialchars($prod['brand_name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="product-card-body">
                    <div class="product-spec-pills">
                        <?php if ($badge_pill): ?>
                            <span class="spec-pill"><?php echo htmlspecialchars($badge_pill); ?></span>
                        <?php endif; ?>
                        <?php if ($fragrance_family): ?>
                            <span class="spec-pill" style="background: rgba(245, 158, 11, 0.12); color: var(--primary-hover);"><?php echo htmlspecialchars($fragrance_family); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($attrs['gender'])): ?>
                            <span class="spec-pill"><?php echo htmlspecialchars($attrs['gender']); ?></span>
                        <?php endif; ?>
                    </div>

                    <a href="product.php?id=<?php echo $prod['id']; ?>" class="product-card-title">
                        <?php echo htmlspecialchars($prod['name']); ?>
                    </a>

                    <div class="product-card-bottom">
                        <div class="product-price-box">
                            <div class="product-price"><?php echo $price_display; ?></div>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="icon-btn" style="width: 36px; height: 36px; font-size: 13px; color: var(--whatsapp); border-color: rgba(37, 211, 102, 0.3);" title="Order via WhatsApp" onclick="orderOnWhatsApp('<?php echo htmlspecialchars($store_settings['phone']); ?>', '<?php echo addslashes($prod['name']); ?>', '', '<?php echo strip_tags($price_display); ?>', window.location.origin + '/store/product.php?id=<?php echo $prod['id']; ?>')">
                                <i class="fa-brands fa-whatsapp"></i>
                            </button>

                            <?php if (!empty($prod['has_variants'])): ?>
                                <a href="product.php?id=<?php echo $prod['id']; ?>" class="btn btn-secondary btn-sm">
                                    <span>Options</span> <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $prod['id']; ?>)">
                                    <i class="fa-solid fa-plus"></i> <span>Add</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Luxury Call to Action Banner -->
    <div style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%); border-radius: var(--radius-lg); padding: 48px; color: #fff; text-align: center; margin-bottom: 60px; border: 1px solid var(--border-color); box-shadow: var(--shadow-md);">
        <h3 style="font-family: var(--font-heading); font-size: 28px; font-weight: 800; margin-bottom: 12px;">
            Need Assistance Selecting Your Signature Aroma?
        </h3>
        <p style="color: var(--text-muted); font-size: 14px; max-width: 600px; margin: 0 auto 24px; line-height: 1.6;">
            Our fragrance specialists are available on WhatsApp to guide you through notes, longevity, sillage, and gifting recommendations.
        </p>
        <button type="button" class="btn btn-whatsapp" style="font-size: 15px; padding: 12px 28px;" onclick="orderOnWhatsApp('<?php echo htmlspecialchars($store_settings['phone']); ?>', 'Consultation Request: Seeking fragrance recommendations', '', '', window.location.href)">
            <i class="fa-brands fa-whatsapp" style="font-size: 18px;"></i> Chat with Our Fragrance Specialist
        </button>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
