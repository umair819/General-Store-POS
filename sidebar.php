<?php
$active_page = basename($_SERVER['PHP_SELF']);
$sidebar_lang = $_COOKIE['lang'] ?? 'en';
$currentUserName = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
$currentUserRole = $_SESSION['role'] ?? 'Administrator';
$avatarInitial = !empty($currentUserName) ? strtoupper(substr($currentUserName, 0, 1)) : 'A';
?>
<?php
$sidebar_pkg = getActivePackage();
$is_hybrid = isHybridPackage();
$pkgBadgeText = [
    'O1' => 'O1 Solo',
    'O2' => 'O2 Pro',
    'O3' => 'O3 Enterprise',
    'H1' => 'H1 Hybrid',
    'H2' => 'H2 Multi-Store',
    'H3' => 'H3 Omni ERP',
][$sidebar_pkg] ?? $sidebar_pkg;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<aside class="sidebar" id="mainSidebar">
    <!-- Brand Header Logo -->
    <div class="sidebar-brand-wrapper">
        <a href="index.php" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-shop"></i>
            </div>
            <div class="sidebar-brand-text">Tijarat<span>Pro</span></div>
        </a>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding: 4px 8px; background: var(--bg-input); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
            <span style="font-size: 11px; font-weight: 700; color: var(--accent); display: flex; align-items: center; gap: 5px;">
                <i class="fa-solid <?php echo $is_hybrid ? 'fa-cloud' : 'fa-hard-drive'; ?>"></i>
                <?php echo htmlspecialchars($pkgBadgeText); ?>
            </span>
            <a href="setup_wizard.php" title="Run Setup Wizard / Change Item Types" style="font-size: 11px; color: var(--text-muted); text-decoration: none; padding: 2px 6px; border-radius: 4px; background: var(--bg-card);">
                <i class="fa-solid fa-sliders"></i>
            </a>
        </div>
    </div>
    
<?php
$pending_web_orders = 0;
try {
    $res = dbQuery("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'pending'");
    $pending_web_orders = (int)($res[0]['cnt'] ?? 0);
} catch (Exception $e) {}
?>
    <!-- Flat Menu Navigation (Sleek Buttons, No Extra Group Headers) -->
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="menu-link <?php echo ($active_page === 'index.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="billing.php" class="menu-link <?php echo ($active_page === 'billing.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-receipt"></i> <span>POS Billing</span>
            </a>
        </li>
        <li>
            <a href="online_orders.php" class="menu-link <?php echo ($active_page === 'online_orders.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-earth-americas"></i> <span>Online Orders</span>
                <?php if ($pending_web_orders > 0): ?>
                    <span style="margin-left: auto; background: var(--accent); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 9999px;"><?php echo $pending_web_orders; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="purchases.php" class="menu-link <?php echo ($active_page === 'purchases.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-invoice"></i> <span>Purchase Invoice</span>
            </a>
        </li>
        <li>
            <a href="products.php" class="menu-link <?php echo ($active_page === 'products.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-boxes-stacked"></i> <span>Products & Catalog</span>
            </a>
        </li>
        <li>
            <a href="categories.php" class="menu-link <?php echo ($active_page === 'categories.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-folder-tree"></i> <span>Product Categories</span>
            </a>
        </li>
        <li>
            <a href="inventory.php" class="menu-link <?php echo ($active_page === 'inventory.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-rotate"></i> <span>Stock Adjustments</span>
            </a>
        </li>
        <li>
            <a href="customers.php" class="menu-link <?php echo ($active_page === 'customers.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> <span>Customers (Udhaar)</span>
            </a>
        </li>
        <li>
            <a href="reports.php" class="menu-link <?php echo ($active_page === 'reports.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i> <span>Business Reports</span>
            </a>
        </li>
        <li>
            <a href="whatsapp.php" class="menu-link <?php echo ($active_page === 'whatsapp.php') ? 'active' : ''; ?>">
                <i class="fa-brands fa-whatsapp"></i> <span>WhatsApp Bot</span>
            </a>
        </li>
        <li>
            <a href="marketing.php" class="menu-link <?php echo ($active_page === 'marketing.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i> <span>Marketing Tools</span>
            </a>
        </li>
    </ul>

    <!-- User Profile Card (Travelista / TripSync Style) -->
    <div class="sidebar-user-card">
        <div class="sidebar-user-avatar">
            <?php echo htmlspecialchars($avatarInitial); ?>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?php echo htmlspecialchars($currentUserName); ?></div>
            <div class="sidebar-user-role"><?php echo htmlspecialchars(strtoupper($currentUserRole)); ?></div>
        </div>
    </div>

    <!-- Bottom Action Buttons (TripSync Outline Pill Buttons) -->
    <div class="sidebar-bottom-actions">
        <button onclick="toggleSidebarCollapse()" class="sidebar-action-btn">
            <i class="fa-solid fa-chevron-left sidebar-toggle-icon"></i> <span>Collapse Sidebar</span>
        </button>
        <button onclick="toggleTheme()" class="sidebar-action-btn">
            <i class="fa-solid fa-moon"></i> <span>Dark Mode</span>
        </button>
        <a href="settings.php" class="sidebar-action-btn <?php echo ($active_page === 'settings.php') ? 'active-action' : ''; ?>">
            <i class="fa-solid fa-gear"></i> <span>Settings</span>
        </a>
        <a href="index.php?action=logout" class="sidebar-action-btn logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebarCollapse() {
    const sb = document.getElementById('mainSidebar');
    if (!sb) return;
    const isCollapsed = sb.classList.toggle('collapsed');
    localStorage.setItem('tijarat_sidebar_collapsed', isCollapsed ? 'true' : 'false');
}

// Restore collapsed state on load
document.addEventListener('DOMContentLoaded', () => {
    const sb = document.getElementById('mainSidebar');
    if (sb && localStorage.getItem('tijarat_sidebar_collapsed') === 'true') {
        sb.classList.add('collapsed');
    }
});
</script>
