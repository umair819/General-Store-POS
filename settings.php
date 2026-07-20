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
        'receipt_template' => 'Thermal Receipt Design Template',
        'template_default' => 'Default Classic (Thermal)',
        'template_modern' => 'Sleek Modern (Sans-serif)',
        'template_elegant' => 'Elegant Serif (Traditional)',
        'template_compact' => 'Compact (Paper Saver)',
        'template_bold' => 'Bold Accent (High Contrast)',
        'template_blue' => 'Corporate Blue',
        'template_green' => 'Grocery Green',
        'template_urdu' => 'Urdu Traditional (RTL)',
        'template_luxury' => 'Retail Luxury (Spaced)',
        'template_vintage' => 'Vintage Dot-Matrix',
    ],
    'ur' => [
        'title' => 'TijaratPro - Settings',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Stock & Inventory',
        'menu_customers' => '👥 Customers & Khata (Udhaar)',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '⚙️ POS Settings & Utilities',
        'backup_head' => '🔄 Advanced Restore & Migration Engine',
        'backup_sub' => 'Bina kisi pareshani ke doosray billing softwares (jaise Vyapar) ki backup file se saara data fori import karein.',
        'select_file' => 'Backup File select karein',
        'allowed_formats' => 'Supported Formats: Vyapar Backup Archive (.vyb)',
        'btn_restore' => 'Migration / Restore start karein',
        'store_details' => 'Store Information',
        'store_name' => 'Store / Shop Ka Naam',
        'store_phone' => 'Contact Number',
        'store_address' => 'Shop Ka Address',
        'store_currency' => 'Base Currency',
        'save_settings' => 'Configuration Save Karein',
        'success_saved' => 'Settings successfully save ho gayin.',
        'migration_in_progress' => 'Backup file process ho rahi hai... Please wait karein.',
        'migration_success' => 'Migration completed successfully!',
        'gemini_api_key' => 'Google Gemini API Key',
        'whatsapp_mode' => 'WhatsApp Marketing Send Mode',
        'whatsapp_mode_link' => 'WhatsApp Web (Browser Tabs - Safe)',
        'whatsapp_mode_api' => 'Local headless background service (Needs Setup)',
        'receipt_template' => 'Thermal Receipt Design Template',
        'template_default' => 'Default Classic (Thermal)',
        'template_modern' => 'Sleek Modern (Sans-serif)',
        'template_elegant' => 'Elegant Serif (Traditional)',
        'template_compact' => 'Compact (Paper Saver)',
        'template_bold' => 'Bold Accent (High Contrast)',
        'template_blue' => 'Corporate Blue',
        'template_green' => 'Grocery Green',
        'template_urdu' => 'Urdu Traditional (RTL)',
        'template_luxury' => 'Retail Luxury (Spaced)',
        'template_vintage' => 'Vintage Dot-Matrix',
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
        'receipt_template' => trim($_POST['receipt_template'] ?? 'default'),
        'tax_number' => trim($_POST['tax_number'] ?? ''),
        'tax_enabled' => isset($_POST['tax_enabled']) ? '1' : '0',
        'stop_negative_stock' => isset($_POST['stop_negative_stock']) ? '1' : '0',
        'enable_passcode' => isset($_POST['enable_passcode']) ? '1' : '0',
        'decimal_places' => intval($_POST['decimal_places'] ?? 2),
        'font_scale' => intval($_POST['font_scale'] ?? 100),
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
    'receipt_template' => 'default',
    'tax_number' => '',
    'tax_enabled' => '0',
    'stop_negative_stock' => '0',
    'enable_passcode' => '0',
    'decimal_places' => 2,
    'font_scale' => 100,
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
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }
        
        /* Premium Settings Tabs Layout */
        .settings-container { display: flex; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-md); min-height: 520px; }
        .settings-tabs { width: 240px; background-color: #0f172a; border-right: 1px solid #1e293b; display: flex; flex-direction: column; padding: 20px 10px; gap: 6px; flex-shrink: 0; }
        .settings-tab-btn { display: flex; align-items: center; gap: 12px; padding: 12px 18px; color: #94a3b8; background: transparent; border: none; border-radius: 6px; font-family: var(--font-heading); font-size: 14px; font-weight: 500; cursor: pointer; text-align: left; transition: var(--transition-smooth); width: 100%; }
        .settings-tab-btn:hover { background-color: #1e293b; color: #fff; }
        .settings-tab-btn.active { background-color: var(--accent); color: #fff; font-weight: 600; }
        
        .settings-form { flex-grow: 1; padding: 40px; }
        .settings-tab-content { display: none; }
        .settings-tab-content.active { display: block; }
        
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px; }
        .settings-group { display: flex; flex-direction: column; gap: 8px; }
        
        .settings-checkbox-group { display: flex; align-items: flex-start; gap: 12px; margin: 20px 0; }
        .settings-checkbox-group input[type="checkbox"] { width: 18px; height: 18px; margin-top: 3px; cursor: pointer; }
        
        /* Drag & Drop File Upload Field */
        .dropzone-container { border: 2px dashed var(--border-color); padding: 35px 20px; border-radius: var(--radius-md); text-align: center; background: var(--bg-input); cursor: pointer; transition: var(--transition-smooth); }
        .dropzone-container:hover { border-color: var(--accent); background: var(--bg-card); }
        .dropzone-icon { font-size: 40px; margin-bottom: 12px; display: block; }
        .dropzone-text { font-size: 14px; font-weight: 500; color: var(--text-main); }
        .dropzone-subtext { font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block; }
        
        /* Progress Banner */
        .status-alert { padding: 16px 24px; border-radius: var(--radius-sm); border: 1px solid transparent; font-size: 14px; display: none; font-weight: 500; }
        .status-alert.info { background: rgba(59, 130, 246, 0.08); color: var(--primary-blue); border-color: var(--primary-blue); display: block; }
        .status-alert.success { background: rgba(16, 185, 129, 0.08); color: var(--success); border-color: var(--success); display: block; }
        .status-alert.danger { background: rgba(239, 68, 68, 0.08); color: var(--danger); border-color: var(--danger); display: block; }
    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">
 
    <div class="layout-wrapper">
        
        <?php include __DIR__ . '/sidebar.php'; ?>
 
        <main class="content-panel">
            
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
                <div class="status-alert success" style="display: block;">
                    ✅ <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
 
            <div class="settings-container">
                <aside class="settings-tabs">
                    <button type="button" class="settings-tab-btn active" onclick="switchSettingsTab('general', this)">⚙️ General Settings</button>
                    <button type="button" class="settings-tab-btn" onclick="switchSettingsTab('pos-print', this)">🖨️ POS & Print</button>
                    <button type="button" class="settings-tab-btn" onclick="switchSettingsTab('taxes', this)">⚖️ Taxes & VAT</button>
                    <button type="button" class="settings-tab-btn" onclick="switchSettingsTab('inventory', this)">📦 Inventory Rules</button>
                    <button type="button" class="settings-tab-btn" onclick="switchSettingsTab('integrations', this)">📱 Integrations</button>
                    <button type="button" class="settings-tab-btn" onclick="switchSettingsTab('restore', this)">🔄 Backup & Restore</button>
                </aside>

                <form class="settings-form" method="POST" action="settings.php">
                    <input type="hidden" name="action" value="save_config">
                    
                    <div class="settings-tab-content active" id="set-general">
                        <h3 style="font-size: 18px; font-weight: 600;">🏢 Store Information</h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>
                        
                        <div class="settings-grid">
                            <div class="settings-group">
                                <label class="form-label" for="shopName"><?php echo $trans[$lang]['store_name']; ?></label>
                                <input class="form-control" type="text" id="shopName" name="shop_name" required value="<?php echo htmlspecialchars($config['shop_name']); ?>">
                            </div>
                            <div class="settings-group">
                                <label class="form-label" for="shopPhone"><?php echo $trans[$lang]['store_phone']; ?></label>
                                <input class="form-control" type="text" id="shopPhone" name="shop_phone" value="<?php echo htmlspecialchars($config['shop_phone']); ?>">
                            </div>
                        </div>

                        <div class="settings-grid">
                            <div class="settings-group">
                                <label class="form-label" for="shopAddress"><?php echo $trans[$lang]['store_address']; ?></label>
                                <input class="form-control" type="text" id="shopAddress" name="shop_address" value="<?php echo htmlspecialchars($config['shop_address']); ?>">
                            </div>
                            <div class="settings-group">
                                <label class="form-label" for="shopCurrency"><?php echo $trans[$lang]['store_currency']; ?></label>
                                <input class="form-control" type="text" id="shopCurrency" name="shop_currency" required value="<?php echo htmlspecialchars($config['shop_currency']); ?>">
                            </div>
                        </div>

                        <div class="settings-group" style="margin-top: 15px;">
                            <label class="form-label" for="fontScale">Interface Zoom (Font Scale): <span id="fontScaleVal" style="font-weight: bold; color: var(--accent);"><?php echo intval($config['font_scale'] ?? 100); ?>%</span></label>
                            <input type="range" id="fontScale" name="font_scale" min="85" max="115" step="5" value="<?php echo intval($config['font_scale'] ?? 100); ?>" oninput="document.getElementById('fontScaleVal').textContent = this.value + '%'" style="width: 100%; cursor: pointer;">
                            <span style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">Adjust the zoom factor of text, tables, and buttons across POS pages.</span>
                        </div>
                        
                        <button class="btn btn-primary" type="submit" style="margin-top: 35px; border-radius: 30px; padding: 12px 30px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </div>
                    
                    <div class="settings-tab-content" id="set-pos-print">
                        <h3 style="font-size: 18px; font-weight: 600;">🖨️ POS Checkout & Printing</h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>

                        <div class="settings-grid">
                            <div class="settings-group">
                                <label class="form-label" for="decimalPlaces">Decimal Rounding Places</label>
                                <select class="form-control" id="decimalPlaces" name="decimal_places">
                                    <option value="0" <?php echo ($config['decimal_places'] == 0) ? 'selected' : ''; ?>>0 (No Decimals)</option>
                                    <option value="1" <?php echo ($config['decimal_places'] == 1) ? 'selected' : ''; ?>>1 Decimal Place</option>
                                    <option value="2" <?php echo ($config['decimal_places'] == 2) ? 'selected' : ''; ?>>2 Decimal Places</option>
                                </select>
                            </div>
                            <div class="settings-group">
                                <label class="form-label" for="receiptTemplate"><?php echo $trans[$lang]['receipt_template']; ?></label>
                                <select class="form-control" id="receiptTemplate" name="receipt_template">
                                    <option value="default" <?php echo (($config['receipt_template'] ?? 'default') === 'default') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_default']; ?></option>
                                    <option value="modern" <?php echo (($config['receipt_template'] ?? 'default') === 'modern') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_modern']; ?></option>
                                    <option value="elegant" <?php echo (($config['receipt_template'] ?? 'default') === 'elegant') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_elegant']; ?></option>
                                    <option value="compact" <?php echo (($config['receipt_template'] ?? 'default') === 'compact') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_compact']; ?></option>
                                    <option value="bold" <?php echo (($config['receipt_template'] ?? 'default') === 'bold') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_bold']; ?></option>
                                    <option value="blue" <?php echo (($config['receipt_template'] ?? 'default') === 'blue') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_blue']; ?></option>
                                    <option value="green" <?php echo (($config['receipt_template'] ?? 'default') === 'green') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_green']; ?></option>
                                    <option value="urdu" <?php echo (($config['receipt_template'] ?? 'default') === 'urdu') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_urdu']; ?></option>
                                    <option value="luxury" <?php echo (($config['receipt_template'] ?? 'default') === 'luxury') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_luxury']; ?></option>
                                    <option value="vintage" <?php echo (($config['receipt_template'] ?? 'default') === 'vintage') ? 'selected' : ''; ?>><?php echo $trans[$lang]['template_vintage']; ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-checkbox-group">
                            <input type="checkbox" id="enablePasscode" name="enable_passcode" value="1" <?php echo ($config['enable_passcode'] == '1') ? 'checked' : ''; ?>>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; cursor: pointer;" for="enablePasscode">Enable Password Protection</label>
                                <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Require admin PIN code authentication (1234) before critical actions (e.g. deleting receipts or clearing records).</span>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="margin-top: 35px; border-radius: 30px; padding: 12px 30px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </div>

                    <div class="settings-tab-content" id="set-taxes">
                        <h3 style="font-size: 18px; font-weight: 600;">⚖️ Tax & VAT Configurations</h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>

                        <div class="settings-group" style="margin-bottom: 25px;">
                            <label class="form-label" for="taxNumber">Tax / NTN / GST Registration Number</label>
                            <input class="form-control" type="text" id="taxNumber" name="tax_number" value="<?php echo htmlspecialchars($config['tax_number'] ?? ''); ?>" placeholder="E.g. 1234567-8 or GST-12-34-5678-910">
                            <span style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">This number will print at the top header of thermal checkouts.</span>
                        </div>

                        <div class="settings-checkbox-group">
                            <input type="checkbox" id="taxEnabled" name="tax_enabled" value="1" <?php echo ($config['tax_enabled'] == '1') ? 'checked' : ''; ?>>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; cursor: pointer;" for="taxEnabled">Apply Tax (GST/VAT) on Checkout</label>
                                <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Automatically compute and append standard sales tax calculation logic to shopping carts.</span>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="margin-top: 35px; border-radius: 30px; padding: 12px 30px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </div>

                    <div class="settings-tab-content" id="set-inventory">
                        <h3 style="font-size: 18px; font-weight: 600;">📦 Inventory & Catalog Settings</h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>

                        <div class="settings-checkbox-group">
                            <input type="checkbox" id="stopNegativeStock" name="stop_negative_stock" value="1" <?php echo ($config['stop_negative_stock'] == '1') ? 'checked' : ''; ?>>
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-weight: 600; cursor: pointer;" for="stopNegativeStock">Stop Sale on Negative Stock</label>
                                <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Enforce strict stock boundaries, blocking POS invoicing if item store balance runs below requested billing quantities.</span>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="margin-top: 35px; border-radius: 30px; padding: 12px 30px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </div>

                    <div class="settings-tab-content" id="set-integrations">
                        <h3 style="font-size: 18px; font-weight: 600;">📱 Integrations & API Configs</h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>

                        <div class="settings-grid">
                            <div class="settings-group">
                                <label class="form-label" for="whatsappMode"><?php echo $trans[$lang]['whatsapp_mode']; ?></label>
                                <select class="form-control" id="whatsappMode" name="whatsapp_mode">
                                    <option value="link" <?php echo (($config['whatsapp_mode'] ?? 'link') === 'link') ? 'selected' : ''; ?>><?php echo $trans[$lang]['whatsapp_mode_link']; ?></option>
                                    <option value="local_api" <?php echo (($config['whatsapp_mode'] ?? 'link') === 'local_api') ? 'selected' : ''; ?>><?php echo $trans[$lang]['whatsapp_mode_api']; ?></option>
                                </select>
                            </div>
                            <div class="settings-group">
                                <label class="form-label" for="geminiApiKey"><?php echo $trans[$lang]['gemini_api_key']; ?></label>
                                <input class="form-control" type="password" id="geminiApiKey" name="gemini_api_key" value="<?php echo htmlspecialchars($config['gemini_api_key'] ?? ''); ?>" placeholder="Enter Gemini key starting with AIza...">
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="margin-top: 35px; border-radius: 30px; padding: 12px 30px;">
                            💾 <?php echo $trans[$lang]['save_settings']; ?>
                        </button>
                    </div>

                    <div class="settings-tab-content" id="set-restore">
                        <h3 style="font-size: 18px; font-weight: 600;">🔄 <?php echo $trans[$lang]['backup_head']; ?></h3>
                        <div style="height: 1px; background: var(--border-color); margin: 15px 0 25px 0;"></div>
                        
                        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 25px;">
                            <?php echo $trans[$lang]['backup_sub']; ?>
                        </p>

                        <div class="dropzone-container" onclick="triggerFileSelect()" style="max-width: 480px; margin: 0 auto 20px auto;">
                            <span class="dropzone-icon">📤</span>
                            <span class="dropzone-text"><?php echo $trans[$lang]['select_file']; ?></span>
                            <span class="dropzone-subtext"><?php echo $trans[$lang]['allowed_formats']; ?></span>
                            <input type="file" id="backupFileInput" accept=".vyb" style="display: none;" onchange="handleFileChange(event)">
                        </div>

                        <div style="margin-top: 15px; font-weight: 600; font-size: 14px; display: none; text-align: center;" id="fileNameDisplay"></div>

                        <button class="btn btn-success" type="button" id="restoreBtn" style="width: 100%; max-width: 480px; margin: 20px auto 0 auto; display: none; border-radius: 30px; padding: 12px 30px;" onclick="startMigration()">
                            🚀 <?php echo $trans[$lang]['btn_restore']; ?>
                        </button>

                        <div class="status-alert" id="statusAlert" style="max-width: 480px; margin: 20px auto 0 auto;"></div>
                    </div>

                </form>
            </div>
 
        </main>
 
    </div>
 
    <script>
        const lang = "<?php echo $lang; ?>";
        const trans = <?php echo json_encode($trans[$lang]); ?>;
        let selectedFileBase64 = "";

        // Client-side Settings Tabs Switcher
        function switchSettingsTab(tabId, btn) {
            // Hide all tab content panes
            const contents = document.querySelectorAll('.settings-tab-content');
            contents.forEach(el => el.classList.remove('active'));
            
            // Remove active classes from all tab selector buttons
            const buttons = document.querySelectorAll('.settings-tab-btn');
            buttons.forEach(el => el.classList.remove('active'));
            
            // Display selected panel & highlight toggle
            document.getElementById('set-' + tabId).classList.add('active');
            btn.classList.add('active');
        }
 
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
            document.getElementById('restoreBtn').style.display = 'block';
            
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
