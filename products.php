<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

$trans = [
    'en' => [
        'title' => 'Tijarat Inventory - Products & Catalog',
        'heading' => '📦 Products & Multi-Type Catalog',
        'add_new' => '➕ Add Product',
        'add_brand' => '🏷️ Add Brand',
        'all_types' => 'All Items',
        'tbl_barcode' => 'Barcode',
        'tbl_name' => 'Product Name',
        'tbl_type' => 'Item Type',
        'tbl_brand' => 'Brand',
        'tbl_category' => 'Category',
        'tbl_purchase' => 'Cost (Rs)',
        'tbl_sale' => 'Sale (Rs)',
        'tbl_unit' => 'Unit',
        'tbl_stock' => 'Stock',
        'tbl_actions' => 'Actions',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'save' => 'Save Product',
        'cancel' => 'Cancel',
        'edit_product' => 'Edit Product',
        'new_product' => 'Add New Product',
        'confirm_delete' => 'Are you sure you want to delete this product?',
        'select_category' => 'Select Category',
        'select_type' => 'Select Item Type',
        'select_brand' => 'Select Brand (Optional)',
        'no_category' => 'Uncategorized',
        'search_placeholder' => 'Search products by name, barcode, brand, or notes...',
        'variants_title' => 'Product Variants (Volumes, Sizes, Colors)',
        'has_variants' => 'This product has multiple variants (e.g. 50ml, 100ml / S, M, L)',
        'add_variant' => '➕ Add Variant Row',
        'var_label' => 'Variant Label',
        'var_barcode' => 'Variant Barcode',
        'var_sku' => 'SKU',
        'var_purchase' => 'Purchase Price',
        'var_sale' => 'Sale Price',
        'var_stock' => 'Stock Qty',
    ],
    'ur' => [
        'title' => 'تجارت انوینٹری - پروڈکٹس اور کیٹلاگ',
        'heading' => '📦 پروڈکٹس مینجمنٹ اور کیٹلاگ',
        'add_new' => '➕ نئی پروڈکٹ',
        'add_brand' => '🏷️ نیا برانڈ',
        'all_types' => 'تمام آئٹمز',
        'tbl_barcode' => 'بارکوڈ',
        'tbl_name' => 'پروڈکٹ کا نام',
        'tbl_type' => 'آئٹم ٹائپ',
        'tbl_brand' => 'برانڈ',
        'tbl_category' => 'کیٹیگری',
        'tbl_purchase' => 'خریداری (روپے)',
        'tbl_sale' => 'فروخت (روپے)',
        'tbl_unit' => 'یونٹ',
        'tbl_stock' => 'سٹاک',
        'tbl_actions' => 'ایکشنز',
        'btn_edit' => 'ترمیم',
        'btn_delete' => 'ڈیلیٹ',
        'save' => 'محفوظ کریں',
        'cancel' => 'منسوخ',
        'edit_product' => 'پروڈکٹ میں ترمیم',
        'new_product' => 'نئی پروڈکٹ شامل کریں',
        'confirm_delete' => 'کیا آپ واقعی اس پروڈکٹ کو ڈیلیٹ کرنا چاہتے ہیں؟',
        'select_category' => 'کیٹیگری منتخب کریں',
        'select_type' => 'آئٹم ٹائپ منتخب کریں',
        'select_brand' => 'برانڈ منتخب کریں',
        'no_category' => 'بغیر کیٹیگری',
        'search_placeholder' => 'نام، بارکوڈ یا برانڈ سے تلاش کریں...',
        'variants_title' => 'پروڈکٹ ویرینٹس (سائز، والیم، رنگ)',
        'has_variants' => 'اس پروڈکٹ کے مختلف ویرینٹ ہیں (مثلاً 50ml, 100ml یا S, M, L)',
        'add_variant' => '➕ نیا ویرینٹ شامل کریں',
        'var_label' => 'ویرینٹ لیبل',
        'var_barcode' => 'ویرینٹ بارکوڈ',
        'var_sku' => 'ایس کے یو',
        'var_purchase' => 'قیمت خرید',
        'var_sale' => 'قیمت فروخت',
        'var_stock' => 'سٹاک مقدار',
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $trans[$lang]['title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .content-panel { flex-grow: 1; padding: 30px 40px; display: flex; flex-direction: column; gap: 20px; height: 100vh; overflow-y: auto; background-color: var(--bg-app); }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px 26px; box-shadow: var(--shadow-sm); }

        /* Filter Pills Bar */
        .filter-tabs-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 6px;
        }

        .filter-tab-pill {
            padding: 8px 16px;
            border-radius: 30px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            transition: var(--transition-smooth);
            user-select: none;
        }

        .filter-tab-pill:hover {
            border-color: var(--accent);
            color: var(--text-main);
        }

        .filter-tab-pill.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .filter-tab-badge {
            background: rgba(0,0,0,0.12);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* Action Toolbar */
        .action-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box-wrap {
            position: relative;
            flex: 1;
            max-width: 450px;
        }

        .search-box-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-main);
            font-size: 14px;
            outline: none;
            transition: var(--transition-smooth);
        }

        .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--glow-color);
        }

        /* Table Styling */
        .data-table-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-size: 13px; }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background-color: rgba(245, 158, 11, 0.03); }

        /* Badges */
        .type-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            background: var(--bg-input);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .brand-pill-badge {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-low-stock { background-color: rgba(245, 158, 11, 0.15); color: var(--warning); border: 1px solid var(--warning); padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px; }
        .badge-normal-stock { background-color: rgba(16, 185, 129, 0.12); color: var(--success); border: 1px solid var(--success); padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px; }
        .badge-variant-count { background: var(--accent-light); color: var(--accent-hover); padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 700; margin-left: 4px; }

        /* Modal Layout */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease; padding: 20px; }
        .modal.active { display: flex; opacity: 1; }
        .modal-card { width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 30px; box-shadow: var(--shadow-lg); }

        .modal-section-title {
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--accent);
            margin: 20px 0 12px;
            padding-bottom: 6px;
            border-bottom: 1px dashed var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .form-row-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--text-main); }
        .form-control { width: 100%; padding: 9px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-main); font-size: 13px; outline: none; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 2px var(--glow-color); }

        /* Dynamic Attributes Container */
        .dynamic-attributes-box {
            background: rgba(245, 158, 11, 0.04);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 14px;
        }

        .dynamic-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        /* Variants Matrix Container */
        .variants-matrix-box {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 14px;
            display: none;
        }

        .variants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .variants-table th, .variants-table td {
            padding: 8px 10px;
            font-size: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .variants-table input {
            width: 100%;
            padding: 6px 8px;
            font-size: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: var(--bg-card);
            color: var(--text-main);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="content-panel">
            <!-- Header Nav -->
            <header class="header-nav">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 20px;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 20px; font-weight: 700;"><?php echo $trans[$lang]['heading']; ?></h2>
                        <p style="font-size: 12px; color: var(--text-muted);">Dynamic Multi-Type Catalog & Variant Management</p>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Item Type Filter Tabs -->
            <div class="filter-tabs-bar" id="itemTypeFilterTabs">
                <div class="filter-tab-pill active" data-type-id="all" onclick="filterByItemType('all')">
                    <i class="fa-solid fa-layer-group"></i>
                    <span><?php echo $trans[$lang]['all_types']; ?></span>
                    <span class="filter-tab-badge" id="badgeAllCount">0</span>
                </div>
                <!-- Dynamic Item Types loaded via JS -->
            </div>

            <!-- Action Toolbar (Search & Buttons) -->
            <div class="action-toolbar">
                <div class="search-box-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="search-input" id="searchBox" placeholder="<?php echo $trans[$lang]['search_placeholder']; ?>" onkeyup="onSearchInput()">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-secondary" onclick="openBrandModal()">
                        <?php echo $trans[$lang]['add_brand']; ?>
                    </button>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <?php echo $trans[$lang]['add_new']; ?>
                    </button>
                </div>
            </div>

            <!-- Products Table Container -->
            <div class="data-table-container">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['tbl_barcode']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_name']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_type']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_brand']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_category']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_purchase']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_sale']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_unit']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_stock']; ?></th>
                            <th style="text-align: center;"><?php echo $trans[$lang]['tbl_actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="10" style="text-align: center; padding: 24px; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading catalog...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Product Add/Edit Modal Dialogue -->
    <div class="modal" id="productModal">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 id="modalTitle" style="font-family: var(--font-heading); font-size: 20px; font-weight: 700;">
                    <?php echo $trans[$lang]['new_product']; ?>
                </h3>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            
            <form id="productForm" onsubmit="saveProduct(event)">
                <input type="hidden" id="productId" name="id" value="">

                <!-- Section 1: Item Type & Brand Selection -->
                <div class="modal-section-title">
                    <i class="fa-solid fa-shapes"></i> Item Classification
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="productItemType"><?php echo $trans[$lang]['select_type']; ?> *</label>
                        <select class="form-control" id="productItemType" required onchange="onItemTypeChange()">
                            <!-- Dynamic Options -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productBrand">
                            Brand / Make
                            <a href="javascript:void(0)" onclick="openBrandModal()" style="font-size: 11px; color: var(--accent); margin-left: 4px;">[+ New]</a>
                        </label>
                        <select class="form-control" id="productBrand">
                            <option value=""><?php echo $trans[$lang]['select_brand']; ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productCategory"><?php echo $trans[$lang]['tbl_category']; ?></label>
                        <select class="form-control" id="productCategory">
                            <option value=""><?php echo $trans[$lang]['select_category']; ?></option>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Core Details -->
                <div class="modal-section-title">
                    <i class="fa-solid fa-tag"></i> Product Identification
                </div>

                <div class="form-row-3">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label" for="productName"><?php echo $trans[$lang]['tbl_name']; ?> *</label>
                        <input class="form-control" type="text" id="productName" placeholder="e.g. Royal Oud Eau De Parfum" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productBarcode">
                            Barcode / Code
                            <a href="javascript:void(0)" onclick="generateBarcode()" style="font-size: 11px; color: var(--accent); margin-left: 4px;">[🎲 Gen]</a>
                        </label>
                        <input class="form-control" type="text" id="productBarcode" placeholder="e.g. 896400123456">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="productSku">SKU Code (Optional)</label>
                        <input class="form-control" type="text" id="productSku" placeholder="e.g. PERF-RO-100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productUnit"><?php echo $trans[$lang]['tbl_unit']; ?></label>
                        <select class="form-control" id="productUnit">
                            <option value="Piece">Piece (Bottle/Item)</option>
                            <option value="Box">Box / Carton</option>
                            <option value="Packet">Packet</option>
                            <option value="Kg">Kg</option>
                            <option value="Gram">Gram / Tola</option>
                            <option value="Liter">Liter / ml</option>
                            <option value="Dozen">Dozen</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="expiryDate">Expiry Date (Optional)</label>
                        <input class="form-control" type="date" id="expiryDate">
                    </div>
                </div>

                <!-- Section 3: Dynamic Type Attributes Box -->
                <div class="modal-section-title" id="attrsHeader">
                    <i class="fa-solid fa-sliders"></i> <span id="attrsHeaderTitle">Item Specifications & Attributes</span>
                </div>
                <div class="dynamic-attributes-box">
                    <div class="dynamic-grid" id="dynamicAttributesGrid">
                        <!-- Loaded dynamically based on item type -->
                    </div>
                </div>

                <!-- Section 4: Pricing & Inventory -->
                <div class="modal-section-title">
                    <i class="fa-solid fa-coins"></i> Pricing & Stock
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="purchasePrice"><?php echo $trans[$lang]['tbl_purchase']; ?> *</label>
                        <input class="form-control" type="number" step="0.01" id="purchasePrice" required min="0" value="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="salePrice"><?php echo $trans[$lang]['tbl_sale']; ?> *</label>
                        <input class="form-control" type="number" step="0.01" id="salePrice" required min="0" value="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="minStock">Min Stock Alert Level</label>
                        <input class="form-control" type="number" step="0.01" id="minStock" value="5" min="0">
                    </div>
                </div>

                <div class="form-row-2" id="stockQtyRow">
                    <div class="form-group">
                        <label class="form-label" for="stockQty"><?php echo $trans[$lang]['tbl_stock']; ?> (Initial Quantity)</label>
                        <input class="form-control" type="number" step="0.01" id="stockQty" value="0" min="0">
                    </div>
                    <div></div>
                </div>

                <!-- Section 5: Variants Matrix Toggle -->
                <div style="margin: 18px 0;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" id="hasVariantsCheck" onchange="toggleVariantsMatrix()">
                        <span><?php echo $trans[$lang]['has_variants']; ?></span>
                    </label>
                </div>

                <div class="variants-matrix-box" id="variantsMatrixBox">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; font-size: 13px;"><?php echo $trans[$lang]['variants_title']; ?></span>
                        <button type="button" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;" onclick="addVariantRow()">
                            <?php echo $trans[$lang]['add_variant']; ?>
                        </button>
                    </div>

                    <table class="variants-table" id="variantsTable">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['var_label']; ?></th>
                                <th><?php echo $trans[$lang]['var_barcode']; ?></th>
                                <th><?php echo $trans[$lang]['var_purchase']; ?></th>
                                <th><?php echo $trans[$lang]['var_sale']; ?></th>
                                <th><?php echo $trans[$lang]['var_stock']; ?></th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="variantsTableBody">
                            <!-- Variant rows dynamically added here -->
                        </tbody>
                    </table>
                </div>

                <!-- Modal Actions -->
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <?php echo $trans[$lang]['cancel']; ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProduct">
                        <?php echo $trans[$lang]['save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Brand Add Modal -->
    <div class="modal" id="brandModal" style="z-index: 1050;">
        <div class="modal-card" style="max-width: 450px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-family: var(--font-heading); font-size: 18px; font-weight: 700;">Add New Brand</h3>
                <button type="button" onclick="closeBrandModal()" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            
            <form onsubmit="saveBrand(event)">
                <div class="form-group">
                    <label class="form-label">Brand Name *</label>
                    <input class="form-control" type="text" id="newBrandName" placeholder="e.g. Rasasi, Lattafa, J." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Country of Origin (Optional)</label>
                    <input class="form-control" type="text" id="newBrandCountry" placeholder="e.g. UAE, France, Pakistan">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea class="form-control" id="newBrandDesc" rows="2" placeholder="Brand notes..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeBrandModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const lang = "<?php echo $lang; ?>";
        const trans = <?php echo json_encode($trans[$lang]); ?>;

        let allProducts = [];
        let itemTypes = [];
        let categories = [];
        let brands = [];
        let currentFilterType = 'all';
        let currentTypeAttributes = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadInitialData();
        });

        async function loadInitialData() {
            await Promise.all([
                loadItemTypes(),
                loadCategories(),
                loadBrands()
            ]);
            loadProducts();
        }

        // 1. Load Item Types
        async function loadItemTypes() {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get-item-types' })
                });
                const json = await res.json();
                const resp = json.find(r => r.channel === 'item-types-list');
                if (resp && resp.data) {
                    itemTypes = resp.data.types;
                    renderItemTypeFilterTabs();
                    renderItemTypeSelectOptions();
                }
            } catch (err) {
                console.error('Error loading item types:', err);
            }
        }

        function renderItemTypeFilterTabs() {
            const tabsBar = document.getElementById('itemTypeFilterTabs');
            // Keep the 'All' pill and append active types
            const activeTypes = itemTypes.filter(t => t.is_store_active);
            
            let html = `
                <div class="filter-tab-pill ${currentFilterType === 'all' ? 'active' : ''}" data-type-id="all" onclick="filterByItemType('all')">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>${trans.all_types}</span>
                    <span class="filter-tab-badge" id="badgeAllCount">${allProducts.length}</span>
                </div>
            `;

            activeTypes.forEach(t => {
                const isActive = currentFilterType == t.id ? 'active' : '';
                html += `
                    <div class="filter-tab-pill ${isActive}" data-type-id="${t.id}" onclick="filterByItemType(${t.id})">
                        <span>${t.icon || '📦'}</span>
                        <span>${t.name}</span>
                        <span class="filter-tab-badge">${t.product_count || 0}</span>
                    </div>
                `;
            });

            tabsBar.innerHTML = html;
        }

        function renderItemTypeSelectOptions() {
            const select = document.getElementById('productItemType');
            select.innerHTML = `<option value="">${trans.select_type}</option>`;
            
            const activeTypes = itemTypes.filter(t => t.is_store_active);
            activeTypes.forEach(t => {
                select.innerHTML += `<option value="${t.id}">${t.icon || '📦'} ${t.name}</option>`;
            });
        }

        // 2. Load Categories
        async function loadCategories() {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get-categories' })
                });
                const json = await res.json();
                const resp = json.find(r => r.channel === 'categories-list');
                if (resp && resp.data) {
                    categories = resp.data;
                    const select = document.getElementById('productCategory');
                    select.innerHTML = `<option value="">${trans.select_category}</option>`;
                    categories.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                }
            } catch (err) {
                console.error('Error loading categories:', err);
            }
        }

        // 3. Load Brands
        async function loadBrands() {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get-brands' })
                });
                const json = await res.json();
                const resp = json.find(r => r.channel === 'brands-list');
                if (resp && resp.data && resp.data.brands) {
                    brands = resp.data.brands;
                    const select = document.getElementById('productBrand');
                    select.innerHTML = `<option value="">${trans.select_brand}</option>`;
                    brands.forEach(b => {
                        select.innerHTML += `<option value="${b.id}">${b.name} ${b.country ? '('+b.country+')' : ''}</option>`;
                    });
                }
            } catch (err) {
                console.error('Error loading brands:', err);
            }
        }

        // 4. Load Products
        function loadProducts() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-products' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'products-list');
                if (response && response.data) {
                    allProducts = response.data;
                    document.getElementById('badgeAllCount').innerText = allProducts.length;
                    filterAndRender();
                }
            });
        }

        function filterByItemType(typeId) {
            currentFilterType = typeId;
            document.querySelectorAll('.filter-tab-pill').forEach(pill => {
                if (pill.getAttribute('data-type-id') == typeId) {
                    pill.classList.add('active');
                } else {
                    pill.classList.remove('active');
                }
            });
            filterAndRender();
        }

        function onSearchInput() {
            filterAndRender();
        }

        function filterAndRender() {
            const query = document.getElementById('searchBox').value.trim().toLowerCase();
            
            const filtered = allProducts.filter(p => {
                // Type Filter
                if (currentFilterType !== 'all') {
                    if (parseInt(p.item_type_id) !== parseInt(currentFilterType)) {
                        return false;
                    }
                }

                // Query Filter
                if (query) {
                    const matchName = (p.name || '').toLowerCase().includes(query);
                    const matchBarcode = (p.barcode || '').toLowerCase().includes(query);
                    const matchBrand = (p.brand_name || '').toLowerCase().includes(query);
                    const matchCategory = (p.category_name || '').toLowerCase().includes(query);
                    const matchType = (p.item_type_name || '').toLowerCase().includes(query);
                    return matchName || matchBarcode || matchBrand || matchCategory || matchType;
                }

                return true;
            });

            renderTable(filtered);
        }

        function renderTable(products) {
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 28px; color: var(--text-muted); font-size: 14px;">No matching products found.</td></tr>`;
                return;
            }

            products.forEach(p => {
                const isLow = parseFloat(p.stock_qty) <= parseFloat(p.min_stock_threshold);
                const stockBadge = isLow 
                    ? `<span class="badge-low-stock">${p.stock_qty} ${p.unit}</span>`
                    : `<span class="badge-normal-stock">${p.stock_qty} ${p.unit}</span>`;

                const typeBadge = p.item_type_name
                    ? `<span class="type-pill-badge">${p.item_type_icon || '📦'} ${p.item_type_name}</span>`
                    : `<span style="color: var(--text-muted);">-</span>`;

                const brandBadge = p.brand_name
                    ? `<span class="brand-pill-badge">${p.brand_name}</span>`
                    : `<span style="color: var(--text-muted);">-</span>`;

                const variantBadge = p.has_variants == 1 
                    ? `<span class="badge-variant-count"><i class="fa-solid fa-code-fork"></i> Variants</span>`
                    : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-family: monospace; color: var(--text-muted);">${p.barcode || '-'}</td>
                    <td>
                        <span style="font-weight: 600; font-size: 14px;">${p.name}</span>
                        ${variantBadge}
                    </td>
                    <td>${typeBadge}</td>
                    <td>${brandBadge}</td>
                    <td><small style="color: var(--text-muted); font-weight: 500;">${p.category_name || trans.no_category}</small></td>
                    <td>Rs. ${parseFloat(p.purchase_price).toFixed(2)}</td>
                    <td style="font-weight: 700; color: var(--accent);">Rs. ${parseFloat(p.sale_price).toFixed(2)}</td>
                    <td>${p.unit}</td>
                    <td>${stockBadge}</td>
                    <td style="text-align: center; white-space: nowrap;">
                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="openEditModal(${p.id})">
                            ✏️ ${trans.btn_edit}
                        </button>
                        <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger);" onclick="deleteProduct(${p.id})">
                            🗑️
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // ================= DYNAMIC ITEM TYPE ATTRIBUTES =================
        async function onItemTypeChange(existingAttrs = {}) {
            const typeId = document.getElementById('productItemType').value;
            const container = document.getElementById('dynamicAttributesGrid');
            const titleSpan = document.getElementById('attrsHeaderTitle');

            if (!typeId) {
                container.innerHTML = `<span style="color: var(--text-muted); font-size: 12px;">Select an item type above to display specialized attributes.</span>`;
                return;
            }

            const selType = itemTypes.find(t => t.id == typeId);
            if (selType) {
                titleSpan.innerText = `${selType.icon || '🏷️'} ${selType.name} Specifications`;
            }

            container.innerHTML = `<span style="color: var(--text-muted); font-size: 12px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading specifications...</span>`;

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get-type-attributes', data: { item_type_id: typeId } })
                });
                const json = await res.json();
                const resp = json.find(r => r.channel === 'type-attributes-list');
                
                if (resp && resp.data && resp.data.attributes) {
                    currentTypeAttributes = resp.data.attributes;
                    renderDynamicAttributes(currentTypeAttributes, existingAttrs);
                } else {
                    container.innerHTML = `<span style="color: var(--text-muted); font-size: 12px;">No special attributes for this item type.</span>`;
                }
            } catch (err) {
                console.error('Error fetching type attributes:', err);
                container.innerHTML = `<span style="color: var(--danger); font-size: 12px;">Failed to load attributes.</span>`;
            }
        }

        function renderDynamicAttributes(attrs, values = {}) {
            const container = document.getElementById('dynamicAttributesGrid');
            container.innerHTML = '';

            if (attrs.length === 0) {
                container.innerHTML = `<span style="color: var(--text-muted); font-size: 12px;">Standard product fields only.</span>`;
                return;
            }

            attrs.forEach(attr => {
                const val = values[attr.attribute_key] ?? attr.default_value ?? '';
                const wrapper = document.createElement('div');
                wrapper.className = 'form-group';
                
                const label = document.createElement('label');
                label.className = 'form-label';
                label.innerHTML = `${attr.attribute_name} ${attr.is_variant ? '<span style="color: var(--accent); font-size: 10px;">(Variant)</span>' : ''}`;
                wrapper.appendChild(label);

                if (attr.field_type === 'select' && attr.options_array && attr.options_array.length > 0) {
                    const sel = document.createElement('select');
                    sel.className = 'form-control attr-input';
                    sel.setAttribute('data-attr-key', attr.attribute_key);
                    sel.innerHTML = `<option value="">Select ${attr.attribute_name}</option>`;
                    attr.options_array.forEach(opt => {
                        const isSel = (opt == val) ? 'selected' : '';
                        sel.innerHTML += `<option value="${opt}" ${isSel}>${opt}</option>`;
                    });
                    wrapper.appendChild(sel);
                } else if (attr.field_type === 'color') {
                    const colWrap = document.createElement('div');
                    colWrap.style.display = 'flex';
                    colWrap.style.gap = '8px';
                    colWrap.style.alignItems = 'center';
                    
                    const colInput = document.createElement('input');
                    colInput.type = 'color';
                    colInput.value = val || '#000000';
                    colInput.style.width = '36px';
                    colInput.style.height = '36px';
                    colInput.style.border = 'none';
                    colInput.style.borderRadius = '4px';
                    colInput.style.cursor = 'pointer';

                    const txtInput = document.createElement('input');
                    txtInput.type = 'text';
                    txtInput.className = 'form-control attr-input';
                    txtInput.setAttribute('data-attr-key', attr.attribute_key);
                    txtInput.value = val;
                    txtInput.placeholder = 'e.g. Navy Blue / #1e3a8a';

                    colInput.addEventListener('input', (e) => {
                        txtInput.value = e.target.value;
                    });

                    colWrap.appendChild(colInput);
                    colWrap.appendChild(txtInput);
                    wrapper.appendChild(colWrap);
                } else if (attr.field_type === 'date') {
                    const input = document.createElement('input');
                    input.type = 'date';
                    input.className = 'form-control attr-input';
                    input.setAttribute('data-attr-key', attr.attribute_key);
                    input.value = val;
                    wrapper.appendChild(input);
                } else if (attr.field_type === 'textarea') {
                    const ta = document.createElement('textarea');
                    ta.className = 'form-control attr-input';
                    ta.setAttribute('data-attr-key', attr.attribute_key);
                    ta.rows = 2;
                    ta.value = val;
                    wrapper.appendChild(ta);
                } else {
                    const input = document.createElement('input');
                    input.type = (attr.field_type === 'number') ? 'number' : 'text';
                    input.className = 'form-control attr-input';
                    input.setAttribute('data-attr-key', attr.attribute_key);
                    input.value = val;
                    input.placeholder = attr.placeholder || `Enter ${attr.attribute_name}`;
                    wrapper.appendChild(input);
                }

                container.appendChild(wrapper);
            });
        }

        // ================= VARIANTS MATRIX =================
        function toggleVariantsMatrix() {
            const check = document.getElementById('hasVariantsCheck');
            const box = document.getElementById('variantsMatrixBox');
            box.style.display = check.checked ? 'block' : 'none';
            if (check.checked && document.querySelectorAll('#variantsTableBody tr').length === 0) {
                addVariantRow();
            }
        }

        function addVariantRow(data = {}) {
            const tbody = document.getElementById('variantsTableBody');
            const tr = document.createElement('tr');
            
            const defPurchase = data.purchase_price ?? document.getElementById('purchasePrice').value ?? 0;
            const defSale = data.sale_price ?? document.getElementById('salePrice').value ?? 0;
            const defLabel = data.variant_label ?? '';
            const defBarcode = data.barcode ?? '';
            const defStock = data.stock_qty ?? 0;

            tr.innerHTML = `
                <td><input type="text" class="v-label" value="${defLabel}" placeholder="e.g. 50ml or Large" required></td>
                <td><input type="text" class="v-barcode" value="${defBarcode}" placeholder="Unique Barcode"></td>
                <td><input type="number" step="0.01" class="v-purchase" value="${defPurchase}"></td>
                <td><input type="number" step="0.01" class="v-sale" value="${defSale}"></td>
                <td><input type="number" step="0.01" class="v-stock" value="${defStock}"></td>
                <td style="text-align: center;">
                    <button type="button" onclick="this.closest('tr').remove()" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 14px;">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        function generateBarcode() {
            // Generates standard 12-digit random barcode starting with 89
            const randomSuffix = Math.floor(1000000000 + Math.random() * 9000000000);
            document.getElementById('productBarcode').value = '89' + randomSuffix.toString().substring(0, 10);
        }

        // ================= MODAL OPEN/CLOSE =================
        function openAddModal() {
            document.getElementById('productId').value = '';
            document.getElementById('productBarcode').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productBrand').value = '';
            document.getElementById('productSku').value = '';
            document.getElementById('productUnit').value = 'Piece';
            document.getElementById('purchasePrice').value = '0.00';
            document.getElementById('salePrice').value = '0.00';
            document.getElementById('stockQty').value = '0';
            document.getElementById('minStock').value = '5';
            document.getElementById('expiryDate').value = '';
            document.getElementById('hasVariantsCheck').checked = false;
            document.getElementById('variantsTableBody').innerHTML = '';
            document.getElementById('variantsMatrixBox').style.display = 'none';

            // Auto-select first active type or current filtered type
            const selType = (currentFilterType !== 'all') ? currentFilterType : (itemTypes[0] ? itemTypes[0].id : '');
            document.getElementById('productItemType').value = selType;
            onItemTypeChange();

            document.getElementById('stockQtyRow').style.display = 'grid';
            document.getElementById('modalTitle').innerText = trans.new_product;

            document.getElementById('productModal').classList.add('active');
        }

        async function openEditModal(productId) {
            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get-product-details', data: { id: productId } })
                });
                const json = await res.json();
                const resp = json.find(r => r.channel === 'product-details');

                if (!resp || !resp.data || !resp.data.success) {
                    alert('Could not fetch product details');
                    return;
                }

                const p = resp.data.product;
                const attrs = resp.data.attributes || {};
                const variants = resp.data.variants || [];

                document.getElementById('productId').value = p.id;
                document.getElementById('productBarcode').value = p.barcode || '';
                document.getElementById('productName').value = p.name;
                document.getElementById('productCategory').value = p.category_id || '';
                document.getElementById('productBrand').value = p.brand_id || '';
                document.getElementById('productSku').value = p.sku || '';
                document.getElementById('productUnit').value = p.unit || 'Piece';
                document.getElementById('purchasePrice').value = p.purchase_price;
                document.getElementById('salePrice').value = p.sale_price;
                document.getElementById('minStock').value = p.min_stock_threshold;
                document.getElementById('expiryDate').value = p.expiry_date || '';

                // Select item type and populate dynamic attributes
                document.getElementById('productItemType').value = p.item_type_id || '';
                await onItemTypeChange(attrs);

                // Handle variants
                const hasVars = p.has_variants == 1;
                document.getElementById('hasVariantsCheck').checked = hasVars;
                document.getElementById('variantsMatrixBox').style.display = hasVars ? 'block' : 'none';
                
                const vTbody = document.getElementById('variantsTableBody');
                vTbody.innerHTML = '';
                if (variants.length > 0) {
                    variants.forEach(v => addVariantRow(v));
                }

                document.getElementById('stockQtyRow').style.display = 'none'; // Stock adjusted via Inventory
                document.getElementById('modalTitle').innerText = trans.edit_product;

                document.getElementById('productModal').classList.add('active');
            } catch (err) {
                console.error('Error opening edit modal:', err);
            }
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        // ================= SAVE PRODUCT =================
        function saveProduct(e) {
            e.preventDefault();

            const id = document.getElementById('productId').value;
            const barcode = document.getElementById('productBarcode').value.trim();
            const name = document.getElementById('productName').value.trim();
            const item_type_id = document.getElementById('productItemType').value;
            const brand_id = document.getElementById('productBrand').value;
            const category_id = document.getElementById('productCategory').value;
            const sku = document.getElementById('productSku').value.trim();
            const unit = document.getElementById('productUnit').value;
            const purchase_price = parseFloat(document.getElementById('purchasePrice').value);
            const sale_price = parseFloat(document.getElementById('salePrice').value);
            const stock_qty = parseFloat(document.getElementById('stockQty').value) || 0;
            const min_stock_threshold = parseFloat(document.getElementById('minStock').value) || 5;
            const expiry_date = document.getElementById('expiryDate').value;
            const has_variants = document.getElementById('hasVariantsCheck').checked ? 1 : 0;

            // Collect dynamic attributes
            const attributes = {};
            document.querySelectorAll('.attr-input').forEach(inp => {
                const key = inp.getAttribute('data-attr-key');
                if (key) {
                    attributes[key] = inp.value;
                }
            });

            // Collect variants if has_variants is true
            const variants = [];
            if (has_variants) {
                document.querySelectorAll('#variantsTableBody tr').forEach(row => {
                    const label = row.querySelector('.v-label').value.trim();
                    if (label) {
                        variants.push({
                            variant_label: label,
                            barcode: row.querySelector('.v-barcode').value.trim() || null,
                            purchase_price: parseFloat(row.querySelector('.v-purchase').value) || purchase_price,
                            sale_price: parseFloat(row.querySelector('.v-sale').value) || sale_price,
                            stock_qty: parseFloat(row.querySelector('.v-stock').value) || 0
                        });
                    }
                });
            }

            const btnSave = document.getElementById('btnSaveProduct');
            btnSave.disabled = true;
            btnSave.innerText = 'Saving...';

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-product',
                    data: {
                        id, barcode, name, item_type_id, brand_id, category_id, sku, unit,
                        purchase_price, sale_price, stock_qty, min_stock_threshold, expiry_date,
                        has_variants, attributes, variants
                    }
                })
            })
            .then(res => res.json())
            .then(data => {
                const resp = data.find(r => r.channel === 'product-saved');
                btnSave.disabled = false;
                btnSave.innerText = trans.save;

                if (resp && resp.data.success) {
                    closeModal();
                    loadProducts();
                    loadItemTypes(); // Refresh counts
                } else {
                    alert(resp ? resp.data.msg : 'Error saving product');
                }
            })
            .catch(err => {
                btnSave.disabled = false;
                btnSave.innerText = trans.save;
                alert('Connection error while saving product');
            });
        }

        function deleteProduct(id) {
            if (confirm(trans.confirm_delete)) {
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete-product',
                        data: { id }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const resp = data.find(r => r.channel === 'product-deleted');
                    if (resp && resp.data.success) {
                        loadProducts();
                        loadItemTypes();
                    } else {
                        alert(resp ? resp.data.msg : 'Error deleting product');
                    }
                });
            }
        }

        // ================= BRAND MODAL =================
        function openBrandModal() {
            document.getElementById('newBrandName').value = '';
            document.getElementById('newBrandCountry').value = '';
            document.getElementById('newBrandDesc').value = '';
            document.getElementById('brandModal').classList.add('active');
        }

        function closeBrandModal() {
            document.getElementById('brandModal').classList.remove('active');
        }

        function saveBrand(e) {
            e.preventDefault();
            const name = document.getElementById('newBrandName').value.trim();
            const country = document.getElementById('newBrandCountry').value.trim();
            const description = document.getElementById('newBrandDesc').value.trim();

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-brand',
                    data: { name, country, description }
                })
            })
            .then(res => res.json())
            .then(data => {
                const resp = data.find(r => r.channel === 'brand-saved');
                if (resp && resp.data.success) {
                    closeBrandModal();
                    loadBrands();
                } else {
                    alert(resp ? resp.data.msg : 'Error adding brand');
                }
            });
        }

        function toggleLanguage() {
            const currentLang = "<?php echo $lang; ?>";
            const newLang = (currentLang === 'en') ? 'ur' : 'en';
            document.cookie = "lang=" + newLang + "; path=/; max-age=" + (365*24*60*60);
            window.location.reload();
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            document.cookie = "theme=" + newTheme + "; path=/; max-age=" + (365*24*60*60);
            window.location.reload();
        }
    </script>
</body>
</html>
