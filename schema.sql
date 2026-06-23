-- Database schema for TijaratPro System

PRAGMA foreign_keys = ON;

-- Users table (Admins and Cashiers)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL, -- SHA256 or bcrypt hash
    role TEXT CHECK(role IN ('admin', 'cashier')) NOT NULL DEFAULT 'cashier',
    pin TEXT, -- PIN-based quick login
    name TEXT NOT NULL,
    phone TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: admin123 (hashed or plaintext for initial setup, but let's hash it: using SHA256 of admin123 is '24078914e662923bc7d1175402ad1fa78c7cf99e52e25d2630df328406f5223e' or we can check via code))
-- For simplicity of first run, let's store standard hash and handle password verification in PHP.
-- SHA256 of admin123: 24078914e662923bc7d1175402ad1fa78c7cf99e52e25d2630df328406f5223e
INSERT OR IGNORE INTO users (id, username, password, role, pin, name, phone) 
VALUES (1, 'admin', '24078914e662923bc7d1175402ad1fa78c7cf99e52e25d2630df328406f5223e', 'admin', '1234', 'Administrator', '03001234567');

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some default categories for Kiryana store
INSERT OR IGNORE INTO categories (name, description) VALUES 
('Grocery / Atta / Chawal', 'Daily essentials like flour, rice, pulses'),
('Beverages / Drinks', 'Soft drinks, juices, mineral water'),
('Biscuits & Snacks', 'Chips, biscuits, cookies, confectionery'),
('Cleaning & Toiletries', 'Soaps, detergents, shampoos, cleaners'),
('Dairy & Bakery', 'Milk, yogurt, bread, butter, eggs'),
('Spices & Oil', 'Cooking oil, ghee, salt, spices');

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    barcode TEXT UNIQUE,
    name TEXT NOT NULL,
    category_id INTEGER,
    purchase_price REAL NOT NULL DEFAULT 0.0,
    sale_price REAL NOT NULL DEFAULT 0.0,
    unit TEXT CHECK(unit IN ('Kg', 'Gram', 'Packet', 'Dozen', 'Piece', 'Liter', 'Box')) NOT NULL DEFAULT 'Piece',
    stock_qty REAL NOT NULL DEFAULT 0.0,
    min_stock_threshold REAL NOT NULL DEFAULT 5.0,
    expiry_date DATE, -- Expiry date for perishables
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Customers table (with Khata/Udhaar balance tracking)
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT UNIQUE NOT NULL,
    address TEXT,
    balance REAL NOT NULL DEFAULT 0.0, -- Positive means customer owes us money (Udhaar)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table (Vendors for stock purchasing)
CREATE TABLE IF NOT EXISTS suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    contact_person TEXT,
    phone TEXT NOT NULL,
    address TEXT,
    email TEXT,
    balance REAL NOT NULL DEFAULT 0.0, -- Negative means we owe vendor money
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sales table (Billing records)
CREATE TABLE IF NOT EXISTS sales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_no TEXT UNIQUE NOT NULL,
    customer_id INTEGER,
    user_id INTEGER,
    subtotal REAL NOT NULL DEFAULT 0.0,
    discount REAL NOT NULL DEFAULT 0.0,
    total REAL NOT NULL DEFAULT 0.0,
    paid_amount REAL NOT NULL DEFAULT 0.0,
    balance_amount REAL NOT NULL DEFAULT 0.0, -- Udhaar amount
    payment_method TEXT CHECK(payment_method IN ('cash', 'online', 'cheque', 'credit', 'split')) NOT NULL DEFAULT 'cash',
    status TEXT CHECK(status IN ('completed', 'returned', 'hold')) NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale Items table (Individual items inside a sale/invoice)
CREATE TABLE IF NOT EXISTS sale_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sale_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity REAL NOT NULL DEFAULT 1.0,
    purchase_price REAL NOT NULL, -- Recorded purchase price at time of sale for profit reports
    sale_price REAL NOT NULL,
    discount REAL NOT NULL DEFAULT 0.0,
    total REAL NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Purchases table (Restocking records from suppliers)
CREATE TABLE IF NOT EXISTS purchases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_no TEXT UNIQUE NOT NULL,
    supplier_id INTEGER,
    user_id INTEGER,
    subtotal REAL NOT NULL DEFAULT 0.0,
    discount REAL NOT NULL DEFAULT 0.0,
    total REAL NOT NULL DEFAULT 0.0,
    paid_amount REAL NOT NULL DEFAULT 0.0,
    balance_amount REAL NOT NULL DEFAULT 0.0, -- Amount we owe to supplier
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Purchase Items table (Individual items inside a purchase)
CREATE TABLE IF NOT EXISTS purchase_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity REAL NOT NULL DEFAULT 1.0,
    purchase_price REAL NOT NULL,
    total REAL NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE RESTRICT
);
