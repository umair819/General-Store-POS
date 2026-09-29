# 🧴 Tijarat PRO — Perfume Retail Chain ERP System
**Project Status:** 🟡 Phase 1 In Progress (Foundation & Database Migration)  
**Priority:** ⭐⭐⭐⭐⭐ Very High  
**Target Market:** Perfume retail chains, fragrance stores, attar shops across Pakistan  
**Tech Stack:** PHP + MySQL (online) / SQLite (offline POS) + Electron + HTML/CSS/JS  

---

## 📋 Project Overview

A unified business software for perfume retail chains that combines:
- **POS System** — In-store billing with barcode scanning
- **E-commerce Store** — Online catalog, cart, checkout, order management
- **WhatsApp Integration** — Order notifications, promotions, customer engagement
- **Multi-Branch Chain Management** — Inventory transfers, consolidated reports
- **Full ERP** — Suppliers, purchases, expenses, accounts, CRM, loyalty

All managed from a single software platform.

---

## ✅ Completed Features (Inherited from POS v1)

### 🔐 Login & User Management
- [x] Admin / Owner login
- [x] Cashier / Staff login (limited access)
- [x] PIN-based quick login at POS screen
- [x] Activity log (kon ne kya kiya)

### 🛒 POS / Billing Screen
- [x] Dual-language support (Urdu / English quick toggle)
- [x] Barcode scanner support (USB barcode reader)
- [x] Quick item search by name or barcode
- [x] Touch/click item to add to cart
- [x] Quantity adjustment in cart
- [x] Multiple items per bill
- [x] Discount per item or on total bill
- [x] Hold bill / resume later
- [x] Fast checkout with keyboard shortcuts
- [x] Cash tendered & change calculation

### 💳 Payment Methods
- [x] Cash payment
- [x] Easypaisa / JazzCash (manual confirm)
- [x] Bank transfer / cheque
- [x] Credit (Udhaar / Khata) — customer balance tracking
- [x] Split payment (partial cash + partial Udhaar)

### 📦 Product Management
- [x] Add products (Name, Category, Barcode, Purchase Price, Sale Price, Unit)
- [x] Categories management
- [x] Multiple unit types
- [x] Product photo (optional)
- [x] Bulk product import via Excel/CSV
- [x] Vyapar Backup Import Tool

### 📊 Inventory / Stock Management
- [x] Current stock levels per item
- [x] Low stock alert (set minimum threshold)
- [x] Near-expiry stock alerts
- [x] Stock adjustment (damage, theft, correction)
- [x] Expiry date tracking

### 🧾 Purchase Management
- [x] Supplier/vendor list
- [x] Purchase order creation
- [x] Goods received entry
- [x] Supplier payment tracking
- [x] Supplier ledger (outstanding balance)
- [x] 🤖 Gemini AI OCR Bill Scanner

### 👥 Customer & Khata Management
- [x] Customer list (Name, Phone, Address)
- [x] Udhaar (credit) tracking per customer
- [x] Payment collection from customer
- [x] Customer statement print
- [x] Send balance reminder via WhatsApp

### 🧾 Receipt & Invoice Printing
- [x] 80mm thermal receipt printing
- [x] Shop name, logo, address on receipt
- [x] Duplicate receipt reprint

### 📈 Reports
- [x] Daily sales report
- [x] Low stock report
- [x] Udhaar/pending payments report
- [x] Supplier outstanding report

### 📱 WhatsApp Integration
- [x] Automated Udhaar/balance reminder scheduler
- [x] Near-expiry & low stock alert notifications
- [x] Receipt share via WhatsApp
- [x] Marketing campaigns

---

## 🆕 New Features for Perfume Retail Chain ERP

### Phase 1: Foundation & Database Migration ⏳
- [x] MySQL schema with all new tables (28 tables)
- [x] Dual-mode db_config.php (SQLite + MySQL)
- [x] Cross-DB query helpers (dbConcat, dbDateFormat, dbNow)
- [x] SQLite → MySQL migration script
- [x] Updated api.php for cross-DB compatibility
- [ ] Test MySQL connection and migration
- [ ] Settings migration to key-value store

### Phase 2: Perfume-Specific Product System 🔴
- [ ] Brand management CRUD (with logo, country)
- [ ] Fragrance family management (12 families pre-loaded)
- [ ] Product variants (sizes: 10ml, 30ml, 50ml, 100ml, 200ml)
- [ ] Enhanced product form (concentration, gender, description, notes)
- [ ] SKU auto-generation
- [ ] Tester management (opened bottles tracking)
- [ ] Gift set / combo creation
- [ ] Product image gallery

### Phase 3: Multi-Branch Chain System 🔴
- [ ] Branch management CRUD (name, location, manager)
- [ ] Branch-wise inventory tracking
- [ ] User-branch assignment
- [ ] Inter-branch stock transfers (request → in_transit → received)
- [ ] Branch-level access control (manager, cashier per branch)
- [ ] Transfer history & reporting

### Phase 4: Enhanced POS for Perfume Store 🔴
- [ ] Updated POS UI with perfume product cards (brand logo, size, fragrance family)
- [ ] Branch-aware billing (stock from branch_stock)
- [ ] Loyalty points earn at POS
- [ ] Loyalty points redeem at checkout
- [ ] Tester checkout tracking
- [ ] Gift set billing as single item
- [ ] Sale channel tracking (pos/online/whatsapp)

### Phase 5: E-commerce / Online Store 🔴
- [ ] Public storefront design (modern, perfume-focused UI)
- [ ] Product catalog with filters (brand, family, concentration, price, gender)
- [ ] Product detail page (images, reviews, notes breakdown, similar products)
- [ ] Shopping cart (persistent for logged-in, session for guests)
- [ ] Customer registration / login (phone + OTP via WhatsApp)
- [ ] Checkout flow (COD, Easypaisa, JazzCash, bank transfer)
- [ ] Order placement & confirmation (WhatsApp notification)
- [ ] Order management admin panel (status, fulfillment, shipping)
- [ ] Customer account (order history, wishlist, loyalty points)
- [ ] Coupons & discount codes
- [ ] SEO-optimized product pages

### Phase 6: Enhanced WhatsApp Module 🔴
- [ ] Order confirmation via WhatsApp
- [ ] Order status updates (shipped, delivered, etc.)
- [ ] New arrival notifications
- [ ] Promotional campaign builder with scheduling
- [ ] Birthday/anniversary auto-offers with loyalty points
- [ ] WhatsApp catalog integration
- [ ] Daily sales summary to owner

### Phase 7: Advanced Reports & Analytics 🔴
- [ ] Brand-wise sales reports
- [ ] Branch-wise comparison dashboard
- [ ] Online vs offline analytics
- [ ] Customer retention metrics
- [ ] Profit & loss (branch + consolidated)
- [ ] Inventory valuation report
- [ ] Expense tracking & reporting
- [ ] Best-selling perfumes chart
- [ ] Category-wise sales breakdown

### Phase 8: Polish, Security & Launch 🔴
- [ ] License system implementation (PC ID, trial, activation)
- [ ] User roles & permissions matrix
- [ ] Performance optimization
- [ ] MySQL backup & restore
- [ ] Documentation
- [ ] Deployment setup (VPS for e-commerce, Electron for POS)
- [ ] Code protection (IonCube PHP / JS obfuscator)

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+ |
| Database | MySQL 8+ (online/chain) / SQLite 3 (offline POS) |
| Frontend (Admin/POS) | HTML + CSS + JS (Outfit / Poppins typography) |
| Frontend (E-commerce) | PHP + HTML/CSS/JS (public storefront) |
| Barcode | USB barcode scanner (HID input) |
| Printing | 80mm thermal (HTML preview / print) |
| WhatsApp | Baileys (WhatsApp Web API) via Node.js service |
| Packaging | Electron wrapper (desktop POS) |
| Hosting | VPS (online store + API) |

---

## 📁 Folder Structure

```
Tijarat-PRO/
├── api.php                  # Central AJAX backend API
├── billing.php              # POS billing checkout interface
├── categories.php           # Categories registry CRUD
├── customers.php            # Customer Khata & ledger
├── purchases.php            # Stock purchases & Gemini AI OCR
├── marketing.php            # WhatsApp promotions campaigns
├── products.php             # Product catalog CRUD
├── inventory.php            # Stock adjustments
├── reports.php              # Business reports
├── settings.php             # Shop settings & config
├── whatsapp.php             # WhatsApp bot management
├── sidebar.php              # Shared navigation sidebar
├── login.php                # Authentication
├── db_config.php            # Database configuration (dual-mode)
├── db_config.json           # Config values (JSON store)
├── schema.sql               # SQLite schema (original)
├── schema_mysql.sql          # MySQL schema (new, 28 tables)
├── migrate_to_mysql.php     # SQLite → MySQL migration tool
├── license_manager.php      # License verification
├── main.js                  # Electron bootstrapper
├── package.json             # Electron + Node dependencies
├── whatsapp_service.js      # Baileys WhatsApp service
├── css/
│   └── style.css            # Core stylesheet
├── js/
│   ├── chart.js             # Chart.js library
│   ├── ipc-shim.js          # Electron IPC shim
│   └── tailwind.js          # Tailwind standalone
├── store/                    # 🆕 E-commerce storefront (Phase 5)
│   ├── index.php            # Store homepage
│   ├── catalog.php          # Product catalog
│   ├── product.php          # Product detail page
│   ├── cart.php             # Shopping cart
│   ├── checkout.php         # Checkout flow
│   ├── account.php          # Customer account
│   ├── api.php              # Store API endpoints
│   └── assets/              # Store CSS/JS/images
└── PROJECT_PLAN.md          # This file
```

---

## 📊 Database Tables (28 Total)

| # | Table | Purpose |
|---|-------|---------|
| 1 | users | Admin, managers, cashiers |
| 2 | branches | Store chain branches |
| 3 | brands | Perfume brands (Chanel, Dior, local) |
| 4 | fragrance_families | Woody, Floral, Oriental, etc. |
| 5 | categories | Product categories (Men's, Women's, Attar) |
| 6 | products | Master product catalog |
| 7 | product_variants | Size/volume variants |
| 8 | branch_stock | Per-branch inventory |
| 9 | stock_transfers | Inter-branch transfers |
| 10 | stock_transfer_items | Transfer line items |
| 11 | testers | Opened tester bottles |
| 12 | customers | CRM + e-commerce customers |
| 13 | customer_payments | Khata payments |
| 14 | loyalty_transactions | Points earn/redeem history |
| 15 | suppliers | Vendor list |
| 16 | supplier_payments | Vendor payments |
| 17 | purchases | Stock purchases |
| 18 | purchase_items | Purchase line items |
| 19 | sales | POS billing records |
| 20 | sale_items | Sale line items |
| 21 | orders | Online e-commerce orders |
| 22 | order_items | Order line items |
| 23 | order_status_history | Order status audit trail |
| 24 | gift_sets | Pre-packaged combos |
| 25 | gift_set_items | Combo contents |
| 26 | expense_categories | Expense types |
| 27 | expenses | Expense tracking |
| 28 | whatsapp_campaigns | Campaign management |
| 29 | whatsapp_messages | Message log |
| 30 | product_reviews | Customer reviews |
| 31 | wishlists | Customer wishlists |
| 32 | cart_items | Persistent shopping cart |
| 33 | coupons | Discount codes |
| 34 | settings | Key-value config store |
| 35 | activity_log | Audit trail |

---

## 💰 Monetization Plan

| Plan | Price | Features |
|------|-------|----------|
| Trial | Free (15 days) | All features |
| Single Store POS | Rs. 10,000 - 15,000 | POS + inventory + reports |
| Chain (3 branches) | Rs. 25,000 - 35,000 | Multi-branch + POS |
| Full ERP + E-commerce | Rs. 50,000 - 80,000 | Everything including online store |
| Annual Support | Rs. 5,000/year | Updates + support |

---

## 📝 Notes

- **Existing code is preserved** — All original POS features remain fully functional in SQLite mode
- **Dual-mode database** — System works in both SQLite (offline) and MySQL (online) modes
- **Incremental migration** — Switch to MySQL when ready, no data loss
- **Phase-wise development** — Each phase is independently testable and deployable
