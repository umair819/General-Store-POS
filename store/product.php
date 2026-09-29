<?php
require_once __DIR__ . '/init.php';

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header("Location: catalog.php");
    exit;
}

// Fetch product details
$product = dbQuery("
    SELECT p.*, 
           b.name as brand_name, 
           b.logo_url as brand_logo,
           it.name as item_type_name,
           it.slug as item_type_slug
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN item_types it ON p.item_type_id = it.id 
    WHERE p.id = ? AND (p.is_active = 1 OR p.is_active IS NULL)
", [$product_id]);

if (empty($product)) {
    header("Location: catalog.php");
    exit;
}

$prod = $product[0];
$page_title = $prod['name'] . ' — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';

// Fetch product variants
$variants = dbQuery("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC", [$product_id]);

// Default selected variant
$default_variant = !empty($variants) ? $variants[0] : null;
$initial_price = $default_variant ? $default_variant['sale_price'] : $prod['sale_price'];
$initial_stock = $default_variant ? $default_variant['stock_qty'] : $prod['stock_qty'];
$initial_sku   = $default_variant ? $default_variant['sku'] : $prod['sku'];

// Fetch dynamic EAV attributes
$attrs = getProductAttributes($prod['id']);

// Fetch related products
$related = dbQuery("
    SELECT p.*, b.name as brand_name, (SELECT MIN(sale_price) FROM product_variants WHERE product_id = p.id AND is_active = 1) as min_variant_price
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.id != ? AND (p.brand_id = ? OR p.category = ? OR p.item_type_id = ?) AND (p.is_active = 1 OR p.is_active IS NULL)
    LIMIT 4
", [$product_id, $prod['brand_id'], $prod['category'], $prod['item_type_id']]);
?>

<main class="store-container">
    <!-- Breadcrumb -->
    <div class="product-breadcrumb" style="margin-bottom: 30px;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <span>/</span>
        <a href="catalog.php">Catalog</a>
        <?php if (!empty($prod['item_type_name'])): ?>
            <span>/</span>
            <a href="catalog.php?type=<?php echo urlencode($prod['item_type_slug']); ?>"><?php echo htmlspecialchars($prod['item_type_name']); ?></a>
        <?php endif; ?>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($prod['name']); ?></span>
    </div>

    <!-- Product Main Grid -->
    <div class="product-detail-layout">
        <!-- Gallery -->
        <div class="product-gallery">
            <?php if (!empty($prod['image_url'])): ?>
                <img id="mainProductImg" src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-main-image">
            <?php else: ?>
                <div style="height: 380px; display: flex; align-items: center; justify-content: center; background: var(--bg-muted); border-radius: var(--radius-md);">
                    <i class="fa-solid fa-wine-bottle" style="font-size: 110px; color: var(--primary); opacity: 0.4;"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info-panel">
            <?php if (!empty($prod['brand_name'])): ?>
                <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--primary);">
                    <?php echo htmlspecialchars($prod['brand_name']); ?>
                </div>
            <?php endif; ?>

            <h1 class="product-title-lg"><?php echo htmlspecialchars($prod['name']); ?></h1>

            <!-- Price Hero -->
            <div class="product-price-hero">
                <div class="price-current" id="productCurrentPrice">
                    <?php echo formatStorePrice($initial_price); ?>
                </div>
                <div id="productStockBadge" style="padding: 4px 10px; border-radius: var(--radius-full); font-size: 12px; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: var(--success);">
                    <?php echo ((float)$initial_stock > 0) ? '● In Stock & Ready to Dispatch' : '○ Out of Stock'; ?>
                </div>
            </div>

            <?php if (!empty($prod['description'])): ?>
                <p style="font-size: 14.5px; color: var(--text-secondary); line-height: 1.7;">
                    <?php echo nl2br(htmlspecialchars($prod['description'])); ?>
                </p>
            <?php endif; ?>

            <!-- Dynamic Variant Picker (Volume / Size) -->
            <?php if (!empty($variants)): ?>
            <div class="variant-picker-section">
                <div class="variant-picker-label">Select Edition / Size:</div>
                <div class="variant-buttons">
                    <?php foreach ($variants as $idx => $v): ?>
                        <button type="button" 
                                class="variant-btn <?php echo ($idx === 0) ? 'active' : ''; ?>" 
                                data-variant-id="<?php echo $v['id']; ?>"
                                data-price="<?php echo $v['sale_price']; ?>"
                                data-formatted-price="<?php echo htmlspecialchars(formatStorePrice($v['sale_price'])); ?>"
                                data-stock="<?php echo $v['stock_qty']; ?>"
                                data-sku="<?php echo htmlspecialchars($v['sku'] ?? ''); ?>"
                                data-label="<?php echo htmlspecialchars($v['variant_label']); ?>"
                                onclick="selectVariant(this)">
                            <?php echo htmlspecialchars($v['variant_label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quantity & Actions -->
            <div style="display: flex; gap: 14px; align-items: center; margin-top: 10px; flex-wrap: wrap;">
                <div class="qty-control">
                    <button type="button" class="qty-btn" onclick="adjustDetailQty(-1)">-</button>
                    <input type="text" id="detailQtyInput" class="qty-input" value="1" readonly>
                    <button type="button" class="qty-btn" onclick="adjustDetailQty(1)">+</button>
                </div>

                <button type="button" class="btn btn-primary" style="flex: 1; padding: 12px 24px; font-size: 15px;" onclick="addCurrentToBag()">
                    <i class="fa-solid fa-bag-shopping"></i> Add to Shopping Bag
                </button>

                <button type="button" class="btn btn-whatsapp" style="padding: 12px 20px;" onclick="orderCurrentViaWhatsApp()">
                    <i class="fa-brands fa-whatsapp" style="font-size: 18px;"></i> ⚡ 1-Click WhatsApp
                </button>
            </div>

            <!-- Authentic Guarantee Box -->
            <div style="background: var(--bg-muted); border-radius: var(--radius-sm); padding: 14px 18px; display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--text-secondary); margin-top: 10px;">
                <i class="fa-solid fa-shield-check" style="font-size: 20px; color: var(--primary);"></i>
                <span>Guaranteed 100% Original & Authentic product directly bottled / imported.</span>
            </div>

            <!-- Dynamic Specifications Table -->
            <?php if (!empty($attrs)): ?>
            <div style="margin-top: 20px;">
                <h3 style="font-family: var(--font-heading); font-size: 18px; font-weight: 700; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                    Product Specifications
                </h3>
                <table class="specs-table">
                    <tbody>
                        <?php foreach ($attrs as $k => $val): 
                            if (empty($val)) continue;
                            $label = ucwords(str_replace('_', ' ', $k));
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($label); ?></td>
                            <td><?php echo htmlspecialchars($val); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!empty($prod['category'])): ?>
                        <tr>
                            <td>Category</td>
                            <td><?php echo htmlspecialchars($prod['category']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <div style="margin-top: 60px;">
        <div class="section-header">
            <div>
                <h2 class="section-title">You May Also Admire</h2>
                <p class="section-subtitle">Complementary scents and curated pairings</p>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach ($related as $rel): 
                $rPrice = formatStorePrice($rel['sale_price']);
                if (!empty($rel['min_variant_price'])) {
                    $rPrice = '<small>From </small>' . formatStorePrice($rel['min_variant_price']);
                }
            ?>
            <div class="product-card">
                <div class="product-thumb-wrap" style="height: 200px;">
                    <?php if (!empty($rel['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>" class="product-thumb-img">
                    <?php else: ?>
                        <div class="product-placeholder-icon">
                            <i class="fa-solid fa-wine-bottle" style="color: var(--primary); font-size: 48px;"></i>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($rel['brand_name'])): ?>
                        <div class="product-brand-tag"><?php echo htmlspecialchars($rel['brand_name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="product-card-body">
                    <a href="product.php?id=<?php echo $rel['id']; ?>" class="product-card-title">
                        <?php echo htmlspecialchars($rel['name']); ?>
                    </a>
                    <div class="product-card-bottom">
                        <div class="product-price"><?php echo $rPrice; ?></div>
                        <a href="product.php?id=<?php echo $rel['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
let selectedVariantId = <?php echo $default_variant ? $default_variant['id'] : 'null'; ?>;
let selectedVariantLabel = '<?php echo $default_variant ? addslashes($default_variant['variant_label']) : ''; ?>';
let selectedPriceFormatted = '<?php echo addslashes(formatStorePrice($initial_price)); ?>';
let productName = '<?php echo addslashes($prod['name']); ?>';
let shopPhone = '<?php echo htmlspecialchars($store_settings['phone']); ?>';

function selectVariant(btn) {
    document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    selectedVariantId = btn.getAttribute('data-variant-id');
    selectedVariantLabel = btn.getAttribute('data-label');
    selectedPriceFormatted = btn.getAttribute('data-formatted-price');

    document.getElementById('productCurrentPrice').textContent = selectedPriceFormatted;

    const stock = parseFloat(btn.getAttribute('data-stock') || 0);
    const badge = document.getElementById('productStockBadge');
    if (stock > 0) {
        badge.textContent = '● In Stock & Ready to Dispatch';
        badge.style.background = 'rgba(16, 185, 129, 0.15)';
        badge.style.color = 'var(--success)';
    } else {
        badge.textContent = '○ Out of Stock';
        badge.style.background = 'rgba(239, 68, 68, 0.15)';
        badge.style.color = 'var(--danger)';
    }
}

function adjustDetailQty(delta) {
    const input = document.getElementById('detailQtyInput');
    let val = parseInt(input.value || 1) + delta;
    if (val < 1) val = 1;
    input.value = val;
}

function addCurrentToBag() {
    const qty = parseInt(document.getElementById('detailQtyInput').value || 1);
    addToCart(<?php echo $prod['id']; ?>, selectedVariantId, qty);
}

function orderCurrentViaWhatsApp() {
    orderOnWhatsApp(shopPhone, productName, selectedVariantLabel, selectedPriceFormatted, window.location.href);
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
