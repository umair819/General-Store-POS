<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

// Fetch initial data
$products_list = dbQuery("SELECT id, name, barcode, purchase_price FROM products ORDER BY name ASC");

$trans = [
    'en' => [
        'title' => 'Purchases & Supplier Management',
        'menu_billing' => '🛒 POS Billing',
        'menu_inventory' => '📦 Stock/Inventory',
        'menu_customers' => '👥 Customer Khata',
        'menu_purchases' => '🧾 Purchases',
        'menu_marketing' => '📢 Marketing Tool',
        'menu_settings' => '⚙️ Settings',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        
        'tab_purchases' => 'Purchases Log',
        'tab_suppliers' => 'Suppliers Registry',
        'btn_new_purchase' => 'New Stock Purchase',
        'btn_add_supplier' => 'Add Supplier',
        
        'search_suppliers' => 'Search suppliers...',
        'search_purchases' => 'Search purchases...',
        
        // Supplier Columns
        'supp_name' => 'Supplier / Company Name',
        'supp_contact' => 'Contact Person',
        'supp_phone' => 'Phone',
        'supp_address' => 'Address',
        'supp_balance' => 'Outstanding Balance (We Owe)',
        'actions' => 'Actions',
        
        // Purchase Columns
        'pur_no' => 'Purchase Bill No.',
        'pur_supplier' => 'Supplier',
        'pur_total' => 'Total Bill',
        'pur_paid' => 'Paid Amount',
        'pur_balance' => 'Balance Due',
        'pur_date' => 'Date',
        
        // Form Fields
        'save' => 'Save Supplier',
        'edit_supplier' => 'Edit Supplier Details',
        'close' => 'Close',
        'delete_confirm' => 'Are you sure you want to delete this supplier?',
        
        // New Purchase Form
        'new_purchase_title' => 'Record Stock Purchase Invoice',
        'select_supplier' => 'Select Supplier',
        'walk_in' => '-- Walk-in Supplier --',
        'add_item' => 'Add Product',
        'product' => 'Product Name',
        'qty' => 'Quantity',
        'price' => 'Purchase Price',
        'expiry' => 'Expiry Date (optional)',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'total' => 'Total',
        'paid' => 'Amount Paid',
        'balance' => 'Balance Due (Credit)',
        'save_purchase' => 'Save Purchase & Update Stock',
        
        // AI OCR Scanning
        'ai_ocr_btn' => '🤖 Scan Bill using Gemini AI',
        'ocr_title' => 'Gemini AI OCR Bill Scanner',
        'ocr_drag_text' => 'Drag and drop your invoice image here or click to select',
        'ocr_allowed' => 'Supported formats: PNG, JPG, JPEG',
        'ocr_processing' => 'Extracting invoice details using Gemini AI...',
        'ocr_match_title' => 'Link Scanned Items to Product Catalog',
        'ocr_scanned_item' => 'Scanned Item',
        'ocr_matching_product' => 'Matched Product in Catalog',
        'ocr_new_product' => '[ Create as New Product ]',
        'apply_invoice' => 'Apply to Invoice Form',
    ],
    'ur' => [
        'title' => 'خریداری اور سپلائرز کی تفصیل',
        'menu_billing' => '🛒 بلنگ / سیلز',
        'menu_inventory' => '📦 اسٹاک / انوینٹری',
        'menu_customers' => '👥 کسٹمر کھاتہ',
        'menu_purchases' => '🧾 خریداری',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'menu_settings' => '⚙️ سیٹنگز',
        'logout' => '🚪 لاگ آؤٹ',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        
        'tab_purchases' => 'خریداری کا ریکارڈ',
        'tab_suppliers' => 'سپلائرز کی فہرست',
        'btn_new_purchase' => 'نیا اسٹاک خریدیں',
        'btn_add_supplier' => 'نیا سپلائر شامل کریں',
        
        'search_suppliers' => 'سپلائر تلاش کریں...',
        'search_purchases' => 'خریداری کا بل تلاش کریں...',
        
        'supp_name' => 'کمپنی / سپلائر کا نام',
        'supp_contact' => 'رابطہ کار کا نام',
        'supp_phone' => 'فون نمبر',
        'supp_address' => 'پتہ',
        'supp_balance' => 'بقایا رقم (جو ہم نے دینی ہے)',
        'actions' => 'اختیارات',
        
        'pur_no' => 'خریداری بل نمبر',
        'pur_supplier' => 'سپلائر',
        'pur_total' => 'کل بل',
        'pur_paid' => 'ادا کردہ رقم',
        'pur_balance' => 'بقایا رقم',
        'pur_date' => 'تاریخ',
        
        'save' => 'محفوظ کریں',
        'edit_supplier' => 'سپلائر کی تفصیل تبدیل کریں',
        'close' => 'بند کریں',
        'delete_confirm' => 'کیا آپ واقعی اس سپلائر کو حذف کرنا چاہتے ہیں؟',
        
        'new_purchase_title' => 'خریداری اور اسٹاک ان کا اندراج',
        'select_supplier' => 'سپلائر منتخب کریں',
        'walk_in' => '-- عام سپلائر --',
        'add_item' => 'پروڈکٹ شامل کریں',
        'product' => 'پروڈکٹ کا نام',
        'qty' => 'تعداد',
        'price' => 'قیمتِ خریداری',
        'expiry' => 'تاریخِ میعاد (اختیاری)',
        'subtotal' => 'رقم',
        'discount' => 'ڈسکاؤنٹ',
        'total' => 'کل رقم',
        'paid' => 'ادا کردہ رقم',
        'balance' => 'بقایا رقم (ادھار)',
        'save_purchase' => 'خریداری محفوظ کریں اور اسٹاک بڑھائیں',
        
        'ai_ocr_btn' => '🤖 آرٹیفیشل انٹیلیجنس (Gemini AI) بل اسکینر',
        'ocr_title' => 'جیمنی اے آئی بل اسکینر',
        'ocr_drag_text' => 'خریداری بل کی تصویر یہاں کھینچ کر لائیں یا اپ لوڈ کریں',
        'ocr_allowed' => 'سپورٹڈ فائلز: PNG, JPG, JPEG',
        'ocr_processing' => 'بل سے تفصیلات نکالی جا رہی ہیں... براہ کرم انتظار کریں۔',
        'ocr_match_title' => 'اسکین شدہ آئٹمز کو اپنی پروڈکٹس سے جوڑیں',
        'ocr_scanned_item' => 'اسکین شدہ آئٹم',
        'ocr_matching_product' => 'موجودہ پروڈکٹ سے لنک کریں',
        'ocr_new_product' => '[ نئی پروڈکٹ بنائیں ]',
        'apply_invoice' => 'بل فارم میں ڈیٹا منتقل کریں',
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
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 24px; height: 100vh; }
        .sidebar-brand { font-family: var(--font-heading); font-size: 24px; font-weight: 700; color: var(--accent); margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
        .menu-link { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-radius: var(--radius-sm); color: var(--text-main); text-decoration: none; font-family: var(--font-heading); font-weight: 500; transition: var(--transition-smooth); }
        .menu-link:hover, .menu-link.active { background-color: var(--bg-input); color: var(--accent); border-left: 4px solid var(--accent); padding-left: 16px; }
        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }

        /* Tabs Interface */
        .tabs-header { display: flex; gap: 10px; border-bottom: 2px solid var(--border-color); padding-bottom: 0px; }
        .tab-btn { padding: 12px 24px; border: none; background: none; font-family: var(--font-heading); font-weight: 600; color: var(--text-muted); cursor: pointer; transition: var(--transition-smooth); border-bottom: 3px solid transparent; font-size: 15px; }
        .tab-btn:hover { color: var(--accent); }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab-pane { display: none; flex-direction: column; gap: 20px; }
        .tab-pane.active { display: flex; }

        /* Tables & Lists */
        .table-responsive { width: 100%; overflow-x: auto; background-color: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); }
        .data-table tr:hover { background-color: rgba(0, 0, 0, 0.02); }

        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .search-bar { position: relative; flex-grow: 1; max-width: 450px; }
        .search-input { width: 100%; padding: 12px 16px; padding-left: 40px; border-radius: 30px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 15px; outline: none; }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        .balance-badge { padding: 6px 12px; border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .balance-badge.due { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .balance-badge.clear { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }

        /* Modals */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; visibility: hidden; opacity: 0; transition: var(--transition-smooth); }
        .modal-overlay.active { visibility: visible; opacity: 1; }
        .modal-card { background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); width: 100%; max-width: 600px; padding: 30px; box-shadow: var(--shadow-lg); transform: translateY(-20px); transition: var(--transition-smooth); max-height: 90vh; overflow-y: auto; }
        .modal-overlay.active .modal-card { transform: translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 20px; }

        /* Purchase Grid and Entries */
        .purchase-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
        .purchase-items-table th, .purchase-items-table td { padding: 10px 12px; font-size: 13px; }
        .form-control-sm { padding: 6px 10px; font-size: 13px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background-color: var(--bg-input); color: var(--text-main); }
        
        /* Drag and drop OCR zone */
        .ocr-dropzone { border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 30px; text-align: center; cursor: pointer; background: var(--bg-input); transition: var(--transition-smooth); }
        .ocr-dropzone:hover { border-color: var(--accent); background: var(--bg-card); }
        .ocr-dropzone-icon { font-size: 48px; margin-bottom: 10px; display: block; }
        
        .ocr-loader { display: none; flex-direction: column; align-items: center; gap: 15px; padding: 30px; text-align: center; }
        .ocr-spinner { width: 50px; height: 50px; border: 5px solid var(--border-color); border-top-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite; }
        
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Product Match Grid */
        .ocr-match-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .ocr-match-table th, .ocr-match-table td { border: 1px solid var(--border-color); padding: 10px; font-size: 13px; text-align: left; }
        .ocr-match-table th { background: var(--bg-input); }

        /* RTL Handling overrides */
        .lang-urdu .sidebar { border-right: none; border-left: 1px solid var(--border-color); }
        .lang-urdu .menu-link:hover, .lang-urdu .menu-link.active { border-left: none; border-right: 4px solid var(--accent); padding-left: 20px; padding-right: 16px; }
        .lang-urdu .search-input { padding-left: 16px; padding-right: 40px; }
        .lang-urdu .search-icon { left: auto; right: 16px; }
        .lang-urdu .data-table, .lang-urdu .ocr-match-table th, .lang-urdu .ocr-match-table td { text-align: right; }
    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="TijaratPro.png" alt="TijaratPro" style="width: 28px; height: 28px; border-radius: 6px; vertical-align: middle; margin-right: 6px;"> <?php echo ($lang === 'ur') ? 'تجارت پرو' : 'TijaratPro'; ?>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php" class="menu-link">🏠 <?php echo $trans[$lang]['dashboard']; ?></a></li>
                <li><a href="billing.php" class="menu-link">🛒 <?php echo $trans[$lang]['menu_billing']; ?></a></li>
                <li><a href="products.php" class="menu-link">📦 <?php echo $trans[$lang]['menu_inventory']; ?></a></li>
                <li><a href="customers.php" class="menu-link">👥 <?php echo $trans[$lang]['menu_customers']; ?></a></li>
                <li><a href="purchases.php" class="menu-link active">🧾 <?php echo $trans[$lang]['menu_purchases']; ?></a></li>
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
            
            <!-- Top Controls -->
            <header class="header-nav">
                <h2 style="font-size: 22px; font-weight: 600;">
                    🧾 <?php echo $trans[$lang]['title']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Tabs Navigation -->
            <nav class="tabs-header">
                <button class="tab-btn active" id="tabBtnPurchases" onclick="switchTab('purchases')">📊 <?php echo $trans[$lang]['tab_purchases']; ?></button>
                <button class="tab-btn" id="tabBtnSuppliers" onclick="switchTab('suppliers')">🏢 <?php echo $trans[$lang]['tab_suppliers']; ?></button>
            </nav>

            <!-- TAB 1: PURCHASES LOG -->
            <div class="tab-pane active" id="tabPurchases">
                <div class="toolbar">
                    <div class="search-bar">
                        <span class="search-icon">🔍</span>
                        <input class="search-input" type="text" id="purchasesSearch" placeholder="<?php echo $trans[$lang]['search_purchases']; ?>" onkeyup="filterPurchases()">
                    </div>
                    <button class="btn btn-primary" onclick="openNewPurchaseScreen()">
                        ➕ <?php echo $trans[$lang]['btn_new_purchase']; ?>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="purchasesTable">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['pur_no']; ?></th>
                                <th><?php echo $trans[$lang]['pur_supplier']; ?></th>
                                <th><?php echo $trans[$lang]['pur_total']; ?></th>
                                <th><?php echo $trans[$lang]['pur_paid']; ?></th>
                                <th><?php echo $trans[$lang]['pur_balance']; ?></th>
                                <th><?php echo $trans[$lang]['pur_date']; ?></th>
                            </tr>
                        </thead>
                        <tbody id="purchasesTableBody">
                            <!-- Loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: SUPPLIERS REGISTRY -->
            <div class="tab-pane" id="tabSuppliers">
                <div class="toolbar">
                    <div class="search-bar">
                        <span class="search-icon">🔍</span>
                        <input class="search-input" type="text" id="suppliersSearch" placeholder="<?php echo $trans[$lang]['search_suppliers']; ?>" onkeyup="filterSuppliers()">
                    </div>
                    <button class="btn btn-primary" onclick="openSupplierModal()">
                        ➕ <?php echo $trans[$lang]['btn_add_supplier']; ?>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="suppliersTable">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['supp_name']; ?></th>
                                <th><?php echo $trans[$lang]['supp_contact']; ?></th>
                                <th><?php echo $trans[$lang]['supp_phone']; ?></th>
                                <th><?php echo $trans[$lang]['supp_address']; ?></th>
                                <th><?php echo $trans[$lang]['supp_balance']; ?></th>
                                <th><?php echo $trans[$lang]['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody id="suppliersTableBody">
                            <!-- Loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

    </div>

    <!-- MODAL 1: ADD/EDIT SUPPLIER -->
    <div class="modal-overlay" id="supplierModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="suppModalTitle"><?php echo $trans[$lang]['btn_add_supplier']; ?></h3>
                <button onclick="closeSupplierModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form id="supplierForm" onsubmit="saveSupplier(event)">
                <input type="hidden" id="suppId" name="id">
                
                <div class="form-group">
                    <label class="form-label" for="suppNameInput"><?php echo $trans[$lang]['supp_name']; ?> *</label>
                    <input class="form-control" type="text" id="suppNameInput" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="suppContactInput"><?php echo $trans[$lang]['supp_contact']; ?></label>
                    <input class="form-control" type="text" id="suppContactInput">
                </div>

                <div class="form-group">
                    <label class="form-label" for="suppPhoneInput"><?php echo $trans[$lang]['supp_phone']; ?> *</label>
                    <input class="form-control" type="text" id="suppPhoneInput" placeholder="e.g. 03001234567" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="suppAddressInput"><?php echo $trans[$lang]['supp_address']; ?></label>
                    <input class="form-control" type="text" id="suppAddressInput">
                </div>

                <div class="form-group">
                    <label class="form-label" for="suppEmailInput">Email Address</label>
                    <input class="form-control" type="email" id="suppEmailInput">
                </div>

                <div class="form-group" id="suppInitialBalanceGroup">
                    <label class="form-label" for="suppBalanceInput"><?php echo $trans[$lang]['supp_balance']; ?></label>
                    <input class="form-control" type="number" id="suppBalanceInput" step="0.01" value="0.00" placeholder="Positive means they owe us, Negative means we owe them">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" onclick="closeSupplierModal()"><?php echo $trans[$lang]['close']; ?></button>
                    <button class="btn btn-primary" type="submit"><?php echo $trans[$lang]['save']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: RECORD NEW STOCK PURCHASE -->
    <div class="modal-overlay" id="newPurchaseModal">
        <div class="modal-card" style="max-width: 1000px;">
            <div class="modal-header">
                <h3>🛍️ <?php echo $trans[$lang]['new_purchase_title']; ?></h3>
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-success" style="padding: 8px 16px; font-size: 13px;" onclick="openOcrModal()">
                        <?php echo $trans[$lang]['ai_ocr_btn']; ?>
                    </button>
                    <button onclick="closeNewPurchaseScreen()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
            </div>

            <form id="purchaseForm" onsubmit="savePurchaseOrder(event)">
                <div class="purchase-grid">
                    
                    <!-- Left Column: Items Table -->
                    <div class="card" style="padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h4 style="font-size: 16px;">Items Registry</h4>
                            <button class="btn btn-secondary" type="button" style="padding: 6px 12px; font-size: 13px;" onclick="addPurchaseItemRow()">
                                ➕ <?php echo $trans[$lang]['add_item']; ?>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="data-table purchase-items-table" id="purchaseItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;"><?php echo $trans[$lang]['product']; ?></th>
                                        <th style="width: 15%;"><?php echo $trans[$lang]['qty']; ?></th>
                                        <th style="width: 20%;"><?php echo $trans[$lang]['price']; ?></th>
                                        <th style="width: 20%;"><?php echo $trans[$lang]['expiry']; ?></th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseItemsTableBody">
                                    <!-- Dynamic rows loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Bill Checkout Summary -->
                    <div class="card" style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"><?php echo $trans[$lang]['select_supplier']; ?></label>
                            <select class="form-control" id="purSupplierSelect" required>
                                <option value=""><?php echo $trans[$lang]['walk_in']; ?></option>
                                <!-- Loaded dynamically -->
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"><?php echo $trans[$lang]['pur_no']; ?></label>
                            <input class="form-control" type="text" id="purInvoiceNo" placeholder="Auto-generated">
                        </div>

                        <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo $trans[$lang]['subtotal']; ?></span>
                                <span style="font-weight: 600;" id="lblPurSubtotal">0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo $trans[$lang]['discount']; ?></span>
                                <input class="form-control-sm" type="number" id="purDiscount" value="0.00" style="width: 80px; text-align: right;" onchange="calculatePurchaseTotals()">
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                                <span style="font-weight: 600;"><?php echo $trans[$lang]['total']; ?></span>
                                <span style="font-weight: 700; color: var(--accent);" id="lblPurTotal">0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo $trans[$lang]['paid']; ?></span>
                                <input class="form-control-sm" type="number" id="purPaidAmount" value="0.00" style="width: 100px; text-align: right;" onchange="calculatePurchaseTotals()">
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo $trans[$lang]['balance']; ?></span>
                                <span style="font-weight: 600;" id="lblPurBalance">0.00</span>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="width: 100%; border-radius: var(--radius-sm); margin-top: 10px;">
                            💾 <?php echo $trans[$lang]['save_purchase']; ?>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: GEMINI AI OCR BILL SCANNER -->
    <div class="modal-overlay" id="ocrModal">
        <div class="modal-card" style="max-width: 750px;">
            <div class="modal-header">
                <h3>🤖 <?php echo $trans[$lang]['ocr_title']; ?></h3>
                <button onclick="closeOcrModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <!-- Upload Area -->
            <div class="ocr-dropzone" id="ocrDropzone" onclick="triggerOcrFileSelect()">
                <span class="ocr-dropzone-icon">📸</span>
                <span style="font-weight: 600; display: block; margin-bottom: 4px;"><?php echo $trans[$lang]['ocr_drag_text']; ?></span>
                <span style="font-size: 12px; color: var(--text-muted);"><?php echo $trans[$lang]['ocr_allowed']; ?></span>
                <input type="file" id="ocrFileInput" accept="image/*" style="display: none;" onchange="handleOcrFile(event)">
            </div>

            <!-- Loader Progress -->
            <div class="ocr-loader" id="ocrLoader">
                <div class="ocr-spinner"></div>
                <h4 id="ocrStatusMsg"><?php echo $trans[$lang]['ocr_processing']; ?></h4>
            </div>

            <!-- Product Match Dashboard -->
            <div id="ocrMatchSection" style="display: none; margin-top: 20px;">
                <h4 style="margin-bottom: 10px; font-size: 15px;">🔍 <?php echo $trans[$lang]['ocr_match_title']; ?></h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="ocr-match-table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['ocr_scanned_item']; ?></th>
                                <th><?php echo $trans[$lang]['qty']; ?></th>
                                <th><?php echo $trans[$lang]['price']; ?></th>
                                <th><?php echo $trans[$lang]['ocr_matching_product']; ?></th>
                            </tr>
                        </thead>
                        <tbody id="ocrMatchTableBody">
                            <!-- Populated with scanned lines -->
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer" style="padding-top: 15px; margin-top: 15px;">
                    <button class="btn btn-secondary" onclick="closeOcrModal()"><?php echo $trans[$lang]['close']; ?></button>
                    <button class="btn btn-success" onclick="applyScannedInvoice()"><?php echo $trans[$lang]['apply_invoice']; ?></button>
                </div>
            </div>

        </div>
    </div>

    <script>
        let allSuppliers = [];
        let allPurchases = [];
        let productsCatalog = <?php echo json_encode($products_list); ?>;
        const trans = <?php echo json_encode($trans[$lang]); ?>;
        let scannedResultData = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadSuppliers();
            loadPurchases();
        });

        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            if (tab === 'purchases') {
                document.getElementById('tabBtnPurchases').classList.add('active');
                document.getElementById('tabPurchases').classList.add('active');
            } else {
                document.getElementById('tabBtnSuppliers').classList.add('active');
                document.getElementById('tabSuppliers').classList.add('active');
            }
        }

        // --- SUPPLIERS LOGIC ---
        function loadSuppliers() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-suppliers' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'suppliers-list');
                if (response) {
                    allSuppliers = response.data;
                    renderSuppliersTable(allSuppliers);
                    populateSupplierDropdowns();
                }
            })
            .catch(err => console.error("Error loading suppliers:", err));
        }

        function renderSuppliersTable(suppliers) {
            const tbody = document.getElementById('suppliersTableBody');
            tbody.innerHTML = '';
            if (suppliers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No suppliers found.</td></tr>`;
                return;
            }

            suppliers.forEach(s => {
                const balanceVal = parseFloat(s.balance);
                const weOwe = balanceVal < 0;
                const balanceClass = weOwe ? 'due' : 'clear';
                // Convert negative balance to positive for display
                const absBalance = Math.abs(balanceVal).toFixed(2) + " PKR";

                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 500;">${escapeHtml(s.name)}</td>
                        <td>${escapeHtml(s.contact_person || '-')}</td>
                        <td>${escapeHtml(s.phone)}</td>
                        <td>${escapeHtml(s.address || '-')}</td>
                        <td>
                            <span class="balance-badge ${balanceClass}">
                                ${weOwe ? '⚠️' : '✅'} ${absBalance}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="editSupplier(${s.id})">✏️</button>
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="deleteSupplier(${s.id})">🗑️</button>
                        </td>
                    </tr>
                `;
            });
        }

        function filterSuppliers() {
            const query = document.getElementById('suppliersSearch').value.toLowerCase().trim();
            if (!query) {
                renderSuppliersTable(allSuppliers);
                return;
            }
            const filtered = allSuppliers.filter(s => 
                s.name.toLowerCase().includes(query) || 
                s.phone.includes(query) || 
                (s.contact_person && s.contact_person.toLowerCase().includes(query))
            );
            renderSuppliersTable(filtered);
        }

        function openSupplierModal() {
            document.getElementById('supplierForm').reset();
            document.getElementById('suppId').value = '';
            document.getElementById('suppInitialBalanceGroup').style.display = 'block';
            document.getElementById('suppModalTitle').innerText = trans.btn_add_supplier;
            document.getElementById('supplierModal').classList.add('active');
        }

        function closeSupplierModal() {
            document.getElementById('supplierModal').classList.remove('active');
        }

        function saveSupplier(e) {
            e.preventDefault();
            const id = document.getElementById('suppId').value;
            const name = document.getElementById('suppNameInput').value.trim();
            const contact_person = document.getElementById('suppContactInput').value.trim();
            const phone = document.getElementById('suppPhoneInput').value.trim();
            const address = document.getElementById('suppAddressInput').value.trim();
            const email = document.getElementById('suppEmailInput').value.trim();
            const balance = document.getElementById('suppInitialBalanceInput') ? document.getElementById('suppBalanceInput').value : 0;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-supplier',
                    data: { id, name, contact_person, phone, address, email, balance }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'supplier-saved');
                if (response && response.data.success) {
                    closeSupplierModal();
                    loadSuppliers();
                } else {
                    alert(response ? response.data.msg : "Error saving supplier.");
                }
            })
            .catch(err => console.error("Error saving supplier:", err));
        }

        function editSupplier(id) {
            const s = allSuppliers.find(item => item.id == id);
            if (!s) return;

            document.getElementById('suppId').value = s.id;
            document.getElementById('suppNameInput').value = s.name;
            document.getElementById('suppContactInput').value = s.contact_person || '';
            document.getElementById('suppPhoneInput').value = s.phone;
            document.getElementById('suppAddressInput').value = s.address || '';
            document.getElementById('suppEmailInput').value = s.email || '';
            document.getElementById('suppInitialBalanceGroup').style.display = 'none'; // Lock balance editing directly
            
            document.getElementById('suppModalTitle').innerText = trans.edit_supplier;
            document.getElementById('supplierModal').classList.add('active');
        }

        function deleteSupplier(id) {
            if (!confirm(trans.delete_confirm)) return;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete-supplier',
                    data: { id }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'supplier-deleted');
                if (response && response.data.success) {
                    loadSuppliers();
                } else {
                    alert(response ? response.data.msg : "Cannot delete supplier.");
                }
            })
            .catch(err => console.error("Error deleting supplier:", err));
        }

        // --- PURCHASES LOGIC ---
        function loadPurchases() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-purchases' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'purchases-list');
                if (response) {
                    allPurchases = response.data;
                    renderPurchasesTable(allPurchases);
                }
            })
            .catch(err => console.error("Error loading purchases:", err));
        }

        function renderPurchasesTable(purchases) {
            const tbody = document.getElementById('purchasesTableBody');
            tbody.innerHTML = '';
            if (purchases.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No purchases logged yet.</td></tr>`;
                return;
            }

            purchases.forEach(p => {
                const date = new Date(p.created_at).toLocaleString();
                const bal = parseFloat(p.balance_amount);
                const hasDue = bal > 0;
                
                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 600;">${escapeHtml(p.purchase_no)}</td>
                        <td>${escapeHtml(p.supplier_name || 'Walk-in Supplier')}</td>
                        <td>${parseFloat(p.total).toFixed(2)} PKR</td>
                        <td>${parseFloat(p.paid_amount).toFixed(2)} PKR</td>
                        <td>
                            <span class="balance-badge ${hasDue ? 'due' : 'clear'}">
                                ${bal.toFixed(2)} PKR
                            </span>
                        </td>
                        <td>${date}</td>
                    </tr>
                `;
            });
        }

        function filterPurchases() {
            const query = document.getElementById('purchasesSearch').value.toLowerCase().trim();
            if (!query) {
                renderPurchasesTable(allPurchases);
                return;
            }
            const filtered = allPurchases.filter(p => 
                p.purchase_no.toLowerCase().includes(query) || 
                (p.supplier_name && p.supplier_name.toLowerCase().includes(query))
            );
            renderPurchasesTable(filtered);
        }

        function populateSupplierDropdowns() {
            const select = document.getElementById('purSupplierSelect');
            select.innerHTML = `<option value="">${trans.walk_in}</option>`;
            allSuppliers.forEach(s => {
                select.innerHTML += `<option value="${s.id}">${escapeHtml(s.name)} (${s.phone})</option>`;
            });
        }

        // --- NEW PURCHASE ORDER CREATION ---
        function openNewPurchaseScreen() {
            document.getElementById('purchaseForm').reset();
            document.getElementById('purchaseItemsTableBody').innerHTML = '';
            document.getElementById('lblPurSubtotal').innerText = '0.00';
            document.getElementById('lblPurTotal').innerText = '0.00';
            document.getElementById('lblPurBalance').innerText = '0.00';
            
            // Add a default blank item row
            addPurchaseItemRow();
            
            document.getElementById('newPurchaseModal').classList.add('active');
        }

        function closeNewPurchaseScreen() {
            document.getElementById('newPurchaseModal').classList.remove('active');
        }

        function addPurchaseItemRow(productId = '', qty = 1, price = 0, expiry = '') {
            const tbody = document.getElementById('purchaseItemsTableBody');
            const rowId = 'pur-row-' + Date.now() + Math.random().toString(36).substr(2, 5);

            let productOptions = '<option value="">-- Select Product --</option>';
            productsCatalog.forEach(p => {
                productOptions += `<option value="${p.id}" ${p.id == productId ? 'selected' : ''}>${escapeHtml(p.name)} (${p.barcode || 'No Barcode'})</option>`;
            });

            const rowHtml = `
                <tr id="${rowId}">
                    <td>
                        <select class="form-control-sm" style="width: 100%;" onchange="onPurchaseProductSelect('${rowId}', this)">
                            ${productOptions}
                        </select>
                    </td>
                    <td>
                        <input class="form-control-sm" type="number" style="width: 100%; text-align: center;" value="${qty}" min="0.1" step="0.1" onchange="calculatePurchaseTotals()">
                    </td>
                    <td>
                        <input class="form-control-sm" type="number" style="width: 100%; text-align: right;" value="${parseFloat(price).toFixed(2)}" min="0" step="0.01" onchange="calculatePurchaseTotals()">
                    </td>
                    <td>
                        <input class="form-control-sm" type="date" style="width: 100%;" value="${expiry}" onchange="calculatePurchaseTotals()">
                    </td>
                    <td>
                        <button class="btn btn-danger" type="button" style="padding: 4px 8px; border-radius: 4px;" onclick="removePurchaseRow('${rowId}')">&times;</button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', rowHtml);
            calculatePurchaseTotals();
        }

        function removePurchaseRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) row.remove();
            calculatePurchaseTotals();
        }

        function onPurchaseProductSelect(rowId, selectElem) {
            const prodId = selectElem.value;
            const row = document.getElementById(rowId);
            if (!row || !prodId) return;

            const product = productsCatalog.find(p => p.id == prodId);
            if (product) {
                // Prefill purchase price
                row.cells[2].querySelector('input').value = parseFloat(product.purchase_price).toFixed(2);
            }
            calculatePurchaseTotals();
        }

        function calculatePurchaseTotals() {
            const tbody = document.getElementById('purchaseItemsTableBody');
            const rows = tbody.querySelectorAll('tr');
            let subtotal = 0;

            rows.forEach(row => {
                const prodSelect = row.cells[0].querySelector('select');
                const qtyInput = row.cells[1].querySelector('input');
                const priceInput = row.cells[2].querySelector('input');

                if (prodSelect.value) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    subtotal += (qty * price);
                }
            });

            const discount = parseFloat(document.getElementById('purDiscount').value) || 0;
            const total = Math.max(0, subtotal - discount);
            const paid = parseFloat(document.getElementById('purPaidAmount').value) || 0;
            const balance = Math.max(0, total - paid);

            document.getElementById('lblPurSubtotal').innerText = subtotal.toFixed(2);
            document.getElementById('lblPurTotal').innerText = total.toFixed(2);
            document.getElementById('lblPurBalance').innerText = balance.toFixed(2);
        }

        function savePurchaseOrder(e) {
            e.preventDefault();
            
            const tbody = document.getElementById('purchaseItemsTableBody');
            const rows = tbody.querySelectorAll('tr');
            const items = [];

            rows.forEach(row => {
                const product_id = row.cells[0].querySelector('select').value;
                const qty = parseFloat(row.cells[1].querySelector('input').value) || 0;
                const price = parseFloat(row.cells[2].querySelector('input').value) || 0;
                const expiry = row.cells[3].querySelector('input').value;

                if (product_id) {
                    items.push({ product_id, qty, price, expiry });
                }
            });

            if (items.length === 0) {
                alert("Please add at least one product in the purchase invoice.");
                return;
            }

            const supplier_id = document.getElementById('purSupplierSelect').value;
            const purchase_no = document.getElementById('purInvoiceNo').value.trim();
            const subtotal = parseFloat(document.getElementById('lblPurSubtotal').innerText);
            const discount = parseFloat(document.getElementById('purDiscount').value) || 0;
            const total = parseFloat(document.getElementById('lblPurTotal').innerText);
            const paid_amount = parseFloat(document.getElementById('purPaidAmount').value) || 0;
            const balance_amount = parseFloat(document.getElementById('lblPurBalance').innerText);

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'process-purchase',
                    data: { purchase_no, supplier_id, subtotal, discount, total, paid_amount, balance_amount, items }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'purchase-processed');
                if (response && response.data.success) {
                    closeNewPurchaseScreen();
                    loadPurchases();
                    loadSuppliers(); // Balance updates
                } else {
                    alert(response ? response.data.msg : "Stock purchase recording failed.");
                }
            })
            .catch(err => console.error("Error saving purchase order:", err));
        }

        // --- GEMINI AI OCR BILL SCANNER LOGIC ---
        function openOcrModal() {
            document.getElementById('ocrMatchSection').style.display = 'none';
            document.getElementById('ocrLoader').style.display = 'none';
            document.getElementById('ocrDropzone').style.display = 'block';
            document.getElementById('ocrModal').classList.add('active');
        }

        function closeOcrModal() {
            document.getElementById('ocrModal').classList.remove('active');
        }

        function triggerOcrFileSelect() {
            document.getElementById('ocrFileInput').click();
        }

        function handleOcrFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Show Loader Spinner
            document.getElementById('ocrDropzone').style.display = 'none';
            document.getElementById('ocrLoader').style.display = 'flex';

            const reader = new FileReader();
            reader.onload = function(evt) {
                const base64Data = evt.target.result;
                
                // Call scan-invoice api
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'scan-invoice',
                        data: { image_data: base64Data }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const response = data.find(r => r.channel === 'invoice-scanned');
                    if (response && response.data.success) {
                        scannedResultData = response.data.data;
                        renderOcrMatchScreen(scannedResultData);
                    } else {
                        document.getElementById('ocrLoader').style.display = 'none';
                        document.getElementById('ocrDropzone').style.display = 'block';
                        alert(response ? response.data.msg : "Scanning failed. Unknown database response.");
                    }
                })
                .catch(err => {
                    document.getElementById('ocrLoader').style.display = 'none';
                    document.getElementById('ocrDropzone').style.display = 'block';
                    alert("Network error: " + err.message);
                });
            };
            reader.readAsDataURL(file);
        }

        function renderOcrMatchScreen(data) {
            document.getElementById('ocrLoader').style.display = 'none';
            document.getElementById('ocrMatchSection').style.display = 'block';

            // Auto-detect supplier matching
            if (data.supplier) {
                const matchedSupp = allSuppliers.find(s => s.name.toLowerCase().includes(data.supplier.toLowerCase()));
                if (matchedSupp) {
                    document.getElementById('purSupplierSelect').value = matchedSupp.id;
                }
            }

            if (data.invoice_no) {
                document.getElementById('purInvoiceNo').value = data.invoice_no;
            }

            const tbody = document.getElementById('ocrMatchTableBody');
            tbody.innerHTML = '';

            data.items.forEach((item, index) => {
                // Auto-suggest catalog item with best string match
                let bestMatchId = '';
                let highestScore = 0;
                
                productsCatalog.forEach(p => {
                    const score = similarity(p.name.toLowerCase(), item.name.toLowerCase());
                    if (score > highestScore && score > 0.4) {
                        highestScore = score;
                        bestMatchId = p.id;
                    }
                });

                // Render match dropdown
                let productOptions = `<option value="NEW">${trans.ocr_new_product}</option>`;
                productsCatalog.forEach(p => {
                    productOptions += `<option value="${p.id}" ${p.id == bestMatchId ? 'selected' : ''}>${escapeHtml(p.name)}</option>`;
                });

                tbody.innerHTML += `
                    <tr data-scanned-name="${escapeHtml(item.name)}">
                        <td style="font-weight: 500;">${escapeHtml(item.name)}</td>
                        <td><input class="form-control-sm" type="number" value="${item.qty}" style="width: 70px; text-align: center;"></td>
                        <td><input class="form-control-sm" type="number" value="${parseFloat(item.price).toFixed(2)}" style="width: 90px; text-align: right;"></td>
                        <td>
                            <select class="form-control-sm select-ocr-match" style="width: 100%;">
                                ${productOptions}
                            </select>
                        </td>
                    </tr>
                `;
            });
        }

        function applyScannedInvoice() {
            if (!scannedResultData) return;

            const matchRows = document.querySelectorAll('#ocrMatchTableBody tr');
            const itemsToInject = [];
            
            // Loop through matches and collect
            const promises = [];
            
            matchRows.forEach(row => {
                const scannedName = row.getAttribute('data-scanned-name');
                const qty = parseFloat(row.cells[1].querySelector('input').value) || 0;
                const price = parseFloat(row.cells[2].querySelector('input').value) || 0;
                const selectVal = row.cells[3].querySelector('select').value;

                if (selectVal === 'NEW') {
                    // Create product dynamically or insert placeholder to be edited later
                    // To keep things simple and extremely responsive, we send an API request to create the product on the fly!
                    const barcode = 'AUTO-' + Math.floor(Math.random() * 900000000 + 100000000);
                    
                    const promise = fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'save-product',
                            data: { name: scannedName, barcode: barcode, purchase_price: price, sale_price: price * 1.15, unit: 'Piece', stock_qty: 0 }
                        })
                    })
                    .then(res => res.json())
                    .then(resData => {
                        const response = resData.find(r => r.channel === 'product-saved');
                        if (response && response.data.success) {
                            // Fetch all products again to rebuild catalog
                            return fetch('api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'get-products' })
                            })
                            .then(r => r.json())
                            .then(pData => {
                                const pResponse = pData.find(r => r.channel === 'products-list');
                                if (pResponse) {
                                    productsCatalog = pResponse.data;
                                    // Match by scanned name
                                    const createdProd = productsCatalog.find(p => p.name === scannedName);
                                    if (createdProd) {
                                        itemsToInject.push({ product_id: createdProd.id, qty, price });
                                    }
                                }
                            });
                        }
                    });
                    promises.push(promise);
                } else {
                    itemsToInject.push({ product_id: selectVal, qty, price });
                }
            });

            // Wait for all inline product additions to complete
            Promise.all(promises).then(() => {
                // Clear manual rows and inject scanned ones
                const tbody = document.getElementById('purchaseItemsTableBody');
                tbody.innerHTML = '';
                
                itemsToInject.forEach(item => {
                    addPurchaseItemRow(item.product_id, item.qty, item.price);
                });

                if (scannedResultData.total) {
                    // Estimate discount if any
                    const subtotalVal = parseFloat(document.getElementById('lblPurSubtotal').innerText);
                    const diff = subtotalVal - parseFloat(scannedResultData.total);
                    if (diff > 0) {
                        document.getElementById('purDiscount').value = diff.toFixed(2);
                    }
                }
                
                calculatePurchaseTotals();
                closeOcrModal();
            });
        }

        // Levenshtein / similarity helper for naming suggestion matches
        function similarity(s1, s2) {
            let longer = s1;
            let shorter = s2;
            if (s1.length < s2.length) {
                longer = s2;
                shorter = s1;
            }
            let longerLength = longer.length;
            if (longerLength === 0) {
                return 1.0;
            }
            return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
        }

        function editDistance(s1, s2) {
            s1 = s1.toLowerCase();
            s2 = s2.toLowerCase();

            let costs = new Array();
            for (let i = 0; i <= s1.length; i++) {
                let lastValue = i;
                for (let j = 0; j <= s2.length; j++) {
                    if (i == 0)
                        costs[j] = j;
                    else {
                        if (j > 0) {
                            let newValue = costs[j - 1];
                            if (s1.charAt(i - 1) != s2.charAt(j - 1))
                                newValue = Math.min(Math.min(newValue, lastValue),
                                    costs[j]) + 1;
                            costs[j - 1] = lastValue;
                            lastValue = newValue;
                        }
                    }
                }
                if (i > 0)
                    costs[s2.length] = lastValue;
            }
            return costs[s2.length];
        }

        // Helpers
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
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
