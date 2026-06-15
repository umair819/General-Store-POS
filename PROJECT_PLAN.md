# 🏪 General Store / Kiryana POS System
**Project Status:** 🔴 Not Started  
**Priority:** ⭐⭐⭐⭐⭐ Very High  
**Target Market:** Kiryana stores, general shops, departmental stores, utility stores across Pakistan  
**Tech Stack (Planned):** PHP + MySQL OR Node.js + SQLite + HTML/CSS/JS

---

## 📋 Project Overview

Pakistan mein 3 million se zyada kiryana/general stores hain. Inme se 95% abhi bhi 
manually kaam karte hain ya simple register use karte hain. Ek bilkul simple, fast, 
aur offline-capable POS system jo mobile/tablet pe bhi chale — yeh ek massive market hai.
BiteDesk POS aur PharmaPOS ki technology ka combination use kar ke yeh jaldi ban sakta hai.

---

## ✅ Features To Develop (Kya Banana Hai)

### 1. 🔐 Login & User Management
- [ ] Admin / Owner login
- [ ] Cashier / Staff login (limited access)
- [ ] PIN-based quick login at POS screen
- [ ] Activity log (kon ne kya kiya)

### 2. 🛒 POS / Billing Screen (Main Screen)
- [ ] Barcode scanner support (USB barcode reader)
- [ ] Quick item search by name or barcode
- [ ] Touch/click item to add to cart
- [ ] Quantity adjustment in cart
- [ ] Multiple items per bill
- [ ] Discount per item or on total bill
- [ ] Hold bill / resume later
- [ ] Fast checkout with keyboard shortcuts
- [ ] Cash tendered & change calculation

### 3. 💳 Payment Methods
- [ ] Cash payment
- [ ] Easypaisa / JazzCash (manual confirm)
- [ ] Bank transfer / cheque
- [ ] Credit (Udhaar / Khata) — customer balance tracking
- [ ] Split payment (partial cash + partial Udhaar)

### 4. 📦 Product / Item Management
- [ ] Add products (Name, Category, Barcode, Purchase Price, Sale Price, Unit)
- [ ] Categories (Atta/Chawal, Drinks, Biscuits, Cleaning, etc.)
- [ ] Units: Kg, Gram, Packet, Dozen, Piece, Liter
- [ ] Multiple purchase units vs sale units (e.g., buy by carton, sell by piece)
- [ ] Product photo (optional)
- [ ] Bulk product import via Excel/CSV

### 5. 📊 Inventory / Stock Management
- [ ] Current stock levels per item
- [ ] Low stock alert (set minimum threshold)
- [ ] Stock adjustment (damage, theft, correction)
- [ ] Stock in (purchase/restock entry)
- [ ] Expiry date tracking for perishables

### 6. 🧾 Purchase Management
- [ ] Supplier/vendor list
- [ ] Purchase order creation
- [ ] Goods received entry
- [ ] Supplier payment tracking
- [ ] Supplier ledger (outstanding balance)

### 7. 👥 Customer & Khata (Udhaar) Management
- [ ] Customer list (Name, Phone, Address)
- [ ] Udhaar (credit) tracking per customer
- [ ] Payment collection from customer
- [ ] Customer statement print karo
- [ ] Send balance reminder via WhatsApp

### 8. 🧾 Receipt & Invoice Printing
- [ ] 80mm thermal receipt printing
- [ ] A4/A5 invoice printing
- [ ] Shop name, logo, address on receipt
- [ ] Duplicate receipt reprint

### 9. 📈 Reports & Analytics
- [ ] Daily sales report
- [ ] Item-wise sales report (best sellers)
- [ ] Category-wise sales
- [ ] Profit & loss report (daily/monthly)
- [ ] Low stock report
- [ ] Udhaar/pending payments report
- [ ] Supplier outstanding report

### 10. 📱 WhatsApp Integration
- [ ] Daily sales summary to owner on WhatsApp
- [ ] Udhaar reminder to customer via WhatsApp
- [ ] Low stock alert to owner
- [ ] Receipt share to customer via WhatsApp (optional)

### 11. ⚙️ Settings
- [ ] Shop name, logo, address, phone
- [ ] Tax/GST settings (optional)
- [ ] Default currency (PKR)
- [ ] Receipt header/footer customization
- [ ] Backup & restore
- [ ] Multi-branch support (Phase 2)

### 12. 🔒 Licensing
- [ ] 15-day trial
- [ ] License key system
- [ ] Single PC / Network version

---

## 🛠️ Tech Stack Details

| Layer | Technology |
|-------|-----------|
| Backend | Node.js + Express OR PHP |
| Database | SQLite (offline first) |
| Frontend | HTML + CSS + JS |
| Barcode | USB barcode scanner (HID input) |
| Printing | 80mm thermal + PDF |
| WhatsApp | Baileys Node.js |
| Packaging | Electron (.exe) OR XAMPP-based |

---

## 📁 Planned Folder Structure

```
General-Store-POS/
├── index.js / server.php    # Main backend
├── database.js / db.php     # DB operations
├── db_config.json
├── index.html               # POS billing screen
├── inventory.html           # Stock management
├── purchases.html           # Purchase management
├── customers.html           # Customer/Khata list
├── reports.html             # All reports
├── settings.html            # Settings page
├── whatsapp/
│   └── wa-server.js
├── 1. Start_POS.bat
├── 1. End_POS.bat
└── PROJECT_PLAN.md
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
| Phase 1 | Products + Categories + Inventory | 🔴 Not Started |
| Phase 2 | POS billing screen + Payments | 🔴 Not Started |
| Phase 3 | Customer Khata + Udhaar tracking | 🔴 Not Started |
| Phase 4 | Purchase management + Suppliers | 🔴 Not Started |
| Phase 5 | Reports + Printing | 🔴 Not Started |
| Phase 6 | WhatsApp + Licensing + Packaging | 🔴 Not Started |

---

## 📝 Notes & Ideas

- BiteDesk-POS-Web aur PharmaPOS ka code bohot reuse ho sakta hai
- Barcode scanner support add karo — yeh killer feature hai kiryana stores ke liye
- Urdu interface option rakhna — shopkeepers English nahi jaante
- Android tablet version bhi socho (web-based so it's easy)
- Target: Rs. 5,000-8,000 per shop — 1000 shops = Rs. 50 lakh+

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
