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
        'title' => 'Tijarat Inventory - Categories',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Tijarat Inventory',
        'menu_customers' => '👥 Tijarat Ledger (Khata)',
        'menu_purchases' => '🧾 Purchases',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '🗂️ Manage Categories',
        'add_new' => '➕ Add Category',
        'tbl_id' => 'ID',
        'tbl_name' => 'Category Name',
        'tbl_desc' => 'Description',
        'tbl_actions' => 'Actions',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'edit_category' => 'Edit Category',
        'new_category' => 'New Category',
        'confirm_delete' => 'Are you sure you want to delete this category?',
    ],
    'ur' => [
        'title' => 'Tijarat Inventory - Categories',
        'dashboard' => 'Dashboard Overview',
        'menu_billing' => '🛒 Tijarat POS',
        'menu_inventory' => '📦 Stock & Inventory',
        'menu_customers' => '👥 Customers & Khata (Udhaar)',
        'menu_purchases' => '🧾 Purchases (Khareedari)',
        'menu_reports' => '📈 Sales Reports',
        'menu_settings' => '⚙️ Settings',
        'menu_marketing' => '📢 Marketing Tool',
        'logout' => '🚪 Logout',
        'heading' => '🗂️ Categories Management',
        'add_new' => '➕ Add Category',
        'tbl_id' => 'ID',
        'tbl_name' => 'Category Name',
        'tbl_desc' => 'Description',
        'tbl_actions' => 'Actions',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'edit_category' => 'Category Edit Karein',
        'new_category' => 'Nayi Category',
        'confirm_delete' => 'Kya aap sach mein is category ko delete karna chahte hain?',
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
        /* Shared Sidebar/Header Layout */
        .layout-wrapper { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 280px; background-color: var(--bg-card); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 24px; height: 100vh; }
        .sidebar-brand { font-family: var(--font-heading); font-size: 24px; font-weight: 700; color: var(--accent); margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
        .menu-link { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-radius: var(--radius-sm); color: var(--text-main); text-decoration: none; font-family: var(--font-heading); font-weight: 500; transition: var(--transition-smooth); }
        .menu-link:hover, .menu-link.active { background-color: var(--bg-input); color: var(--accent); border-left: 4px solid var(--accent); padding-left: 16px; }
        .content-panel { flex-grow: 1; padding: 40px; display: flex; flex-direction: column; gap: 30px; height: 100vh; overflow-y: auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 30px; box-shadow: var(--shadow-sm); }
        
        /* Table Styling */
        .data-table-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th, .data-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); }
        .data-table th { background-color: var(--bg-input); font-weight: 600; font-family: var(--font-heading); color: var(--text-muted); font-size: 14px; text-transform: uppercase; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background-color: var(--bg-app); }

        /* Modal Overlay & Card */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease; }
        .modal.active { display: flex; opacity: 1; }
        .modal-card { width: 100%; max-width: 500px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal.active .modal-card { transform: scale(1); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }


    </style>
</head>
<body class="<?php echo ($lang === 'ur') ? 'lang-urdu' : ''; ?>">

    <div class="layout-wrapper">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

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

            <!-- Actions Bar -->
            <div style="display: flex; justify-content: <?php echo ($lang === 'ur') ? 'flex-start' : 'flex-end'; ?>;">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <?php echo $trans[$lang]['add_new']; ?>
                </button>
            </div>

            <!-- Categories Data Table -->
            <div class="data-table-container">
                <table class="data-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th><?php echo $trans[$lang]['tbl_id']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_name']; ?></th>
                            <th><?php echo $trans[$lang]['tbl_desc']; ?></th>
                            <th style="text-align: center;"><?php echo $trans[$lang]['tbl_actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Categories dynamically loaded here -->
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Loading categories...</td></tr>
                    </tbody>
                </table>
            </div>

        </main>

    </div>

    <!-- Category Add/Edit Modal Dialogue -->
    <div class="modal" id="categoryModal">
        <div class="card modal-card" style="text-align: <?php echo ($lang === 'ur') ? 'right' : 'left'; ?>">
            <h3 id="modalTitle" style="margin-bottom: 20px;">Category</h3>
            
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <input type="hidden" id="categoryId" name="id" value="">
                
                <div class="form-group">
                    <label class="form-label" for="categoryName"><?php echo $trans[$lang]['tbl_name']; ?></label>
                    <input class="form-control" type="text" id="categoryName" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="categoryDesc"><?php echo $trans[$lang]['tbl_desc']; ?></label>
                    <textarea class="form-control" id="categoryDesc" rows="3" style="resize: none;"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <?php echo $trans[$lang]['cancel']; ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $trans[$lang]['save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const lang = "<?php echo $lang; ?>";
        const trans = <?php echo json_encode($trans[$lang]); ?>;

        document.addEventListener('DOMContentLoaded', loadCategories);

        function loadCategories() {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get-categories' })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'categories-list');
                if (response && response.data) {
                    renderTable(response.data);
                }
            });
        }

        function renderTable(categories) {
            const tbody = document.querySelector('#categoriesTable tbody');
            tbody.innerHTML = '';

            if (categories.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No categories found.</td></tr>`;
                return;
            }

            categories.forEach(cat => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${cat.id}</td>
                    <td style="font-weight: 500;">${cat.name}</td>
                    <td style="color: var(--text-muted);">${cat.description || '-'}</td>
                    <td style="text-align: center;">
                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 13px;" onclick="openEditModal(${cat.id}, '${cat.name.replace(/'/g, "\\'")}', '${(cat.description || '').replace(/'/g, "\\'")}')">
                            ✏️ ${trans.btn_edit}
                        </button>
                        <button class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="deleteCategory(${cat.id})">
                            🗑️ ${trans.btn_delete}
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function openAddModal() {
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDesc').value = '';
            document.getElementById('modalTitle').innerText = trans.new_category;
            
            const modal = document.getElementById('categoryModal');
            modal.classList.add('active');
        }

        function openEditModal(id, name, desc) {
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryDesc').value = desc;
            document.getElementById('modalTitle').innerText = trans.edit_category;
            
            const modal = document.getElementById('categoryModal');
            modal.classList.add('active');
        }

        function closeModal() {
            const modal = document.getElementById('categoryModal');
            modal.classList.remove('active');
        }

        function saveCategory(e) {
            e.preventDefault();
            const id = document.getElementById('categoryId').value;
            const name = document.getElementById('categoryName').value;
            const description = document.getElementById('categoryDesc').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save-category',
                    data: { id, name, description }
                })
            })
            .then(res => res.json())
            .then(data => {
                const response = data.find(r => r.channel === 'category-saved');
                if (response && response.data.success) {
                    closeModal();
                    loadCategories();
                } else {
                    alert(response ? response.data.msg : 'Error saving category');
                }
            });
        }

        function deleteCategory(id) {
            if (confirm(trans.confirm_delete)) {
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete-category',
                        data: { id }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const response = data.find(r => r.channel === 'category-deleted');
                    if (response && response.data.success) {
                        loadCategories();
                    } else {
                        alert(response ? response.data.msg : 'Error deleting category');
                    }
                });
            }
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
