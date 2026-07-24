<?php
session_start();
require_once __DIR__ . '/db_config.php';

// Redirect to login if session is empty
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Translations dictionary
$trans = [
    'en' => [
        'title' => 'TijaratPro',
        'welcome' => 'Welcome',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'sales_today' => 'Sales Today',
        'low_stock' => 'Low Stock Items',
        'pending_udhaar' => 'Pending Udhaar',
        'total_products' => 'Total Products',
        'currency' => 'PKR',
        'quick_actions' => 'Quick Actions',
        'new_sale' => 'New Bill',
        'add_product' => 'Add Product',
        'add_customer' => 'Add Customer',
        // Redesign labels
        'cash_bank' => 'Cash & Bank',
        'cash_in_hand' => 'Cash in Hand',
        'bank_balance' => 'Bank Balance',
        'to_receive' => 'To Receive (Udhaar)',
        'to_pay' => 'To Pay (Supplier)',
        'stock_value' => 'Stock Valuation',
        'value_cost' => 'Valuation at Cost',
        'value_retail' => 'Valuation at Retail',
        'profit_summary' => 'Business Profitability',
        'profit_today' => 'Profit Today',
        'profit_month' => 'Profit This Month',
        'recent_tx' => 'Recent Transactions',
        'tx_date' => 'Date',
        'tx_ref' => 'Ref No.',
        'tx_type' => 'Type',
        'tx_party' => 'Party Name',
        'tx_total' => 'Total',
        'tx_remaining' => 'Remaining',
        'tx_status' => 'Status',
        'day_book' => 'Day Book',
        'profit_loss' => 'Profit & Loss',
        'cash_in' => 'Cash In (Inflow)',
        'cash_out' => 'Cash Out (Outflow)',
        'net_change' => 'Net Cash Change',
        'total_revenue' => 'Total Revenue',
        'cost_of_goods' => 'Cost of Goods Sold',
        'profit_margin' => 'Profit Margin',
        'ribbon_sale' => '🛒 Add Sale (F8)',
        'ribbon_purchase' => '🧾 Add Purchase',
        'ribbon_receive' => '👥 Collect Udhaar',
        'ribbon_product' => '📦 Add Product',
    ],
    'ur' => [
        'title' => 'TijaratPro',
        'welcome' => 'Welcome',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Stock & Inventory',
        'menu_customers' => '👥 Customers & Khata (Udhaar)',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'sales_today' => 'Aaj Ki Sales',
        'low_stock' => 'Low Stock Items',
        'pending_udhaar' => 'Pending Udhaar',
        'total_products' => 'Total Products',
        'currency' => 'PKR',
        'quick_actions' => 'Quick Links',
        'new_sale' => 'Naya Bill Banayein',
        'add_product' => 'Nayi Product',
        'add_customer' => 'Naya Customer',
        // Redesign labels
        'cash_bank' => 'Cash & Bank',
        'cash_in_hand' => 'Cash in Hand',
        'bank_balance' => 'Bank Balance',
        'to_receive' => 'Udhaar Inflow (To Receive)',
        'to_pay' => 'Supplier Outflow (To Pay)',
        'stock_value' => 'Stock Valuation',
        'value_cost' => 'Valuation at Purchase Cost',
        'value_retail' => 'Valuation at Retail Price',
        'profit_summary' => 'Business Profitability',
        'profit_today' => 'Aaj Ka Profit',
        'profit_month' => 'Is Month Ka Profit',
        'recent_tx' => 'Recent Transactions',
        'tx_date' => 'Date',
        'tx_ref' => 'Ref No.',
        'tx_type' => 'Type',
        'tx_party' => 'Party Name',
        'tx_total' => 'Total Amount',
        'tx_remaining' => 'Remaining Amount',
        'tx_status' => 'Status',
        'day_book' => 'Day Book',
        'profit_loss' => 'Profit & Loss',
        'cash_in' => 'Cash In (Inflow)',
        'cash_out' => 'Cash Out (Outflow)',
        'net_change' => 'Net Cash Change',
        'total_revenue' => 'Total Sales Revenue',
        'cost_of_goods' => 'Cost of Goods Sold (COGS)',
        'profit_margin' => 'Profit Margin %',
        'ribbon_sale' => '🛒 Sale Bill Banayein',
        'ribbon_purchase' => '🧾 Khareedari Add Karein',
        'ribbon_receive' => '👥 Udhaar Collect Karein',
        'ribbon_product' => '📦 Nayi Product Add Karein',
    ]
];

// Query comprehensive statistics for dashboard
$total_sales_today = dbQueryFirst("SELECT SUM(total) as total FROM sales WHERE DATE(created_at) = DATE('now')");
$today_sales_val = (float)($total_sales_today['total'] ?? 0.0);

$total_sales_month = dbQueryFirst("SELECT SUM(total) as total FROM sales WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')");
$month_sales_val = (float)($total_sales_month['total'] ?? 0.0);

$total_purchases_today = dbQueryFirst("SELECT SUM(total) as total FROM purchases WHERE DATE(created_at) = DATE('now')");
$today_purchases_val = (float)($total_purchases_today['total'] ?? 0.0);

$total_purchases_month = dbQueryFirst("SELECT SUM(total) as total FROM purchases WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')");
$month_purchases_val = (float)($total_purchases_month['total'] ?? 0.0);

$low_stock_count = dbQueryFirst("SELECT COUNT(*) as count FROM products WHERE stock_qty <= min_stock_threshold");
$low_stock_val = $low_stock_count['count'] ?? 0;

$pending_udhaar = dbQueryFirst("SELECT SUM(balance) as total FROM customers WHERE balance > 0");
$receivables_val = (float)($pending_udhaar['total'] ?? 0.0);

$pending_supplier_pay = dbQueryFirst("SELECT SUM(ABS(balance)) as total FROM suppliers WHERE balance < 0");
$payables_val = (float)($pending_supplier_pay['total'] ?? 0.0);

$total_products = dbQueryFirst("SELECT COUNT(*) as count FROM products");
$total_products_val = $total_products['count'] ?? 0;

// Stock Value at Purchase Price (Cost) and Sale Price (Retail)
$stock_val_cost = dbQueryFirst("SELECT SUM(stock_qty * purchase_price) as total FROM products");
$stock_value_cost_val = (float)($stock_val_cost['total'] ?? 0.0);

$stock_val_retail = dbQueryFirst("SELECT SUM(stock_qty * sale_price) as total FROM products");
$stock_value_retail_val = (float)($stock_val_retail['total'] ?? 0.0);

// Profit Calculations
$profit_today = dbQueryFirst("SELECT SUM((si.sale_price - si.purchase_price) * si.quantity) as profit FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE DATE(s.created_at) = DATE('now')");
$profit_today_val = (float)($profit_today['profit'] ?? 0.0);

$profit_month = dbQueryFirst("SELECT SUM((si.sale_price - si.purchase_price) * si.quantity) as profit FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE strftime('%Y-%m', s.created_at) = strftime('%Y-%m', 'now')");
$profit_month_val = (float)($profit_month['profit'] ?? 0.0);

$cogs_today_query = dbQueryFirst("SELECT SUM(si.purchase_price * si.quantity) as total FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE DATE(s.created_at) = DATE('now')");
$cogs_today = (float)($cogs_today_query['total'] ?? 0.0);

$cogs_month_query = dbQueryFirst("SELECT SUM(si.purchase_price * si.quantity) as total FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE strftime('%Y-%m', s.created_at) = strftime('%Y-%m', 'now')");
$cogs_month = (float)($cogs_month_query['total'] ?? 0.0);

// Cash-in-Hand calculation elements
$cash_sales = dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE payment_method = 'cash'");
$cash_sales_val = (float)($cash_sales['total'] ?? 0.0);

$online_sales = dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE payment_method = 'online'");
$online_sales_val = (float)($online_sales['total'] ?? 0.0);

$cust_cash_payments = dbQueryFirst("SELECT SUM(amount) as total FROM customer_payments WHERE payment_method = 'cash'");
$cust_cash_payments_val = (float)($cust_cash_payments['total'] ?? 0.0);

$cust_online_payments = dbQueryFirst("SELECT SUM(amount) as total FROM customer_payments WHERE payment_method = 'online'");
$cust_online_payments_val = (float)($cust_online_payments['total'] ?? 0.0);

$supp_cash_payments = dbQueryFirst("SELECT SUM(amount) as total FROM supplier_payments WHERE payment_method = 'cash'");
$supp_cash_payments_val = (float)($supp_cash_payments['total'] ?? 0.0);

$supp_online_payments = dbQueryFirst("SELECT SUM(amount) as total FROM supplier_payments WHERE payment_method = 'online'");
$supp_online_payments_val = (float)($supp_online_payments['total'] ?? 0.0);

$purchases_paid = dbQueryFirst("SELECT SUM(paid_amount) as total FROM purchases");
$purchases_paid_val = (float)($purchases_paid['total'] ?? 0.0);

$cash_in_hand = ($cash_sales_val + $cust_cash_payments_val) - ($supp_cash_payments_val + $purchases_paid_val);
if ($cash_in_hand < 0) $cash_in_hand = 0.0;

$bank_balance = ($online_sales_val + $cust_online_payments_val) - $supp_online_payments_val;
if ($bank_balance < 0) $bank_balance = 0.0;

// Unified Recent Transactions List (Last 5 transactions)
$recent_tx = dbQuery("
    SELECT * FROM (
        SELECT 'Sale' as tx_type, id as tx_id, invoice_no as ref_no, (SELECT name FROM customers WHERE id = customer_id) as party, total, paid_amount, balance_amount, created_at FROM sales
        UNION ALL
        SELECT 'Purchase' as tx_type, id as tx_id, purchase_no as ref_no, (SELECT name FROM suppliers WHERE id = supplier_id) as party, total, paid_amount, balance_amount, created_at FROM purchases
    ) ORDER BY created_at DESC LIMIT 5
");

// Fetch Last 7 Days Sales vs Purchases for Chart
$chart_dates = [];
$chart_sales = [];
$chart_purchases = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[] = date('d M', strtotime($date));
    
    $day_sales = dbQueryFirst("SELECT SUM(total) as total FROM sales WHERE DATE(created_at) = DATE(?)", [$date]);
    $chart_sales[] = (float)($day_sales['total'] ?? 0.0);
    
    $day_purchases = dbQueryFirst("SELECT SUM(total) as total FROM purchases WHERE DATE(created_at) = DATE(?)", [$date]);
    $chart_purchases[] = (float)($day_purchases['total'] ?? 0.0);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $trans[$lang]['title']; ?> - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Redesign UI overrides and additional styling */
        .ribbon-actions {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .ribbon-btn {
            flex: 1;
            min-width: 180px;
            padding: 16px 20px;
            border-radius: var(--radius-md);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-sm);
            border: none;
            cursor: pointer;
        }
        .ribbon-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            filter: brightness(1.1);
        }
        .btn-sale { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #020617 !important; font-weight: 700; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25); }
        .btn-purchase { background-color: #1e293b; color: #f8fafc !important; border: 1px solid #334155; }
        .btn-payment { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #ffffff !important; font-weight: 600; }
        .btn-product { background-color: #0f172a; color: #f8fafc !important; border: 1px solid #334155; }

        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        .section-box {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }
        .section-box h3 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 15px;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
            color: var(--text-main);
            display: flex;
            justify-content: space-between;
        }
        .section-metrics {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }
        .metric-row:not(:last-child) {
            border-bottom: 1px dashed var(--border-color);
        }
        .metric-label {
            font-size: 13.5px;
            color: var(--text-muted);
        }
        .metric-val {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--text-main);
        }
        .val-positive { color: var(--success) !important; }
        .val-negative { color: var(--danger) !important; }
        .val-warning { color: var(--warning) !important; }

        .charts-grid {
            display: grid;
            grid-template-columns: 1.8fr 1.2fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Tabs System styling */
        .tab-nav {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 16px;
            gap: 12px;
        }
        .tab-btn {
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 8px 12px;
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition-smooth);
        }
        .tab-btn:hover {
            color: var(--text-main);
        }
        .tab-btn.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-sale {
            background-color: rgba(237, 26, 59, 0.1);
            color: var(--accent);
            border: 1px solid var(--accent);
        }
        .badge-purchase {
            background-color: rgba(15, 118, 110, 0.1);
            color: #0f766e;
            border: 1px solid #0f766e;
        }
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        .badge-red {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table th, .data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13.5px;
        }
        .data-table th {
            background-color: var(--bg-input);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11.5px;
        }
        .data-table tr:hover td {
            background-color: var(--bg-app);
        }
        /* Sidebar Layout Structure */
        .layout-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 24px;
            transition: var(--transition-smooth);
            height: 100vh;
        }

        .sidebar-brand {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            color: var(--text-main);
            text-decoration: none;
            font-family: var(--font-heading);
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .menu-link:hover, .menu-link.active {
            background-color: var(--bg-input);
            color: var(--accent);
            border-left: 4px solid var(--accent);
            padding-left: 16px;
        }

        /* Main Content Panel */
        .content-panel {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 30px;
            height: 100vh;
            overflow-y: auto;
        }

        /* Top Header Navigation */
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px 30px;
            box-shadow: var(--shadow-sm);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--accent);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: capitalize;
        }

        /* Dashboard Overview Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition-smooth);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-details {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            font-family: var(--font-heading);
        }

        .stat-icon {
            font-size: 36px;
            opacity: 0.8;
            padding: 12px;
            border-radius: var(--radius-sm);
            background-color: var(--bg-app);
        }

        /* Action Cards Section */
        .action-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .action-list {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 15px;
        }


    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Display Panel -->
        <main class="content-panel">
            
            <?php 
            $currentUserName = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
            $currentUserRole = $_SESSION['role'] ?? 'Admin';
            $avatarInitial = !empty($currentUserName) ? strtoupper(substr($currentUserName, 0, 1)) : 'A';
            ?>
            <!-- Top Navigation and System Controls -->
            <header class="header-nav">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo htmlspecialchars($avatarInitial); ?>
                    </div>
                    <div class="user-details" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                        <span class="user-name"><?php echo $trans[$lang]['welcome']; ?>, <?php echo htmlspecialchars($currentUserName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars(ucfirst($currentUserRole)); ?></span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()">
                        <?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?>
                    </button>
                    <button class="toggle-btn" onclick="toggleTheme()">
                        <?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?>
                    </button>
                </div>
            </header>

            <!-- Ribbon Actions (Vyapar Style) -->
            <div class="ribbon-actions">
                <a href="billing.php" class="ribbon-btn btn-sale">
                    <?php echo $trans[$lang]['ribbon_sale']; ?>
                </a>
                <a href="purchases.php" class="ribbon-btn btn-purchase">
                    <?php echo $trans[$lang]['ribbon_purchase']; ?>
                </a>
                <a href="customers.php" class="ribbon-btn btn-payment">
                    <?php echo $trans[$lang]['ribbon_receive']; ?>
                </a>
                <a href="products.php" class="ribbon-btn btn-product">
                    <?php echo $trans[$lang]['ribbon_product']; ?>
                </a>
            </div>

            <!-- Financial Status Metrics Grid -->
            <section class="dashboard-sections">
                <!-- Section: Cash Book -->
                <div class="section-box">
                    <h3>💵 <?php echo $trans[$lang]['cash_bank']; ?></h3>
                    <div class="section-metrics">
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['cash_in_hand']; ?></span>
                            <span class="metric-val val-positive"><?php echo number_format($cash_in_hand, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['bank_balance']; ?></span>
                            <span class="metric-val val-positive"><?php echo number_format($bank_balance, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                    </div>
                </div>

                <!-- Section: Receivables & Payables -->
                <div class="section-box">
                    <h3>👥 Udhaar / Ledger (Khata)</h3>
                    <div class="section-metrics">
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['to_receive']; ?></span>
                            <span class="metric-val val-negative"><?php echo number_format($receivables_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['to_pay']; ?></span>
                            <span class="metric-val val-warning"><?php echo number_format($payables_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                    </div>
                </div>

                <!-- Section: Stock Valuation -->
                <div class="section-box">
                    <h3>📦 <?php echo $trans[$lang]['stock_value']; ?></h3>
                    <div class="section-metrics">
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['value_cost']; ?></span>
                            <span class="metric-val"><?php echo number_format($stock_value_cost_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['value_retail']; ?></span>
                            <span class="metric-val"><?php echo number_format($stock_value_retail_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                    </div>
                </div>

                <!-- Section: Business Profitability -->
                <div class="section-box">
                    <h3>📈 <?php echo $trans[$lang]['profit_summary']; ?></h3>
                    <div class="section-metrics">
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['profit_today']; ?></span>
                            <span class="metric-val val-positive"><?php echo number_format($profit_today_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label"><?php echo $trans[$lang]['profit_month']; ?></span>
                            <span class="metric-val val-positive"><?php echo number_format($profit_month_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts and Reports Panels -->
            <section class="charts-grid">
                <!-- Trend Chart -->
                <div class="card" style="padding: 20px;">
                    <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 15px;">📊 7-Day Business Trend (Guzashta 7 din ki sales aur khareedari)</h3>
                    <div style="height: 320px; position: relative;">
                        <canvas id="businessTrendChart"></canvas>
                    </div>
                </div>

                <!-- Day Book & P&L Tabs -->
                <div class="card" style="padding: 20px;">
                    <div class="tab-nav">
                        <button class="tab-btn active" onclick="switchTab(event, 'daybook-panel')">
                            📖 <?php echo $trans[$lang]['day_book']; ?>
                        </button>
                        <button class="tab-btn" onclick="switchTab(event, 'pl-panel')">
                            📊 <?php echo $trans[$lang]['profit_loss']; ?>
                        </button>
                    </div>

                    <!-- Panel: Day Book -->
                    <div id="daybook-panel" class="tab-panel active">
                        <div class="section-metrics" style="margin-top: 10px;">
                            <div class="metric-row">
                                <span class="metric-label"><?php echo $trans[$lang]['cash_in']; ?></span>
                                <span class="metric-val val-positive">+<?php echo number_format($cash_sales_val + $cust_cash_payments_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label"><?php echo $trans[$lang]['cash_out']; ?></span>
                                <span class="metric-val val-negative">-<?php echo number_format($supp_cash_payments_val + $purchases_paid_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                            </div>
                            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 8px 0;">
                            <?php 
                            $cash_diff = ($cash_sales_val + $cust_cash_payments_val) - ($supp_cash_payments_val + $purchases_paid_val);
                            ?>
                            <div class="metric-row">
                                <span class="metric-label" style="font-weight: 600;"><?php echo $trans[$lang]['net_change']; ?></span>
                                <span class="metric-val <?php echo $cash_diff >= 0 ? 'val-positive' : 'val-negative'; ?>" style="font-weight: 800;">
                                    <?php echo ($cash_diff >= 0 ? '+' : '') . number_format($cash_diff, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Profit & Loss -->
                    <div id="pl-panel" class="tab-panel">
                        <div class="section-metrics" style="margin-top: 10px;">
                            <div class="metric-row">
                                <span class="metric-label"><?php echo $trans[$lang]['total_revenue']; ?> (Month)</span>
                                <span class="metric-val"><?php echo number_format($month_sales_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label"><?php echo $trans[$lang]['cost_of_goods']; ?> (Month)</span>
                                <span class="metric-val" style="color: var(--text-muted);"><?php echo number_format($cogs_month, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small></span>
                            </div>
                            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 8px 0;">
                            <div class="metric-row">
                                <span class="metric-label" style="font-weight: 600;"><?php echo $trans[$lang]['profit_month']; ?></span>
                                <span class="metric-val val-positive" style="font-weight: 800;">
                                    <?php echo number_format($profit_month_val, 2); ?> <small><?php echo $trans[$lang]['currency']; ?></small>
                                </span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label"><?php echo $trans[$lang]['profit_margin']; ?></span>
                                <span class="metric-val val-positive" style="font-weight: 800;">
                                    <?php 
                                    $margin = $month_sales_val > 0 ? ($profit_month_val / $month_sales_val * 100) : 0.0;
                                    echo number_format($margin, 1) . '%';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Transactions Table -->
            <section class="card" style="padding: 24px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    📝 <?php echo $trans[$lang]['recent_tx']; ?>
                </h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['tx_date']; ?></th>
                                <th><?php echo $trans[$lang]['tx_ref']; ?></th>
                                <th><?php echo $trans[$lang]['tx_type']; ?></th>
                                <th><?php echo $trans[$lang]['tx_party']; ?></th>
                                <th><?php echo $trans[$lang]['tx_total']; ?></th>
                                <th><?php echo $trans[$lang]['tx_remaining']; ?></th>
                                <th><?php echo $trans[$lang]['tx_status']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_tx)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">No transactions logged yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_tx as $tx): ?>
                                    <?php 
                                    $status_badge = '';
                                    if ($tx['balance_amount'] <= 0) {
                                        $status_badge = '<span class="badge badge-success">' . ($lang === 'ur' ? 'Paid' : 'Paid') . '</span>';
                                    } else if ($tx['paid_amount'] > 0) {
                                        $status_badge = '<span class="badge badge-warning">' . ($lang === 'ur' ? 'Kuch Baqi' : 'Partial') . '</span>';
                                    } else {
                                        $status_badge = '<span class="badge badge-red">' . ($lang === 'ur' ? 'Unpaid' : 'Unpaid') . '</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y H:i', strtotime($tx['created_at'])); ?></td>
                                        <td>
                                            <strong>
                                                <?php if ($tx['tx_type'] === 'Sale'): ?>
                                                    <a href="billing.php?view_invoice=<?php echo $tx['ref_no']; ?>" style="color: var(--accent); font-family: monospace; font-size: 14px; text-decoration: none;"><?php echo $tx['ref_no']; ?></a>
                                                <?php else: ?>
                                                    <a href="purchases.php" style="color: #0f766e; font-family: monospace; font-size: 14px; text-decoration: none;"><?php echo $tx['ref_no']; ?></a>
                                                <?php endif; ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $tx['tx_type'] === 'Sale' ? 'badge-sale' : 'badge-purchase'; ?>">
                                                <?php echo $tx['tx_type'] === 'Sale' ? ($lang === 'ur' ? 'Sale (Bikri)' : 'Sale') : ($lang === 'ur' ? 'Purchase (Stock)' : 'Purchase'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($tx['party'] ?? ($tx['tx_type'] === 'Sale' ? ($lang === 'ur' ? 'Cash Customer' : 'Cash Customer') : ($lang === 'ur' ? 'Cash Supplier' : 'Cash Supplier'))); ?></td>
                                        <td><strong><?php echo number_format($tx['total'], 2); ?></strong> <small style="font-size: 11px;"><?php echo $trans[$lang]['currency']; ?></small></td>
                                        <td style="color: <?php echo $tx['balance_amount'] > 0 ? 'var(--danger)' : 'inherit'; ?>; font-weight: <?php echo $tx['balance_amount'] > 0 ? '700' : 'normal'; ?>">
                                            <?php echo number_format($tx['balance_amount'], 2); ?> <small style="font-size: 11px;"><?php echo $trans[$lang]['currency']; ?></small>
                                        </td>
                                        <td><?php echo $status_badge; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>

    </div>

    <script>
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

        function switchTab(event, panelId) {
            // Remove active class from all buttons and panels
            const card = event.currentTarget.closest('.card');
            card.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            card.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
            
            // Set current active
            event.currentTarget.classList.add('active');
            card.querySelector('#' + panelId).classList.add('active');
        }

        // Render Business Trend Chart using Chart.js
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('businessTrendChart').getContext('2d');
            
            // Ensure color theme configurations are synced
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const labelColor = isDark ? '#f8fafc' : '#334155';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_dates); ?>,
                    datasets: [
                        {
                            label: 'Sales (Bikri)',
                            data: <?php echo json_encode($chart_sales); ?>,
                            backgroundColor: 'rgba(245, 158, 11, 0.85)',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Purchases (Khareedari)',
                            data: <?php echo json_encode($chart_purchases); ?>,
                            backgroundColor: 'rgba(71, 85, 105, 0.85)',
                            borderColor: '#475569',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: labelColor,
                                font: { family: 'Poppins', size: 12 }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: labelColor }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: { color: labelColor }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
