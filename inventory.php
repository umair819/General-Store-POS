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
        'title' => 'Tijarat Inventory - Stock Management',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '📊 Stock & Inventory Adjustments',
        'adjust_stock' => 'Adjust Stock',
        'tbl_name' => 'Product Name',
        'tbl_barcode' => 'Barcode',
        'tbl_category' => 'Category',
        'tbl_stock' => 'Current Stock',
        'tbl_min' => 'Min Threshold',
        'tbl_expiry' => 'Expiry Date',
        'tbl_action' => 'Action',
        'status_critical' => 'Critical / Low Stock',
        'status_expiring' => 'Expiring Soon',
        'status_normal' => 'Normal Stock',
        'btn_adjust' => 'Update Stock',
        'save' => 'Apply Adjustment',
        'cancel' => 'Cancel',
        'quantity_adj' => 'Quantity (+ to add, - to subtract)',
        'no_category' => 'Uncategorized',
    ],
    'ur' => [
        'title' => 'تجارت انوینٹری - اسٹاک کا انتظام',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 اسٹاک اور انوینٹری',
        'menu_customers' => '👥 Tijarat Ledger (کھاتہ)',
        'menu_purchases' => '🧾 خریداری',
        'menu_reports' => '📈 سیلز رپورٹ',
        'menu_settings' => '⚙️ سیٹنگز',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'logout' => '🚪 لاگ آؤٹ',
        'heading' => '📊 اسٹاک اور انوینٹری ایڈجسٹمنٹ',
        'adjust_stock' => 'اسٹاک ایڈجسٹ کریں',
        'tbl_name' => 'پروڈکٹ کا نام',
        'tbl_barcode' => 'بارکوڈ',
        'tbl_category' => 'کیٹیگری',
        'tbl_stock' => 'موجودہ اسٹاک',
        'tbl_min' => 'الرٹ حد',
        'tbl_expiry' => 'تاریخ ختم شدگی (Expiry)',
        'tbl_action' => 'ایکشن',
        'status_critical' => 'انتہائی کم اسٹاک',
        'status_expiring' => 'جلد ختم ہونے والے (Expiring)',
        'status_normal' => 'نارمل اسٹاک',
        'btn_adjust' => 'اسٹاک تبدیل کریں',
        'save' => 'ایڈجسٹمنٹ لاگو کریں',
        'cancel' => 'منسوخ',
        'quantity_adj' => 'مقدار (شامل کرنے کے لیے +، نکالنے کے لیے -)',
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
        
        /* Stats Panel */
        .stats-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .stat-box { padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: var(--bg-card); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
        
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
        .modal-card { width: 100%; max-width: 450px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal.active .modal-card { transform: scale(1); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }

        /* Badges */
        .badge-red { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .badge-orange { background-color: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid var(--warning); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .badge-green { background-color: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid var(--success); padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }

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

            <!-- Stats summary panel -->
            <div class="stats-summary" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                <div class="stat-box" style="border-left: 5px solid var(--success);">
                    <div>
                        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;"><?php echo $trans[$lang]['status_normal']; ?></div>
                        <div style="font-size: 24px; font-weight: 700; margin-top: 4px;" id="normalCount">0</div>
                    </div>
                    <div style="font-size: 32px;">✅</div>
                </div>
                <div class="stat-box" style="border-left: 5px solid var(--warning);">
                    <div>
                        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;"><?php echo $trans[$lang]['status_critical']; ?></div>
                        <div style="font-size: 24px; font-weight: 700; margin-top: 4px;" id="criticalCount">0</div>
                    </div>
                    <div style="font-size: 32px;">⚠️</div>
                </div>
                <div class="stat-box" style="border-left: 5px solid var(--danger);">
                    <div>
                        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;"><?php echo $trans[$lang]['status_expiring']; ?></div>
                        <div style="font-size: 24px; font-weight: 700; margin-top: 4px;" id="expiryCount">0</div>
                    </div>
                    <div style="font-size: 32px;">⏰</div>
                </div>
            </div>

            <!-- Stock Table -->
            <div class="data-table-container">
                <table class="data-table" id="stockTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['tbl_barcode']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_name']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_category']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_stock']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_min']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_expiry']; ?></th>
                            <th style="text-align: center;"><?php echo $trans[$lang]['tbl_action']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Loading inventory...</td></tr>
                    </tbody>
                </table>
            </div>

        </main>

    </div>

    <!-- Stock Adjust Modal -->
    <div class="modal" id="adjustModal">
        <div class="card modal-card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
            <h3 style="margin-bottom: 20px;" id="adjustTitle">Adjust Stock</h3>
            
            <form id="adjustForm" onsubmit="applyAdjustment(event)">
                <input type="hidden" id="adjustProductId" name="id" value="">
                
                <div class="form-group">
                    <label class="form-label" for="adjustQty"><?php echo $trans[$lang]['quantity_adj']; ?></label>
                    <input class="form-control" type="number" step="0.01" id="adjustQty" required placeholder="e.g. 10 or -5">
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

        document.addEventListener('DOMContentLoaded', loadStock);

        function loadStock() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-products' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'products-list');
                if (response && response.data) {
                    processInventory(response.data);
                }
            });
        }

        function processInventory(products) {
            let normal = 0;
            let critical = 0;
            let expiring = 0;
            const now = new Date();

            const tbody = document.querySelector('#stockTable tbody');
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No inventory items found.</td></tr>`;
                return;
            }

            products.forEach(p => {
                const isLow = parseFloat(p.stock_qty) <= parseFloat(p.min_stock_threshold);
                
                // Expiry calculation
                let isExpiringSoon = false;
                if (p.expiry_date) {
                    const expiry = new Date(p.expiry_date);
                    const diffTime = expiry - now;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays >= 0 && diffDays <= 30) {
                        isExpiringSoon = true;
                    }
                }

                // Increment counts
                if (isExpiringSoon) expiring++;
                else if (isLow) critical++;
                else normal++;

                // Badges
                let statusBadge = `<span class="badge-green">${p.stock_qty} ${p.unit}</span>`;
                if (isExpiringSoon) {
                    statusBadge = `<span class="badge-red">${p.stock_qty} ${p.unit} (Expiring)</span>`;
                } else if (isLow) {
                    statusBadge = `<span class="badge-orange">${p.stock_qty} ${p.unit} (Low)</span>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-family: monospace; color: var(--text-muted);">${p.barcode || '-'}</td>
                    <td style="font-weight: 500;">${p.name}</td>
                    <td><small style="color: var(--text-muted); font-weight: 500;">${p.category_name || trans.no_category}</small></td>
                    <td>${statusBadge}</td>
                    <td>${p.min_stock_threshold}</td>
                    <td><span style="font-size: 13px; font-weight: 500; ${isExpiringSoon ? 'color: var(--danger); font-weight: bold;' : ''}">${p.expiry_date || '-'}</span></td>
                    <td style="text-align: center;">
                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick="openAdjustModal(${p.id}, '${p.name.replace(/'/g, "\\'")}')">
                            🔄 ${trans.btn_adjust}
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('normalCount').innerText = normal;
            document.getElementById('criticalCount').innerText = critical;
            document.getElementById('expiryCount').innerText = expiring;
        }

        function openAdjustModal(id, name) {
            document.getElementById('adjustProductId').value = id;
            document.getElementById('adjustQty').value = '';
            document.getElementById('adjustTitle').innerText = trans.adjust_stock + ' - ' + name;
            
            const modal = document.getElementById('adjustModal');
            modal.classList.add('active');
        }

        function closeModal() {
            const modal = document.getElementById('adjustModal');
            modal.classList.remove('active');
        }

        function applyAdjustment(e) {
            e.preventDefault();
            const id = document.getElementById('adjustProductId').value;
            const adjustment = document.getElementById('adjustQty').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'adjust-stock',
                    data: { id, adjustment }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'stock-adjusted');
                if (response && response.data.success) {
                    closeModal();
                    loadStock();
                } else {
                    alert(response ? response.data.msg : 'Error adjusting stock');
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
