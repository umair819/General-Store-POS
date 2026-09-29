# 🏪 Tijarat PRO — Universal Retail Chain ERP
## Master Planning Document
### "Ek Software, Har Store, Har Item Type"

**Version:** 2.0.0  
**Created:** September 29, 2026  
**Status:** 🟡 Phase 1 Complete — Planning & Architecture  
**Author:** TechBrain Development Team

---

## 📌 Vision Statement

Tijarat PRO ek **universal retail ERP** hai jo **kisi bhi type ki retail store** ke liye kaam karega:
- 🧴 Perfume & Fragrance Shops
- 🛒 General / Kiryana Stores
- 👔 Garment & Clothing Stores
- 📱 Mobile & Electronics Shops
- 💊 Medical Stores / Pharmacies
- 🔧 Hardware & Auto Parts Shops
- 👟 Shoe & Accessories Stores
- 🎁 Gift & Stationery Shops
- ...aur koi bhi retail business

Software ka core **item type system** hai — jab owner apni store type select karega, usay us type ke relevant fields, variants, aur features milenge.

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    TIJARAT PRO ERP                           │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │   ELECTRON    │  │  PHP BUILT-IN│  │  CLOUD SERVER    │   │
│  │   DESKTOP APP │  │  WEB SERVER  │  │  (Hybrid Mode)   │   │
│  │   (main.js)   │  │  (localhost)  │  │  (MySQL + API)   │   │
│  └──────┬───────┘  └──────┬───────┘  └────────┬─────────┘   │
│         │                  │                    │              │
│         ▼                  ▼                    ▼              │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │              DUAL DATABASE ENGINE                        │  │
│  │                                                          │  │
│  │   LOCAL (SQLite)  ◄──── SYNC ────►  CLOUD (MySQL)       │  │
│  │   Always works       Engine         When internet        │  │
│  │   offline-first                     available             │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │   WHATSAPP    │  │  E-COMMERCE  │  │  REPORTS &       │   │
│  │   ENGINE      │  │  STOREFRONT  │  │  ANALYTICS       │   │
│  │ (Evolution API│  │  (Hybrid     │  │  (Branch-wise    │   │
│  │  + Baileys)   │  │   packages)  │  │   + Chain-wide)  │   │
│  └──────────────┘  └──────────────┘  └──────────────────┘   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Package System (6 Packages)

### 🔵 OFFLINE PACKAGES (3) — Electron Desktop App, No Internet Required

| # | Package Name | Target | Key Features |
|---|-------------|--------|--------------|
| **O1** | **Tijarat Starter** | Single small shop, 1 PC | POS + Products + Customers + Basic Reports + Thermal Printing |
| **O2** | **Tijarat Business** | Medium store, multi-cashier | O1 + Multi-user (2-3 cashiers) + Khata/Udhaar + Purchase Management + WhatsApp (Baileys local) + Advanced Reports + Inventory Alerts |
| **O3** | **Tijarat Enterprise** | Large store, full ERP | O2 + Expense Tracking + A4 Invoice Templates + Loyalty Points + Item Variants + Multiple Payment Methods + Full Analytics + LAN Multi-terminal |

### 🟢 HYBRID PACKAGES (3) — Electron Desktop + Cloud Sync

| # | Package Name | Target | Key Features |
|---|-------------|--------|--------------|
| **H1** | **Tijarat Cloud Lite** | Single store + online presence | O2 features + Cloud Sync + Basic E-commerce Storefront (product catalog + WhatsApp ordering) |
| **H2** | **Tijarat Chain** | Multi-branch retail chain | O3 features + Multi-Branch Management + Inter-branch Stock Transfers + Consolidated Reports + Cloud Dashboard + Full E-commerce Store |
| **H3** | **Tijarat Ultimate** | Enterprise chain + full digital | H2 + Full E-commerce (cart, checkout, payments) + Evolution API WhatsApp Bot + Coupons & Promotions + Customer App/Portal + Advanced CRM + API Access |

### Feature Matrix

| Feature | O1 | O2 | O3 | H1 | H2 | H3 |
|---------|:--:|:--:|:--:|:--:|:--:|:--:|
| POS Billing | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Product Management | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Barcode Scanner | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Thermal Receipt | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Customer List | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Khata / Udhaar | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Multi-User / Cashiers | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Purchase Management | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Supplier Ledger | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| WhatsApp (Baileys) | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| AI OCR Bill Scanner | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Inventory Alerts | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Advanced Reports | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Expense Tracking | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Item Variants | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| A4 Invoice Templates | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Loyalty Points | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| LAN Multi-Terminal | ❌ | ❌ | ✅ | ❌ | ✅ | ✅ |
| **Cloud Sync** | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| **E-commerce Catalog** | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Multi-Branch** | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| **Stock Transfers** | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| **Cloud Dashboard** | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| **Full E-commerce** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Evolution API Bot** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Coupons & Promos** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Advanced CRM** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Customer Portal** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **API Access** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 🔄 Hybrid Sync Engine Architecture

Bite 2.0 ka `cloud_sync_api.php` pattern follow karein ge (proven in production):

```
┌──────────────────┐                    ┌──────────────────┐
│   LOCAL PC        │                    │   CLOUD SERVER    │
│   (Electron +     │                    │   (VPS + MySQL    │
│    SQLite)         │                    │    + PHP API)     │
│                    │                    │                    │
│  ┌──────────────┐ │    INTERNET UP     │ ┌──────────────┐  │
│  │ Local SQLite  │ │ ═══════════════►  │ │ Cloud MySQL   │  │
│  │ Database      │ │   PUSH changes    │ │ Database      │  │
│  │              │ │ ◄═══════════════  │ │              │  │
│  │  sync_status  │ │   PULL changes    │ │  sync_cursor  │  │
│  │  = pending    │ │                    │ │  = latest     │  │
│  └──────────────┘ │                    │ └──────────────┘  │
│                    │                    │                    │
│  INTERNET DOWN:    │                    │                    │
│  ► Work continues  │                    │                    │
│  ► Data saved      │                    │                    │
│    locally         │                    │                    │
│  ► Auto-sync when  │                    │                    │
│    internet returns│                    │                    │
└──────────────────┘                    └──────────────────┘
```

### Sync Rules:
1. **Local-first** — Har transaction pehle local SQLite mein save hoti hai
2. **Background sync** — Har 30-60 seconds mein background thread check karta hai
3. **Conflict resolution** — Last-write-wins with server timestamp priority
4. **Selective sync** — Sirf changed records sync hote hain (delta sync via `sync_cursor`)
5. **Offline resilience** — Internet nahi toh kaam nahi ruke ga, records `sync_status = 'pending'` mein queue ho jaate hain
6. **Sync tables** — `sales`, `products`, `customers`, `purchases`, `inventory_changes`, `expenses`

### Sync Status Fields (added to syncable tables):
```sql
sync_status ENUM('pending', 'synced', 'conflict') DEFAULT 'pending'
sync_cursor BIGINT DEFAULT 0
cloud_id VARCHAR(50) DEFAULT NULL  -- Server-side ID after sync
last_synced_at DATETIME DEFAULT NULL
```

---

## 🏷️ Dynamic Item Type System

### Concept

Har store type ke items ke apne unique attributes hote hain. Hum ek **dynamic attribute system** banayenge:

```
┌─────────────┐     ┌──────────────────┐     ┌────────────────────┐
│  ITEM TYPES  │────►│  TYPE ATTRIBUTES  │────►│  PRODUCT ATTRIBUTE  │
│  (perfume,   │     │  (what fields     │     │  VALUES             │
│   garment,   │     │   this type has)  │     │  (actual values     │
│   general)   │     │                    │     │   per product)      │
└─────────────┘     └──────────────────┘     └────────────────────┘
```

### Pre-defined Item Types

| Item Type | Relevant Attributes | Variant Dimensions |
|-----------|--------------------|--------------------|
| **Perfume** | Brand, Fragrance Family, Concentration (Parfum/EDP/EDT/EDC), Gender, Notes (Top/Mid/Base) | Volume (10ml, 30ml, 50ml, 100ml, 200ml) |
| **General / Kiryana** | Weight, Pack Size, Expiry Date, Manufacturer | Pack Size (Single, 6-Pack, 12-Pack, Carton) |
| **Garment / Clothing** | Fabric, Pattern, Sleeve Type, Season, Wash Care | Size (XS, S, M, L, XL, XXL) + Color |
| **Electronics / Mobile** | Brand, Model, Warranty, Specs (RAM/Storage) | Color, Storage Capacity |
| **Shoes / Footwear** | Brand, Material, Style, Season | Size (6-13) + Color |
| **Cosmetics / Beauty** | Brand, Skin Type, SPF, Ingredients | Shade, Volume |
| **Grocery / FMCG** | MFG Date, Expiry Date, Batch No, Manufacturer | Pack Size, Weight |
| **Hardware / Auto** | Material, Specification, Grade, Compatibility | Size, Length, Gauge |
| **Jewelry / Accessories** | Material (Gold/Silver), Karat, Stone Type | Size (Ring), Length (Chain) |
| **Stationery** | Brand, Paper Size, GSM, Ink Type | Pack Count, Color |
| **Custom** | User-defined attributes | User-defined variants |

### Database Design for Dynamic Attributes

```sql
-- Item Types (system-defined + user can add custom)
CREATE TABLE item_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,          -- 'Perfume', 'Garment', 'General', etc.
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    is_system TINYINT(1) DEFAULT 0,      -- System types can't be deleted
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Type Attributes (what fields each item type has)
CREATE TABLE type_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_type_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,   -- 'Fragrance Family', 'Size', 'Color'
    attribute_key VARCHAR(50) NOT NULL,     -- 'fragrance_family', 'size', 'color'
    field_type ENUM('text', 'number', 'select', 'multi_select', 'color', 'date', 'boolean') NOT NULL DEFAULT 'text',
    options JSON,                            -- For select/multi_select: ["Woody","Floral","Oriental"]
    is_variant TINYINT(1) DEFAULT 0,        -- If 1, this attribute creates product variants
    is_required TINYINT(1) DEFAULT 0,
    is_filterable TINYINT(1) DEFAULT 0,     -- Show as filter on e-commerce
    is_visible_pos TINYINT(1) DEFAULT 1,    -- Show on POS product card
    is_visible_store TINYINT(1) DEFAULT 1,  -- Show on e-commerce product page
    sort_order INT DEFAULT 0,
    FOREIGN KEY (item_type_id) REFERENCES item_types(id) ON DELETE CASCADE,
    UNIQUE KEY uk_type_attr (item_type_id, attribute_key)
);

-- Product Attribute Values (actual values per product)
CREATE TABLE product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value_text TEXT,                         -- Stores the actual value
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES type_attributes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_prod_attr (product_id, attribute_id)
);
```

### How It Works in Practice

**Example: Perfume Store Owner**
1. Owner selects "Perfume" item type during initial setup
2. System auto-loads perfume-specific attributes: Brand, Fragrance Family, Concentration, Gender, Notes, Volume
3. When adding a product: form shows perfume-relevant fields
4. Volume is marked as `is_variant = 1`, so system auto-creates variants (30ml, 50ml, 100ml)

**Example: Garment Store Owner**
1. Owner selects "Garment" item type
2. System loads: Fabric, Pattern, Season, Sleeve Type, Size, Color
3. Size + Color are `is_variant = 1`, so system creates variant matrix (S-Red, S-Blue, M-Red, M-Blue, etc.)

**Example: General Store Owner**
1. Owner selects "General / Kiryana"
2. System loads: Weight, Pack Size, Expiry Date, Manufacturer
3. Pack Size is `is_variant = 1` (Single, 6-Pack, Carton)

**Multi-Type Store:**
Owner can enable MULTIPLE item types — e.g. a store selling both perfumes and garments will have both attribute sets available.

---

## 📱 WhatsApp Engine — Evolution API + Baileys

### Architecture Decision

| Feature | Baileys (Direct) | Evolution API (Self-hosted) |
|---------|-------------------|---------------------------|
| Setup | npm install + Node.js | Docker container |
| Multi-number | ❌ One number per instance | ✅ Multi-instance manager |
| API Interface | Custom code | REST API (HTTP calls from PHP) |
| Webhook Support | Custom | Built-in webhooks |
| QR Pairing | Custom UI | Built-in web panel |
| Stability | Good (proven in Bite) | Better (managed reconnection) |
| Chatbot Support | Manual | Typebot / Dify / OpenAI built-in |
| Best For | Offline packages (O2, O3) | Hybrid packages (H1, H2, H3) |

### Implementation Plan:
- **Offline packages (O1-O3):** Use Baileys directly (Bite 2.0 ka `whatsapp_bot.js` port)
- **Hybrid packages (H1-H3):** Use Evolution API via Docker on cloud server
- **Fallback:** If Evolution API unavailable, fall back to Baileys
- **PHP Integration:** `whatsapp_engine.php` — abstraction layer that works with both backends

```php
// Abstraction example:
class WhatsAppEngine {
    private $mode; // 'baileys' or 'evolution'
    
    public function sendMessage($phone, $text) {
        if ($this->mode === 'evolution') {
            return $this->sendViaEvolutionAPI($phone, $text);
        }
        return $this->sendViaBaileys($phone, $text);
    }
    
    public function sendImage($phone, $imageUrl, $caption) { ... }
    public function sendDocument($phone, $filePath) { ... }
    public function getQRCode() { ... }
    public function getStatus() { ... }
}
```

---

## 🔐 License & Package Enforcement

### How Package Features Are Controlled

```php
// In db_config.php or license_manager.php:
function getActivePackage() {
    // Returns: 'O1', 'O2', 'O3', 'H1', 'H2', 'H3'
    return $license_data['package'] ?? 'O1';
}

function isFeatureActive($feature) {
    $pkg = getActivePackage();
    $features = [
        'O1' => ['pos', 'products', 'customers_basic', 'thermal_print', 'barcode'],
        'O2' => ['O1_all', 'khata', 'multi_user', 'purchases', 'suppliers', 'whatsapp_baileys', 'ai_ocr', 'inventory_alerts', 'marketing'],
        'O3' => ['O2_all', 'expenses', 'a4_templates', 'loyalty', 'variants', 'advanced_reports', 'lan_multi_terminal'],
        'H1' => ['O2_all', 'cloud_sync', 'ecommerce_catalog'],
        'H2' => ['O3_all', 'cloud_sync', 'multi_branch', 'stock_transfers', 'cloud_dashboard', 'ecommerce_catalog'],
        'H3' => ['H2_all', 'ecommerce_full', 'evolution_api', 'coupons', 'crm_advanced', 'customer_portal', 'api_access'],
    ];
    // ... check if $feature is in active package's list
}
```

### In UI — Feature Gating:
```php
<?php if (isFeatureActive('multi_branch')): ?>
    <li><a href="branches.php">Branch Management</a></li>
<?php else: ?>
    <li class="locked-feature" title="Upgrade to Chain package">
        <a href="#"><i class="fa-lock"></i> Branch Management <span class="badge">PRO</span></a>
    </li>
<?php endif; ?>
```

---

## 📂 Proposed Folder Structure (Updated)

```
Tijarat-PRO/
├── main.js                    # Electron bootstrapper
├── preload.js                 # Electron preload script
├── package.json               # Electron + Node dependencies
│
├── db_config.php              # Dual-mode database config (SQLite + MySQL)
├── db_config.json             # Runtime config values
├── schema.sql                 # SQLite schema
├── schema_mysql.sql           # MySQL schema (cloud)
├── migrate_to_mysql.php       # SQLite → MySQL migration
├── license_manager.php        # License & package verification
│
├── login.php                  # Authentication
├── index.php                  # Dashboard
├── sidebar.php                # Shared navigation
├── billing.php                # POS billing screen
├── products.php               # Product catalog CRUD
├── categories.php             # Categories CRUD
├── inventory.php              # Stock adjustments
├── customers.php              # Customer Khata & CRM
├── purchases.php              # Purchase management + AI OCR
├── reports.php                # Business reports & analytics
├── settings.php               # Shop settings & config
├── marketing.php              # WhatsApp campaigns
├── whatsapp.php               # WhatsApp bot management
│
├── brands.php                 # 🆕 Brand management (O3+)
├── branches.php               # 🆕 Branch management (H2+)
├── transfers.php              # 🆕 Stock transfers (H2+)
├── expenses.php               # 🆕 Expense tracking (O3+)
├── loyalty.php                # 🆕 Loyalty program (O3+)
├── item_types.php             # 🆕 Item type & attribute config
│
├── api.php                    # Central POS AJAX API
├── cloud_sync_api.php         # 🆕 Cloud sync engine (H1+)
├── whatsapp_engine.php        # 🆕 WhatsApp abstraction (Baileys + Evolution)
├── whatsapp_bot.js            # Baileys WhatsApp service
│
├── store/                     # 🆕 E-commerce storefront (H1+)
│   ├── index.php              # Store homepage
│   ├── catalog.php            # Product catalog + filters
│   ├── product.php            # Product detail page
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout flow
│   ├── account.php            # Customer account
│   ├── api.php                # Store API
│   └── assets/                # Store CSS/JS/images
│
├── css/
│   └── style.css              # Core stylesheet
├── js/
│   ├── chart.js               # Chart.js library
│   ├── ipc-shim.js            # Electron IPC
│   └── tailwind.js            # Tailwind standalone
│
└── docs/
    ├── MASTER_PLAN.md          # THIS FILE — complete planning
    └── CHANGELOG.md            # Version history
```

---

## 🚀 Development Phases (Revised)

### Phase 1: Foundation ✅ COMPLETE
- [x] MySQL schema (35 tables)
- [x] Dual-mode db_config.php (SQLite + MySQL)
- [x] Cross-DB query helpers
- [x] Migration script (SQLite → MySQL)
- [x] Updated api.php for cross-DB compatibility

### Phase 2: Dynamic Item Type System ✅ COMPLETE
- [x] `item_types` table + seed data (11 types: General, Perfume, Garment, Electronics, Footwear, Cosmetics, Grocery, Hardware, Jewelry, Stationery, Pharmacy)
- [x] `type_attributes` table + seed all type-specific attributes (65+ attributes across 11 types)
- [x] `product_attributes` table for storing values (EAV pattern)
- [x] Generic `products` table (removed perfume-specific columns, added `item_type_id`)
- [x] Generic `product_variants` table (label-based instead of volume_ml only)
- [x] `store_config` table for package & item type selection tracking
- [x] `sync_logs` table for hybrid cloud sync
- [x] Item type selection on first-run wizard / settings (`setup_wizard.php`, `settings.php`)
- [x] Dynamic product form that changes based on item type (`products.php`)
- [x] Variant matrix management & barcode auto-generator (`products.php`)
- [x] Update products.php for dynamic attribute UI (`products.php`)
- [x] Update api.php for attribute CRUD (`api.php`)

### Phase 3: Package & License System ✅ COMPLETE
- [x] Package definition in code (O1-O3, H1-H3 feature maps in `db_config.php`)
- [x] `isFeatureActive()` & `hasFeature()` functions
- [x] UI feature gating & package badge in sidebar
- [x] License key system (PC ID + activation + package offline master keys in `license_manager.php`)
- [x] Trial mode (15-day automatic hardware-locked trial)
- [x] Sidebar dynamic package edition badge & settings tab

### Phase 4: Cloud Sync Engine (Hybrid packages) ✅ COMPLETE
- [x] `cloud_sync_api.php` (based on Bite 2.0 proven sync architecture)
- [x] `sync_status` + `sync_cursor` columns on syncable tables (`sales`, `products`, `customers`, `purchases`)
- [x] Push engine (Local SQLite → Cloud MySQL for sales, products, customers)
- [x] Pull engine (Cloud MySQL → Local SQLite for catalog updates and price adjustments)
- [x] Connection test + latency diagnostics endpoint
- [x] Sync status dashboard in settings & API

### Phase 5: WhatsApp Engine Upgrade ✅ COMPLETE
- [x] `WhatsAppEngine` abstraction class (`classes/WhatsAppEngine.php`)
- [x] Baileys integration (`whatsapp_service.js` on port 9001)
- [x] Evolution API REST integration (Docker/Cloud self-hosted in `WhatsAppEngine`)
- [x] Auto-detect mode based on package (O2-H2: Baileys, H3: Evolution API, O1: Link fallback)
- [x] Order/checkout digital receipt templates and auto-dispatch
- [x] WhatsApp status page in dashboard (`whatsapp.php`)

### Phase 6: Multi-Branch System (H2+ packages) 🔴
- [ ] Branch CRUD (name, location, manager, code)
- [ ] User-branch assignment
- [ ] Branch-wise inventory (branch_stock table)
- [ ] Inter-branch stock transfers
- [ ] Branch-level permissions
- [ ] Consolidated reports (all branches)
- [ ] Branch comparison dashboard

### Phase 7: Enhanced POS & Reports 🔴
- [ ] POS UI upgrade with dynamic item type cards
- [ ] Brand logos on product cards
- [ ] Variant selection in billing (size, color picker)
- [ ] Loyalty points earn/redeem at POS
- [ ] A4 invoice templates (10+ designs)
- [ ] Advanced reports (brand-wise, branch-wise, P&L)
- [ ] Expense tracking module
- [ ] Dashboard analytics widgets

### Phase 8: E-commerce Storefront (H1+ packages) ✅ COMPLETE
- [x] Public storefront luxury design system (`store/assets/css/store.css` & `store/assets/js/store.js`)
- [x] Storefront homepage with luxury hero, dynamic category pills & featured products (`store/index.php`)
- [x] Product catalog with dynamic filters based on item type attributes (`store/catalog.php`)
- [x] Product detail page with dynamic variant matrix picker & live price updates (`store/product.php`)
- [x] Shopping cart with live quantity & subtotal calculation (`store/cart.php`)
- [x] Frictionless checkout flow with COD, Bank Transfer & JazzCash (`store/checkout.php`)
- [x] Order success & WhatsApp digital confirmation receipt (`store/order_success.php`)
- [x] Customer self-service order tracking with visual timeline (`store/track.php`)
- [x] Storefront AJAX API (`store/api.php`)
- [x] Admin Online Orders Management with WhatsApp dispatch updates & 1-click POS sale conversion (`online_orders.php`)
- [x] Sidebar navigation link with live pending online orders counter (`sidebar.php`)

### Phase 9: Polish & Launch 🔴
- [ ] Code protection (IonCube / obfuscation)
- [ ] Build scripts (Electron packager)
- [ ] Client bundle creator (like Bite 2.0's `create_client_bundle.py`)
- [ ] Update/patch system
- [ ] Documentation
- [ ] Demo data seeder
- [ ] Deployment scripts

---

## 💰 Pricing Plan (Proposed)

| Package | One-Time Price (PKR) | Target Market |
|---------|---------------------|---------------|
| **O1 — Starter** | 5,000 - 8,000 | Choti dukaan, single counter |
| **O2 — Business** | 10,000 - 15,000 | Medium store, 2-3 staff |
| **O3 — Enterprise** | 20,000 - 30,000 | Bari dukaan, full ERP |
| **H1 — Cloud Lite** | 15,000 - 20,000 | Single store + online |
| **H2 — Chain** | 35,000 - 50,000 | Multi-branch chain |
| **H3 — Ultimate** | 60,000 - 100,000 | Full enterprise + e-commerce |
| **Annual Support** | 3,000 - 10,000/year | Updates + WhatsApp support |

---

## 🛠️ Tech Stack Summary

| Component | Technology |
|-----------|-----------|
| Desktop App | Electron (Node.js) |
| Backend | PHP 8+ (built-in web server via Electron) |
| Local Database | SQLite 3 (offline-first) |
| Cloud Database | MySQL 8+ (hybrid packages) |
| Frontend | HTML + CSS + JavaScript |
| CSS Framework | Vanilla CSS + Tailwind (standalone) |
| Charts | Chart.js |
| Barcode | USB HID barcode scanner |
| Thermal Print | 80mm via HTML/CSS print |
| WhatsApp (Offline) | Baileys (@whiskeysockets/baileys) |
| WhatsApp (Hybrid) | Evolution API (Docker, self-hosted) |
| Cloud Sync | Custom PHP sync engine (Bite 2.0 pattern) |
| E-commerce | PHP + HTML/CSS/JS storefront |
| AI OCR | Google Gemini API |
| Code Protection | IonCube (PHP) + JS Obfuscator |
| Build | electron-packager |

---

## 📝 Important Notes & Decisions Log

### Decision 1: Item Type System (Sep 29, 2026)
- **Decision:** Build a dynamic attribute system instead of hardcoding perfume-specific fields
- **Reason:** Software should work for ANY retail type, not just perfumes
- **Impact:** Products table stays generic; type-specific attributes stored in separate EAV tables

### Decision 2: Package System (Sep 29, 2026)
- **Decision:** 3 offline + 3 hybrid packages
- **Reason:** Different market segments need different feature sets & price points
- **Impact:** Feature gating via `isFeatureActive()`, UI shows locked features with upgrade prompts

### Decision 3: WhatsApp Dual Engine (Sep 29, 2026)
- **Decision:** Baileys for offline packages, Evolution API for hybrid packages
- **Reason:** Evolution API needs Docker/server; offline stores may not have internet
- **Impact:** Abstract WhatsApp engine class that works with both backends

### Decision 4: Sync Engine (Sep 29, 2026)
- **Decision:** Bite 2.0 ka proven cloud_sync_api.php pattern follow karein
- **Reason:** Already tested in production, reliable offline-first approach
- **Impact:** SQLite local + MySQL cloud, delta sync, offline resilience

### Decision 5: Schema Rollback (Sep 29, 2026)
- **Decision:** schema_mysql.sql mein hardcoded perfume tables ko dynamic system se replace karna hoga
- **Reason:** Phase 1 mein perfume-specific tables banaye the (brands, fragrance_families) — ab dynamic attribute system se replace honge
- **Impact:** `brands` table rakhenge (brands har type mein relevant hai), `fragrance_families` ko `type_attributes` options mein convert karenge

### Decision 6: Database Strategy — SQLite Local + MySQL Cloud (Sep 29, 2026)
- **Decision:** SQLite ALWAYS on local PC (Electron), MySQL ONLY on cloud server
- **Reason:** Client ko MySQL install karwana = headache. Bite 2.0 bhi yahi pattern use karta hai. Electron + SQLite = zero-dependency install.
- **Architecture:**
  - **Offline Packages (O1-O3):** SQLite only. No server, no internet needed.
  - **Hybrid Packages (H1-H3):** SQLite primary (local) + MySQL on cloud server. Sync engine bridges both.
  - `db_config.php` → SQLite connection ONLY (no MySQL option for local app)
  - `cloud_sync_api.php` → Handles push/pull between local SQLite ↔ remote MySQL
  - `schema.sql` → SQLite schema (local)
  - `schema_mysql.sql` → MySQL schema (cloud server deployment only)
- **Impact:** Remove MySQL dual-mode from local `db_config.php`. Local app is pure SQLite. Cloud server is separate deployment.

---

## 🧠 Ideas & Future Features

- [ ] **Customer Display** (like Bite 2.0's `customer_display.php` — secondary screen for customer)
- [ ] **Staff Portal** (attendance, targets)
- [ ] **Barcode Label Printing** (generate barcode stickers)
- [ ] **Multi-language** (Urdu, English, Arabic)
- [ ] **Fiscal Printer Integration** (FBR POS integration for Pakistan)
- [ ] **Weight Scale Integration** (for kg-based items)
- [ ] **RFID Support** (for garment stores)
- [ ] **Mobile App** (customer-facing React Native app)
- [ ] **Dashboard TV Mode** (like Bite 2.0's order queue display)

---

> [!IMPORTANT]
> **Yeh document living document hai — har decision, change, aur progress yahan update hoga.**
> **Kuch bhi plan banayein, pehle yahan likho, phir code karo.**
