<?php
$active_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        font-size: <?php echo intval($global_config['font_scale'] ?? 100); ?>% !important;
    }
</style>
<?php

// Set group active classes based on active subpages
$group_transactions_active = ($active_page === 'billing.php' || $active_page === 'purchases.php') ? 'active active-group' : '';
$group_inventory_active = ($active_page === 'products.php' || $active_page === 'categories.php' || $active_page === 'inventory.php') ? 'active active-group' : '';
$group_marketing_active = ($active_page === 'marketing.php' || $active_page === 'whatsapp.php') ? 'active active-group' : '';

// Helper to check language and translations
$sidebar_lang = $_COOKIE['lang'] ?? 'en';
$sidebar_trans = [
    'en' => [
        'trans_header' => 'Transactions',
        'menu_billing' => '🛒 POS Billing',
        'menu_purchases' => '🧾 Purchase Invoice',
        'stock_header' => 'Inventory & Catalog',
        'menu_inventory' => '📦 Products / Items',
        'menu_categories' => '📁 Product Categories',
        'menu_adjust' => '🔄 Adjust Stock Levels',
        'khata_header' => 'Contacts & Khata',
        'menu_customers' => '👥 Customers (Udhaar)',
        'reports_header' => 'Business Reports',
        'menu_reports' => '📊 Reports Dashboard',
        'marketing_header' => 'Marketing & Tools',
        'menu_whatsapp' => '📱 WhatsApp Connection',
        'menu_marketing' => '📢 Message Campaigns',
        'setup_header' => 'Setup Configuration',
        'menu_settings' => '⚙️ Settings Panel',
        'logout' => '🚪 Log Out Session'
    ],
    'ur' => [
        'trans_header' => 'Transactions',
        'menu_billing' => '🛒 POS Billing',
        'menu_purchases' => '🧾 Purchase Invoice',
        'stock_header' => 'Inventory & Catalog',
        'menu_inventory' => '📦 Products / Items',
        'menu_categories' => '📁 Product Categories',
        'menu_adjust' => '🔄 Adjust Stock Levels',
        'khata_header' => 'Contacts & Khata',
        'menu_customers' => '👥 Customers (Udhaar)',
        'reports_header' => 'Business Reports',
        'menu_reports' => '📊 Reports Dashboard',
        'marketing_header' => 'Marketing & Tools',
        'menu_whatsapp' => '📱 WhatsApp Connection',
        'menu_marketing' => '📢 Message Campaigns',
        'setup_header' => 'Setup Configuration',
        'menu_settings' => '⚙️ Settings Panel',
        'logout' => '🚪 Log Out Session'
    ]
];
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="TijaratPro.png" alt="TijaratPro" style="width: 28px; height: 28px; border-radius: 6px; vertical-align: middle; margin-right: 6px;"> Tijarat Pro
    </div>
    
    <ul class="sidebar-menu">
        <!-- Dashboard Link -->
        <li>
            <a href="index.php" class="menu-link <?php echo ($active_page === 'index.php') ? 'active' : ''; ?>">
                <span class="menu-link-content">🏠 Dashboard Overview</span>
            </a>
        </li>

        <!-- Group: Transactions -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['trans_header']; ?></div>
        <li>
            <button class="menu-accordion-btn <?php echo $group_transactions_active; ?>" onclick="toggleAccordion('acc-transactions', this)">
                <span class="menu-link-content">💼 Transactions</span>
                <span class="menu-caret">▶</span>
            </button>
            <ul class="menu-submenu" id="acc-transactions">
                <li><a href="billing.php" class="menu-link <?php echo ($active_page === 'billing.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_billing']; ?></a></li>
                <li><a href="purchases.php" class="menu-link <?php echo ($active_page === 'purchases.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_purchases']; ?></a></li>
            </ul>
        </li>

        <!-- Group: Inventory & Catalog -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['stock_header']; ?></div>
        <li>
            <button class="menu-accordion-btn <?php echo $group_inventory_active; ?>" onclick="toggleAccordion('acc-inventory', this)">
                <span class="menu-link-content">📦 Stock Catalog</span>
                <span class="menu-caret">▶</span>
            </button>
            <ul class="menu-submenu" id="acc-inventory">
                <li><a href="products.php" class="menu-link <?php echo ($active_page === 'products.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_inventory']; ?></a></li>
                <li><a href="categories.php" class="menu-link <?php echo ($active_page === 'categories.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_categories']; ?></a></li>
                <li><a href="inventory.php" class="menu-link <?php echo ($active_page === 'inventory.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_adjust']; ?></a></li>
            </ul>
        </li>

        <!-- Group: Contacts & Khata -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['khata_header']; ?></div>
        <li>
            <a href="customers.php" class="menu-link <?php echo ($active_page === 'customers.php') ? 'active' : ''; ?>">
                <span class="menu-link-content"><?php echo $sidebar_trans[$sidebar_lang]['menu_customers']; ?></span>
            </a>
        </li>

        <!-- Group: Reports -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['reports_header']; ?></div>
        <li>
            <a href="reports.php" class="menu-link <?php echo ($active_page === 'reports.php') ? 'active' : ''; ?>">
                <span class="menu-link-content"><?php echo $sidebar_trans[$sidebar_lang]['menu_reports']; ?></span>
            </a>
        </li>

        <!-- Group: Marketing & Tools -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['marketing_header']; ?></div>
        <li>
            <button class="menu-accordion-btn <?php echo $group_marketing_active; ?>" onclick="toggleAccordion('acc-marketing', this)">
                <span class="menu-link-content">📢 Marketing Tools</span>
                <span class="menu-caret">▶</span>
            </button>
            <ul class="menu-submenu" id="acc-marketing">
                <li><a href="whatsapp.php" class="menu-link <?php echo ($active_page === 'whatsapp.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_whatsapp']; ?></a></li>
                <li><a href="marketing.php" class="menu-link <?php echo ($active_page === 'marketing.php') ? 'active-sub' : ''; ?>"><?php echo $sidebar_trans[$sidebar_lang]['menu_marketing']; ?></a></li>
            </ul>
        </li>

        <!-- Group: Configuration -->
        <div class="menu-group-header"><?php echo $sidebar_trans[$sidebar_lang]['setup_header']; ?></div>
        <li>
            <a href="settings.php" class="menu-link <?php echo ($active_page === 'settings.php') ? 'active' : ''; ?>">
                <span class="menu-link-content"><?php echo $sidebar_trans[$sidebar_lang]['menu_settings']; ?></span>
            </a>
        </li>
    </ul>

    <div style="margin-top: auto; padding-top: 15px; border-top: 1px solid #1e293b;">
        <a href="index.php?action=logout" class="menu-link" style="color: var(--danger) !important; padding: 10px 16px;">
            <span class="menu-link-content"><?php echo $sidebar_trans[$sidebar_lang]['logout']; ?></span>
        </a>
    </div>
</aside>

<script>
    // Accordion Toggle Script with LocalStorage Persistence
    function toggleAccordion(id, btn) {
        const submenu = document.getElementById(id);
        if (!submenu) return;
        
        const isOpen = submenu.classList.toggle('active');
        btn.classList.toggle('active', isOpen);
        
        // Save state to localStorage
        localStorage.setItem('sidebar_acc_' + id, isOpen ? 'open' : 'closed');
    }

    // Restore accordion states on page load
    document.addEventListener('DOMContentLoaded', () => {
        const accordions = ['acc-transactions', 'acc-inventory', 'acc-marketing'];
        accordions.forEach(id => {
            const submenu = document.getElementById(id);
            const btn = submenu?.previousElementSibling;
            if (!submenu || !btn) return;
            
            // Check if active page is inside this group to auto-expand
            const hasActiveSub = submenu.querySelector('.active-sub') !== null;
            const savedState = localStorage.getItem('sidebar_acc_' + id);
            
            if (hasActiveSub || savedState === 'open') {
                submenu.classList.add('active');
                btn.classList.add('active');
                localStorage.setItem('sidebar_acc_' + id, 'open');
            } else if (savedState === 'closed') {
                submenu.classList.remove('active');
                btn.classList.remove('active');
            } else {
                // Default collapsed
                submenu.classList.remove('active');
                btn.classList.remove('active');
            }
        });
    });
</script>
