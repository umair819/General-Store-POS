<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';
$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

$trans = [
    'en' => ['title' => 'TijaratPro', 'subtitle' => 'Dukaan se godown tak, poora hisaab aik jagah.', 'login' => 'Login', 'username' => 'Username', 'password' => 'Password', 'enter_pin' => 'Enter Quick Login PIN', 'use_pass' => 'Use Username & Password', 'use_pin' => 'Use Quick PIN Login', 'invalid' => 'Invalid username/password or PIN.', 'pin_placeholder' => 'Enter 4-Digit PIN', 'powered_by' => 'Powered by TechBrain', 'premium_portal' => 'Premium General Store Portal'],
    'ur' => ['title' => 'TijaratPro', 'subtitle' => 'Dukaan se godown tak, poora hisaab aik jagah.', 'login' => 'Login Karein', 'username' => 'Username', 'password' => 'Password', 'enter_pin' => 'Quick Login PIN enter karein', 'use_pass' => 'Username & Password use karein', 'use_pin' => 'Quick PIN Login use karein', 'invalid' => 'Username/Password ya PIN galat hai.', 'pin_placeholder' => '4-Digit PIN enter karein', 'powered_by' => 'Powered by TechBrain', 'premium_portal' => 'Premium General Store Portal']
];

$config_file = __DIR__ . '/db_config.json';
$shopName = 'TijaratPro';
if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    if ($config_data && isset($config_data['shop_name'])) { $shopName = $config_data['shop_name']; }
}
if (empty($shopName) || strtolower($shopName) === 'ah biryani' || strtolower($shopName) === 'my shop' || strtolower($shopName) === 'sapling schooling system' || strtolower($shopName) === 'sapling school') {
    $shopName = 'TijaratPro';
}

$license_status = $_GET['license_status'] ?? '';
$license_message = $_GET['message'] ?? '';
$activation_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'activate_license') {
    $key = trim($_POST['license_key'] ?? '');
    $res = activate_license($key);
    if ($res['success']) { header("Location: login.php"); exit(); }
    else { $activation_error = $res['message']; $license_status = 'invalid'; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? ''); $password = trim($_POST['password'] ?? '');
    if (!empty($username) && !empty($password)) {
        $hashed = hash('sha256', $password);
        $user = dbQueryFirst("SELECT * FROM users WHERE username = ? AND password = ?", [$username, $hashed]);
        if ($user) { $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role'] = $user['role']; $_SESSION['name'] = $user['name']; header('Location: index.php'); exit; }
        else { $error = $trans[$lang]['invalid']; }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pin_login') {
    $pin = trim($_POST['pin'] ?? '');
    if (!empty($pin)) {
        $user = dbQueryFirst("SELECT * FROM users WHERE pin = ?", [$pin]);
        if ($user) { $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role'] = $user['role']; $_SESSION['name'] = $user['name']; header('Location: index.php'); exit; }
        else { $error = $trans[$lang]['invalid']; }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shopName); ?> - Login</title>
    <script src="js/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'Poppins'; src: url('fonts/Poppins-Regular.ttf'); }
        @font-face { font-family: 'Poppins'; src: url('fonts/Poppins-Bold.ttf'); font-weight: 700; }
        body { font-family: 'Poppins', sans-serif; }
        .fade-in { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .slide-up { animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-12px); } }
        .float-anim { animation: float 6s ease-in-out infinite; }
        .feature-item { opacity: 0; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .feature-item:nth-child(1) { animation-delay: 0.3s; }
        .feature-item:nth-child(2) { animation-delay: 0.45s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }
        .feature-item:nth-child(4) { animation-delay: 0.75s; }
    </style>
</head>
<body class="bg-slate-950 h-screen flex overflow-hidden relative">

    <!-- ═══════════════ LEFT PANEL — Branding ═══════════════ -->
    <div class="hidden lg:flex w-[55%] relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-950 via-slate-950 to-yellow-950 z-0"></div>
        <div class="absolute -top-[20%] -left-[15%] w-[60%] h-[60%] bg-amber-500/15 rounded-full blur-[150px] float-anim"></div>
        <div class="absolute -bottom-[15%] -right-[10%] w-[50%] h-[50%] bg-yellow-500/12 rounded-full blur-[130px] float-anim" style="animation-delay: 3s;"></div>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:32px_32px] z-[1]"></div>

        <div class="relative z-10 slide-up">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 bg-gradient-to-tr from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/20">
                    <i class="fa-solid fa-shop"></i>
                </div>
                <span class="text-white font-black text-xl tracking-tight">TijaratPro</span>
            </div>
        </div>

        <div class="relative z-10 -mt-8">
            <h2 class="text-4xl font-black text-white leading-tight tracking-tight mb-4 slide-up">
                Smart General Store<br>
                <span class="bg-gradient-to-r from-amber-400 to-yellow-400 bg-clip-text text-transparent">Point of Sale</span>
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed mb-10 max-w-md slide-up" style="animation-delay: 0.15s;">
                Advanced kiryana retail POS designed to handle high-frequency barcode billing, supplier ledgers, cashier PIN security, and RTL Urdu printouts.
            </p>

            <div class="space-y-4 max-w-sm">
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-amber-500/15 rounded-xl flex items-center justify-center shrink-0"><i class="fa-solid fa-calculator text-amber-400 text-sm"></i></div>
                    <div><div class="text-white text-[13px] font-bold">Quick Cashier PIN Security</div><div class="text-slate-500 text-[11px] font-medium">Fast screen unlock without passwords</div></div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-yellow-500/15 rounded-xl flex items-center justify-center shrink-0"><i class="fa-solid fa-print text-yellow-400 text-sm"></i></div>
                    <div><div class="text-white text-[13px] font-bold">10+ Custom Print Layouts</div><div class="text-slate-500 text-[11px] font-medium">Thermal, A4, and RTL Urdu invoice templates</div></div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-orange-500/15 rounded-xl flex items-center justify-center shrink-0"><i class="fa-solid fa-tags text-orange-400 text-sm"></i></div>
                    <div><div class="text-white text-[13px] font-bold">Bulk Margins & Packs</div><div class="text-slate-500 text-[11px] font-medium">Auto price scaling for wholesale or loose items</div></div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-lime-500/15 rounded-xl flex items-center justify-center shrink-0"><i class="fa-solid fa-users-viewfinder text-lime-400 text-sm"></i></div>
                    <div><div class="text-white text-[13px] font-bold">Supplier Credit Ledgers</div><div class="text-slate-500 text-[11px] font-medium">Track vendor balances and cash payouts</div></div>
                </div>
            </div>
        </div>

        <div class="relative z-10">
            <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest">© 2026 TechBrain • All Rights Reserved</p>
        </div>
    </div>

    <!-- ═══════════════ RIGHT PANEL — Login Form ═══════════════ -->
    <div class="w-full lg:w-[45%] flex items-center justify-center p-6 lg:p-12 relative">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[20%] right-[-20%] w-[60%] h-[60%] bg-amber-500/5 rounded-full blur-[100px]"></div>
        </div>

        <div class="w-full max-w-[420px] z-10 fade-in relative">
          <div class="bg-white/[0.04] backdrop-blur-2xl border border-white/[0.08] rounded-[28px] p-10 shadow-2xl shadow-black/30">

            <?php if ($license_status === 'invalid'): ?>
            <div class="mb-6 bg-rose-500/10 border border-rose-500/25 p-4 rounded-2xl text-rose-300 text-[11px] font-bold flex items-center justify-between gap-3">
                <div class="flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-rose-500 text-base shrink-0"></i><span><?php echo htmlspecialchars($license_message ?: 'License expired.'); ?></span></div>
                <button onclick="toggleActivationForm(true)" class="bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-950 font-black px-3 py-2 rounded-xl transition uppercase text-[9px] shrink-0">Activate</button>
            </div>
            <?php endif; ?>

            <!-- Mobile brand -->
            <div class="flex flex-col items-center mb-8 lg:hidden">
                <div class="h-16 w-16 bg-gradient-to-tr from-amber-500 to-yellow-500 rounded-2xl flex items-center justify-center text-white text-3xl shadow-xl shadow-amber-500/20 mb-4"><i class="fa-solid fa-shop"></i></div>
                <h1 class="text-3xl font-black text-white tracking-tight"><?php echo htmlspecialchars($shopName); ?></h1>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-2"><?php echo $trans[$lang]['premium_portal']; ?></p>
            </div>

            <!-- Desktop welcome -->
            <div class="hidden lg:block mb-8">
                <h2 class="text-2xl font-black text-white tracking-tight mb-1">Welcome Back</h2>
                <p class="text-slate-500 text-sm font-medium">Sign in to <?php echo htmlspecialchars($shopName); ?></p>
            </div>

            <?php if ($error): ?>
            <div class="mb-5 bg-rose-500/10 border border-rose-500/20 p-3 rounded-xl text-rose-400 text-xs font-bold text-center"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Standard Login -->
            <form method="POST" id="loginForm" class="space-y-5">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1"><?php echo $trans[$lang]['username']; ?></label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="username" class="w-full py-3.5 pl-12 pr-4 bg-slate-900/60 border border-slate-800 focus:border-amber-500 text-white focus:bg-slate-900/80 rounded-2xl outline-none font-bold text-sm transition-all focus:ring-4 focus:ring-amber-500/10 placeholder-slate-600" placeholder="admin" required autofocus>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1"><?php echo $trans[$lang]['password']; ?></label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" class="w-full py-3.5 pl-12 pr-4 bg-slate-900/60 border border-slate-800 focus:border-amber-500 text-white focus:bg-slate-900/80 rounded-2xl outline-none font-bold text-sm transition-all focus:ring-4 focus:ring-amber-500/10 placeholder-slate-600" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 py-4 rounded-2xl font-black shadow-lg shadow-amber-500/15 transition-all transform active:scale-95 flex items-center justify-center gap-2 text-sm tracking-wider uppercase">
                    <span><?php echo $trans[$lang]['login']; ?></span><i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <!-- Quick PIN toggle -->
            <div class="mt-4 text-center">
                <button onclick="document.getElementById('pinSection').classList.toggle('hidden')" class="text-amber-400/70 text-[11px] font-bold hover:text-amber-400 transition"><?php echo $trans[$lang]['use_pin']; ?></button>
            </div>

            <div id="pinSection" class="hidden mt-5">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="pin_login">
                    <div class="relative">
                        <i class="fa-solid fa-hashtag absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="pin" maxlength="4" class="w-full py-3.5 pl-12 pr-4 bg-slate-900/60 border border-slate-800 focus:border-amber-500 text-white text-center rounded-2xl outline-none font-mono font-bold text-xl tracking-[0.5em] transition-all focus:ring-4 focus:ring-amber-500/10 placeholder-slate-600" placeholder="• • • •" required>
                    </div>
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-amber-400 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition">Unlock with PIN</button>
                </form>
            </div>

            <!-- License Activation Overlay -->
            <div id="activationCard" class="<?php echo ($activation_error) ? '' : 'hidden'; ?> fixed inset-0 bg-slate-950/90 backdrop-blur-2xl z-50 flex items-center justify-center p-6">
                <div class="bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl w-full max-w-md p-10 rounded-[32px] shadow-2xl">
                    <div class="flex flex-col items-center mb-8">
                        <div class="h-16 w-16 bg-gradient-to-tr from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center text-white text-3xl shadow-xl shadow-amber-500/20 mb-4"><i class="fa-solid fa-key"></i></div>
                        <h2 class="text-2xl font-black text-white tracking-tight">Activate POS</h2>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-2">Enter your license key</p>
                    </div>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="activate_license">
                        <div><div class="relative"><i class="fa-solid fa-key absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i><input type="text" name="license_key" id="licenseKey" class="w-full py-3.5 pl-12 pr-4 bg-slate-950/40 border border-slate-800 focus:border-amber-500 text-white rounded-2xl outline-none font-mono font-bold text-sm tracking-widest uppercase placeholder-slate-700" placeholder="TBBT-XXXX-XXXX-XXXX" required></div></div>
                        <div class="flex gap-4">
                            <button type="button" onclick="toggleActivationForm(false)" class="w-1/2 bg-slate-800 hover:bg-slate-700 text-slate-300 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition">Cancel</button>
                            <button type="submit" class="w-1/2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 py-3.5 rounded-2xl font-black shadow-lg transition transform active:scale-95 text-xs uppercase tracking-wider">Activate</button>
                        </div>
                    </form>
                    <?php if ($activation_error): ?><p class="text-rose-400 text-xs font-bold text-center mt-5 bg-rose-500/10 p-3 rounded-xl border border-rose-500/20"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?php echo $activation_error; ?></p><?php endif; ?>
                </div>
            </div>

            <div class="mt-8 text-center border-t border-slate-800/40 pt-6">
                <p class="text-[9px] text-slate-600 font-black uppercase tracking-widest"><?php echo $trans[$lang]['powered_by']; ?></p>
            </div>
          </div><!-- glass card end -->
        </div>
    </div>

    <script>
        function toggleActivationForm(show) {
            const card = document.getElementById('activationCard');
            if (show) { card.classList.remove('hidden'); document.getElementById('licenseKey').focus(); }
            else { card.classList.add('hidden'); }
        }
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('license_status') === 'invalid') { toggleActivationForm(true); }
    </script>
</body>
</html>
