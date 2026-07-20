<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

// Language Translations Dictionary
$trans = [
    'en' => [
        'title' => '📊 Reports & Financial Engine',
        'logout' => '🚪 Logout',
        'tab_daybook' => '📖 Day Book',
        'tab_pl' => '📈 Profit & Loss',
        'tab_stock' => '📦 Stock Valuation',
        'tab_customers' => '👥 Receivables (Udhaar)',
        'tab_trial' => '⚖️ Trial Balance',
        'select_date' => 'Select Date:',
        'cash_inflow' => 'Cash In (Inflow)',
        'cash_outflow' => 'Cash Out (Outflow)',
        'sales_revenue' => 'Sales Revenue',
        'purchases_cost' => 'Purchases / Inventory Cost',
        'customer_payments' => 'Customer Payments Collected',
        'supplier_payments' => 'Supplier Payments Made',
        'net_flow' => 'Net Cash Change',
        'month' => 'Month',
        'sales' => 'Total Sales',
        'cogs' => 'Cost of Goods (COGS)',
        'gross_profit' => 'Gross Profit',
        'margin' => 'Profit Margin %',
        'barcode' => 'Barcode',
        'product_name' => 'Product Name',
        'stock_qty' => 'Stock Qty',
        'valuation_cost' => 'Valued (Cost)',
        'valuation_retail' => 'Valued (Retail)',
        'customer_name' => 'Customer Name',
        'phone' => 'Phone',
        'outstanding' => 'Outstanding Balance',
        'print' => '🖨️ Print Report',
        'export' => '📥 Export CSV',
        'account_title' => 'Account / Ledger Title',
        'debit' => 'Debit (Dr)',
        'credit' => 'Credit (Cr)'
    ],
    'ur' => [
        'title' => '📊 Reports & Financial Engine',
        'logout' => '🚪 Logout',
        'tab_daybook' => '📖 Day Book',
        'tab_pl' => '📈 Profit & Loss',
        'tab_stock' => '📦 Stock Valuation',
        'tab_customers' => '👥 Receivables (Udhaar)',
        'tab_trial' => '⚖️ Trial Balance',
        'select_date' => 'Select Date:',
        'cash_inflow' => 'Cash In (Inflow)',
        'cash_outflow' => 'Cash Out (Outflow)',
        'sales_revenue' => 'Sales Revenue',
        'purchases_cost' => 'Purchases / Inventory Cost',
        'customer_payments' => 'Customer Payments Collected',
        'supplier_payments' => 'Supplier Payments Made',
        'net_flow' => 'Net Cash Change',
        'month' => 'Month',
        'sales' => 'Total Sales',
        'cogs' => 'Cost of Goods (COGS)',
        'gross_profit' => 'Gross Profit',
        'margin' => 'Profit Margin %',
        'barcode' => 'Barcode',
        'product_name' => 'Product Name',
        'stock_qty' => 'Stock Qty',
        'valuation_cost' => 'Valued (Cost)',
        'valuation_retail' => 'Valued (Retail)',
        'customer_name' => 'Customer Name',
        'phone' => 'Phone',
        'outstanding' => 'Outstanding Balance',
        'print' => '🖨️ Print Report',
        'export' => '📥 Export CSV',
        'account_title' => 'Account / Ledger Title',
        'debit' => 'Debit (Dr)',
        'credit' => 'Credit (Cr)'
    ]
];

// Helper variables
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_month = $_GET['month'] ?? date('Y-m');

// Load settings database parameters
$config = ['shop_currency' => 'PKR'];
if (file_exists(__DIR__ . '/db_config.json')) {
    $json = json_decode(file_get_contents(__DIR__ . '/db_config.json'), true);
    if ($json) $config = array_merge($config, $json);
}
$currency = $config['shop_currency'];

// --- Day Book Queries ---
$db_cash_sales = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE DATE(created_at) = DATE(:dt) AND payment_method = 'cash'", ['dt' => $selected_date])['total'] ?? 0);
$db_online_sales = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE DATE(created_at) = DATE(:dt) AND payment_method = 'online'", ['dt' => $selected_date])['total'] ?? 0);

$db_cust_payments = dbQuery("SELECT cp.amount, c.name, cp.payment_method, cp.note FROM customer_payments cp JOIN customers c ON cp.customer_id = c.id WHERE DATE(cp.created_at) = DATE(:dt)", ['dt' => $selected_date]);
$db_cust_payments_total = 0.0;
foreach ($db_cust_payments as $cp) $db_cust_payments_total += (float)$cp['amount'];

$db_purchases_paid = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM purchases WHERE DATE(created_at) = DATE(:dt)", ['dt' => $selected_date])['total'] ?? 0);

$db_supp_payments = dbQuery("SELECT sp.amount, s.name, sp.payment_method, sp.note FROM supplier_payments sp JOIN suppliers s ON sp.supplier_id = s.id WHERE DATE(sp.created_at) = DATE(:dt)", ['dt' => $selected_date]);
$db_supp_payments_total = 0.0;
foreach ($db_supp_payments as $sp) $db_supp_payments_total += (float)$sp['amount'];

$daybook_inflow = $db_cash_sales + $db_online_sales + $db_cust_payments_total;
$daybook_outflow = $db_purchases_paid + $db_supp_payments_total;
$daybook_net = $daybook_inflow - $daybook_outflow;


// --- Profit & Loss Queries ---
$pl_records = dbQuery("
    SELECT 
        strftime('%Y-%m', s.created_at) as month,
        SUM(s.total) as total_sales,
        SUM((SELECT SUM(purchase_price * quantity) FROM sale_items WHERE sale_id = s.id)) as total_cogs
    FROM sales s
    GROUP BY month
    ORDER BY month DESC
");


// --- Stock Status Queries ---
$stock_records = dbQuery("
    SELECT barcode, name, purchase_price, sale_price, stock_qty, min_stock_threshold,
           (purchase_price * stock_qty) as cost_val,
           (sale_price * stock_qty) as retail_val
    FROM products
    ORDER BY stock_qty ASC
");
$stock_cost_total = 0.0;
$stock_retail_total = 0.0;
foreach ($stock_records as $st) {
    $stock_cost_total += (float)$st['cost_val'];
    $stock_retail_total += (float)$st['retail_val'];
}


// --- Customer Udhaar Summary ---
$customer_records = dbQuery("
    SELECT name, phone, address, balance
    FROM customers
    WHERE balance > 0
    ORDER BY balance DESC
");
$customer_udhaar_total = 0.0;
foreach ($customer_records as $cr) $customer_udhaar_total += (float)$cr['balance'];


// --- Trial Balance Elements ---
// Assets:
$all_cash_sales = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE payment_method = 'cash'")['total'] ?? 0);
$all_online_sales = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM sales WHERE payment_method = 'online'")['total'] ?? 0);
$all_cust_cash = (float) (dbQueryFirst("SELECT SUM(amount) as total FROM customer_payments WHERE payment_method = 'cash'")['total'] ?? 0);
$all_cust_online = (float) (dbQueryFirst("SELECT SUM(amount) as total FROM customer_payments WHERE payment_method = 'online'")['total'] ?? 0);
$all_supp_cash = (float) (dbQueryFirst("SELECT SUM(amount) as total FROM supplier_payments WHERE payment_method = 'cash'")['total'] ?? 0);
$all_supp_online = (float) (dbQueryFirst("SELECT SUM(amount) as total FROM supplier_payments WHERE payment_method = 'online'")['total'] ?? 0);
$all_purchases_paid = (float) (dbQueryFirst("SELECT SUM(paid_amount) as total FROM purchases")['total'] ?? 0);

$cash_in_hand = ($all_cash_sales + $all_cust_cash) - ($all_supp_cash + $all_purchases_paid);
$bank_balance = ($all_online_sales + $all_cust_online) - $all_supp_online;
$supplier_payables = (float) (dbQueryFirst("SELECT SUM(ABS(balance)) as total FROM suppliers WHERE balance < 0")['total'] ?? 0);
$accumulated_profit = (float) (dbQueryFirst("SELECT SUM((si.sale_price - si.purchase_price) * si.quantity) as profit FROM sale_items si")['profit'] ?? 0);
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
        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }
        
        .tab-nav { display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 25px; }
        .tab-btn { background: transparent; border: none; padding: 12px 24px; font-family: var(--font-heading); font-size: 15px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-radius: var(--radius-sm); transition: var(--transition-smooth); }
        .tab-btn.active { background-color: var(--bg-input); color: var(--accent); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .report-tab-container { max-width: 1100px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 20px; }
        .report-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .financial-summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .fin-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm); }
        .fin-label { font-size: 12.5px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .fin-value { font-size: 22px; font-weight: 700; font-family: var(--font-heading); }
        
        /* Print Styles Override */
        @media print {
            body { background: #fff; color: #000; }
            .sidebar, .header-nav, .tab-nav, .report-actions, .filter-block { display: none !important; }
            .layout-wrapper { display: block; }
            .content-panel { padding: 0; height: auto; overflow: visible; }
            .tab-content { display: none !important; }
            .tab-content.active { display: block !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

    <div class="layout-wrapper">
        
        <!-- Render shared Premium Collapsible Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Display Panel -->
        <main class="content-panel">
            
            <header class="header-nav">
                <h2 style="font-size: 22px; font-weight: 600;">
                    📊 <?php echo $trans[$lang]['title']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="window.print()">🖨️ Print View</button>
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Report Navigation Tab Bar -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('daybook', this)"><?php echo $trans[$lang]['tab_daybook']; ?></button>
                <button class="tab-btn" onclick="switchTab('pl', this)"><?php echo $trans[$lang]['tab_pl']; ?></button>
                <button class="tab-btn" onclick="switchTab('stock', this)"><?php echo $trans[$lang]['tab_stock']; ?></button>
                <button class="tab-btn" onclick="switchTab('customers', this)"><?php echo $trans[$lang]['tab_customers']; ?></button>
                <button class="tab-btn" onclick="switchTab('trial', this)"><?php echo $trans[$lang]['tab_trial']; ?></button>
            </div>

            <!-- Tab 1: Day Book -->
            <section class="tab-content active" id="tab-daybook">
                <div class="report-tab-container">
                    <div class="report-header-flex filter-block">
                    <form method="GET" action="reports.php" style="display: flex; align-items: center; gap: 12px;">
                        <input type="hidden" name="tab" value="daybook">
                        <label style="font-weight: 600; font-size: 14.5px;"><?php echo $trans[$lang]['select_date']; ?></label>
                        <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()" style="padding: 8px 14px; width: 180px;">
                    </form>
                </div>

                <div class="financial-summary-cards">
                    <div class="fin-card">
                        <div class="fin-label"><?php echo $trans[$lang]['cash_inflow']; ?></div>
                        <div class="fin-value" style="color: var(--success);"><?php echo number_format($daybook_inflow, 2) . " $currency"; ?></div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label"><?php echo $trans[$lang]['cash_outflow']; ?></div>
                        <div class="fin-value" style="color: var(--danger);"><?php echo number_format($daybook_outflow, 2) . " $currency"; ?></div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label"><?php echo $trans[$lang]['net_flow']; ?></div>
                        <div class="fin-value" style="color: <?php echo ($daybook_net >= 0) ? 'var(--success)' : 'var(--danger)'; ?>;">
                            <?php echo number_format($daybook_net, 2) . " $currency"; ?>
                        </div>
                    </div>
                </div>

                <div class="card" style="padding: 24px;">
                    <h3 style="margin-bottom: 15px;">📥 Day Inflow Logs</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category / Source</th>
                                <th>Description / Party Name</th>
                                <th>Payment Mode</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>POS Cash Sales</strong></td>
                                <td>Daily Counter Inflow (Cash)</td>
                                <td>Cash</td>
                                <td style="text-align: right;"><?php echo number_format($db_cash_sales, 2) . " $currency"; ?></td>
                            </tr>
                            <tr>
                                <td><strong>POS Online Sales</strong></td>
                                <td>Daily Counter Inflow (Online)</td>
                                <td>Online</td>
                                <td style="text-align: right;"><?php echo number_format($db_online_sales, 2) . " $currency"; ?></td>
                            </tr>
                            <?php foreach ($db_cust_payments as $cp): ?>
                            <tr>
                                <td>Customer Khata Payment</td>
                                <td><?php echo htmlspecialchars($cp['name']); ?> (<?php echo htmlspecialchars($cp['note'] ?? 'No Note'); ?>)</td>
                                <td><?php echo ucfirst($cp['payment_method']); ?></td>
                                <td style="text-align: right; color: var(--success); font-weight: 500;">+<?php echo number_format($cp['amount'], 2) . " $currency"; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card" style="padding: 24px; margin-top: 25px;">
                    <h3 style="margin-bottom: 15px;">📤 Day Outflow Logs</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category / Destination</th>
                                <th>Description / Supplier Name</th>
                                <th>Payment Mode</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Purchases Cost</strong></td>
                                <td>Purchase Inventory Restock Bills</td>
                                <td>Cash/Bank</td>
                                <td style="text-align: right;"><?php echo number_format($db_purchases_paid, 2) . " $currency"; ?></td>
                            </tr>
                            <?php foreach ($db_supp_payments as $sp): ?>
                            <tr>
                                <td>Supplier Bill Settlement</td>
                                <td><?php echo htmlspecialchars($sp['name']); ?> (<?php echo htmlspecialchars($sp['note'] ?? 'No Note'); ?>)</td>
                                <td><?php echo ucfirst($sp['payment_method']); ?></td>
                                <td style="text-align: right; color: var(--danger); font-weight: 500;">-<?php echo number_format($sp['amount'], 2) . " $currency"; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div> <!-- Close report-tab-container -->
            </section>

            <!-- Tab 2: Profit & Loss Statement -->
            <section class="tab-content" id="tab-pl">
                <div class="report-tab-container">
                    <div class="card" style="padding: 24px;">
                    <h3 style="margin-bottom: 20px;">📊 Monthly Profit & Loss Statement</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['month']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['sales']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['cogs']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['gross_profit']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['margin']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pl_records as $pl): 
                                $sales = (float)$pl['total_sales'];
                                $cogs = (float)$pl['total_cogs'];
                                $gp = $sales - $cogs;
                                $margin_pct = ($sales > 0) ? ($gp / $sales) * 100 : 0.0;
                            ?>
                            <tr>
                                <td><strong><?php echo date('F Y', strtotime($pl['month'] . '-01')); ?></strong></td>
                                <td style="text-align: right;"><?php echo number_format($sales, 2) . " $currency"; ?></td>
                                <td style="text-align: right;"><?php echo number_format($cogs, 2) . " $currency"; ?></td>
                                <td style="text-align: right; color: var(--success); font-weight: 600;"><?php echo number_format($gp, 2) . " $currency"; ?></td>
                                <td style="text-align: right; font-weight: 500;"><?php echo number_format($margin_pct, 1) . "%"; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div> <!-- Close report-tab-container -->
            </section>

            <!-- Tab 3: Stock Valuation -->
            <section class="tab-content" id="tab-stock">
                <div class="report-tab-container">
                    <div class="financial-summary-cards">
                    <div class="fin-card">
                        <div class="fin-label"><?php echo $trans[$lang]['valuation_cost']; ?></div>
                        <div class="fin-value"><?php echo number_format($stock_cost_total, 2) . " $currency"; ?></div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label"><?php echo $trans[$lang]['valuation_retail']; ?></div>
                        <div class="fin-value"><?php echo number_format($stock_retail_total, 2) . " $currency"; ?></div>
                    </div>
                    <div class="fin-card">
                        <div class="fin-label">Projected Gross Margin</div>
                        <div class="fin-value" style="color: var(--success);">
                            <?php 
                            $profit_proj = $stock_retail_total - $stock_cost_total;
                            echo number_format($profit_proj, 2) . " $currency";
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card" style="padding: 24px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['barcode']; ?></th>
                                <th><?php echo $trans[$lang]['product_name']; ?></th>
                                <th style="text-align: center;"><?php echo $trans[$lang]['stock_qty']; ?></th>
                                <th style="text-align: right;">Cost Value</th>
                                <th style="text-align: right;">Retail Value</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock_records as $st): 
                                $is_low = ((int)$st['stock_qty'] <= (int)$st['min_stock_threshold']);
                            ?>
                            <tr>
                                <td><span style="font-family: monospace; font-size: 13.5px;"><?php echo htmlspecialchars($st['barcode']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($st['name']); ?></strong></td>
                                <td style="text-align: center;"><?php echo (int)$st['stock_qty']; ?></td>
                                <td style="text-align: right;"><?php echo number_format($st['cost_val'], 2) . " $currency"; ?></td>
                                <td style="text-align: right;"><?php echo number_format($st['retail_val'], 2) . " $currency"; ?></td>
                                <td style="text-align: center;">
                                    <?php if ($is_low): ?>
                                        <span class="status-badge status-unpaid" style="font-size: 11px; padding: 2px 8px;">Low Stock</span>
                                    <?php else: ?>
                                        <span class="status-badge status-paid" style="font-size: 11px; padding: 2px 8px;">In Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div> <!-- Close report-tab-container -->
            </section>

            <!-- Tab 4: Receivables -->
            <section class="tab-content" id="tab-customers">
                <div class="report-tab-container">
                    <div class="financial-summary-cards" style="max-width: 320px; margin: 0;">
                    <div class="fin-card">
                        <div class="fin-label">Total Outflow (Receivables)</div>
                        <div class="fin-value" style="color: var(--danger);"><?php echo number_format($customer_udhaar_total, 2) . " $currency"; ?></div>
                    </div>
                </div>

                <div class="card" style="padding: 24px;">
                    <h3 style="margin-bottom: 20px;">👥 Customer Khata (Udhaar) Statements</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['customer_name']; ?></th>
                                <th><?php echo $trans[$lang]['phone']; ?></th>
                                <th>Address</th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['outstanding']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer_records as $cr): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cr['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cr['phone']); ?></td>
                                <td><?php echo htmlspecialchars($cr['address'] ?? '-'); ?></td>
                                <td style="text-align: right; color: var(--danger); font-weight: 600;"><?php echo number_format($cr['balance'], 2) . " $currency"; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div> <!-- Close report-tab-container -->
            </section>

            <!-- Tab 5: Trial Balance -->
            <section class="tab-content" id="tab-trial">
                <div class="report-tab-container">
                    <div class="card" style="padding: 30px;">
                        <div style="text-align: center; margin-bottom: 25px;">
                        <h2 style="font-size: 20px; font-weight: 700;">⚖️ Trial Balance Report</h2>
                        <p style="color: var(--text-muted); font-size: 13.5px;">Double-entry balances snapshot as of today</p>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $trans[$lang]['account_title']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['debit']; ?></th>
                                <th style="text-align: right;"><?php echo $trans[$lang]['credit']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Cash in Hand (Asset)</td>
                                <td style="text-align: right;"><?php echo number_format($cash_in_hand, 2) . " $currency"; ?></td>
                                <td style="text-align: right;">-</td>
                            </tr>
                            <tr>
                                <td>Bank Account Ledger (Asset)</td>
                                <td style="text-align: right;"><?php echo number_format($bank_balance, 2) . " $currency"; ?></td>
                                <td style="text-align: right;">-</td>
                            </tr>
                            <tr>
                                <td>Stock Inventory Account (Asset)</td>
                                <td style="text-align: right;"><?php echo number_format($stock_cost_total, 2) . " $currency"; ?></td>
                                <td style="text-align: right;">-</td>
                            </tr>
                            <tr>
                                <td>Customer Receivables (Asset)</td>
                                <td style="text-align: right;"><?php echo number_format($customer_udhaar_total, 2) . " $currency"; ?></td>
                                <td style="text-align: right;">-</td>
                            </tr>
                            <tr>
                                <td>Supplier Payables (Liability)</td>
                                <td style="text-align: right;">-</td>
                                <td style="text-align: right;"><?php echo number_format($supplier_payables, 2) . " $currency"; ?></td>
                            </tr>
                            <tr>
                                <td>Retained Earnings / Retained Profit (Equity)</td>
                                <td style="text-align: right;">-</td>
                                <td style="text-align: right;"><?php echo number_format($accumulated_profit, 2) . " $currency"; ?></td>
                            </tr>
                            
                            <!-- Balancing Capital -->
                            <?php 
                            $debits_total = $cash_in_hand + $bank_balance + $stock_cost_total + $customer_udhaar_total;
                            $credits_total = $supplier_payables + $accumulated_profit;
                            $capital_balancing = $debits_total - $credits_total;
                            ?>
                            <tr>
                                <td>Opening Capital Account (Equity - Balancing)</td>
                                <td style="text-align: right;">-</td>
                                <td style="text-align: right;"><?php echo number_format($capital_balancing, 2) . " $currency"; ?></td>
                            </tr>
                            
                            <!-- Totals -->
                            <tr style="border-top: 2px solid var(--text-main); font-weight: 700;">
                                <td><strong>Total Balancing Ledger Check</strong></td>
                                <td style="text-align: right; text-decoration: underline double;"><?php echo number_format($debits_total, 2) . " $currency"; ?></td>
                                <td style="text-align: right; text-decoration: underline double;"><?php echo number_format($credits_total + $capital_balancing, 2) . " $currency"; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div> <!-- Close report-tab-container -->
            </section>

        </main>

    </div>

    <script>
        // Simple client-side tab switcher
        function switchTab(tabId, btn) {
            // Hide all contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(el => el.classList.remove('active'));
            
            // Unhighlight all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(el => el.classList.remove('active'));
            
            // Highlight and show the current selection
            document.getElementById('tab-' + tabId).classList.add('active');
            btn.classList.add('active');
            
            // Save active tab in URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }

        // Language & Theme helper hooks
        function toggleLanguage() {
            const currentLang = document.cookie.replace(/(?:(?:^|.*;\s*)lang\s*\=\s*([^;]*).*$)|^.*$/, "$1");
            const newLang = currentLang === 'ur' ? 'en' : 'ur';
            document.cookie = `lang=${newLang}; path=/; max-age=31536000`;
            location.reload();
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
            location.reload();
        }

        // On page load, read active tab from URL query params
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'daybook';
            const targetBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.outerHTML.includes(activeTab));
            if (targetBtn) {
                switchTab(activeTab, targetBtn);
            }
        });
    </script>
</body>
</html>
