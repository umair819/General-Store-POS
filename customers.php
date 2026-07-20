<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

$whatsapp_mode = 'link';
if (file_exists(__DIR__ . '/db_config.json')) {
    $config_json = json_decode(file_get_contents(__DIR__ . '/db_config.json'), true);
    if (isset($config_json['whatsapp_mode'])) {
        $whatsapp_mode = $config_json['whatsapp_mode'];
    }
}

$trans = [
    'en' => [
        'title' => 'Customer Khata (Udhaar)',
        'menu_billing' => '🛒 POS Billing',
        'menu_inventory' => '📦 Stock/Inventory',
        'menu_customers' => '👥 Customer Khata',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        'add_customer' => 'Add Customer',
        'search_placeholder' => 'Search by Name or Phone...',
        'name' => 'Name',
        'phone' => 'Phone Number',
        'address' => 'Address',
        'balance' => 'Outstanding Balance (Udhaar)',
        'birthdate' => 'Birthdate',
        'anniversary' => 'Anniversary',
        'actions' => 'Actions',
        'view_ledger' => 'Ledger / Statement',
        'collect_payment' => 'Collect Payment',
        'send_reminder' => 'Send WhatsApp Reminder',
        'save' => 'Save Customer',
        'edit_customer' => 'Edit Customer',
        'delete_confirm' => 'Are you sure you want to delete this customer?',
        'no_customers' => 'No customers found.',
        'payment_amount' => 'Payment Amount (PKR)',
        'payment_method' => 'Payment Method',
        'note' => 'Note / Memo',
        'record_payment' => 'Record Payment',
        'ledger_title' => 'Customer Ledger Statement',
        'date' => 'Date & Time',
        'reference' => 'Reference No.',
        'debit' => 'Debit (Sale)',
        'credit' => 'Credit (Paid)',
        'running_balance' => 'Running Balance',
        'close' => 'Close',
        'print_ledger' => 'Print Statement',
        'whatsapp_preview' => 'WhatsApp Reminder Preview',
        'send_via_wa' => 'Send via WhatsApp Web',
        'empty_ledger' => 'No transactions recorded for this customer.',
    ],
    'ur' => [
        'title' => 'Customer Khata (Udhaar)',
        'menu_billing' => '🛒 POS Billing',
        'menu_inventory' => '📦 Stock/Inventory',
        'menu_customers' => '👥 Customer Khata',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        'add_customer' => 'Naya Customer Add Karein',
        'search_placeholder' => 'Name ya Phone number se search karein...',
        'name' => 'Name',
        'phone' => 'Phone Number',
        'address' => 'Address',
        'balance' => 'Outstanding Balance (Udhaar)',
        'birthdate' => 'Birthdate',
        'anniversary' => 'Anniversary',
        'actions' => 'Actions',
        'view_ledger' => 'Ledger / Statement',
        'collect_payment' => 'Payment Collect Karein',
        'send_reminder' => 'Send WhatsApp Reminder',
        'save' => 'Customer Save Karein',
        'edit_customer' => 'Customer Edit Karein',
        'delete_confirm' => 'Kya aap sach mein is customer ko delete karna chahte hain?',
        'no_customers' => 'Koi customer nahi mila.',
        'payment_amount' => 'Payment Amount (PKR)',
        'payment_method' => 'Payment Method',
        'note' => 'Note / Memo',
        'record_payment' => 'Payment Record Karein',
        'ledger_title' => 'Customer Ledger Statement',
        'date' => 'Date & Time',
        'reference' => 'Reference No.',
        'debit' => 'Debit (Sale)',
        'credit' => 'Credit (Paid)',
        'running_balance' => 'Running Balance',
        'close' => 'Close',
        'print_ledger' => 'Print Statement',
        'whatsapp_preview' => 'WhatsApp Reminder Preview',
        'send_via_wa' => 'Send via WhatsApp Web',
        'empty_ledger' => 'Is customer ka koi transaction record nahi hai.',
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
        
        /* Toolbar */
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .search-bar { position: relative; flex-grow: 1; max-width: 450px; }
        .search-input { width: 100%; padding: 12px 16px; padding-left: 40px; border-radius: 30px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 15px; outline: none; }
        .search-input:focus { border-color: var(--accent); }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        /* Customer Table Grid */
        .table-responsive { width: 100%; overflow-x: auto; background-color: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); }
        .data-table tr:hover { background-color: rgba(0, 0, 0, 0.02); }
        
        .balance-badge { padding: 6px 12px; border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .balance-badge.due { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .balance-badge.clear { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }

        /* Modal Overlay and Content */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; visibility: hidden; opacity: 0; transition: var(--transition-smooth); }
        .modal-overlay.active { visibility: visible; opacity: 1; }
        .modal-card { background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); width: 100%; max-width: 600px; padding: 30px; box-shadow: var(--shadow-lg); transform: translateY(-20px); transition: var(--transition-smooth); max-height: 90vh; overflow-y: auto; }
        .modal-overlay.active .modal-card { transform: translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 20px; }

        /* Ledger specific styling */
        .ledger-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; background: var(--bg-input); padding: 16px; border-radius: var(--radius-sm); }
        .ledger-summary-item { display: flex; flex-direction: column; gap: 4px; }
        .ledger-summary-lbl { font-size: 12px; color: var(--text-muted); font-weight: 500; }
        .ledger-summary-val { font-size: 18px; font-weight: 700; font-family: var(--font-heading); }


    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Display Panel -->
        <main class="content-panel">
            
            <!-- Top Controls -->
            <header class="header-nav">
                <h2 style="font-size: 22px; font-weight: 600;">
                    👥 <?php echo $trans[$lang]['title']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <!-- Toolbar -->
            <section class="toolbar">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input class="search-input" type="text" id="customerSearch" placeholder="<?php echo $trans[$lang]['search_placeholder']; ?>" onkeyup="filterCustomers()">
                </div>
                <button class="btn btn-primary" onclick="openCustomerModal()">
                    ➕ <?php echo $trans[$lang]['add_customer']; ?>
                </button>
            </section>

            <!-- Customers Data Table -->
            <section class="table-responsive">
                <table class="data-table" id="customerTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['name']; ?></th>
                            <th><?php echo $trans[$lang]['phone']; ?></th>
                            <th><?php echo $trans[$lang]['address']; ?></th>
                            <th><?php echo $trans[$lang]['balance']; ?></th>
                            <th><?php echo $trans[$lang]['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <!-- Loaded via Ajax -->
                    </tbody>
                </table>
            </section>

        </main>

    </div>

    <!-- MODAL 1: ADD/EDIT CUSTOMER -->
    <div class="modal-overlay" id="customerModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle"><?php echo $trans[$lang]['add_customer']; ?></h3>
                <button onclick="closeCustomerModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form id="customerForm" onsubmit="saveCustomer(event)">
                <input type="hidden" id="customerId" name="id">
                
                <div class="form-group">
                    <label class="form-label" for="custName"><?php echo $trans[$lang]['name']; ?> *</label>
                    <input class="form-control" type="text" id="custName" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="custPhone"><?php echo $trans[$lang]['phone']; ?> *</label>
                    <input class="form-control" type="text" id="custPhone" placeholder="e.g. 03001234567" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="custAddress"><?php echo $trans[$lang]['address']; ?></label>
                    <input class="form-control" type="text" id="custAddress">
                </div>

                <div class="form-group" id="initialBalanceGroup">
                    <label class="form-label" for="custBalance"><?php echo $trans[$lang]['balance']; ?></label>
                    <input class="form-control" type="number" id="custBalance" step="0.01" value="0.00">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="custBirthdate"><?php echo $trans[$lang]['birthdate']; ?></label>
                        <input class="form-control" type="date" id="custBirthdate">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="custAnniversary"><?php echo $trans[$lang]['anniversary']; ?></label>
                        <input class="form-control" type="date" id="custAnniversary">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" onclick="closeCustomerModal()"><?php echo $trans[$lang]['close']; ?></button>
                    <button class="btn btn-primary" type="submit"><?php echo $trans[$lang]['save']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: LEDGER VIEW -->
    <div class="modal-overlay" id="ledgerModal">
        <div class="modal-card" style="max-width: 800px;">
            <div class="modal-header">
                <h3>📜 <?php echo $trans[$lang]['ledger_title']; ?></h3>
                <button onclick="closeLedgerModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div class="ledger-summary">
                <div class="ledger-summary-item">
                    <span class="ledger-summary-lbl"><?php echo $trans[$lang]['name']; ?></span>
                    <span class="ledger-summary-val" id="ledgerCustName">-</span>
                </div>
                <div class="ledger-summary-item">
                    <span class="ledger-summary-lbl"><?php echo $trans[$lang]['phone']; ?></span>
                    <span class="ledger-summary-val" id="ledgerCustPhone">-</span>
                </div>
                <div class="ledger-summary-item">
                    <span class="ledger-summary-lbl"><?php echo $trans[$lang]['balance']; ?></span>
                    <span class="ledger-summary-val" id="ledgerCustBalance" style="color: var(--accent);">-</span>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="data-table" id="ledgerTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['date']; ?></th>
                            <th><?php echo $trans[$lang]['reference']; ?></th>
                            <th><?php echo $trans[$lang]['debit']; ?></th>
                            <th><?php echo $trans[$lang]['credit']; ?></th>
                            <th><?php echo $trans[$lang]['running_balance']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="ledgerTableBody">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeLedgerModal()"><?php echo $trans[$lang]['close']; ?></button>
                <button class="btn btn-success" onclick="printLedger()"><span style="font-size: 13px;">🖨️</span> <?php echo $trans[$lang]['print_ledger']; ?></button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: COLLECT PAYMENT -->
    <div class="modal-overlay" id="paymentModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>💵 <?php echo $trans[$lang]['collect_payment']; ?></h3>
                <button onclick="closePaymentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <form id="paymentForm" onsubmit="recordPayment(event)">
                <input type="hidden" id="paymentCustId">
                
                <div class="form-group">
                    <label class="form-label" for="payAmount"><?php echo $trans[$lang]['payment_amount']; ?> *</label>
                    <input class="form-control" type="number" id="payAmount" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payMethod"><?php echo $trans[$lang]['payment_method']; ?></label>
                    <select class="form-control" id="payMethod">
                        <option value="cash">Cash</option>
                        <option value="online">Online / EasyPaisa / JazzCash</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payNote"><?php echo $trans[$lang]['note']; ?></label>
                    <input class="form-control" type="text" id="payNote" placeholder="e.g. Received by hand / Slip #123">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" onclick="closePaymentModal()"><?php echo $trans[$lang]['close']; ?></button>
                    <button class="btn btn-primary" type="submit"><?php echo $trans[$lang]['record_payment']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: WHATSAPP REMINDER PREVIEW -->
    <div class="modal-overlay" id="whatsappModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>💬 <?php echo $trans[$lang]['whatsapp_preview']; ?></h3>
                <button onclick="closeWhatsappModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input class="form-control" type="text" id="waPhoneDisplay" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Message Template</label>
                <textarea class="form-control" id="waMessageText" rows="6" style="resize: vertical; font-family: inherit;"></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeWhatsappModal()"><?php echo $trans[$lang]['close']; ?></button>
                <button class="btn btn-success" onclick="dispatchWhatsapp()"><span style="font-size: 13px;">📲</span> <?php echo $trans[$lang]['send_via_wa']; ?></button>
            </div>
        </div>
    </div>

    <script>
        let allCustomers = [];
        const trans = <?php echo json_encode($trans[$lang]); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            loadCustomers();
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
                if (response) {
                    allCustomers = response.data;
                    renderCustomersTable(allCustomers);
                }
            })
            .catch(err => console.error("Error loading customers:", err));
        }

        function renderCustomersTable(customers) {
            const tbody = document.getElementById('customerTableBody');
            tbody.innerHTML = '';

            if (customers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">${trans.no_customers}</td></tr>`;
                return;
            }

            customers.forEach(c => {
                const balanceVal = parseFloat(c.balance);
                const isDue = balanceVal > 0;
                const balanceClass = isDue ? 'due' : 'clear';
                const balanceTxt = balanceVal.toFixed(2) + " PKR";

                tbody.innerHTML += `
                    <tr id="cust-row-${c.id}">
                        <td style="font-weight: 500;">${escapeHtml(c.name)}</td>
                        <td>${escapeHtml(c.phone)}</td>
                        <td>${escapeHtml(c.address || '-')}</td>
                        <td>
                            <span class="balance-badge ${balanceClass}">
                                ${isDue ? '⚠️' : '✅'} ${balanceTxt}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button class="btn btn-secondary" style="padding: 6px 14px; font-size: 12px;" onclick="viewLedger(${c.id})">
                                    📜 ${trans.view_ledger}
                                </button>
                                ${isDue ? `
                                    <button class="btn btn-success" style="padding: 6px 14px; font-size: 12px;" onclick="openPaymentModal(${c.id})">
                                        💵 ${trans.collect_payment}
                                    </button>
                                    <button class="btn btn-primary" style="padding: 6px 14px; font-size: 12px; background-color: var(--primary-blue);" onclick="openWhatsappModal(${c.id}, ${balanceVal})">
                                        📲 ${trans.send_reminder}
                                    </button>
                                ` : ''}
                                <button class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;" onclick="editCustomer(${c.id})">
                                    ✏️
                                </button>
                                <button class="btn btn-danger" style="padding: 6px 10px; font-size: 12px;" onclick="deleteCustomer(${c.id})">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        function filterCustomers() {
            const query = document.getElementById('customerSearch').value.toLowerCase().trim();
            if (!query) {
                renderCustomersTable(allCustomers);
                return;
            }

            const filtered = allCustomers.filter(c => 
                c.name.toLowerCase().includes(query) || 
                c.phone.includes(query)
            );
            renderCustomersTable(filtered);
        }

        // Customer Add/Edit
        function openCustomerModal() {
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            document.getElementById('modalTitle').innerText = trans.add_customer;
            document.getElementById('initialBalanceGroup').style.display = 'block';
            document.getElementById('customerModal').classList.add('active');
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').classList.remove('active');
        }

        function saveCustomer(e) {
            e.preventDefault();
            const id = document.getElementById('customerId').value;
            const name = document.getElementById('custName').value.trim();
            const phone = document.getElementById('custPhone').value.trim();
            const address = document.getElementById('custAddress').value.trim();
            const balance = document.getElementById('custBalance').value;
            const birthdate = document.getElementById('custBirthdate').value;
            const anniversary = document.getElementById('custAnniversary').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-customer',
                    data: { id, name, phone, address, balance, birthdate, anniversary }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'customer-saved');
                if (response && response.data.success) {
                    closeCustomerModal();
                    loadCustomers();
                } else {
                    alert(response ? response.data.msg : "Failed to save customer");
                }
            })
            .catch(err => console.error("Error saving customer:", err));
        }

        function editCustomer(id) {
            const c = allCustomers.find(item => item.id == id);
            if (!c) return;

            document.getElementById('customerId').value = c.id;
            document.getElementById('custName').value = c.name;
            document.getElementById('custPhone').value = c.phone;
            document.getElementById('custAddress').value = c.address || '';
            document.getElementById('custBalance').value = c.balance;
            document.getElementById('custBirthdate').value = c.birthdate || '';
            document.getElementById('custAnniversary').value = c.anniversary || '';
            
            document.getElementById('modalTitle').innerText = trans.edit_customer;
            document.getElementById('initialBalanceGroup').style.display = 'none'; // Don't modify initial balance here
            document.getElementById('customerModal').classList.add('active');
        }

        function deleteCustomer(id) {
            if (!confirm(trans.delete_confirm)) return;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete-customer',
                    data: { id }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'customer-deleted');
                if (response && response.data.success) {
                    loadCustomers();
                } else {
                    alert(response ? response.data.msg : "Cannot delete customer.");
                }
            })
            .catch(err => console.error("Error deleting customer:", err));
        }

        // Ledger View & Print
        function viewLedger(id) {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get-customer-ledger',
                    data: { customer_id: id }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'customer-ledger');
                if (response && response.data.success) {
                    const c = response.data.customer;
                    document.getElementById('ledgerCustName').innerText = c.name;
                    document.getElementById('ledgerCustPhone').innerText = c.phone;
                    document.getElementById('ledgerCustBalance').innerText = parseFloat(c.balance).toFixed(2) + " PKR";
                    
                    const tbody = document.getElementById('ledgerTableBody');
                    tbody.innerHTML = '';
                    
                    const ledger = response.data.ledger;
                    if (ledger.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">${trans.empty_ledger}</td></tr>`;
                    } else {
                        ledger.forEach(entry => {
                            const date = new Date(entry.created_at).toLocaleString();
                            const isSale = entry.type === 'sale';
                            const debitText = isSale ? parseFloat(entry.debit).toFixed(2) : '-';
                            const creditText = !isSale ? parseFloat(entry.credit).toFixed(2) : (parseFloat(entry.credit) > 0 ? parseFloat(entry.credit).toFixed(2) : '-');
                            
                            tbody.innerHTML += `
                                <tr>
                                    <td>${date}</td>
                                    <td><strong style="color: ${isSale ? 'var(--text-main)' : 'var(--success)'};">${entry.reference}</strong></td>
                                    <td style="color: var(--danger);">${debitText}</td>
                                    <td style="color: var(--success);">${creditText}</td>
                                    <td style="font-weight: 600;">${parseFloat(entry.balance).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                    }
                    
                    document.getElementById('ledgerModal').classList.add('active');
                }
            })
            .catch(err => console.error("Error loading ledger:", err));
        }

        function closeLedgerModal() {
            document.getElementById('ledgerModal').classList.remove('active');
        }

        function printLedger() {
            const name = document.getElementById('ledgerCustName').innerText;
            const phone = document.getElementById('ledgerCustPhone').innerText;
            const balance = document.getElementById('ledgerCustBalance').innerText;
            const tableHtml = document.getElementById('ledgerTable').outerHTML;
            
            const printWin = window.open('', '_blank');
            printWin.document.write(`
                <html>
                <head>
                    <title>Ledger - ${name}</title>
                    <style>
                        body { font-family: sans-serif; padding: 40px; color: #333; }
                        h2 { margin-bottom: 5px; }
                        .header-info { margin-bottom: 30px; border-bottom: 2px solid #ccc; padding-bottom: 15px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
                        th { background-color: #f5f5f5; }
                        .text-right { text-align: right; }
                        .total-highlight { font-weight: bold; background-color: #fff9f9; }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    <h2>Customer Ledger Statement</h2>
                    <div class="header-info">
                        <strong>Customer Name:</strong> ${name}<br>
                        <strong>Phone Number:</strong> ${phone}<br>
                        <strong>Outstanding Balance:</strong> ${balance}
                    </div>
                    ${tableHtml}
                </body>
                </html>
            `);
            printWin.document.close();
        }

        // Payment Collection
        function openPaymentModal(id) {
            document.getElementById('paymentForm').reset();
            document.getElementById('paymentCustId').value = id;
            
            // Populate auto suggestion of total balance due
            const c = allCustomers.find(item => item.id == id);
            if (c) {
                document.getElementById('payAmount').value = parseFloat(c.balance).toFixed(2);
            }
            
            document.getElementById('paymentModal').classList.add('active');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('active');
        }

        function recordPayment(e) {
            e.preventDefault();
            const customer_id = document.getElementById('paymentCustId').value;
            const amount = document.getElementById('payAmount').value;
            const payment_method = document.getElementById('payMethod').value;
            const note = document.getElementById('payNote').value.trim();

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'collect-customer-payment',
                    data: { customer_id, amount, payment_method, note }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'customer-payment-collected');
                if (response && response.data.success) {
                    closePaymentModal();
                    loadCustomers();
                } else {
                    alert(response ? response.data.msg : "Failed to record payment");
                }
            })
            .catch(err => console.error("Error recording payment:", err));
        }

        // WhatsApp Reminder Modal
        let activeWaCustomer = null;
        function openWhatsappModal(id, balance) {
            const c = allCustomers.find(item => item.id == id);
            if (!c) return;

            activeWaCustomer = c;
            document.getElementById('waPhoneDisplay').value = c.phone;
            
            // Format template message
            const template = `Assalam-o-Alaikum ${c.name},\n\nAap ke khate mein Rs. ${balance.toFixed(2)} ka outstanding udhaar baaqi hai. Baraye meharbani jald az jald payment jama karwain.\n\nShukriya!\n*TijaratPro*`;
            document.getElementById('waMessageText').value = template;
            
            document.getElementById('whatsappModal').classList.add('active');
        }

        function closeWhatsappModal() {
            document.getElementById('whatsappModal').classList.remove('active');
        }

        async function dispatchWhatsapp() {
            if (!activeWaCustomer) return;
            
            let phone = activeWaCustomer.phone.trim();
            // Clean phone number format for international whatsapp standard
            // E.g., if number starts with 03001234567, convert to 923001234567
            if (phone.startsWith('0') && !phone.startsWith('00')) {
                phone = '92' + phone.substring(1);
            } else if (phone.startsWith('+')) {
                phone = phone.substring(1);
            } else if (phone.startsWith('00')) {
                phone = phone.substring(2);
            }
            
            const message = document.getElementById('waMessageText').value;
            const whatsappMode = "<?php echo $whatsapp_mode; ?>";
            
            if (whatsappMode === 'local_api') {
                const sendBtn = document.querySelector('#whatsappModal .btn-success');
                const originalText = sendBtn.innerHTML;
                sendBtn.disabled = true;
                sendBtn.innerHTML = 'Sending...';
                
                try {
                    const res = await fetch('http://127.0.0.1:9001/send-message', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ phone: phone, message: message })
                    });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const data = await res.json();
                    
                    if (data.success) {
                        alert('Message sent successfully in the background!');
                        closeWhatsappModal();
                    } else {
                        throw new Error(data.error || 'Server error');
                    }
                } catch (err) {
                    console.error('Failed to send via local background service:', err);
                    if (confirm('Background WhatsApp service is disconnected or returned an error. Click OK to fallback and send via WhatsApp Web browser tab instead.')) {
                        const encodedText = encodeURIComponent(message);
                        const waUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`;
                        window.open(waUrl, '_blank');
                        closeWhatsappModal();
                    }
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalText;
                }
            } else {
                const encodedText = encodeURIComponent(message);
                const waUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`;
                window.open(waUrl, '_blank');
                closeWhatsappModal();
            }
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
