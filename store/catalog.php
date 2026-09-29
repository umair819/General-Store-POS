<?php
require_once __DIR__ . '/init.php';

$page_title = 'Catalog & Collections — ' . $store_settings['name'];
require_once __DIR__ . '/header.php';

// Filter parameters
$search_query   = trim($_GET['q'] ?? '');
$filter_type    = trim($_GET['type'] ?? '');
$filter_brand   = (int)($_GET['brand'] ?? 0);
$filter_cat     = trim($_GET['category'] ?? '');
$filter_gender  = trim($_GET['gender'] ?? '');
$filter_conc    = trim($_GET['concentration'] ?? '');
$filter_family  = trim($_GET['family'] ?? '');
$filter_instock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : 0;
$min_price      = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price      = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort_by        = trim($_GET['sort'] ?? 'newest');

// Fetch dynamic filter options from DB
$item_types = dbQuery("SELECT * FROM item_types WHERE is_active = 1 ORDER BY sort_order ASC");
$brands     = dbQuery("SELECT * FROM brands WHERE is_active = 1 ORDER BY name ASC");
$categories = dbQuery("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");

// Build Query
$where = ["(p.is_active = 1 OR p.is_active IS NULL)"];
$params = [];

if ($search_query !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ? OR b.name LIKE ?)";
    $like = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($filter_type !== '') {
    $where[] = "it.slug = ?";
    $params[] = $filter_type;
}

if ($filter_brand > 0) {
    $where[] = "p.brand_id = ?";
    $params[] = $filter_brand;
}

if ($filter_cat !== '') {
    $where[] = "p.category = ?";
    $params[] = $filter_cat;
}

if ($filter_instock === 1) {
    $where[] = "p.stock_qty > 0";
}

if ($min_price > 0) {
    $where[] = "p.sale_price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $where[] = "p.sale_price <= ?";
    $params[] = $max_price;
}

// Sorting
$orderBy = "p.id DESC";
if ($sort_by === 'price_asc') {
    $orderBy = "p.sale_price ASC";
} elseif ($sort_by === 'price_desc') {
    $orderBy = "p.sale_price DESC";
} elseif ($sort_by === 'name_asc') {
    $orderBy = "p.name ASC";
}

$whereSql = implode(' AND ', $where);
$sql = "
    SELECT p.*, 
           b.name as brand_name, 
           it.name as item_type_name,
           it.slug as item_type_slug,
           (SELECT MIN(sale_price) FROM product_variants WHERE product_id = p.id AND is_active = 1) as min_variant_price,
           (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id AND is_active = 1) as variant_count
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN item_types it ON p.item_type_id = it.id 
    WHERE {$whereSql}
    ORDER BY {$orderBy}
";

$products = dbQuery($sql, $params);

// Attribute filter post-processing if concentration or fragrance_family selected
if ($filter_conc !== '' || $filter_family !== '' || $filter_gender !== '') {
    $filtered = [];
    foreach ($products as $prod) {
        $attrs = getProductAttributes($prod['id']);
        $match = true;
        if ($filter_conc !== '' && strtolower($attrs['concentration'] ?? '') !== strtolower($filter_conc)) {
            $match = false;
        }
        if ($filter_family !== '' && strtolower($attrs['fragrance_family'] ?? '') !== strtolower($filter_family)) {
            $match = false;
        }
        if ($filter_gender !== '' && strtolower($attrs['gender'] ?? '') !== strtolower($filter_gender)) {
            $match = false;
        }
        if ($match) {
            $filtered[] = $prod;
        }
    }
    $products = $filtered;
}
?>

<main class="store-container">
    <div class="product-breadcrumb" style="margin-bottom: 24px;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 700;">Catalog</span>
        <?php if ($filter_type !== ''): ?>
            <span>/</span>
            <span style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars(ucfirst($filter_type)); ?></span>
        <?php endif; ?>
    </div>

    <div class="catalog-layout">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <div class="filter-header">
                <h3><i class="fa-solid fa-sliders" style="color: var(--primary); margin-right: 6px;"></i> Filters</h3>
                <a href="catalog.php" class="filter-clear-btn">Reset All</a>
            </div>

            <form action="catalog.php" method="GET" id="catalogFilterForm">
                <?php if ($search_query !== ''): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                <?php endif; ?>

                <!-- Item Type -->
                <?php if (!empty($item_types)): ?>
                <div class="filter-group">
                    <div class="filter-title">Product Type</div>
                    <div class="filter-options">
                        <label class="filter-label">
                            <input type="radio" name="type" value="" <?php echo ($filter_type === '') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span>All Collections</span>
                        </label>
                        <?php foreach ($item_types as $t): ?>
                            <label class="filter-label">
                                <input type="radio" name="type" value="<?php echo htmlspecialchars($t['slug']); ?>" <?php echo ($filter_type === $t['slug']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span><?php echo htmlspecialchars($t['icon'] ?? ''); ?> <?php echo htmlspecialchars($t['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Brands -->
                <?php if (!empty($brands)): ?>
                <div class="filter-group">
                    <div class="filter-title">Brand</div>
                    <div class="filter-options">
                        <label class="filter-label">
                            <input type="radio" name="brand" value="0" <?php echo ($filter_brand === 0) ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span>All Brands</span>
                        </label>
                        <?php foreach ($brands as $b): ?>
                            <label class="filter-label">
                                <input type="radio" name="brand" value="<?php echo $b['id']; ?>" <?php echo ($filter_brand === (int)$b['id']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span><?php echo htmlspecialchars($b['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Perfume Specific: Concentration -->
                <div class="filter-group">
                    <div class="filter-title">Concentration</div>
                    <div class="filter-options">
                        <?php 
                        $concentrations = ['Parfum', 'EDP', 'EDT', 'EDC', 'Attar', 'Body Spray'];
                        foreach ($concentrations as $c): 
                        ?>
                            <label class="filter-label">
                                <input type="radio" name="concentration" value="<?php echo htmlspecialchars($c); ?>" <?php echo ($filter_conc === $c) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span><?php echo htmlspecialchars($c); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Gender -->
                <div class="filter-group">
                    <div class="filter-title">Gender</div>
                    <div class="filter-options">
                        <?php foreach (['Men', 'Women', 'Unisex'] as $g): ?>
                            <label class="filter-label">
                                <input type="radio" name="gender" value="<?php echo htmlspecialchars($g); ?>" <?php echo ($filter_gender === $g) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span><?php echo htmlspecialchars($g); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Fragrance Family -->
                <div class="filter-group">
                    <div class="filter-title">Fragrance Family</div>
                    <div class="filter-options">
                        <?php foreach (['Woody', 'Floral', 'Oriental', 'Fresh', 'Citrus', 'Gourmand', 'Oud', 'Amber'] as $f): ?>
                            <label class="filter-label">
                                <input type="radio" name="family" value="<?php echo htmlspecialchars($f); ?>" <?php echo ($filter_family === $f) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span><?php echo htmlspecialchars($f); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- In Stock Only -->
                <div class="filter-group">
                    <label class="filter-label" style="font-weight: 700; color: var(--text-primary);">
                        <input type="checkbox" name="in_stock" value="1" <?php echo ($filter_instock === 1) ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <span>In Stock Only</span>
                    </label>
                </div>
            </form>
        </aside>

        <!-- Product Grid & Toolbar Area -->
        <div>
            <!-- Toolbar -->
            <div class="catalog-toolbar">
                <div style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">
                    Showing <strong style="color: var(--text-primary);"><?php echo count($products); ?></strong> exquisite creations
                    <?php if ($search_query !== ''): ?>
                        for "<span style="color: var(--primary);"><?php echo htmlspecialchars($search_query); ?></span>"
                    <?php endif; ?>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <label for="catalogSort" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Sort By:</label>
                    <select id="catalogSort" class="catalog-sort-select" onchange="applySort(this.value)">
                        <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 80px 20px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <i class="fa-solid fa-filter-circle-xmark" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">No matching items found</h3>
                    <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 20px;">Try clearing filters or searching for another fragrance note or brand.</p>
                    <a href="catalog.php" class="btn btn-primary">
                        <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $prod): 
                        $price_display = formatStorePrice($prod['sale_price']);
                        if (!empty($prod['has_variants']) && !empty($prod['min_variant_price'])) {
                            $price_display = '<small>From </small>' . formatStorePrice($prod['min_variant_price']);
                        }

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
        </div>
    </div>
</main>

<script>
function applySort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
