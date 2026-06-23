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
        'title' => 'Advanced Marketing Tool',
        'menu_billing' => '🛒 POS Billing',
        'menu_inventory' => '📦 Stock/Inventory',
        'menu_customers' => '👥 Customer Khata',
        'menu_purchases' => '🧾 Purchases',
        'menu_marketing' => '📢 Marketing Tool',
        'menu_settings' => '⚙️ Settings',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        
        'campaign_selector' => 'Select Marketing Campaign',
        'camp_promo' => '📢 Bulk Promotion',
        'camp_udhaar' => '⚠️ Udhaar Reminders',
        'camp_birthday' => '🎂 Today\'s Birthdays',
        'camp_anniversary' => '🎉 Today\'s Anniversaries',
        
        'template_editor' => 'Customize Message Template',
        'placeholders_info' => 'Placeholders: <code>{name}</code>, <code>{balance}</code>, <code>{shop_name}</code>, <code>{shop_phone}</code>',
        'load_contacts' => 'Load Campaign Contacts',
        
        'target_audience' => 'Target Contacts Registry',
        'name' => 'Name',
        'phone' => 'Phone',
        'balance' => 'Balance (Udhaar)',
        'preview' => 'Message Preview',
        'action' => 'Action',
        'send_btn' => 'Send WhatsApp',
        'sent_status' => 'Sent',
        'no_contacts' => 'No contacts match the selected campaign criteria.',
        'safety_notice' => '💡 <b>Anti-Ban Safe Send:</b> Opening messages in sequential browser tabs is highly recommended. Headless background automation carries a high risk of WhatsApp numbers getting banned.',
    ],
    'ur' => [
        'title' => 'ایڈوانسڈ مارکیٹنگ ٹول',
        'menu_billing' => '🛒 بلنگ / سیلز',
        'menu_inventory' => '📦 اسٹاک / انوینٹری',
        'menu_customers' => '👥 کسٹمر کھاتہ',
        'menu_purchases' => '🧾 خریداری',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'menu_settings' => '⚙️ سیٹنگز',
        'logout' => '🚪 لاگ آؤٹ',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        
        'campaign_selector' => 'مارکیٹنگ مہم منتخب کریں',
        'camp_promo' => '📢 عام ڈسکاؤنٹ پروموشن',
        'camp_udhaar' => '⚠️ ادھار واپسی ریمائنڈرز',
        'camp_birthday' => '🎂 آج جن کی سالگرہ ہے',
        'camp_anniversary' => '🎉 آج جن کی شادی کی سالگرہ ہے',
        
        'template_editor' => 'پیغام کا ٹیمپلیٹ ایڈٹ کریں',
        'placeholders_info' => 'پیغام میں یہ الفاظ کسٹمر کے نام اور ادھار سے خود تبدیل ہو جائیں گے: <code>{name}</code>, <code>{balance}</code>, <code>{shop_name}</code>, <code>{shop_phone}</code>',
        'load_contacts' => 'رابطہ فہرست لوڈ کریں',
        
        'target_audience' => 'ٹارگٹ کسٹمرز کی فہرست',
        'name' => 'نام',
        'phone' => 'فون نمبر',
        'balance' => 'بقایا ادھار',
        'preview' => 'پیغام کا جائزہ',
        'action' => 'ایکشن',
        'send_btn' => 'واٹس ایپ بھیجیں',
        'sent_status' => 'بھیج دیا',
        'no_contacts' => 'منتخب کردہ مہم کے لیے کوئی کسٹمرز دستیاب نہیں ہیں۔',
        'safety_notice' => '💡 <b>اینٹی بین وارننگ:</b> واٹس ایپ کے قوانین کے مطابق براؤزر کے ذریعے دستی مہم چلانا سب سے محفوظ طریقہ ہے۔ آٹومیٹک سافٹ ویئرز سے نمبر بلاک ہونے کا شدید خطرہ ہوتا ہے۔',
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

        /* Campaign Setup Grid */
        .campaign-grid { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
        .card-menu-item { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; transition: var(--transition-smooth); font-family: var(--font-heading); font-weight: 600; background-color: var(--bg-card); }
        .card-menu-item:hover, .card-menu-item.active { border-color: var(--accent); background-color: rgba(237, 26, 59, 0.05); color: var(--accent); }
        
        .code-pill { background: var(--bg-input); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }

        /* Contacts Table */
        .table-responsive { width: 100%; overflow-x: auto; background-color: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-top: 20px; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 14px 18px; border-bottom: 1px solid var(--border-color); font-size: 13px; }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); }
        .data-table tr:hover { background-color: rgba(0, 0, 0, 0.02); }

        .preview-box { max-width: 300px; max-height: 80px; overflow-y: auto; font-size: 12px; background: var(--bg-input); padding: 8px; border-radius: 4px; border: 1px solid var(--border-color); white-space: pre-wrap; font-family: inherit; }

        /* RTL Handling overrides */
        .lang-urdu .sidebar { border-right: none; border-left: 1px solid var(--border-color); }
        .lang-urdu .menu-link:hover, .lang-urdu .menu-link.active { border-left: none; border-right: 4px solid var(--accent); padding-left: 20px; padding-right: 16px; }
        .lang-urdu .data-table { text-align: right; }
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
                <li><a href="purchases.php" class="menu-link">🧾 <?php echo $trans[$lang]['menu_purchases']; ?></a></li>
                <li><a href="marketing.php" class="menu-link active">📢 <?php echo $trans[$lang]['menu_marketing']; ?></a></li>
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
                    📢 <?php echo $trans[$lang]['title']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <div class="campaign-grid">
                
                <!-- Left Panel: Campaign selector and Template Editor -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <section class="card" style="display: flex; flex-direction: column; gap: 10px;">
                        <h3>🎯 <?php echo $trans[$lang]['campaign_selector']; ?></h3>
                        
                        <div class="card-menu-item active" id="btnPromo" onclick="selectCampaign('promotion')">
                            📢 <?php echo $trans[$lang]['camp_promo']; ?>
                        </div>
                        <div class="card-menu-item" id="btnUdhaar" onclick="selectCampaign('udhaar_reminder')">
                            ⚠️ <?php echo $trans[$lang]['camp_udhaar']; ?>
                        </div>
                        <div class="card-menu-item" id="btnBirthday" onclick="selectCampaign('birthday_wishes')">
                            🎂 <?php echo $trans[$lang]['camp_birthday']; ?>
                        </div>
                        <div class="card-menu-item" id="btnAnniversary" onclick="selectCampaign('anniversary_wishes')">
                            🎉 <?php echo $trans[$lang]['camp_anniversary']; ?>
                        </div>
                    </section>

                    <section class="card" style="display: flex; flex-direction: column; gap: 12px;">
                        <h3>📝 <?php echo $trans[$lang]['template_editor']; ?></h3>
                        <textarea class="form-control" id="templateText" rows="7" style="resize: vertical; font-family: inherit; font-size: 14px;" onkeyup="compileAllPreviews()"></textarea>
                        <p style="font-size: 12px; color: var(--text-muted);">
                            <?php echo $trans[$lang]['placeholders_info']; ?>
                        </p>
                    </section>

                </div>

                <!-- Right Panel: Contacts Matching Campaign -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="card" style="border-left: 4px solid var(--primary-blue); background: var(--bg-card); padding: 16px;">
                        <span style="font-size: 13px; font-weight: 500;"><?php echo $trans[$lang]['safety_notice']; ?></span>
                    </div>

                    <section class="card" style="padding: 20px;">
                        <h3>👥 <?php echo $trans[$lang]['target_audience']; ?></h3>
                        
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th><?php echo $trans[$lang]['name']; ?></th>
                                        <th><?php echo $trans[$lang]['phone']; ?></th>
                                        <th><?php echo $trans[$lang]['balance']; ?></th>
                                        <th><?php echo $trans[$lang]['preview']; ?></th>
                                        <th><?php echo $trans[$lang]['action']; ?></th>
                                    </tr>
                                </thead>
                                <tbody id="marketingTableBody">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>

        </main>

    </div>

    <script>
        let currentCampaign = 'promotion';
        let campaignContacts = [];
        let shopName = 'TijaratPro';
        let shopPhone = '';
        const trans = <?php echo json_encode($trans[$lang]); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            loadCampaignData();
        });

        function selectCampaign(type) {
            currentCampaign = type;
            document.querySelectorAll('.card-menu-item').forEach(item => item.classList.remove('active'));
            
            if (type === 'promotion') document.getElementById('btnPromo').classList.add('active');
            if (type === 'udhaar_reminder') document.getElementById('btnUdhaar').classList.add('active');
            if (type === 'birthday_wishes') document.getElementById('btnBirthday').classList.add('active');
            if (type === 'anniversary_wishes') document.getElementById('btnAnniversary').classList.add('active');

            loadCampaignData();
        }

        function loadCampaignData() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get-marketing-campaign',
                    data: { campaign_type: currentCampaign }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'marketing-campaign-data');
                if (response && response.data.success) {
                    shopName = response.data.shop_name;
                    shopPhone = response.data.shop_phone || '';
                    document.getElementById('templateText').value = response.data.template;
                    campaignContacts = response.data.customers;
                    renderContactsTable();
                }
            })
            .catch(err => console.error("Error loading marketing campaign:", err));
        }

        function compileTemplate(template, customer) {
            let msg = template;
            msg = msg.replace(/{name}/g, customer.name);
            msg = msg.replace(/{balance}/g, parseFloat(customer.balance).toFixed(2));
            msg = msg.replace(/{shop_name}/g, shopName);
            msg = msg.replace(/{shop_phone}/g, shopPhone);
            return msg;
        }

        function renderContactsTable() {
            const tbody = document.getElementById('marketingTableBody');
            tbody.innerHTML = '';

            if (campaignContacts.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">${trans.no_contacts}</td></tr>`;
                return;
            }

            const template = document.getElementById('templateText').value;

            campaignContacts.forEach((c, index) => {
                const compiledMsg = compileTemplate(template, c);
                tbody.innerHTML += `
                    <tr id="camp-row-${index}">
                        <td style="font-weight: 500;">${escapeHtml(c.name)}</td>
                        <td>${escapeHtml(c.phone)}</td>
                        <td><strong style="color: ${parseFloat(c.balance) > 0 ? 'var(--danger)' : 'var(--text-muted)'};">${parseFloat(c.balance).toFixed(2)} PKR</strong></td>
                        <td>
                            <div class="preview-box" id="preview-box-${index}">${escapeHtml(compiledMsg)}</div>
                        </td>
                        <td>
                            <button class="btn btn-success" style="padding: 6px 14px; font-size: 12px;" onclick="sendSingleMessage(${index})">
                                📲 ${trans.send_btn}
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        function compileAllPreviews() {
            const template = document.getElementById('templateText').value;
            campaignContacts.forEach((c, index) => {
                const compiledMsg = compileTemplate(template, c);
                const box = document.getElementById(`preview-box-${index}`);
                if (box) box.innerText = compiledMsg;
            });
        }

        function sendSingleMessage(index) {
            const c = campaignContacts[index];
            const template = document.getElementById('templateText').value;
            const message = compileTemplate(template, c);
            
            let phone = c.phone.trim();
            // Clean phone number format for international whatsapp standard
            if (phone.startsWith('0') && !phone.startsWith('00')) {
                phone = '92' + phone.substring(1);
            } else if (phone.startsWith('+')) {
                phone = phone.substring(1);
            } else if (phone.startsWith('00')) {
                phone = phone.substring(2);
            }
            
            const encodedText = encodeURIComponent(message);
            const waUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`;
            
            window.open(waUrl, '_blank');

            // Visual confirmation indicator
            const row = document.getElementById(`camp-row-${index}`);
            if (row) {
                const btnCell = row.cells[4];
                btnCell.innerHTML = `<span style="color: var(--success); font-weight: 600;">✅ ${trans.sent_status}</span>`;
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
