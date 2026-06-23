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
        'title' => 'Tijarat Inventory - Products',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '📦 Manage Products',
        'add_new' => '➕ Add Product',
        'tbl_barcode' => 'Barcode / Code',
        'tbl_name' => 'Product Name',
        'tbl_category' => 'Category',
        'tbl_purchase' => 'Purchase Price',
        'tbl_sale' => 'Sale Price',
        'tbl_unit' => 'Unit',
        'tbl_stock' => 'Stock',
        'tbl_min_stock' => 'Min Alert Limit',
        'tbl_expiry' => 'Expiry Date',
        'tbl_actions' => 'Actions',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'edit_product' => 'Edit Product',
        'new_product' => 'New Product',
        'confirm_delete' => 'Are you sure you want to delete this product?',
        'select_category' => 'Select Category',
        'no_category' => 'Uncategorized',
    ],
    'ur' => [
        'title' => 'تجارت انوینٹری - پروڈکٹس',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 اسٹاک اور انوینٹری',
        'menu_customers' => '👥 Tijarat Ledger (کھاتہ)',
        'menu_purchases' => '🧾 خریداری',
        'menu_reports' => '📈 سیلز رپورٹ',
        'menu_settings' => '⚙️ سیٹنگز',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'logout' => '🚪 لاگ آؤٹ',
        'heading' => '📦 پروڈکٹس کا انتظام',
        'add_new' => '➕ نئی پروڈکٹ',
        'tbl_barcode' => 'بارکوڈ / کوڈ',
        'tbl_name' => 'پروڈکٹ کا نام',
        'tbl_category' => 'کیٹیگری',
        'tbl_purchase' => 'خریداری کی قیمت',
        'tbl_sale' => 'فروخت کی قیمت',
        'tbl_unit' => 'یونٹ',
        'tbl_stock' => 'اسٹاک',
        'tbl_min_stock' => 'کم سے کم الرٹ حد',
        'tbl_expiry' => 'تاریخ ختم شدگی (Expiry)',
        'tbl_actions' => 'ایکشنز',
        'btn_edit' => 'ترمیم',
        'btn_delete' => 'خارج کریں',
        'save' => 'محفوظ کریں',
        'cancel' => 'منسوخ',
        'edit_product' => 'پروڈکٹ تبدیل کریں',
        'new_product' => 'نئی پروڈکٹ',
        'confirm_delete' => 'کیا آپ واقعی اس پروڈکٹ کو خارج کرنا چاہتے ہیں؟',
        'select_category' => 'کیٹیگری منتخب کریں',
        'no_category' => 'بغیر کیٹیگری',
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
    <style>
        /* Shared Sidebar/Header Layout */
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 24px; height: 100vh; }
        .sidebar-brand { font-family: var(--font-heading); font-size: 24px; font-weight: 700; color: var(--accent); margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
        .menu-link { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-radius: var(--radius-sm); color: var(--text-main); text-decoration: none; font-family: var(--font-heading); font-weight: 500; transition: var(--transition-smooth); }
        .menu-link:hover, .menu-link.active { background-color: var(--bg-input); color: var(--accent); border-left: 4px solid var(--accent); padding-left: 16px; }
        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }
        
        /* Table Styling */
        .data-table-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 14px 18px; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); text-transform: uppercase; font-size: 12px; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background-color: var(--bg-app); }

        /* Modal Overlay & Form Columns */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease; }
        .modal.active { display: flex; opacity: 1; }
        .modal-card { width: 100%; max-width: 650px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal.active .modal-card { transform: scale(1); }
        
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }

        /* Low stock highlight */
        .badge-low-stock { background-color: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid var(--warning); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .badge-normal-stock { background-color: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid var(--success); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }

        /* RTL Layout overrides */
        .lang-urdu .sidebar { border-right: none; border-left: 1px solid var(--border-color); }
        .lang-urdu .menu-link:hover, .lang-urdu .menu-link.active { border-left: none; border-right: 4px solid var(--accent); padding-left: 20px; padding-right: 16px; }
        .lang-urdu .data-table { text-align: right; }
    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        
        <!-- Sidebar Navigation Drawer -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="TijaratPro.png" alt="TijaratPro" style="width: 28px; height: 28px; border-radius: 6px; vertical-align: middle; margin-right: 6px;"> <?php echo ($lang === 'ur') ? 'تجارت پرو' : 'TijaratPro'; ?>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php" class="menu-link">🏠 <?php echo $trans[$lang]['dashboard']; ?></a></li>
                <li><a href="billing.php" class="menu-link">🛒 <?php echo $trans[$lang]['menu_billing']; ?></a></li>
                <li><a href="products.php" class="menu-link active">📦 <?php echo $trans[$lang]['menu_inventory']; ?></a></li>
                <li><a href="customers.php" class="menu-link">👥 <?php echo $trans[$lang]['menu_customers']; ?></a></li>
                <li><a href="purchases.php" class="menu-link">🧾 <?php echo $trans[$lang]['menu_purchases']; ?></a></li>
                <li><a href="marketing.php" class="menu-link">📢 <?php echo $trans[$lang]['menu_marketing']; ?></a></li>
                <li><a href="settings.php" class="menu-link">⚙️ <?php echo $trans[$lang]['menu_settings']; ?></a></li>
            </ul>

            <div style="margin-top: auto;">
                <a href="index.php?action=logout" class="menu-link" style="color: var(--danger); border-left: none !important; border-right: none !important;">
                    <?php echo $trans[$lang]['logout']; ?>
                </a>
            </div>
        </aside>

        <!-- Main Display Panel -->
        <main class="content-panel">
            
            <!-- Header Nav -->
            <header class="header-nav">
                <h2 style="font-size: 22px; font-weight: 600;">
                    <?php echo $trans[$lang]['heading']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Actions Bar -->
            <div style="display: flex; justify-content: <?php echo ($lang === 'ur') ? 'flex-start' : 'flex-end'; ?>;">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <?php echo $trans[$lang]['add_new']; ?>
                </button>
            </div>

            <!-- Products Data Table -->
            <div class="data-table-container">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['tbl_barcode']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_name']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_category']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_purchase']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_sale']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_unit']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_stock']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_expiry']; ?></th>
                            <th style="text-align: center;"><?php echo $trans[$lang]['tbl_actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Products dynamically loaded here -->
                        <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">Loading products...</td></tr>
                    </tbody>
                </table>
            </div>

        </main>

    </div>

    <!-- Product Add/Edit Modal Dialogue -->
    <div class="modal" id="productModal">
        <div class="card modal-card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
            <h3 id="modalTitle" style="margin-bottom: 20px;">Product</h3>
            
            <form id="productForm" onsubmit="saveProduct(event)">
                <input type="hidden" id="productId" name="id" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="productBarcode"><?php echo $trans[$lang]['tbl_barcode']; ?></label>
                        <input class="form-control" type="text" id="productBarcode" placeholder="e.g. 12345678">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productName"><?php echo $trans[$lang]['tbl_name']; ?></label>
                        <input class="form-control" type="text" id="productName" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="productCategory"><?php echo $trans[$lang]['tbl_category']; ?></label>
                        <select class="form-control" id="productCategory">
                            <option value=""><?php echo $trans[$lang]['select_category']; ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="productUnit"><?php echo $trans[$lang]['tbl_unit']; ?></label>
                        <select class="form-control" id="productUnit">
                            <option value="Piece">Piece</option>
                            <option value="Packet">Packet</option>
                            <option value="Kg">Kg</option>
                            <option value="Gram">Gram</option>
                            <option value="Dozen">Dozen</option>
                            <option value="Liter">Liter</option>
                            <option value="Box">Box</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="purchasePrice"><?php echo $trans[$lang]['tbl_purchase']; ?></label>
                        <input class="form-control" type="number" step="0.01" id="purchasePrice" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="salePrice"><?php echo $trans[$lang]['tbl_sale']; ?></label>
                        <input class="form-control" type="number" step="0.01" id="salePrice" required min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="stockQtyRow">
                        <label class="form-label" for="stockQty"><?php echo $trans[$lang]['tbl_stock']; ?> (Initial)</label>
                        <input class="form-control" type="number" step="0.01" id="stockQty" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="minStock"><?php echo $trans[$lang]['tbl_min_stock']; ?></label>
                        <input class="form-control" type="number" step="0.01" id="minStock" value="5" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="expiryDate"><?php echo $trans[$lang]['tbl_expiry']; ?></label>
                        <input class="form-control" type="date" id="expiryDate">
                    </div>
                    <div></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <?php echo $trans[$lang]['cancel']; ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $trans[$lang]['save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const lang = "<?php echo $lang; ?>";
        const trans = <?php echo json_encode($trans[$lang]); ?>;
        let categories = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadCategories();
            loadProducts();
        });

        function loadCategories() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-categories' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'categories-list');
                if (response && response.data) {
                    categories = response.data;
                    const select = document.getElementById('productCategory');
                    select.innerHTML = `<option value="">${trans.select_category}</option>`;
                    categories.forEach(cat => {
                        select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                }
            });
        }

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
                    renderTable(response.data);
                }
            });
        }

        function renderTable(products) {
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--text-muted);">No products found.</td></tr>`;
                return;
            }

            products.forEach(p => {
                const isLow = parseFloat(p.stock_qty) <= parseFloat(p.min_stock_threshold);
                const stockBadge = isLow 
                    ? `<span class="badge-low-stock">${p.stock_qty} ${p.unit}</span>`
                    : `<span class="badge-normal-stock">${p.stock_qty} ${p.unit}</span>`;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-family: monospace; color: var(--text-muted);">${p.barcode || '-'}</td>
                    <td style="font-weight: 500;">${p.name}</td>
                    <td><small style="color: var(--text-muted); font-weight: 500;">${p.category_name || trans.no_category}</small></td>
                    <td>${parseFloat(p.purchase_price).toFixed(2)}</td>
                    <td>${parseFloat(p.sale_price).toFixed(2)}</td>
                    <td>${p.unit}</td>
                    <td>${stockBadge}</td>
                    <td><span style="font-size: 13px;">${p.expiry_date || '-'}</span></td>
                    <td style="text-align: center;">
                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick='openEditModal(${JSON.stringify(p)})'>
                            ✏️ ${trans.btn_edit}
                        </button>
                        <button class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="deleteProduct(${p.id})">
                            🗑️ ${trans.btn_delete}
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openAddModal() {
            document.getElementById('productId').value = '';
            document.getElementById('productBarcode').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productUnit').value = 'Piece';
            document.getElementById('purchasePrice').value = '';
            document.getElementById('salePrice').value = '';
            document.getElementById('stockQty').value = '0';
            document.getElementById('minStock').value = '5';
            document.getElementById('expiryDate').value = '';
            
            document.getElementById('stockQtyRow').style.display = 'block'; // Show stock only on create
            document.getElementById('modalTitle').innerText = trans.new_product;
            
            const modal = document.getElementById('productModal');
            modal.classList.add('active');
        }

        function openEditModal(p) {
            document.getElementById('productId').value = p.id;
            document.getElementById('productBarcode').value = p.barcode || '';
            document.getElementById('productName').value = p.name;
            document.getElementById('productCategory').value = p.category_id || '';
            document.getElementById('productUnit').value = p.unit || 'Piece';
            document.getElementById('purchasePrice').value = p.purchase_price;
            document.getElementById('salePrice').value = p.sale_price;
            document.getElementById('minStock').value = p.min_stock_threshold;
            document.getElementById('expiryDate').value = p.expiry_date || '';
            
            document.getElementById('stockQtyRow').style.display = 'none'; // Hide stock on update
            document.getElementById('modalTitle').innerText = trans.edit_product;
            
            const modal = document.getElementById('productModal');
            modal.classList.add('active');
        }

        function closeModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('active');
        }

        function saveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('productId').value;
            const barcode = document.getElementById('productBarcode').value;
            const name = document.getElementById('productName').value;
            const category_id = document.getElementById('productCategory').value;
            const unit = document.getElementById('productUnit').value;
            const purchase_price = document.getElementById('purchasePrice').value;
            const sale_price = document.getElementById('salePrice').value;
            const stock_qty = document.getElementById('stockQty').value;
            const min_stock_threshold = document.getElementById('minStock').value;
            const expiry_date = document.getElementById('expiryDate').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-product',
                    data: { id, barcode, name, category_id, unit, purchase_price, sale_price, stock_qty, min_stock_threshold, expiry_date }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'product-saved');
                if (response && response.data.success) {
                    closeModal();
                    loadProducts();
                } else {
                    alert(response ? response.data.msg : 'Error saving product');
                }
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
                    const response = data.find(r => r.channel === 'product-deleted');
                    if (response && response.data.success) {
                        loadProducts();
                    } else {
                        alert(response ? response.data.msg : 'Error deleting product');
                    }
                });
            }
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
