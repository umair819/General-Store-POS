<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lang = $_COOKIE['lang'] ?? 'en';
$theme = $_COOKIE['theme'] ?? 'light';

// Current config values
$shop_name = getConfig('shop_name', 'TijaratPro Store');
$shop_phone = getConfig('shop_phone', '');
$shop_address = getConfig('shop_address', '');
$shop_currency = getConfig('shop_currency', 'PKR');
$active_package = getActivePackage();
$active_item_types = getActiveItemTypes();
$store_setup_complete = getConfig('store_setup_complete', '0');

// Fetch item types from DB
$item_types = dbQuery("SELECT * FROM item_types ORDER BY sort_order ASC, name ASC");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Setup Wizard — Tijarat PRO</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--bg-app);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .wizard-container {
            width: 100%;
            max-width: 980px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wizard-header {
            padding: 32px 40px 24px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.02));
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon-box {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }

        .brand-text h1 {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-text h1 span {
            color: var(--accent);
        }

        .brand-text p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Step Indicator */
        .step-progress-bar {
            display: flex;
            gap: 16px;
            padding: 20px 40px;
            background: var(--bg-input);
            border-bottom: 1px solid var(--border-color);
        }

        .step-pill {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: var(--transition-smooth);
            cursor: pointer;
        }

        .step-pill.active {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent);
        }

        .step-pill.completed {
            border-color: var(--success);
            color: var(--success);
        }

        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--border-color);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .step-pill.active .step-num {
            background: var(--accent);
            color: #fff;
        }

        .step-pill.completed .step-num {
            background: var(--success);
            color: #fff;
        }

        /* Wizard Content Pages */
        .wizard-body {
            padding: 36px 40px;
            min-height: 480px;
        }

        .wizard-step {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .wizard-step.active {
            display: block;
        }

        .step-title-block {
            margin-bottom: 24px;
        }

        .step-title-block h2 {
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .step-title-block p {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Form Controls */
        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background-color: var(--bg-input);
            color: var(--text-main);
            font-size: 14px;
            outline: none;
            transition: var(--transition-smooth);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--glow-color);
        }

        /* Item Types Grid Selection */
        .types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 16px;
            max-height: 380px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .type-card {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            background: var(--bg-input);
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 8px;
            user-select: none;
        }

        .type-card:hover {
            transform: translateY(-2px);
            border-color: var(--accent);
            box-shadow: var(--shadow-sm);
        }

        .type-card.selected {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.08);
            box-shadow: 0 0 0 2px var(--accent);
        }

        .type-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .type-icon {
            font-size: 28px;
        }

        .type-check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: transparent;
            transition: all 0.2s;
        }

        .type-card.selected .type-check {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .type-name {
            font-family: var(--font-heading);
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
        }

        .type-name-ur {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
        }

        .type-desc {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Package Tier Selection */
        .tier-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .tier-tab-btn {
            flex: 1;
            padding: 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: var(--bg-input);
            color: var(--text-main);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition-smooth);
        }

        .tier-tab-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .pkg-card {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            background: var(--bg-card);
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .pkg-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            box-shadow: var(--shadow-md);
        }

        .pkg-card.selected {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.06);
            box-shadow: 0 0 0 2px var(--accent);
        }

        .pkg-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background: var(--accent-light);
            color: var(--accent-hover);
        }

        .pkg-name {
            font-family: var(--font-heading);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .pkg-code {
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 12px;
        }

        .pkg-desc {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 16px;
            min-height: 48px;
        }

        .pkg-features {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .pkg-features li {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .pkg-features li i {
            color: var(--success);
            font-size: 12px;
            margin-top: 2px;
        }

        /* Wizard Footer */
        .wizard-footer {
            padding: 24px 40px;
            background: var(--bg-input);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .btn-success:hover {
            background: #059669;
        }

        /* Summary Preview Box */
        .summary-box {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .summary-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .summary-val {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .types-tags-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .type-tag-pill {
            background: var(--accent-light);
            color: var(--accent-hover);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <div class="wizard-container">
        
        <!-- Header -->
        <header class="wizard-header">
            <div class="brand-title">
                <div class="brand-icon-box">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div class="brand-text">
                    <h1>Tijarat<span>PRO</span> Quick Setup</h1>
                    <p>Configure your retail store identity, item types & package in seconds</p>
                </div>
            </div>
            <div>
                <?php if ($store_setup_complete === '1'): ?>
                    <a href="index.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                        <i class="fa-solid fa-arrow-left"></i> Return to Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Progress Steps -->
        <nav class="step-progress-bar">
            <div class="step-pill active" id="pill-1" onclick="goToStep(1)">
                <span class="step-num">1</span>
                <span>Store Profile</span>
            </div>
            <div class="step-pill" id="pill-2" onclick="goToStep(2)">
                <span class="step-num">2</span>
                <span>Business & Item Types</span>
            </div>
            <div class="step-pill" id="pill-3" onclick="goToStep(3)">
                <span class="step-num">3</span>
                <span>Package Edition</span>
            </div>
            <div class="step-pill" id="pill-4" onclick="goToStep(4)">
                <span class="step-num">4</span>
                <span>Review & Launch</span>
            </div>
        </nav>

        <!-- Body -->
        <div class="wizard-body">

            <!-- STEP 1: Store Profile -->
            <section class="wizard-step active" id="step-1">
                <div class="step-title-block">
                    <h2>🏪 Store Profile & Identity</h2>
                    <p>Enter your store's basic information. These will appear on POS receipts, invoices, and reports.</p>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="setupShopName">Shop / Business Name *</label>
                        <input type="text" class="form-control" id="setupShopName" value="<?php echo htmlspecialchars($shop_name); ?>" placeholder="e.g. Al-Madina Perfumes & Oud" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="setupShopPhone">Contact Phone / WhatsApp *</label>
                        <input type="text" class="form-control" id="setupShopPhone" value="<?php echo htmlspecialchars($shop_phone); ?>" placeholder="e.g. 03001234567" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="setupShopAddress">Shop Address / Location</label>
                        <input type="text" class="form-control" id="setupShopAddress" value="<?php echo htmlspecialchars($shop_address); ?>" placeholder="e.g. Shop # 12, Saddar Shopping Mall, Karachi">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="setupCurrency">Currency Code</label>
                        <select class="form-control" id="setupCurrency">
                            <option value="PKR" <?php echo $shop_currency === 'PKR' ? 'selected' : ''; ?>>PKR — Pakistani Rupee (Rs.)</option>
                            <option value="AED" <?php echo $shop_currency === 'AED' ? 'selected' : ''; ?>>AED — UAE Dirham</option>
                            <option value="SAR" <?php echo $shop_currency === 'SAR' ? 'selected' : ''; ?>>SAR — Saudi Riyal</option>
                            <option value="USD" <?php echo $shop_currency === 'USD' ? 'selected' : ''; ?>>USD — US Dollar ($)</option>
                            <option value="EUR" <?php echo $shop_currency === 'EUR' ? 'selected' : ''; ?>>EUR — Euro (€)</option>
                            <option value="GBP" <?php echo $shop_currency === 'GBP' ? 'selected' : ''; ?>>GBP — British Pound (£)</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- STEP 2: Business & Item Types -->
            <section class="wizard-step" id="step-2">
                <div class="step-title-block">
                    <h2>🏷️ Select Store Item Types</h2>
                    <p>Choose what products your store sells. Specialized attributes (e.g. Fragrance Notes, Sizes, Volumes) will adapt automatically. You can select multiple!</p>
                </div>

                <div class="types-grid" id="typesGrid">
                    <?php foreach ($item_types as $it): 
                        $isSelected = in_array((int)$it['id'], $active_item_types);
                    ?>
                        <div class="type-card <?php echo $isSelected ? 'selected' : ''; ?>" 
                             data-id="<?php echo $it['id']; ?>"
                             data-name="<?php echo htmlspecialchars($it['name']); ?>"
                             data-icon="<?php echo htmlspecialchars($it['icon']); ?>"
                             onclick="toggleItemType(this)">
                            <div class="type-card-header">
                                <span class="type-icon"><?php echo $it['icon']; ?></span>
                                <div class="type-check"><i class="fa-solid fa-check"></i></div>
                            </div>
                            <div class="type-name"><?php echo htmlspecialchars($it['name']); ?></div>
                            <div class="type-name-ur"><?php echo htmlspecialchars($it['name_ur'] ?? ''); ?></div>
                            <div class="type-desc"><?php echo htmlspecialchars($it['description'] ?? ''); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- STEP 3: Package Edition -->
            <section class="wizard-step" id="step-3">
                <div class="step-title-block">
                    <h2>📦 Choose Tijarat PRO Package</h2>
                    <p>Select your deployment edition. Offline packages run 100% locally with zero internet dependency. Hybrid packages sync with the cloud for multi-store chains & online storefronts.</p>
                </div>

                <div class="tier-tabs">
                    <button type="button" class="tier-tab-btn active" id="tabBtnOffline" onclick="switchTier('offline')">
                        <i class="fa-solid fa-hard-drive"></i> Offline Local Packages (O1, O2, O3)
                    </button>
                    <button type="button" class="tier-tab-btn" id="tabBtnHybrid" onclick="switchTier('hybrid')">
                        <i class="fa-solid fa-cloud"></i> Hybrid Cloud Packages (H1, H2, H3)
                    </button>
                </div>

                <!-- Offline Packages -->
                <div class="packages-grid" id="pkgGridOffline">
                    <!-- O1 -->
                    <div class="pkg-card <?php echo $active_package === 'O1' ? 'selected' : ''; ?>" data-code="O1" onclick="selectPackage('O1')">
                        <span class="pkg-badge">Single PC</span>
                        <div class="pkg-name">Offline Solo</div>
                        <div class="pkg-code">EDITION O1</div>
                        <div class="pkg-desc">Fast, single-terminal POS for small single-counter retail shops. Zero internet needed.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Core Fast POS Billing</li>
                            <li><i class="fa-solid fa-check"></i> Barcode Scanner Support</li>
                            <li><i class="fa-solid fa-check"></i> Thermal Slip (58/80mm)</li>
                            <li><i class="fa-solid fa-check"></i> Basic Customer Khata</li>
                        </ul>
                    </div>

                    <!-- O2 -->
                    <div class="pkg-card <?php echo $active_package === 'O2' ? 'selected' : ''; ?>" data-code="O2" onclick="selectPackage('O2')">
                        <span class="pkg-badge" style="background:#fef3c7; color:#b45309;">Best Offline</span>
                        <div class="pkg-name">Offline Pro</div>
                        <div class="pkg-code">EDITION O2</div>
                        <div class="pkg-desc">Full retail workflow with supplier purchasing, automated WhatsApp receipts & Gemini AI OCR invoice scanner.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Everything in O1</li>
                            <li><i class="fa-solid fa-check"></i> Baileys WhatsApp Bot</li>
                            <li><i class="fa-solid fa-check"></i> Gemini AI Bill OCR</li>
                            <li><i class="fa-solid fa-check"></i> Supplier Purchasing & Khata</li>
                            <li><i class="fa-solid fa-check"></i> Marketing & Birthday Alerts</li>
                        </ul>
                    </div>

                    <!-- O3 -->
                    <div class="pkg-card <?php echo $active_package === 'O3' ? 'selected' : ''; ?>" data-code="O3" onclick="selectPackage('O3')">
                        <span class="pkg-badge" style="background:#e0e7ff; color:#4338ca;">LAN Setup</span>
                        <div class="pkg-name">Offline Enterprise</div>
                        <div class="pkg-code">EDITION O3</div>
                        <div class="pkg-desc">Multi-counter LAN setup with dynamic variants (size/color/volume), customer loyalty engine & A4 custom invoices.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Everything in O2</li>
                            <li><i class="fa-solid fa-check"></i> Multi-Variant Matrix</li>
                            <li><i class="fa-solid fa-check"></i> Customer Loyalty Points</li>
                            <li><i class="fa-solid fa-check"></i> LAN Multi-Terminal Sharing</li>
                            <li><i class="fa-solid fa-check"></i> Custom A4 Bill Templates</li>
                        </ul>
                    </div>
                </div>

                <!-- Hybrid Packages -->
                <div class="packages-grid" id="pkgGridHybrid" style="display: none;">
                    <!-- H1 -->
                    <div class="pkg-card <?php echo $active_package === 'H1' ? 'selected' : ''; ?>" data-code="H1" onclick="selectPackage('H1')">
                        <span class="pkg-badge" style="background:#e0f2fe; color:#0369a1;">Web Catalog</span>
                        <div class="pkg-name">Hybrid Starter</div>
                        <div class="pkg-code">EDITION H1</div>
                        <div class="pkg-desc">Offline-first desktop POS that seamlessly syncs with your online web catalog. Works without internet.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Full O2 Retail Locally</li>
                            <li><i class="fa-solid fa-check"></i> Auto Cloud Sync Engine</li>
                            <li><i class="fa-solid fa-check"></i> Online Web Storefront</li>
                            <li><i class="fa-solid fa-check"></i> Remote Sales Dashboard</li>
                        </ul>
                    </div>

                    <!-- H2 -->
                    <div class="pkg-card <?php echo $active_package === 'H2' ? 'selected' : ''; ?>" data-code="H2" onclick="selectPackage('H2')">
                        <span class="pkg-badge" style="background:#fce7f3; color:#be185d;">Perfume Chain</span>
                        <div class="pkg-name">Hybrid Multi-Store</div>
                        <div class="pkg-code">EDITION H2</div>
                        <div class="pkg-desc">Connect multiple retail branches with central warehouse, live inter-branch stock transfers & cloud dashboard.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Full O3 on Every Branch</li>
                            <li><i class="fa-solid fa-check"></i> Multi-Branch Sync Engine</li>
                            <li><i class="fa-solid fa-check"></i> Inter-Branch Stock Transfers</li>
                            <li><i class="fa-solid fa-check"></i> Consolidated HQ Reports</li>
                        </ul>
                    </div>

                    <!-- H3 -->
                    <div class="pkg-card <?php echo $active_package === 'H3' ? 'selected' : ''; ?>" data-code="H3" onclick="selectPackage('H3')">
                        <span class="pkg-badge" style="background:#ede9fe; color:#6d28d9;">Ultimate ERP</span>
                        <div class="pkg-name">Omni-Channel ERP</div>
                        <div class="pkg-code">EDITION H3</div>
                        <div class="pkg-desc">Full e-commerce cart/checkout, Evolution API WhatsApp auto-responder, customer portal & REST API.</div>
                        <ul class="pkg-features">
                            <li><i class="fa-solid fa-check"></i> Everything in H2</li>
                            <li><i class="fa-solid fa-check"></i> Full Web Cart & Checkout</li>
                            <li><i class="fa-solid fa-check"></i> Evolution API WhatsApp</li>
                            <li><i class="fa-solid fa-check"></i> Customer Online Portal</li>
                            <li><i class="fa-solid fa-check"></i> REST API for Mobile App</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- STEP 4: Review & Launch -->
            <section class="wizard-step" id="step-4">
                <div class="step-title-block">
                    <h2>🚀 Review & Complete Setup</h2>
                    <p>Review your store configuration. You can change these anytime from Settings.</p>
                </div>

                <div class="summary-box">
                    <div class="summary-row">
                        <span class="summary-label">Store Name:</span>
                        <span class="summary-val" id="sumShopName">-</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Contact Phone:</span>
                        <span class="summary-val" id="sumShopPhone">-</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Store Address:</span>
                        <span class="summary-val" id="sumShopAddress">-</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Currency:</span>
                        <span class="summary-val" id="sumCurrency">-</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Active Item Types:</span>
                        <div class="types-tags-wrap" id="sumItemTypes"></div>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Selected Package:</span>
                        <span class="summary-val" id="sumPackage" style="color: var(--accent); font-size: 16px;">-</span>
                    </div>
                </div>
            </section>

        </div>

        <!-- Footer Navigation Controls -->
        <footer class="wizard-footer">
            <button type="button" class="btn btn-secondary" id="btnPrev" onclick="prevStep()" style="visibility: hidden;">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <div style="display: flex; gap: 12px;">
                <button type="button" class="btn btn-primary" id="btnNext" onclick="nextStep()">
                    Next Step <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-success" id="btnFinish" onclick="finishSetup()" style="display: none;">
                    <i class="fa-solid fa-circle-check"></i> Launch Store
                </button>
            </div>
        </footer>

    </div>

    <script>
        let currentStep = 1;
        let selectedPackage = "<?php echo $active_package; ?>";
        let selectedItemTypes = <?php echo json_encode($active_item_types); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            // If active package starts with 'H', switch to hybrid tab
            if (selectedPackage.startsWith('H')) {
                switchTier('hybrid');
            }
        });

        function toggleItemType(card) {
            const id = parseInt(card.getAttribute('data-id'));
            const idx = selectedItemTypes.indexOf(id);

            if (idx > -1) {
                // Must keep at least one
                if (selectedItemTypes.length === 1) {
                    alert('Please keep at least one active item type for your store.');
                    return;
                }
                selectedItemTypes.splice(idx, 1);
                card.classList.remove('selected');
            } else {
                selectedItemTypes.push(id);
                card.classList.add('selected');
            }
        }

        function switchTier(tier) {
            const tabOffline = document.getElementById('tabBtnOffline');
            const tabHybrid = document.getElementById('tabBtnHybrid');
            const gridOffline = document.getElementById('pkgGridOffline');
            const gridHybrid = document.getElementById('pkgGridHybrid');

            if (tier === 'offline') {
                tabOffline.classList.add('active');
                tabHybrid.classList.remove('active');
                gridOffline.style.display = 'grid';
                gridHybrid.style.display = 'none';
            } else {
                tabHybrid.classList.add('active');
                tabOffline.classList.remove('active');
                gridHybrid.style.display = 'grid';
                gridOffline.style.display = 'none';
            }
        }

        function selectPackage(code) {
            selectedPackage = code;
            document.querySelectorAll('.pkg-card').forEach(c => {
                if (c.getAttribute('data-code') === code) {
                    c.classList.add('selected');
                } else {
                    c.classList.remove('selected');
                }
            });
        }

        function goToStep(step) {
            if (step < 1 || step > 4) return;
            
            // Validate step 1 if moving forward
            if (step > 1) {
                const name = document.getElementById('setupShopName').value.trim();
                const phone = document.getElementById('setupShopPhone').value.trim();
                if (!name || !phone) {
                    alert('Please enter your Shop Name and Contact Phone before proceeding.');
                    return;
                }
            }

            // Validate step 2
            if (step > 2 && selectedItemTypes.length === 0) {
                alert('Please select at least one item type for your store.');
                return;
            }

            currentStep = step;

            // Update Steps UI
            for (let i = 1; i <= 4; i++) {
                const pill = document.getElementById(`pill-${i}`);
                const page = document.getElementById(`step-${i}`);

                pill.classList.remove('active');
                page.classList.remove('active');

                if (i < currentStep) {
                    pill.classList.add('completed');
                } else {
                    pill.classList.remove('completed');
                }

                if (i === currentStep) {
                    pill.classList.add('active');
                    page.classList.add('active');
                }
            }

            // Update footer buttons
            const btnPrev = document.getElementById('btnPrev');
            const btnNext = document.getElementById('btnNext');
            const btnFinish = document.getElementById('btnFinish');

            btnPrev.style.visibility = (currentStep === 1) ? 'hidden' : 'visible';

            if (currentStep === 4) {
                btnNext.style.display = 'none';
                btnFinish.style.display = 'inline-flex';
                populateSummary();
            } else {
                btnNext.style.display = 'inline-flex';
                btnFinish.style.display = 'none';
            }
        }

        function nextStep() {
            goToStep(currentStep + 1);
        }

        function prevStep() {
            goToStep(currentStep - 1);
        }

        function populateSummary() {
            document.getElementById('sumShopName').innerText = document.getElementById('setupShopName').value.trim();
            document.getElementById('sumShopPhone').innerText = document.getElementById('setupShopPhone').value.trim();
            document.getElementById('sumShopAddress').innerText = document.getElementById('setupShopAddress').value.trim() || 'Not specified';
            document.getElementById('sumCurrency').innerText = document.getElementById('setupCurrency').value;

            // Selected item type pills
            const tagsContainer = document.getElementById('sumItemTypes');
            tagsContainer.innerHTML = '';
            document.querySelectorAll('.type-card.selected').forEach(c => {
                const icon = c.getAttribute('data-icon');
                const name = c.getAttribute('data-name');
                const tag = document.createElement('span');
                tag.className = 'type-tag-pill';
                tag.innerHTML = `${icon} ${name}`;
                tagsContainer.appendChild(tag);
            });

            // Package
            const pkgNames = {
                'O1': 'Offline Solo (O1)',
                'O2': 'Offline Pro (O2)',
                'O3': 'Offline Enterprise (O3)',
                'H1': 'Hybrid Starter (H1)',
                'H2': 'Hybrid Multi-Store (H2)',
                'H3': 'Omni-Channel ERP (H3)'
            };
            document.getElementById('sumPackage').innerText = pkgNames[selectedPackage] || selectedPackage;
        }

        function finishSetup() {
            const shop_name = document.getElementById('setupShopName').value.trim();
            const shop_phone = document.getElementById('setupShopPhone').value.trim();
            const shop_address = document.getElementById('setupShopAddress').value.trim();
            const shop_currency = document.getElementById('setupCurrency').value;

            const btnFinish = document.getElementById('btnFinish');
            btnFinish.disabled = true;
            btnFinish.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Setup...';

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'complete-store-setup',
                    data: {
                        shop_name,
                        shop_phone,
                        shop_address,
                        shop_currency,
                        active_package: selectedPackage,
                        active_item_types: selectedItemTypes
                    }
                })
            })
            .then(res => res.json())
            .then(data => {
                const resp = data.find(r => r.channel === 'store-setup-completed');
                if (resp && resp.data.success) {
                    window.location.href = 'index.php?setup=success';
                } else {
                    alert(resp ? resp.data.msg : 'Error completing setup.');
                    btnFinish.disabled = false;
                    btnFinish.innerHTML = '<i class="fa-solid fa-circle-check"></i> Launch Store';
                }
            })
            .catch(err => {
                alert('Connection error while saving setup.');
                btnFinish.disabled = false;
                btnFinish.innerHTML = '<i class="fa-solid fa-circle-check"></i> Launch Store';
            });
        }
    </script>
</body>
</html>
