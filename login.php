<?php
session_start();
require_once __DIR__ . '/db_config.php';

// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

// Translations dictionary
$trans = [
    'en' => [
        'title' => 'TijaratPro',
        'subtitle' => 'Dukaan se godown tak, poora hisaab aik jagah.',
        'login' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'enter_pin' => 'Enter Quick Login PIN',
        'use_pass' => 'Use Username & Password',
        'use_pin' => 'Use Quick PIN Login',
        'invalid' => 'Invalid username/password or PIN.',
        'pin_placeholder' => 'Enter 4-Digit PIN',
    ],
    'ur' => [
        'title' => 'تجارت پرو',
        'subtitle' => 'دکان سے گودام تک، پورا حساب ایک جگہ۔',
        'login' => 'لاگ ان کریں',
        'username' => 'صارف کا نام (Username)',
        'password' => 'پاس ورڈ (Password)',
        'enter_pin' => 'فوری لاگ ان پن (PIN) درج کریں',
        'use_pass' => 'صارف کا نام اور پاس ورڈ استعمال کریں',
        'use_pin' => 'فوری پن لاگ ان استعمال کریں',
        'invalid' => 'صارف کا نام/پاس ورڈ یا پن غلط ہے۔',
        'pin_placeholder' => '4 ہندسوں کا پن درج کریں',
    ]

];

// Handle Standard Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!empty($username) && !empty($password)) {
        // Hash password using SHA256 (same as default insert in schema.sql)
        $hashed = hash('sha256', $password);
        
        $user = dbQueryFirst("SELECT * FROM users WHERE username = ? AND password = ?", [$username, $hashed]);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = $trans[$lang]['invalid'];
        }
    }
}

// Handle PIN Form Submission (API / POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pin_login') {
    $pin = trim($_POST['pin'] ?? '');
    
    if (!empty($pin)) {
        $user = dbQueryFirst("SELECT * FROM users WHERE pin = ?", [$pin]);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = $trans[$lang]['invalid'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $trans[$lang]['title']; ?> - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Extra styles for login toggles */
        .login-toggle-mode {
            margin-top: 24px;
            font-size: 14px;
            color: var(--accent);
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-weight: 500;
        }
        .login-toggle-mode:hover {
            text-decoration: underline;
        }
        .error-banner {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
        }
        .pin-display {
            letter-spacing: 12px;
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            height: 40px;
            margin-bottom: 15px;
            color: var(--accent);
        }
    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <!-- System Control Toggles -->
    <div class="system-toggles">
        <button class="toggle-btn" onclick="toggleLanguage()">
            <?php echo ($lang === 'ur') ? 'English' : 'اردو'; ?>
        </button>
        <button class="toggle-btn" onclick="toggleTheme()">
            <?php echo ($theme === 'dark') ? '☀️ Light' : '🌙 Dark'; ?>
        </button>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="TijaratPro.png" alt="TijaratPro" style="width: 64px; height: 64px; border-radius: 12px; margin-bottom: 8px; object-fit: contain;">
                    <div><?php echo $trans[$lang]['title']; ?></div>
                </div>
                <div class="login-subtitle">
                    <?php echo $trans[$lang]['subtitle']; ?>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-banner">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Username/Password Login Form -->
            <form id="passForm" method="POST" action="login.php" style="display: <?php echo (isset($_GET['mode']) && $_GET['mode'] === 'pin') ? 'none' : 'block'; ?>">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                    <label class="form-label" for="username"><?php echo $trans[$lang]['username']; ?></label>
                    <input class="form-control" type="text" id="username" name="username" required placeholder="admin" autofocus>
                </div>

                <div class="form-group" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
                    <label class="form-label" for="password"><?php echo $trans[$lang]['password']; ?></label>
                    <input class="form-control" type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 10px;">
                    🚀 <?php echo $trans[$lang]['login']; ?>
                </button>

                <div class="login-toggle-mode" onclick="switchMode('pin')">
                    🔑 <?php echo $trans[$lang]['use_pin']; ?>
                </div>
            </form>

            <!-- PIN-based Login Form -->
            <form id="pinForm" method="POST" action="login.php" style="display: <?php echo (isset($_GET['mode']) && $_GET['mode'] === 'pin') ? 'block' : 'none'; ?>">
                <input type="hidden" name="action" value="pin_login">
                <input type="hidden" id="pinInput" name="pin" value="">

                <div class="form-group">
                    <label class="form-label"><?php echo $trans[$lang]['enter_pin']; ?></label>
                    <div class="pin-dots">
                        <div class="pin-dot" id="dot1"></div>
                        <div class="pin-dot" id="dot2"></div>
                        <div class="pin-dot" id="dot3"></div>
                        <div class="pin-dot" id="dot4"></div>
                    </div>
                    <div class="pin-display" id="pinDisplay"></div>
                </div>

                <div class="pin-login-grid">
                    <button type="button" class="pin-btn" onclick="pressPin('1')">1</button>
                    <button type="button" class="pin-btn" onclick="pressPin('2')">2</button>
                    <button type="button" class="pin-btn" onclick="pressPin('3')">3</button>
                    <button type="button" class="pin-btn" onclick="pressPin('4')">4</button>
                    <button type="button" class="pin-btn" onclick="pressPin('5')">5</button>
                    <button type="button" class="pin-btn" onclick="pressPin('6')">6</button>
                    <button type="button" class="pin-btn" onclick="pressPin('7')">7</button>
                    <button type="button" class="pin-btn" onclick="pressPin('8')">8</button>
                    <button type="button" class="pin-btn" onclick="pressPin('9')">9</button>
                    <button type="button" class="pin-btn btn-danger" onclick="clearPin()">✖</button>
                    <button type="button" class="pin-btn" onclick="pressPin('0')">0</button>
                    <button type="button" class="pin-btn btn-success" onclick="submitPin()">✔</button>
                </div>

                <div class="login-toggle-mode" onclick="switchMode('pass')">
                    👤 <?php echo $trans[$lang]['use_pass']; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        let pin = "";

        function switchMode(mode) {
            const passForm = document.getElementById('passForm');
            const pinForm = document.getElementById('pinForm');
            const url = new URL(window.location.href);

            if (mode === 'pin') {
                passForm.style.display = 'none';
                pinForm.style.display = 'block';
                url.searchParams.set('mode', 'pin');
            } else {
                passForm.style.display = 'block';
                pinForm.style.display = 'none';
                url.searchParams.delete('mode');
            }
            window.history.replaceState({}, '', url);
            clearPin();
        }

        // Quick PIN keypad logic
        function pressPin(num) {
            if (pin.length < 4) {
                pin += num;
                updatePinDots();
            }
            if (pin.length === 4) {
                // Auto submit when 4 digits are completed
                setTimeout(submitPin, 300);
            }
        }

        function clearPin() {
            pin = "";
            updatePinDots();
        }

        function updatePinDots() {
            const pinDisplay = document.getElementById('pinDisplay');
            pinDisplay.innerText = "• ".repeat(pin.length);
            
            for (let i = 1; i <= 4; i++) {
                const dot = document.getElementById('dot' + i);
                if (i <= pin.length) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            }
        }

        function submitPin() {
            if (pin.length === 4) {
                document.getElementById('pinInput').value = pin;
                document.getElementById('pinForm').submit();
            }
        }

        // Toggles helper
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
            
            // Reload the theme display
            const themeBtn = document.querySelector('.system-toggles button:nth-child(2)');
            themeBtn.innerText = (newTheme === 'dark') ? '☀️ Light' : '🌙 Dark';
        }
    </script>
</body>
</html>
