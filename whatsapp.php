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
        'title' => '📱 WhatsApp Connection Setup',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Stock & Inventory',
        'menu_customers' => '👥 Customers & Khata (Udhaar)',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_marketing' => '📢 Marketing Tool',
        'menu_settings' => '⚙️ Settings',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        
        'setup_header' => 'WhatsApp Background Service Connection',
        'status_label' => 'Connection Status:',
        'qr_instruction' => 'Open WhatsApp on your mobile device, tap Settings -> Linked Devices -> Link a Device, and scan this QR code.',
        'btn_disconnect' => 'Disconnect WhatsApp Account',
        'connected_info' => 'Your phone is linked, and background notifications are running.',
        'connected_number' => 'Linked Number:',
        'checking_service' => 'Checking background service connection status...',
        'service_down' => '⚠️ Background WhatsApp service is offline. Please restart the desktop application.'
    ],
    'ur' => [
        'title' => '📱 WhatsApp Connection Setup',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Stock & Inventory',
        'menu_customers' => '👥 Customers & Khata (Udhaar)',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_marketing' => '📢 Marketing Tool',
        'menu_settings' => '⚙️ Settings',
        'logout' => '🚪 Logout',
        'dashboard' => 'Dashboard Overview',
        
        'setup_header' => 'WhatsApp Setup Configure karein',
        'status_label' => 'Connection Status:',
        'qr_instruction' => 'Apne mobile par WhatsApp open karein, Settings -> Linked Devices par tap karein aur is QR code ko scan karein.',
        'btn_disconnect' => 'WhatsApp Account Disconnect karein',
        'connected_info' => 'Aapka number link ho chuka hai aur background notifications chal rahi hain.',
        'connected_number' => 'Linked Number:',
        'checking_service' => 'Background service connection check ho raha hai...',
        'service_down' => '⚠️ Background WhatsApp service offline hai. Barahe meharbani desktop application restart karein.'
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

        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }
        
        /* Setup Specific Cards */
        .setup-card { max-width: 580px; margin: 0 auto; padding: 35px; text-align: center; }
        .status-badge-container { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 15px; font-size: 16px; font-weight: 500; }
        
        .qr-wrapper { margin: 30px auto; padding: 15px; width: 290px; height: 290px; background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); position: relative; }
        .qr-spinner { width: 45px; height: 45px; border: 4px solid rgba(0,0,0,0.1); border-top-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite; }
        .qr-img { width: 100%; height: 100%; display: none; }
        
        .setup-instructions { font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; }
        .connected-info-box { background-color: rgba(16, 185, 129, 0.08); border: 1px solid var(--success); color: var(--text-main); border-radius: var(--radius-sm); padding: 20px; margin: 25px 0; font-size: 14px; text-align: left; }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="layout-wrapper">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Display Panel -->
        <main class="content-panel">
            
            <header class="header-nav">
                <h2 style="font-size: 22px; font-weight: 600;">
                    📱 <?php echo $trans[$lang]['title']; ?>
                </h2>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button class="toggle-btn" onclick="toggleLanguage()"><?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?></button>
                    <button class="toggle-btn" onclick="toggleTheme()"><?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?></button>
                </div>
            </header>

            <div style="flex-grow: 1; display: flex; align-items: center; justify-content: center;">
                
                <!-- Alert: Background Daemon Offline -->
                <div class="card" id="serviceDownAlert" style="max-width: 480px; padding: 30px; text-align: center; border-color: var(--danger); background-color: rgba(239, 68, 68, 0.04); display: none;">
                    <p style="font-weight: 600; color: var(--danger);"><?php echo $trans[$lang]['service_down']; ?></p>
                </div>

                <!-- Setup Card -->
                <div class="card setup-card" id="whatsappContentCard" style="display: block;">
                    <h3><?php echo $trans[$lang]['setup_header']; ?></h3>
                    
                    <div class="status-badge-container">
                        <span><?php echo $trans[$lang]['status_label']; ?></span>
                        <span id="statusText">Checking...</span>
                    </div>

                    <!-- QR Pair Window -->
                    <div id="qrContainer" style="display: none;">
                        <div class="qr-wrapper">
                            <div class="qr-spinner" id="qrSpinner"></div>
                            <img class="qr-img" id="qrImage" alt="WhatsApp Connection QR">
                        </div>
                        <p class="setup-instructions"><?php echo $trans[$lang]['qr_instruction']; ?></p>
                    </div>

                    <!-- Connected Details -->
                    <div id="connectedDetails" style="display: none;">
                        <div class="connected-info-box">
                            <p style="font-weight: 600; color: var(--success); margin-bottom: 8px;">✔️ Active Connection</p>
                            <p style="color: var(--text-muted); margin-bottom: 12px;"><?php echo $trans[$lang]['connected_info']; ?></p>
                            <p style="font-weight: 500;">
                                <?php echo $trans[$lang]['connected_number']; ?> <span id="linkedNumber" style="font-family: monospace; font-size: 15px; background: var(--bg-input); padding: 2px 8px; border-radius: 4px;">+92...</span>
                            </p>
                        </div>
                    </div>

                    <!-- Disconnect Action -->
                    <button class="btn btn-danger" id="disconnectBtn" onclick="disconnectWhatsapp()" style="display: none; width: 100%; border-radius: 30px; margin-top: 10px;">
                        <?php echo $trans[$lang]['btn_disconnect']; ?>
                    </button>
                </div>

            </div>

        </main>

    </div>

    <script>
        const serviceUrl = 'http://127.0.0.1:9001';
        let statusInterval = null;

        async function checkStatus() {
            try {
                const res = await fetch(`${serviceUrl}/status`);
                if (!res.ok) throw new Error('Offline');
                const data = await res.json();
                
                document.getElementById('serviceDownAlert').style.display = 'none';
                document.getElementById('whatsappContentCard').style.display = 'block';
                
                updateUI(data);
            } catch (err) {
                document.getElementById('serviceDownAlert').style.display = 'block';
                document.getElementById('whatsappContentCard').style.display = 'none';
            }
        }

        function updateUI(data) {
            const statusText = document.getElementById('statusText');
            const qrContainer = document.getElementById('qrContainer');
            const connectedDetails = document.getElementById('connectedDetails');
            const disconnectBtn = document.getElementById('disconnectBtn');
            
            if (data.state === 'CONNECTED') {
                statusText.innerHTML = '<span style="color: var(--success); font-weight: bold;"><?php echo ($lang === "ur") ? "Active (Connected)" : "Active (Connected)"; ?></span>';
                qrContainer.style.display = 'none';
                connectedDetails.style.display = 'block';
                disconnectBtn.style.display = 'block';
                
                if (data.user) {
                    document.getElementById('linkedNumber').textContent = '+' + data.user.id.split(':')[0].split('@')[0];
                }
            } else if (data.state === 'QR_READY') {
                statusText.innerHTML = '<span style="color: var(--primary-blue); font-weight: bold;"><?php echo ($lang === "ur") ? "Scan QR Code" : "Scan QR Code"; ?></span>';
                connectedDetails.style.display = 'none';
                disconnectBtn.style.display = 'none';
                qrContainer.style.display = 'block';
                
                fetchQR();
            } else if (data.state === 'CONNECTING') {
                statusText.innerHTML = '<span style="color: var(--warning); font-weight: bold;"><?php echo ($lang === "ur") ? "Connecting..." : "Connecting..."; ?></span>';
                qrContainer.style.display = 'none';
                connectedDetails.style.display = 'none';
                disconnectBtn.style.display = 'none';
            } else {
                statusText.innerHTML = '<span style="color: var(--danger); font-weight: bold;"><?php echo ($lang === "ur") ? "Disconnected" : "Disconnected"; ?></span>';
                qrContainer.style.display = 'none';
                connectedDetails.style.display = 'none';
                disconnectBtn.style.display = 'none';
            }
        }

        async function fetchQR() {
            try {
                const res = await fetch(`${serviceUrl}/qr`);
                const data = await res.json();
                if (data.qr) {
                    document.getElementById('qrImage').src = data.qr;
                    document.getElementById('qrSpinner').style.display = 'none';
                    document.getElementById('qrImage').style.display = 'block';
                } else {
                    document.getElementById('qrSpinner').style.display = 'block';
                    document.getElementById('qrImage').style.display = 'none';
                }
            } catch (e) {}
        }

        async function disconnectWhatsapp() {
            if (!confirm('Are you sure you want to unlink WhatsApp?')) return;
            try {
                const res = await fetch(`${serviceUrl}/disconnect`, { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    checkStatus();
                }
            } catch (e) {
                alert('Failed to disconnect.');
            }
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

        // Initialize status loops
        checkStatus();
        statusInterval = setInterval(checkStatus, 3000);
    </script>
</body>
</html>
