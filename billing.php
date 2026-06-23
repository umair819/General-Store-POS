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
        'title' => 'Tijarat POS - Billing Screen',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'search_placeholder' => '🔍 Search by Product Name or Barcode (F2)',
        'walk_in' => 'Walk-in Customer',
        'select_customer' => '👤 Select Customer / Khata',
        'cart_empty' => 'Cart is empty. Add products to start billing.',
        'subtotal' => 'Subtotal',
        'discount' => 'Total Discount',
        'payable' => 'Total Payable',
        'paid' => 'Amount Paid',
        'change' => 'Change Due',
        'payment_method' => 'Payment Method',
        'btn_hold' => '⏸️ Hold Bill',
        'btn_resume' => '▶️ Resume Held',
        'btn_clear' => '✖ Clear Cart (F4)',
        'btn_checkout' => '⚡ Pay & Print Invoice (F8)',
        'shortcuts' => 'Keyboard Shortcuts: F2 Search | F8 Pay | F4 Clear | F9 Held Bills',
        'invoice' => 'Invoice Receipt',
        'print' => 'Print',
        'close' => 'Close',
        'col_item' => 'Item',
        'col_price' => 'Price',
        'col_qty' => 'Qty',
        'col_total' => 'Total',
    ],
    'ur' => [
        'title' => 'Tijarat POS - بلنگ اسکرین',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 اسٹاک اور انوینٹری',
        'menu_customers' => '👥 Tijarat Ledger (کھاتہ)',
        'menu_purchases' => '🧾 خریداری',
        'menu_reports' => '📈 سیلز رپورٹ',
        'menu_settings' => '⚙️ سیٹنگز',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'logout' => '🚪 لاگ آؤٹ',
        'search_placeholder' => '🔍 پروڈکٹ کا نام یا بارکوڈ تلاش کریں (F2)',
        'walk_in' => 'عام گاہک (Walk-in)',
        'select_customer' => '👤 گاہک / کھاتہ منتخب کریں',
        'cart_empty' => 'کارٹ خالی ہے۔ بلنگ شروع کرنے کے لیے پروڈکٹس شامل کریں۔',
        'subtotal' => 'کل رقم (Subtotal)',
        'discount' => 'رعایت (Discount)',
        'payable' => 'قابل ادا رقم (Payable)',
        'paid' => 'وصول شدہ رقم',
        'change' => 'باقیہ رقم (Change)',
        'payment_method' => 'طریقہ ادائیگی',
        'btn_hold' => '⏸️ بل روکیں (Hold)',
        'btn_resume' => '▶️ روکے ہوئے بل',
        'btn_clear' => '✖ کارٹ صاف کریں (F4)',
        'btn_checkout' => '⚡ بل بنائیں اور پرنٹ کریں (F8)',
        'shortcuts' => 'شارٹ کٹس: F2 سرچ | F8 بل ادائیگی | F4 کارٹ صاف | F9 روکے گئے بل',
        'invoice' => 'بل کی رسید',
        'print' => 'پرنٹ کریں',
        'close' => 'بند کریں',
        'col_item' => 'پروڈکٹ',
        'col_price' => 'قیمت',
        'col_qty' => 'تعداد',
        'col_total' => 'ٹوٹل',
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
        .content-panel { flex-grow: 1; padding: 30px; display: flex; flex-direction: column; gap: 20px; height: 100vh; overflow: hidden; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 15px 30px; box-shadow: var(--shadow-sm); }

        /* Billing Screen Specific Splits */
        .billing-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 20px; flex-grow: 1; height: calc(100vh - 120px); overflow: hidden; }
        
        .search-catalog-panel { display: flex; flex-direction: column; gap: 16px; height: 100%; overflow: hidden; }
        .checkout-cart-panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-md); display: flex; flex-direction: column; height: 100%; overflow: hidden; }

        /* Live Catalog / Auto-suggest list */
        .catalog-results { flex-grow: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; padding-bottom: 20px; }
        .product-catalog-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; cursor: pointer; transition: var(--transition-smooth); display: flex; flex-direction: column; gap: 6px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden; }
        .product-catalog-card:hover { border-color: var(--accent); transform: scale(1.03); box-shadow: var(--shadow-md); }
        .product-catalog-card .name { font-weight: 600; font-size: 14px; color: var(--text-main); }
        .product-catalog-card .barcode { font-size: 11px; color: var(--text-muted); font-family: monospace; }
        .product-catalog-card .price { font-size: 16px; font-weight: 700; color: var(--accent); font-family: var(--font-heading); margin-top: auto; }
        .product-catalog-card .stock-badge { position: absolute; top: 8px; right: 8px; font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; }
        
        /* Cart items table list */
        .cart-items-wrapper { flex-grow: 1; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: var(--bg-app); margin-bottom: 20px; min-height: 180px; }
        .cart-table { width: 100%; border-collapse: collapse; text-align: left; }
        .cart-table th { background: var(--bg-input); font-size: 11px; text-transform: uppercase; font-weight: 600; color: var(--text-muted); padding: 10px 14px; position: sticky; top: 0; }
        .cart-table td { padding: 8px 14px; border-bottom: 1px solid var(--border-color); font-size: 13px; background: var(--bg-card); }
        .cart-table tr:last-child td { border-bottom: none; }
        .cart-table .qty-input { width: 60px; padding: 4px 8px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-input); color: var(--text-main); font-weight: 600; text-align: center; }
        .cart-table .item-total { font-weight: 600; font-family: var(--font-heading); }

        /* Checkout summary blocks */
        .checkout-totals-box { border-top: 2px dashed var(--border-color); padding-top: 15px; display: flex; flex-direction: column; gap: 8px; }
        .total-row { display: flex; justify-content: space-between; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .total-row.grand-total-row { font-size: 26px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading); border-top: 1px solid var(--border-color); padding-top: 8px; margin-top: 4px; }
        .total-row.grand-total-row .val { color: var(--accent); }

        /* Checkout Forms */
        .checkout-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 15px; }

        /* Modal Invoice Print template */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease; }
        .modal.active { display: flex; opacity: 1; }
        .modal-card { width: 100%; max-width: 400px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal.active .modal-card { transform: scale(1); }
        
        /* Thermal invoice structure */
        .thermal-invoice { font-family: 'Courier New', Courier, monospace; background: white; color: black; padding: 20px; border-radius: 4px; box-shadow: var(--shadow-sm); width: 100%; font-size: 12px; line-height: 1.4; text-align: left; }
        .thermal-invoice .center { text-align: center; }
        .thermal-invoice .divider { border-top: 1px dashed black; margin: 10px 0; }
        .thermal-invoice .header-title { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .thermal-invoice .invoice-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .thermal-invoice .invoice-table th, .thermal-invoice .invoice-table td { text-align: left; padding: 2px 0; }
        .thermal-invoice .invoice-table .right, .thermal-invoice .right { text-align: right; }

        /* RTL Layout overrides */
        .lang-urdu .sidebar { border-right: none; border-left: 1px solid var(--border-color); }
        .lang-urdu .menu-link:hover, .lang-urdu .menu-link.active { border-left: none; border-right: 4px solid var(--accent); padding-left: 20px; padding-right: 16px; }
        .lang-urdu .cart-table { text-align: right; }
        .lang-urdu .cart-table th { text-align: right; }
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
                <li><a href="billing.php" class="menu-link active">🛒 <?php echo $trans[$lang]['menu_billing']; ?></a></li>
                <li><a href="products.php" class="menu-link">📦 <?php echo $trans[$lang]['menu_inventory']; ?></a></li>
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
                <div style="font-weight: 500; font-size: 14px;" id="liveClock"></div>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Billing Grid Interface -->
            <div class="billing-grid">
                
                <!-- Left Panel: Product Search & Click-to-add list -->
                <section class="search-catalog-panel">
                    <div style="display: flex; gap: 10px;">
                        <input class="form-control" type="text" id="productSearchInput" placeholder="<?php echo $trans[$lang]['search_placeholder']; ?>" oninput="searchProducts(this.value)" autofocus>
                    </div>

                    <div class="catalog-results" id="catalogResults">
                        <!-- catalog lists populated here -->
                    </div>
                    
                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;" class="center">
                        <?php echo $trans[$lang]['shortcuts']; ?>
                    </div>
                </section>

                <!-- Right Panel: Cart table list & totals checkout -->
                <section class="checkout-cart-panel">
                    
                    <!-- Customer Selector -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <select class="form-control" id="customerSelect" onchange="handleCustomerSelect()">
                            <option value=""><?php echo $trans[$lang]['walk_in']; ?></option>
                        </select>
                    </div>

                    <!-- Cart Scroll List -->
                    <div class="cart-items-wrapper">
                        <table class="cart-table" id="cartTable">
                            <thead>
                                <tr>
                                    <th><?php echo $trans[$lang]['col_item']; ?></th>
                                    <th><?php echo $trans[$lang]['col_price']; ?></th>
                                    <th style="width: 80px; text-align: center;"><?php echo $trans[$lang]['col_qty']; ?></th>
                                    <th><?php echo $trans[$lang]['col_total']; ?></th>
                                    <th style="width: 50px; text-align: center;">✖</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- cart rows populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Checkout calculations totals -->
                    <div class="checkout-totals-box" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                        <div class="total-row">
                            <span><?php echo $trans[$lang]['subtotal']; ?></span>
                            <span id="subtotalVal">0.00</span>
                        </div>
                        <div class="total-row">
                            <span><?php echo $trans[$lang]['discount']; ?></span>
                            <input type="number" id="cartDiscount" class="form-control" style="width: 100px; padding: 4px 8px; font-size: 13px; font-weight: 600; text-align: center;" value="0" min="0" oninput="calculateTotals()">
                        </div>
                        <div class="total-row grand-total-row">
                            <span><?php echo $trans[$lang]['payable']; ?></span>
                            <span><span id="payableVal">0.00</span> <small style="font-size: 12px;"><?php echo ($lang === 'ur') ? 'روپے' : 'PKR'; ?></small></span>
                        </div>
                    </div>

                    <!-- Payment Tender & Method split grid -->
                    <div class="checkout-form-grid" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                        <div class="form-group">
                            <label class="form-label"><?php echo $trans[$lang]['payment_method']; ?></label>
                            <select class="form-control" id="paymentMethod" onchange="handlePaymentMethodChange()">
                                <option value="cash">Cash</option>
                                <option value="online">Easypaisa / JazzCash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="credit">Credit (Udhaar / Khata)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo $trans[$lang]['paid']; ?></label>
                            <input class="form-control" type="number" id="paidAmount" value="0" min="0" oninput="calculateTotals()" style="font-weight: 700; font-size: 18px; color: var(--accent);">
                        </div>
                    </div>

                    <div class="total-row" style="margin-top: 10px; font-size: 15px; font-weight: bold; color: var(--text-main);">
                        <span><?php echo $trans[$lang]['change']; ?>:</span>
                        <span id="changeDueVal">0.00</span>
                    </div>

                    <!-- Cart action buttons -->
                    <div style="display: flex; gap: 8px; margin-top: 15px;">
                        <button class="btn btn-secondary" onclick="holdCurrentBill()" style="flex: 1;">
                            <?php echo $trans[$lang]['btn_hold']; ?>
                        </button>
                        <button class="btn btn-secondary" onclick="openHeldBillsModal()" style="flex: 1;">
                            <?php echo $trans[$lang]['btn_resume']; ?>
                        </button>
                        <button class="btn btn-danger" onclick="clearCart()" style="flex: 0.5;">
                            ✖
                        </button>
                    </div>

                    <button class="btn btn-primary" onclick="submitCheckout()" style="width: 100%; margin-top: 12px; font-size: 16px; padding: 14px 24px; border-radius: var(--radius-md);">
                        <?php echo $trans[$lang]['btn_checkout']; ?>
                    </button>

                </section>

            </div>

        </main>

    </div>

    <!-- Print Invoice Modal Dialogue -->
    <div class="modal" id="printModal">
        <div style="display: flex; flex-direction: column; gap: 15px; align-items: center;">
            <div class="thermal-invoice" id="invoiceContent">
                <!-- Receipt details parsed dynamically -->
            </div>
            <div style="display: flex; gap: 10px; width: 100%;">
                <button class="btn btn-secondary" style="flex: 1;" onclick="closePrintModal()"><?php echo $trans[$lang]['close']; ?></button>
                <button class="btn btn-primary" style="flex: 1;" onclick="triggerPrint()">🖨️ <?php echo $trans[$lang]['print']; ?></button>
            </div>
        </div>
    </div>

    <!-- Held Bills Modal Dialogue -->
    <div class="modal" id="heldBillsModal">
        <div class="card modal-card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
            <h3 style="margin-bottom: 20px;">⏸️ Held Transactions</h3>
            <ul id="heldBillsList" style="list-style: none; display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto; padding: 0;">
                <!-- list of held bills populated dynamically -->
            </ul>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeHeldBillsModal()"><?php echo $trans[$lang]['close']; ?></button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let customers = [];
        let allProducts = [];

        // Clock Handler
        setInterval(() => {
            const date = new Date();
            document.getElementById('liveClock').innerText = date.toLocaleString();
        }, 1000);

        document.addEventListener('DOMContentLoaded', () => {
            loadCustomers();
            loadAllProducts();
            
            // Listen for global Keyboard Shortcuts
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('productSearchInput').focus();
                } else if (e.key === 'F8') {
                    e.preventDefault();
                    submitCheckout();
                } else if (e.key === 'F4') {
                    e.preventDefault();
                    clearCart();
                } else if (e.key === 'F9') {
                    e.preventDefault();
                    openHeldBillsModal();
                }
            });
        });

        function loadCustomers() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-customers' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'customers-list');
                if (response && response.data) {
                    customers = response.data;
                    const select = document.getElementById('customerSelect');
                    select.innerHTML = `<option value="">👤 ${trans.walk_in}</option>`;
                    customers.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.name} - ${c.phone}</option>`;
                    });
                }
            });
        }

        function loadAllProducts() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'search-products', data: { query: '' } })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'search-results');
                if (response && response.data) {
                    allProducts = response.data;
                    renderCatalog(allProducts);
                }
            });
        }

        function searchProducts(query) {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'search-products', data: { query } })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'search-results');
                if (response && response.data) {
                    // Check if it is a single exact barcode scanned match
                    if (response.data.length === 1 && response.data[0].barcode === query) {
                        addToCart(response.data[0]);
                        document.getElementById('productSearchInput').value = '';
                        loadAllProducts();
                    } else {
                        renderCatalog(response.data);
                    }
                }
            });
        }

        function renderCatalog(products) {
            const results = document.getElementById('catalogResults');
            results.innerHTML = '';

            products.forEach(p => {
                const isLow = parseFloat(p.stock_qty) <= parseFloat(p.min_stock_threshold);
                const badgeStyle = isLow 
                    ? 'background-color: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid var(--danger);' 
                    : 'background-color: rgba(16,185,129,0.1); color: var(--success); border: 1px solid var(--success);';
                
                const card = document.createElement('div');
                card.className = 'product-catalog-card';
                card.onclick = () => addToCart(p);
                card.innerHTML = `
                    <span class="stock-badge" style="${badgeStyle}">${p.stock_qty} ${p.unit}</span>
                    <span class="name">${p.name}</span>
                    <span class="barcode">${p.barcode || '-'}</span>
                    <span class="price">${parseFloat(p.sale_price).toFixed(2)}</span>
                `;
                results.appendChild(card);
            });
        }

        // Cart State Management
        function addToCart(product) {
            const existing = cart.find(item => item.id === product.id);
            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.sale_price),
                    qty: 1,
                    discount: 0
                });
            }
            renderCart();
        }

        function updateCartQty(id, qty) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.qty = parseFloat(qty) || 0;
                if (item.qty <= 0) {
                    removeFromCart(id);
                } else {
                    renderCart();
                }
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            document.getElementById('cartDiscount').value = '0';
            document.getElementById('customerSelect').value = '';
            document.getElementById('paymentMethod').value = 'cash';
            renderCart();
        }

        function renderCart() {
            const tbody = document.querySelector('#cartTable tbody');
            tbody.innerHTML = '';

            if (cart.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">${trans.cart_empty}</td></tr>`;
                calculateTotals();
                return;
            }

            cart.forEach(item => {
                const tr = document.createElement('tr');
                const lineTotal = (item.price * item.qty) - item.discount;
                tr.innerHTML = `
                    <td><strong>${item.name}</strong></td>
                    <td>${item.price.toFixed(2)}</td>
                    <td style="text-align: center;">
                        <input type="number" class="qty-input" value="${item.qty}" min="0.1" step="any" oninput="updateCartQty(${item.id}, this.value)">
                    </td>
                    <td class="item-total">${lineTotal.toFixed(2)}</td>
                    <td style="text-align: center;">
                        <button class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;" onclick="removeFromCart(${item.id})">✖</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += (item.price * item.qty) - item.discount;
            });

            const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
            const payable = Math.max(0, subtotal - discount);
            const method = document.getElementById('paymentMethod').value;
            
            // Set auto paid amount if Cash (or defaults)
            const paidInput = document.getElementById('paidAmount');
            if (method === 'credit') {
                paidInput.value = '0';
                paidInput.disabled = true;
            } else {
                paidInput.disabled = false;
                if (parseFloat(paidInput.value) === 0 || paidInput.value === '') {
                    paidInput.value = payable;
                }
            }

            const paid = parseFloat(paidInput.value) || 0;
            const change = Math.max(0, paid - payable);
            const balance = Math.max(0, payable - paid);

            document.getElementById('subtotalVal').innerText = subtotal.toFixed(2);
            document.getElementById('payableVal').innerText = payable.toFixed(2);
            document.getElementById('changeDueVal').innerText = (method === 'credit' || method === 'split') ? "-" + balance.toFixed(2) : change.toFixed(2);
        }

        function handlePaymentMethodChange() {
            const method = document.getElementById('paymentMethod').value;
            const select = document.getElementById('customerSelect');

            if (method === 'credit' && select.value === '') {
                alert("⚠️ Credit/Udhaar billing requires selecting a customer!");
                document.getElementById('paymentMethod').value = 'cash';
            }
            calculateTotals();
        }

        function handleCustomerSelect() {
            const select = document.getElementById('customerSelect');
            if (select.value === '') {
                // If walk-in, reset method from credit
                if (document.getElementById('paymentMethod').value === 'credit') {
                    document.getElementById('paymentMethod').value = 'cash';
                }
            }
            calculateTotals();
        }

        // Hold / Resume Bill Buffer Memory
        function holdCurrentBill() {
            if (cart.length === 0) return;
            
            const heldBills = JSON.parse(localStorage.getItem('gs_held_bills') || '[]');
            const customerId = document.getElementById('customerSelect').value;
            const custName = customerId ? document.getElementById('customerSelect').options[select.selectedIndex].text : trans.walk_in;
            
            heldBills.push({
                id: Date.now(),
                time: new Date().toLocaleTimeString(),
                customer_id: customerId,
                customer_name: custName,
                cart: cart,
                discount: document.getElementById('cartDiscount').value,
                method: document.getElementById('paymentMethod').value
            });
            
            localStorage.setItem('gs_held_bills', JSON.stringify(heldBills));
            clearCart();
            alert("⏸️ Transaction placed on hold.");
        }

        function openHeldBillsModal() {
            const heldBills = JSON.parse(localStorage.getItem('gs_held_bills') || '[]');
            const list = document.getElementById('heldBillsList');
            list.innerHTML = '';

            if (heldBills.length === 0) {
                list.innerHTML = `<li style="text-align: center; color: var(--text-muted); padding: 20px;">No held bills.</li>`;
            } else {
                heldBills.forEach(b => {
                    const li = document.createElement('li');
                    li.style.display = 'flex';
                    li.style.justifyContent = 'space-between';
                    li.style.alignItems = 'center';
                    li.style.padding = '10px';
                    li.style.borderBottom = '1px solid var(--border-color)';
                    
                    li.innerHTML = `
                        <div>
                            <strong>${b.customer_name}</strong> <small style="color: var(--text-muted);">(${b.time})</small>
                            <div style="font-size: 12px; color: var(--accent);">${b.cart.length} items</div>
                        </div>
                        <div>
                            <button class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;" onclick="resumeHeldBill(${b.id})">Resume</button>
                            <button class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="deleteHeldBill(${b.id})">✖</button>
                        </div>
                    `;
                    list.appendChild(li);
                });
            }

            document.getElementById('heldBillsModal').classList.add('active');
        }

        function closeHeldBillsModal() {
            document.getElementById('heldBillsModal').classList.remove('active');
        }

        function resumeHeldBill(id) {
            let heldBills = JSON.parse(localStorage.getItem('gs_held_bills') || '[]');
            const bill = heldBills.find(b => b.id === id);
            if (bill) {
                cart = bill.cart;
                document.getElementById('customerSelect').value = bill.customer_id || '';
                document.getElementById('cartDiscount').value = bill.discount || '0';
                document.getElementById('paymentMethod').value = bill.method || 'cash';
                
                renderCart();
                closeHeldBillsModal();
                
                // Delete from held storage
                heldBills = heldBills.filter(b => b.id !== id);
                localStorage.setItem('gs_held_bills', JSON.stringify(heldBills));
            }
        }

        function deleteHeldBill(id) {
            let heldBills = JSON.parse(localStorage.getItem('gs_held_bills') || '[]');
            heldBills = heldBills.filter(b => b.id !== id);
            localStorage.setItem('gs_held_bills', JSON.stringify(heldBills));
            openHeldBillsModal();
        }

        // Checkout Transaction Processing
        function submitCheckout() {
            if (cart.length === 0) return;

            const customer_id = document.getElementById('customerSelect').value;
            const method = document.getElementById('paymentMethod').value;

            if (method === 'credit' && customer_id === '') {
                alert("⚠️ Credit/Udhaar billing requires selecting a customer!");
                return;
            }

            const invoice_no = 'INV-' + Date.now();
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += (item.price * item.qty) - item.discount;
            });
            const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
            const total = Math.max(0, subtotal - discount);
            const paid_amount = parseFloat(document.getElementById('paidAmount').value) || 0;
            
            let balance_amount = 0;
            if (method === 'credit') {
                balance_amount = total;
            } else if (total > paid_amount) {
                balance_amount = total - paid_amount; // split payment
            }

            const payload = {
                action: 'process-sale',
                data: {
                    invoice_no,
                    customer_id,
                    subtotal,
                    discount,
                    total,
                    paid_amount,
                    balance_amount,
                    payment_method: method,
                    cart: cart
                }
            };

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'sale-processed');
                if (response && response.data.success) {
                    showReceipt(invoice_no, total, paid_amount, balance_amount, method);
                    clearCart();
                    loadAllProducts(); // Refresh stock levels in catalog
                } else {
                    alert(response ? response.data.msg : "Checkout database transaction failed.");
                }
            });
        }

        // Receipt modal generation
        function showReceipt(invoice_no, total, paid, balance, method) {
            const customerId = document.getElementById('customerSelect').value;
            const select = document.getElementById('customerSelect');
            const custName = customerId ? select.options[select.selectedIndex].text : trans.walk_in;
            const date = new Date().toLocaleString();

            let itemsHTML = '';
            cart.forEach(item => {
                const totalItem = (item.price * item.qty) - item.discount;
                itemsHTML += `
                    <tr>
                        <td>${item.name}<br>${item.qty} x ${item.price.toFixed(2)}</td>
                        <td class="right">${totalItem.toFixed(2)}</td>
                    </tr>
                `;
            });

            document.getElementById('invoiceContent').innerHTML = `
                <div class="center">
                    <span class="header-title">TIJARATPRO</span><br>
                    <span>Saddar, Karachi</span><br>
                    <span>Phone: 0300-1234567</span>
                </div>
                <div class="divider"></div>
                <div>
                    <b>Bill No:</b> ${invoice_no}<br>
                    <b>Date:</b> ${date}<br>
                    <b>Customer:</b> ${custName}<br>
                    <b>Cashier:</b> ${"<?php echo $_SESSION['name']; ?>"}<br>
                </div>
                <div class="divider"></div>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                </table>
                <div class="divider"></div>
                <div class="right" style="font-size: 14px; font-weight: bold;">
                    Total: ${total.toFixed(2)} PKR<br>
                    Paid: ${paid.toFixed(2)} PKR<br>
                    ${balance > 0 ? 'Udhaar Balance: ' + balance.toFixed(2) + ' PKR' : 'Change Due: ' + Math.max(0, paid - total).toFixed(2) + ' PKR'}<br>
                    <small style="font-weight: normal; font-size: 10px;">Method: ${method.toUpperCase()}</small>
                </div>
                <div class="divider"></div>
                <div class="center" style="font-size: 10px;">
                    Thank you for shopping with us!<br>
                    Developed by Umair
                </div>
            `;

            document.getElementById('printModal').classList.add('active');
        }

        function closePrintModal() {
            document.getElementById('printModal').classList.remove('active');
        }

        function triggerPrint() {
            // standard window print of only the invoice card content
            const invoiceHTML = document.getElementById('invoiceContent').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=400');
            printWindow.document.write('<html><head><title>Print Receipt</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('.thermal-invoice { font-family: "Courier New", Courier, monospace; font-size: 12px; line-height: 1.4; }');
            printWindow.document.write('.center { text-align: center; }');
            printWindow.document.write('.divider { border-top: 1px dashed black; margin: 10px 0; }');
            printWindow.document.write('.header-title { font-size: 16px; font-weight: bold; }');
            printWindow.document.write('.invoice-table { width: 100%; border-collapse: collapse; margin: 10px 0; }');
            printWindow.document.write('.invoice-table th, .invoice-table td { text-align: left; padding: 2px 0; }');
            printWindow.document.write('.invoice-table .right, .right { text-align: right; }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write('<div class="thermal-invoice">' + invoiceHTML + '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
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
