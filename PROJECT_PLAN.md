# 🏪 General Store / Kiryana POS System
**Project Status:** 🟡 In Progress (Core Checkout, Khata, Purchases, WhatsApp Marketing, and Gemini AI OCR Completed)  
**Priority:** ⭐⭐⭐⭐⭐ Very High  
**Target Market:** Kiryana stores, general shops, departmental stores, utility stores across Pakistan  
**Tech Stack:** PHP + SQLite + Electron + HTML/CSS/JS

---

## 📋 Project Overview

Pakistan mein 3 million se zyada kiryana/general stores hain. Inme se 95% abhi bhi 
manually kaam karte hain ya simple register use karte hain. Ek bilkul simple, fast, 
aur offline-capable POS system jo mobile/tablet pe bhi chale — yeh ek massive market hai.
BiteDesk POS aur PharmaPOS ki technology ka combination use kar ke yeh jaldi ban sakta hai.

---

## ✅ Features To Develop (Kya Banana Hai)

### 1. 🔐 Login & User Management
- [x] Admin / Owner login
- [x] Cashier / Staff login (limited access)
- [x] PIN-based quick login at POS screen
- [x] Activity log (kon ne kya kiya)

### 2. 🛒 POS / Billing Screen (Main Screen)
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

### 3. 💳 Payment Methods
- [x] Cash payment
- [x] Easypaisa / JazzCash (manual confirm)
- [x] Bank transfer / cheque
- [x] Credit (Udhaar / Khata) — customer balance tracking
- [x] Split payment (partial cash + partial Udhaar)

### 4. 📦 Product / Item Management
- [x] Add products (Name, Category, Barcode, Purchase Price, Sale Price, Unit)
- [x] Categories (Atta/Chawal, Drinks, Biscuits, Cleaning, etc.)
- [x] Units: Kg, Gram, Packet, Dozen, Piece, Liter
- [x] Multiple purchase units vs sale units (e.g., buy by carton, sell by piece)
- [x] Product photo (optional)
- [x] Bulk product import via Excel/CSV
- [x] Vyapar Backup Import Tool (Free 1-click migration of Products & Customers from Vyapar Excel/CSV exports)

### 5. 📊 Inventory / Stock Management
- [x] Current stock levels per item
- [x] Low stock alert (set minimum threshold)
- [x] Near-expiry stock alerts/notifications (for perishables)
- [x] Stock adjustment (damage, theft, correction)
- [x] Stock in (purchase/restock entry)
- [x] Expiry date tracking for perishables

### 6. 🧾 Purchase Management
- [x] Supplier/vendor list
- [x] Purchase order creation
- [x] Goods received entry
- [x] Supplier payment tracking
- [x] Supplier ledger (outstanding balance)
- [x] 🤖 Gemini AI OCR Bill Scanner (Auto-extracts bill items, quantities, prices, and matches with catalog to update stock)

### 7. 👥 Customer & Khata (Udhaar) Management
- [x] Customer list (Name, Phone, Address)
- [x] Udhaar (credit) tracking per customer
- [x] Payment collection from customer (Ledger Statement sheets)
- [x] Customer statement print karo
- [x] Send balance reminder via WhatsApp

### 8. 🧾 Receipt & Invoice Printing
- [x] 80mm thermal receipt printing
- [ ] A4/A5 invoice printing
- [ ] 10+ customizable receipt/invoice templates (different layouts, fonts, and colors)
- [x] Shop name, logo, address on receipt
- [x] Duplicate receipt reprint

### 9. 📈 Reports & Analytics
- [x] Daily sales report
- [ ] Item-wise sales report (best sellers)
- [ ] Category-wise sales
- [ ] Profit & loss report (daily/monthly)
- [x] Low stock report
- [x] Udhaar/pending payments report
- [x] Supplier outstanding report

### 10. 📱 WhatsApp Integration
- [ ] Daily sales summary to owner on WhatsApp
- [x] Automated Udhaar/balance reminder scheduler to customers
- [x] Near-expiry & low stock alert notifications to owner
- [x] Receipt share to customer via WhatsApp (Text template)

### 11. ⚙️ Settings
- [x] Shop name, logo, address, phone
- [x] Tax/GST settings (optional)
- [x] Default currency (PKR)
- [x] Receipt header/footer customization
- [x] Backup & restore (Support own POS format backups)
- [x] Advanced Restore & Migration Engine: Support direct upload & 1-click import of Vyapar (.vyb) backups and other market softwares
- [ ] Multi-branch support (Phase 2)

### 12. 🔒 Licensing
- [ ] 15-day trial
- [ ] License key system
- [ ] Single PC / Network version

---

## 🛠️ Tech Stack Details

| Layer | Technology |
|-------|-----------|
| Backend | PHP + SQLite (offline first) |
| Database | SQLite 3 |
| Frontend | HTML + CSS + JS (Outfit / Poppins typography) |
| Barcode | USB barcode scanner (HID input) |
| Printing | 80mm thermal (HTML preview / print) |
| WhatsApp | Web URL redirection & hooks |
| Packaging | Electron wrapper |

---

## 📁 Planned Folder Structure

```
General-Store-POS/
├── api.php                  # Central AJAX backend API
├── billing.php              # POS billing checkout interface
├── categories.php           # Categories registry CRUD
├── customers.php            # Customer Khata & ledger payments
├── purchases.php            # Stock purchases & Gemini AI OCR scanning
├── marketing.php            # WhatsApp promotions campaigns
├── products.php             # Inventory / Products CRUD
├── settings.php             # Shop settings & restoration engine
├── main.js                  # Electron bootstrapper
├── package.json             # Electron configuration
└── PROJECT_PLAN.md          # Project plan roadmap
```

---

## 💰 Monetization Plan

| Plan | Price | Features |
|------|-------|----------|
| Trial | Free (15 days) | All features |
| Basic (1 PC) | Rs. 5,000 - 8,000 | Single PC license |
| Network | Rs. 12,000 - 18,000 | Multiple cashiers |
| Annual Support | Rs. 2,000/year | Updates + support |

---

## 🎯 Development Phases

| Phase | Tasks | Status |
|-------|-------|--------|
| Phase 1 | Products + Categories + Inventory | 🟢 Completed |
| Phase 2 | POS billing screen + Payments | 🟢 Completed |
| Phase 3 | Customer Khata + Udhaar tracking | 🟢 Completed |
| Phase 4 | Purchase management + Suppliers | 🟢 Completed |
| Phase 5 | Reports + Printing | 🟡 In Progress (Receipt printing done) |
| Phase 6 | WhatsApp + Licensing + Packaging | 🟡 In Progress (WhatsApp Campaigns done) |Phase 4 | Purchase management + Suppliers | 🔴 Not Started |
| Phase 5 | Reports + Printing | 🔴 Not Started |
| Phase 6 | WhatsApp + Licensing + Packaging | 🔴 Not Started |

---

## 📝 Notes & Ideas

- **Code Reuse:** BiteDesk-POS-Web aur PharmaPOS ka code bohot reuse ho sakta hai.
- **Competitive Advantage vs Vyapar (vyapar.pk):**
  - **One-time Pricing:** Vyapar is yearly subscription-based (Rs. 3000-6000/year). Hum local shops ko Rs. 5,000-8,000 one-time lifetime license key pe offer karenge, jo Pakistan market ke liye zyada attractive hai.
  - **Urdu-First Simplicity:** Vyapar contains complex accounting terms. Hum interface ko Urdu main aur aam fahm terms (Sale, Purchase, Stock, Udhaar) ke sath rakhain ge.
  - **10+ Receipt/Invoice Templates:** Vyapar ki tarah thermal aur A4 templates ki customization options denge (jis me client apna logo, header, footer aur design choose kar sake).
  - **Free WhatsApp Messaging:** Vyapar charges extra for SMS. Hum direct WhatsApp Web/Baileys integration se free messaging setup karenge.
  - **Advanced Multi-Software Restore Engine:** System settings mein 1-click restore option hoga jo humari backup file ke sath sath Vyapar (.vyb) aur baaqi competitors ke backups se directly data migrate/restore kar sakega (switching cost to zero).
  - **Offline Reliability:** 100% offline local setup without requiring high-speed internet.
- **Hardware Integration:** Barcode scanner support (USB) is critical for faster kiryana checkouts.
- **Device Support:** Tablet version can be supported in Phase 2 via local network web access.
- **Financial Goal:** Target: Rs. 5,000-8,000 per shop — 1000 shops = Rs. 50 lakh+.

---

## 🔐 Final Phase: Security & Licensing System (Launch Se Pehle Lazim)

> **Is phase ko complete kiye baghair software sell nahi karna!**
> **Note:** BiteDesk/PharmaPOS jaisi exact same security — PHP/Node.js stack.

### Steps:

#### Step 1 — PC ID Generation
- [ ] Python helper ya PHP exec se `MachineGuid` read → SHA256
- [ ] Fallback: MAC + hostname → SHA256

#### Step 2 — Trial (Server-Side, 15 days)
- [ ] File nahi — server pe track
- [ ] "Trial — X days remaining"

#### Step 3 — License Activation
- [ ] `XXXX-XXXX-XXXX-XXXX` format
- [ ] `POST /api/activate` → encrypted local save

#### Step 4 — Startup Validation
- [ ] PC ID verify + expiry + 3-din online check
- [ ] 7-din offline grace

#### Step 5 — Deactivation + 3-Day Lock
#### Step 6 — Code Protection (IonCube PHP / JS obfuscator)
#### Step 7 — Admin License Panel

### Phase Table:
| Phase | Tasks | Status |
|-------|-------|--------|
| Phase 7 | PC ID + Trial (server) | 🔴 Not Started |
| Phase 7 | License activation | 🔴 Not Started |
| Phase 7 | Startup validation | 🔴 Not Started |
| Phase 7 | Deactivation + lock | 🔴 Not Started |
| Phase 7 | Admin license panel | 🔴 Not Started |
| Phase 7 | Code protection + build | 🔴 Not Started |
