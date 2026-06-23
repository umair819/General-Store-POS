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
        'title' => 'TijaratPro - Settings',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '⚙️ POS Settings & Utilities',
        'backup_head' => '🔄 Advanced Restore & Migration Engine',
        'backup_sub' => 'Seamlessly import backup archives from competitor billing softwares to start immediately.',
        'select_file' => 'Select Backup File',
        'allowed_formats' => 'Supported Formats: Vyapar Backup Archive (.vyb)',
        'btn_restore' => 'Start Migration / Restore',
        'store_details' => 'Store Information',
        'store_name' => 'Store / Shop Name',
        'store_phone' => 'Contact Number',
        'store_address' => 'Shop Address',
        'store_currency' => 'Base Currency',
        'save_settings' => 'Save Configuration',
        'success_saved' => 'Settings saved successfully.',
        'migration_in_progress' => 'Processing backup container... Please wait, this might take a moment.',
        'migration_success' => 'Migration completed successfully!',
        'gemini_api_key' => 'Google Gemini API Key',
        'whatsapp_mode' => 'WhatsApp Marketing Send Mode',
        'whatsapp_mode_link' => 'WhatsApp Web (Browser Tabs - Safe)',
        'whatsapp_mode_api' => 'Local headless background service (Needs Setup)',
    ],
    'ur' => [
        'title' => 'تجارت پرو - سیٹنگز',
        'dashboard' => 'ڈیش بورڈ جائزہ',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 اسٹاک اور انوینٹری',
        'menu_customers' => '👥 Tijarat Ledger (کھاتہ)',
        'menu_purchases' => '🧾 خریداری',
        'menu_reports' => '📈 سیلز رپورٹ',
        'menu_settings' => '⚙️ سیٹنگز',
        'menu_marketing' => '📢 مارکیٹنگ ٹول',
        'logout' => '🚪 لاگ آؤٹ',
        'heading' => '⚙️ پی او ایس سیٹنگز اور یوٹیلیٹیز',
        'backup_head' => '🔄 ایڈوانسڈ ڈیٹا مائیگریشن اور ریسٹور انجن',
        'backup_sub' => 'بغیر کسی پریشانی کے دوسرے بلنگ سافٹ ویئرز (جیسے Vyapar) کی بیک اپ فائل سے سارا ڈیٹا فوری امپورٹ کریں۔',
        'select_file' => 'بیک اپ فائل منتخب کریں',
        'allowed_formats' => 'سپورٹڈ فائلز: ویاپار بیک اپ فائل (.vyb)',
        'btn_restore' => 'ڈیٹا امپورٹ / ریسٹور شروع کریں',
        'store_details' => 'دکان کی تفصیلات',
        'store_name' => 'دکان کا نام',
        'store_phone' => 'رابطہ نمبر',
        'store_address' => 'دکان کا پتہ',
        'store_currency' => 'کرنسی',
        'save_settings' => 'سیٹنگز محفوظ کریں',
        'success_saved' => 'سیٹنگز کامیابی سے محفوظ ہوگئیں۔',
        'migration_in_progress' => 'بیک اپ فائل پروسیس ہو رہی ہے... براہ کرم انتظار کریں۔',
        'migration_success' => 'ڈیٹا کامیابی سے امپورٹ ہو گیا ہے!',
        'gemini_api_key' => 'گوگل جیمنی اے آئی کی (API Key)',
        'whatsapp_mode' => 'واٹس ایپ پروموشن بھیجنے کا طریقہ',
        'whatsapp_mode_link' => 'واٹس ایپ ویب (محفوظ اور آسان)',
        'whatsapp_mode_api' => 'لوکل بیک گراؤنڈ سروس (سیٹ اپ ضروری ہے)',
    ]
];

// Handle Local Store Settings POST
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_config') {
    $shop_name = trim($_POST['shop_name'] ?? 'TijaratPro');
    
    // Save to configuration JSON
    $config_data = [
        'db_type' => 'sqlite',
        'db_name' => 'general_store.db',
        'shop_name' => $shop_name,
        'shop_phone' => trim($_POST['shop_phone'] ?? ''),
        'shop_address' => trim($_POST['shop_address'] ?? ''),
        'shop_currency' => trim($_POST['shop_currency'] ?? 'PKR'),
        'gemini_api_key' => trim($_POST['gemini_api_key'] ?? ''),
        'whatsapp_mode' => trim($_POST['whatsapp_mode'] ?? 'link'),
    ];
    file_put_contents(__DIR__ . '/db_config.json', json_encode($config_data, JSON_PRETTY_PRINT));
    $success_msg = $trans[$lang]['success_saved'];
}

// Load current configuration
$config = [
    'shop_name' => 'TijaratPro',
    'shop_phone' => '03001234567',
    'shop_address' => 'Saddar, Karachi, Pakistan',
    'shop_currency' => 'PKR',
    'gemini_api_key' => '',
    'whatsapp_mode' => 'link',
];
if (file_exists(__DIR__ . '/db_config.json')) {
    $json = json_decode(file_get_contents(__DIR__ . '/db_config.json'), true);
    if ($json) {
        $config = array_merge($config, $json);
    }
}
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
        
        .settings-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; }
        
        /* Drag & Drop File Upload Field */
        .dropzone-container { border: 2px dashed var(--border-color); padding: 30px 20px; border-radius: var(--radius-md); text-align: center; background: var(--bg-input); cursor: pointer; transition: var(--transition-smooth); margin-top: 15px; }
        .dropzone-container:hover { border-color: var(--accent); background: var(--bg-card); }
        .dropzone-icon { font-size: 40px; margin-bottom: 12px; display: block; }
        .dropzone-text { font-size: 14px; font-weight: 500; color: var(--text-main); }
        .dropzone-subtext { font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block; }
        
        /* Progress Banner */
        .status-alert { padding: 16px 24px; border-radius: var(--radius-sm); border: 1px solid transparent; font-size: 14px; display: none; margin-top: 20px; font-weight: 500; text-align: center; }
        .status-alert.info { background: rgba(59, 130, 246, 0.08); color: var(--primary-blue); border-color: var(--primary-blue); display: block; }
        .status-alert.success { background: rgba(16, 185, 129, 0.08); color: var(--success); border-color: var(--success); display: block; }
        .status-alert.danger { background: rgba(239, 68, 68, 0.08); color: var(--danger); border-color: var(--danger); display: block; }
 
        /* RTL Layout overrides */
        .lang-urdu .sidebar { border-right: none; border-left: 1px solid var(--border-color); }
        .lang-urdu .menu-link:hover, .lang-urdu .menu-link.active { border-left: none; border-right: 4px solid var(--accent); padding-left: 20px; padding-right: 16px; }
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
                <li><a href="products.php" class="menu-link">📦 <?php echo $trans[$lang]['menu_inventory']; ?></a></li>
                <li><a href="customers.php" class="menu-link">👥 <?php echo $trans[$lang]['menu_customers']; ?></a></li>
                <li><a href="purchases.php" class="menu-link">🧾 <?php echo $trans[$lang]['menu_purchases']; ?></a></li>
                <li><a href="marketing.php" class="menu-link">📢 <?php echo $trans[$lang]['menu_marketing']; ?></a></li>
                <li><a href="settings.php" class="menu-link active">⚙️ <?php echo $trans[$lang]['menu_settings']; ?></a></li>
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
 
            <?php if (!empty($success_msg)): ?>
                <div class="status-alert success" style="display: block; margin-top: 0;">
                    ✅ <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
 
            <div class="settings-grid">
                
                <!-- Card 1: Shop details configuration -->
                <section class="card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                    <h3>🏢 <?php echo $trans[$lang]['store_details']; ?></h3>
                    
                    <form method="POST" action="settings.php" style="margin-top: 20px;">
                        <input type="hidden" name="action" value="save_config">
                        
                        <div class="form-group">
                            <label class="form-label" for="shopName"><?php echo $trans[$lang]['store_name']; ?></label>
                            <input class="form-control" type="text" id="shopName" name="shop_name" required value="<?php echo htmlspecialchars($config['shop_name']); ?>">
                        </div>
 
                        <div class="form-group">
                            <label class="form-label" for="shopPhone"><?php echo $trans[$lang]['store_phone']; ?></label>
                            <input class="form-control" type="text" id="shopPhone" name="shop_phone" value="<?php echo htmlspecialchars($config['shop_phone']); ?>">
                        </div>
 
                        <div class="form-group">
                            <label class="form-label" for="shopAddress"><?php echo $trans[$lang]['store_address']; ?></label>
                            <input class="form-control" type="text" id="shopAddress" name="shop_address" value="<?php echo htmlspecialchars($config['shop_address']); ?>">
                        </div>
 
                        <div class="form-group">
                            <label class="form-label" for="shopCurrency"><?php echo $trans[$lang]['store_currency']; ?></label>
                            <input class="form-control" type="text" id="shopCurrency" name="shop_currency" required value="<?php echo htmlspecialchars($config['shop_currency']); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="geminiApiKey"><?php echo $trans[$lang]['gemini_api_key']; ?></label>
                            <input class="form-control" type="password" id="geminiApiKey" name="gemini_api_key" value="<?php echo htmlspecialchars($config['gemini_api_key'] ?? ''); ?>" placeholder="Enter key starting with AIza...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="whatsappMode"><?php echo $trans[$lang]['whatsapp_mode']; ?></label>
                            <select class="form-control" id="whatsappMode" name="whatsapp_mode">
                                <option value="link" <?php echo (($config['whatsapp_mode'] ?? 'link') === 'link') ? 'selected' : ''; ?>><?php echo $trans[$lang]['whatsapp_mode_link']; ?></option>
                                <option value="local_api" <?php echo (($config['whatsapp_mode'] ?? 'link') === 'local_api') ? 'selected' : ''; ?>><?php echo $trans[$lang]['whatsapp_mode_api']; ?></option>
                            </select>
                        </div>
 
                        <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 10px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </form>
                </section>


                <!-- Card 2: Advanced Restore / Migration -->
                <section class="card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                    <h3>🔄 <?php echo $trans[$lang]['backup_head']; ?></h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">
                        <?php echo $trans[$lang]['backup_sub']; ?>
                    </p>

                    <div class="dropzone-container" onclick="triggerFileSelect()">
                        <span class="dropzone-icon">📤</span>
                        <span class="dropzone-text"><?php echo $trans[$lang]['select_file']; ?></span>
                        <span class="dropzone-subtext"><?php echo $trans[$lang]['allowed_formats']; ?></span>
                        <input type="file" id="backupFileInput" accept=".vyb" style="display: none;" onchange="handleFileChange(event)">
                    </div>

                    <div style="margin-top: 15px; font-weight: 500; font-size: 14px; display: none; text-align: center;" id="fileNameDisplay"></div>

                    <button class="btn btn-success" id="restoreBtn" style="width: 100%; margin-top: 20px; display: none;" onclick="startMigration()">
                        🚀 <?php echo $trans[$lang]['btn_restore']; ?>
                    </button>

                    <!-- Alert message container -->
                    <div class="status-alert" id="statusAlert"></div>
                </section>

            </div>

        </main>

    </div>

    <script>
        const lang = "<?php echo $lang; ?>";
        const trans = <?php echo json_encode($trans[$lang]); ?>;
        let selectedFileBase64 = "";

        function triggerFileSelect() {
            document.getElementById('backupFileInput').click();
        }

        function handleFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;

            const nameDisplay = document.getElementById('fileNameDisplay');
            nameDisplay.innerText = "📄 " + file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
            nameDisplay.style.display = 'block';

            // Show restore action button
            document.getElementById('restoreBtn').style.display = 'inline-flex';
            
            // Convert file to base64
            const reader = new FileReader();
            reader.onload = function(evt) {
                selectedFileBase64 = evt.target.result;
            };
            reader.readAsDataURL(file);
        }

        function startMigration() {
            if (!selectedFileBase64) return;

            const alertBox = document.getElementById('statusAlert');
            alertBox.className = "status-alert info";
            alertBox.innerText = trans.migration_in_progress;

            // Disable buttons during execution
            document.getElementById('restoreBtn').disabled = true;
            document.getElementById('backupFileInput').disabled = true;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'restore-vyapar-backup',
                    data: { file_data: selectedFileBase64 }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'backup-restored');
                
                // Re-enable actions
                document.getElementById('restoreBtn').disabled = false;
                document.getElementById('backupFileInput').disabled = false;
                
                if (response && response.data.success) {
                    alertBox.className = "status-alert success";
                    alertBox.innerText = "🎉 " + response.data.msg;
                    // Reset inputs after delay
                    setTimeout(() => {
                        document.getElementById('fileNameDisplay').style.display = 'none';
                        document.getElementById('restoreBtn').style.display = 'none';
                    }, 5000);
                } else {
                    alertBox.className = "status-alert danger";
                    alertBox.innerText = response ? response.data.msg : "Migration failed. Unknown database error.";
                }
            })
            .catch(err => {
                document.getElementById('restoreBtn').disabled = false;
                document.getElementById('backupFileInput').disabled = false;
                alertBox.className = "status-alert danger";
                alertBox.innerText = "Network error during restore process: " + err.message;
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
