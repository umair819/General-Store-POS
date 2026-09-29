-- ============================================================
-- Tijarat PRO — Universal Retail Chain ERP
-- MySQL Schema (Full)
-- Supports: Any retail type via Dynamic Item Type System
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- 1. USERS & AUTHENTICATION
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'cashier', 'warehouse') NOT NULL DEFAULT 'cashier',
    pin VARCHAR(10),
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(200),
    branch_id INT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (password: admin123 SHA256)
INSERT IGNORE INTO users (id, username, password, role, pin, name, phone) 
VALUES (1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', '1234', 'Administrator', '03001234567');

-- -----------------------------------------------------------
-- 2. BRANCHES / STORE CHAIN
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Short code like HQ, BR1, BR2',
    address TEXT,
    city VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(200),
    manager_user_id INT DEFAULT NULL,
    is_main TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Main/HQ branch flag',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default main branch
INSERT IGNORE INTO branches (id, name, code, address, city, is_main) 
VALUES (1, 'Main Store', 'HQ', 'Saddar, Karachi, Pakistan', 'Karachi', 1);

-- Add FK from users to branches (after branches table is created)
ALTER TABLE users ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

-- -----------------------------------------------------------
-- 3. BRANDS (Perfume Brands)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE,
    logo_url VARCHAR(500),
    country VARCHAR(100),
    description TEXT,
    is_local TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Local brand vs imported',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. ITEM TYPES (Dynamic attribute system)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS item_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name_ur VARCHAR(100) COMMENT 'Urdu name',
    icon VARCHAR(50),
    color_hex VARCHAR(7) COMMENT 'Theme color for UI badge',
    description TEXT,
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System types cannot be deleted',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: System item types
INSERT IGNORE INTO item_types (id, name, slug, name_ur, icon, color_hex, is_system, sort_order) VALUES
(1,  'General / Retail',    'general',      'جنرل',         '🛒', '#6366F1', 1, 1),
(2,  'Perfume & Fragrance', 'perfume',       'عطر',          '🧴', '#EC4899', 1, 2),
(3,  'Garment & Clothing',  'garment',       'کپڑے',        '👔', '#8B5CF6', 1, 3),
(4,  'Electronics & Mobile','electronics',   'الیکٹرانکس',  '📱', '#3B82F6', 1, 4),
(5,  'Shoes & Footwear',    'footwear',      'جوتے',        '👟', '#F59E0B', 1, 5),
(6,  'Cosmetics & Beauty',  'cosmetics',     'کاسمیٹکس',    '💄', '#F43F5E', 1, 6),
(7,  'Grocery & FMCG',      'grocery',       'گروسری',       '🧃', '#10B981', 1, 7),
(8,  'Hardware & Auto',     'hardware',      'ہارڈویئر',     '🔧', '#64748B', 1, 8),
(9,  'Jewelry & Accessories','jewelry',      'زیورات',       '💍', '#D97706', 1, 9),
(10, 'Stationery & Books',  'stationery',    'سٹیشنری',      '📚', '#0EA5E9', 1, 10),
(11, 'Medical & Pharmacy',  'pharmacy',      'دوائیں',       '💊', '#EF4444', 1, 11);

-- -----------------------------------------------------------
-- 4b. TYPE ATTRIBUTES (What fields each item type has)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS type_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_type_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL COMMENT 'Display label',
    attribute_key VARCHAR(50) NOT NULL COMMENT 'Programmatic key',
    field_type ENUM('text', 'number', 'select', 'multi_select', 'color', 'date', 'boolean', 'textarea') NOT NULL DEFAULT 'text',
    options JSON COMMENT 'For select/multi_select: ["Option1","Option2"]',
    default_value VARCHAR(200) DEFAULT NULL,
    placeholder VARCHAR(200) DEFAULT NULL,
    is_variant TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If 1, this attribute creates product variants',
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_filterable TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Show as filter on e-commerce',
    is_visible_pos TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Show on POS product card',
    is_visible_store TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Show on e-commerce page',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_type_id) REFERENCES item_types(id) ON DELETE CASCADE,
    UNIQUE KEY uk_type_attr (item_type_id, attribute_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4c. PRODUCT ATTRIBUTE VALUES
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value_text TEXT COMMENT 'Stores the actual attribute value',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES type_attributes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_prod_attr (product_id, attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. CATEGORIES (Generic — owner adds based on their store type)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) UNIQUE NOT NULL,
    name_ur VARCHAR(200) COMMENT 'Urdu name',
    description TEXT,
    item_type_id INT DEFAULT NULL COMMENT 'Optional: link category to an item type',
    parent_id INT DEFAULT NULL COMMENT 'Sub-category support',
    icon VARCHAR(50),
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_type_id) REFERENCES item_types(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default generic categories (owner will customize)
INSERT IGNORE INTO categories (name, description) VALUES
('General', 'Default category for uncategorized items'),
('Featured', 'Featured / promoted items');

-- -----------------------------------------------------------
-- 6. PRODUCTS (Universal — works for any item type)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(100) UNIQUE,
    sku VARCHAR(50) UNIQUE COMMENT 'Internal SKU code',
    name VARCHAR(300) NOT NULL,
    name_ur VARCHAR(300) COMMENT 'Urdu product name',
    slug VARCHAR(300) COMMENT 'URL-friendly name for e-commerce',
    item_type_id INT DEFAULT NULL COMMENT 'Which item type (perfume, garment, etc.)',
    category_id INT DEFAULT NULL,
    brand_id INT DEFAULT NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    compare_price DECIMAL(12,2) DEFAULT NULL COMMENT 'Original/strikethrough price for e-commerce',
    cost_price DECIMAL(12,2) DEFAULT NULL COMMENT 'Landed cost including shipping etc.',
    unit ENUM('Piece', 'Box', 'Dozen', 'Set', 'Kg', 'Gram', 'Liter', 'Meter', 'Pair', 'Packet', 'Carton') NOT NULL DEFAULT 'Piece',
    stock_qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_stock_threshold DECIMAL(12,2) NOT NULL DEFAULT 5.00,
    has_variants TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Product has size/color/volume variants',
    expiry_date DATE DEFAULT NULL,
    description TEXT COMMENT 'Product description for e-commerce',
    short_description VARCHAR(500),
    image_url VARCHAR(500),
    gallery_urls JSON COMMENT 'Array of additional image URLs',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_online TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Show on e-commerce store',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    weight_grams INT DEFAULT NULL COMMENT 'Shipping weight',
    tags VARCHAR(500) COMMENT 'Comma-separated tags for search',
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_type_id) REFERENCES item_types(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    INDEX idx_products_item_type (item_type_id),
    INDEX idx_products_brand (brand_id),
    INDEX idx_products_category (category_id),
    INDEX idx_products_slug (slug),
    FULLTEXT INDEX idx_products_search (name, description, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 7. PRODUCT VARIANTS (Generic — Size, Color, Volume, etc.)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_label VARCHAR(200) NOT NULL COMMENT 'Display label e.g. "100ml", "Large - Red", "32GB"',
    attribute_values JSON COMMENT 'Key-value pairs e.g. {"size":"L","color":"Red"}',
    barcode VARCHAR(100) UNIQUE,
    sku VARCHAR(50) UNIQUE,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    compare_price DECIMAL(12,2) DEFAULT NULL,
    stock_qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_stock_threshold DECIMAL(12,2) NOT NULL DEFAULT 3.00,
    image_url VARCHAR(500) DEFAULT NULL COMMENT 'Variant-specific image (e.g. color swatch)',
    weight_grams INT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_variants_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 8. BRANCH STOCK (Per-branch inventory)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS branch_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    stock_qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    min_threshold DECIMAL(12,2) NOT NULL DEFAULT 3.00,
    shelf_location VARCHAR(50) COMMENT 'e.g. Rack A, Shelf 3',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_product_variant (branch_id, product_id, variant_id),
    INDEX idx_branch_stock_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 9. STOCK TRANSFERS (Inter-branch)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS stock_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transfer_no VARCHAR(50) UNIQUE NOT NULL,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    status ENUM('draft', 'in_transit', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    received_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_at DATETIME DEFAULT NULL,
    FOREIGN KEY (from_branch_id) REFERENCES branches(id),
    FOREIGN KEY (to_branch_id) REFERENCES branches(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_transfer_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    received_qty DECIMAL(12,2) DEFAULT NULL COMMENT 'Actual quantity received (may differ)',
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 10. TESTERS (Opened inventory for display)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS testers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    branch_id INT NOT NULL,
    opened_date DATE NOT NULL,
    status ENUM('active', 'empty', 'damaged', 'returned') NOT NULL DEFAULT 'active',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 11. CUSTOMERS (Enhanced for CRM + E-commerce)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(200),
    password_hash VARCHAR(255) DEFAULT NULL COMMENT 'For e-commerce login',
    address TEXT,
    city VARCHAR(100),
    shipping_address TEXT,
    date_of_birth DATE,
    anniversary DATE,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    loyalty_points INT NOT NULL DEFAULT 0,
    loyalty_tier ENUM('Bronze', 'Silver', 'Gold', 'Platinum') NOT NULL DEFAULT 'Bronze',
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Positive = customer owes us (Udhaar)',
    total_purchases DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Lifetime purchase total',
    total_orders INT NOT NULL DEFAULT 0,
    fragrance_preferences JSON COMMENT 'Preferred fragrance family IDs',
    preferred_branch_id INT DEFAULT NULL,
    source ENUM('walk_in', 'online', 'whatsapp', 'referral') NOT NULL DEFAULT 'walk_in',
    is_online_customer TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_customer_phone (phone),
    INDEX idx_customer_email (email),
    INDEX idx_customer_loyalty (loyalty_tier),
    FOREIGN KEY (preferred_branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 12. CUSTOMER PAYMENTS (Khata/Udhaar payments)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS customer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'online', 'cheque', 'easypaisa', 'jazzcash', 'bank_transfer') NOT NULL DEFAULT 'cash',
    reference_no VARCHAR(100) COMMENT 'Transaction ID for digital payments',
    note TEXT,
    received_by INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 13. LOYALTY TRANSACTIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    points INT NOT NULL COMMENT 'Positive = earned, Negative = redeemed',
    type ENUM('earned', 'redeemed', 'expired', 'bonus', 'adjustment') NOT NULL,
    reference_type VARCHAR(50) COMMENT 'sale, order, manual, birthday_bonus',
    reference_id INT COMMENT 'sale_id or order_id',
    description VARCHAR(500),
    branch_id INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 14. SUPPLIERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    company VARCHAR(200),
    contact_person VARCHAR(200),
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(200),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Pakistan',
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Negative = we owe them',
    payment_terms VARCHAR(100) COMMENT 'e.g. Net 30, COD',
    notes TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 15. SUPPLIER PAYMENTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS supplier_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'online', 'cheque', 'bank_transfer') NOT NULL DEFAULT 'cash',
    reference_no VARCHAR(100),
    note TEXT,
    paid_by INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 16. PURCHASES (Stock Buying from Suppliers)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_no VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT DEFAULT NULL,
    branch_id INT DEFAULT NULL COMMENT 'Which branch received the stock',
    user_id INT DEFAULT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    balance_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('received', 'partial', 'returned', 'cancelled') NOT NULL DEFAULT 'received',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    purchase_price DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    expiry_date DATE DEFAULT NULL,
    batch_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 17. SALES (POS Billing)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    online_order_id INT DEFAULT NULL COMMENT 'Link to e-commerce order if applicable',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    balance_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    loyalty_points_earned INT NOT NULL DEFAULT 0,
    loyalty_points_redeemed INT NOT NULL DEFAULT 0,
    payment_method ENUM('cash', 'online', 'cheque', 'credit', 'split', 'easypaisa', 'jazzcash') NOT NULL DEFAULT 'cash',
    status ENUM('completed', 'returned', 'hold', 'cancelled') NOT NULL DEFAULT 'completed',
    sale_channel ENUM('pos', 'online', 'whatsapp') NOT NULL DEFAULT 'pos',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sales_branch (branch_id),
    INDEX idx_sales_date (created_at),
    INDEX idx_sales_channel (sale_channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    purchase_price DECIMAL(12,2) NOT NULL COMMENT 'Cost price at time of sale for profit calculation',
    sale_price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 18. ONLINE ORDERS (E-commerce)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT DEFAULT NULL,
    branch_id INT DEFAULT NULL COMMENT 'Fulfillment branch',
    
    -- Amounts
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    
    -- Payment
    payment_method ENUM('cod', 'easypaisa', 'jazzcash', 'bank_transfer', 'card') NOT NULL DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    payment_reference VARCHAR(200),
    
    -- Shipping
    shipping_name VARCHAR(200),
    shipping_phone VARCHAR(30),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    tracking_no VARCHAR(100),
    courier VARCHAR(100) COMMENT 'TCS, Leopards, etc.',
    
    -- Order Status
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') NOT NULL DEFAULT 'pending',
    
    -- Loyalty
    loyalty_points_earned INT NOT NULL DEFAULT 0,
    loyalty_points_redeemed INT NOT NULL DEFAULT 0,
    
    -- Channel
    order_channel ENUM('website', 'whatsapp', 'instagram', 'phone') NOT NULL DEFAULT 'website',
    
    -- Linked POS sale (if order is fulfilled via POS)
    sale_id INT DEFAULT NULL,
    
    customer_notes TEXT,
    admin_notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    delivered_at DATETIME DEFAULT NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    INDEX idx_orders_status (order_status),
    INDEX idx_orders_date (created_at),
    INDEX idx_orders_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK from sales to orders (after orders table)
ALTER TABLE sales ADD CONSTRAINT fk_sales_order FOREIGN KEY (online_order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- -----------------------------------------------------------
-- 19. ORDER STATUS HISTORY
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    old_status VARCHAR(30),
    new_status VARCHAR(30) NOT NULL,
    note TEXT,
    changed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 20. GIFT SETS / COMBOS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS gift_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(300) NOT NULL,
    slug VARCHAR(300),
    description TEXT,
    image_url VARCHAR(500),
    sale_price DECIMAL(12,2) NOT NULL,
    compare_price DECIMAL(12,2) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_online TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gift_set_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gift_set_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    FOREIGN KEY (gift_set_id) REFERENCES gift_sets(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 21. EXPENSES TRACKING
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO expense_categories (name) VALUES
('Rent'), ('Utilities'), ('Salaries'), ('Marketing'), ('Packaging'),
('Shipping/Courier'), ('Maintenance'), ('Office Supplies'), ('Miscellaneous');

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    payment_method ENUM('cash', 'online', 'cheque', 'bank_transfer') NOT NULL DEFAULT 'cash',
    reference_no VARCHAR(100),
    receipt_url VARCHAR(500),
    created_by INT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 22. WHATSAPP CAMPAIGNS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS whatsapp_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('promotion', 'udhaar_reminder', 'new_arrival', 'birthday', 'anniversary', 'order_update', 'custom') NOT NULL DEFAULT 'promotion',
    template TEXT NOT NULL,
    target_count INT NOT NULL DEFAULT 0,
    sent_count INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'sending', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    scheduled_at DATETIME DEFAULT NULL,
    created_by INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT DEFAULT NULL,
    customer_id INT DEFAULT NULL,
    phone VARCHAR(30) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('campaign', 'order_notification', 'receipt', 'reminder', 'manual') NOT NULL DEFAULT 'manual',
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'pending',
    error_message TEXT,
    sent_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES whatsapp_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 23. PRODUCT REVIEWS (E-commerce)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT DEFAULT NULL,
    customer_name VARCHAR(200),
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 24. WISHLIST (E-commerce)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    UNIQUE KEY uk_wishlist (customer_id, product_id, variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 25. SHOPPING CART (E-commerce - persistent)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT DEFAULT NULL,
    session_id VARCHAR(128) DEFAULT NULL COMMENT 'For guest carts',
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_cart_session (session_id),
    INDEX idx_cart_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 26. COUPONS & DISCOUNTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200),
    type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    value DECIMAL(12,2) NOT NULL COMMENT 'Percentage or fixed amount',
    min_order_amount DECIMAL(12,2) DEFAULT NULL,
    max_discount_amount DECIMAL(12,2) DEFAULT NULL COMMENT 'Cap for percentage discounts',
    usage_limit INT DEFAULT NULL COMMENT 'Total usage limit',
    used_count INT NOT NULL DEFAULT 0,
    per_customer_limit INT DEFAULT 1,
    valid_from DATETIME DEFAULT NULL,
    valid_until DATETIME DEFAULT NULL,
    applicable_to ENUM('all', 'category', 'brand', 'product') NOT NULL DEFAULT 'all',
    applicable_ids JSON COMMENT 'Array of category/brand/product IDs',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 27. SETTINGS (Key-Value Store)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'general, ecommerce, whatsapp, loyalty, shipping, payment',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES
-- General
('shop_name', 'TijaratPro', 'general'),
('shop_phone', '03001234567', 'general'),
('shop_address', 'Saddar, Karachi, Pakistan', 'general'),
('shop_currency', 'PKR', 'general'),
('shop_logo', '', 'general'),
('tax_enabled', '0', 'general'),
('tax_number', '', 'general'),
('tax_rate', '0', 'general'),
('decimal_places', '2', 'general'),
-- E-commerce
('ecommerce_enabled', '1', 'ecommerce'),
('store_name', 'TijaratPro Store', 'ecommerce'),
('store_tagline', 'Premium Fragrances Delivered to Your Door', 'ecommerce'),
('min_order_amount', '0', 'ecommerce'),
('free_shipping_above', '5000', 'ecommerce'),
('default_shipping_fee', '200', 'ecommerce'),
('cod_enabled', '1', 'ecommerce'),
('easypaisa_enabled', '0', 'ecommerce'),
('jazzcash_enabled', '0', 'ecommerce'),
('bank_transfer_enabled', '0', 'ecommerce'),
('bank_details', '', 'ecommerce'),
-- WhatsApp
('whatsapp_mode', 'baileys', 'whatsapp'),
('whatsapp_business_phone', '', 'whatsapp'),
('order_notification_enabled', '1', 'whatsapp'),
('auto_receipt_enabled', '1', 'whatsapp'),
-- Loyalty
('loyalty_enabled', '1', 'loyalty'),
('points_per_rupee', '1', 'loyalty'),
('points_to_rupee_ratio', '100', 'loyalty'),
('min_redeem_points', '500', 'loyalty'),
('birthday_bonus_points', '100', 'loyalty'),
-- Gemini AI
('gemini_api_key', '', 'ai');

-- -----------------------------------------------------------
-- 28. ACTIVITY LOG
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    branch_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) COMMENT 'product, sale, order, customer, etc.',
    entity_id INT DEFAULT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- SEED: Type Attributes for all Item Types
-- -----------------------------------------------------------

-- GENERAL / RETAIL (type_id = 1)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(1, 'Manufacturer',    'manufacturer',   'text',   NULL, 0, 0, 1, 1),
(1, 'Weight / Size',   'weight_size',    'text',   NULL, 0, 0, 0, 2),
(1, 'Pack Size',       'pack_size',      'select', '["Single","3-Pack","6-Pack","12-Pack","Carton","Box"]', 1, 0, 1, 3),
(1, 'Expiry Date',     'expiry_date',    'date',   NULL, 0, 0, 0, 4),
(1, 'Material',        'material',       'text',   NULL, 0, 0, 0, 5);

-- PERFUME & FRAGRANCE (type_id = 2)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(2, 'Fragrance Family',  'fragrance_family', 'select', '["Woody","Floral","Oriental","Fresh","Citrus","Aquatic","Gourmand","Aromatic","Spicy","Musky","Oud","Amber"]', 0, 0, 1, 1),
(2, 'Concentration',     'concentration',    'select', '["Parfum","EDP","EDT","EDC","Body Spray","Attar","Oil"]', 0, 1, 1, 2),
(2, 'Gender',            'gender',           'select', '["Men","Women","Unisex"]', 0, 0, 1, 3),
(2, 'Volume (ml)',       'volume_ml',        'select', '["5ml","10ml","15ml","30ml","50ml","75ml","100ml","125ml","150ml","200ml","250ml"]', 1, 0, 1, 4),
(2, 'Top Notes',         'top_notes',        'text',   NULL, 0, 0, 0, 5),
(2, 'Middle Notes',      'middle_notes',     'text',   NULL, 0, 0, 0, 6),
(2, 'Base Notes',        'base_notes',       'text',   NULL, 0, 0, 0, 7),
(2, 'Country of Origin', 'country_origin',   'text',   NULL, 0, 0, 1, 8);

-- GARMENT & CLOTHING (type_id = 3)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(3, 'Size',          'size',        'select', '["XS","S","M","L","XL","XXL","3XL","Free Size"]', 1, 0, 1, 1),
(3, 'Color',         'color',       'color',  NULL, 1, 0, 1, 2),
(3, 'Fabric',        'fabric',      'select', '["Cotton","Polyester","Silk","Linen","Wool","Denim","Chiffon","Lawn","Khaddar","Karandi","Viscose","Blend"]', 0, 0, 1, 3),
(3, 'Pattern',       'pattern',     'select', '["Solid","Printed","Striped","Checkered","Embroidered","Floral","Abstract"]', 0, 0, 1, 4),
(3, 'Sleeve Type',   'sleeve_type', 'select', '["Full Sleeve","Half Sleeve","Sleeveless","3/4 Sleeve","Cap Sleeve"]', 0, 0, 0, 5),
(3, 'Season',        'season',      'select', '["Summer","Winter","All Season","Spring","Autumn"]', 0, 0, 1, 6),
(3, 'Gender',        'gender',      'select', '["Men","Women","Kids","Unisex"]', 0, 0, 1, 7),
(3, 'Wash Care',     'wash_care',   'text',   NULL, 0, 0, 0, 8);

-- ELECTRONICS & MOBILE (type_id = 4)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(4, 'Model',          'model',        'text',   NULL, 0, 0, 1, 1),
(4, 'Color',          'color',        'color',  NULL, 1, 0, 1, 2),
(4, 'Storage',        'storage',      'select', '["16GB","32GB","64GB","128GB","256GB","512GB","1TB"]', 1, 0, 1, 3),
(4, 'RAM',            'ram',          'select', '["2GB","3GB","4GB","6GB","8GB","12GB","16GB"]', 0, 0, 1, 4),
(4, 'Warranty',       'warranty',     'select', '["No Warranty","3 Months","6 Months","1 Year","2 Years"]', 0, 0, 1, 5),
(4, 'Condition',      'condition',    'select', '["New","Refurbished","Open Box","Used"]', 0, 0, 1, 6),
(4, 'Specs',          'specs',        'textarea', NULL, 0, 0, 0, 7);

-- SHOES & FOOTWEAR (type_id = 5)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(5, 'Shoe Size',     'shoe_size',   'select', '["5","6","7","8","9","10","11","12","13","36","37","38","39","40","41","42","43","44","45"]', 1, 0, 1, 1),
(5, 'Color',         'color',       'color',  NULL, 1, 0, 1, 2),
(5, 'Material',      'material',    'select', '["Leather","Synthetic","Canvas","Suede","Rubber","Mesh","Fabric"]', 0, 0, 1, 3),
(5, 'Style',         'style',       'select', '["Casual","Formal","Sports","Sandals","Boots","Sneakers","Loafers","Chappal","Peshawari"]', 0, 0, 1, 4),
(5, 'Gender',        'gender',      'select', '["Men","Women","Kids","Unisex"]', 0, 0, 1, 5);

-- COSMETICS & BEAUTY (type_id = 6)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(6, 'Shade / Color', 'shade',       'color',  NULL, 1, 0, 1, 1),
(6, 'Skin Type',     'skin_type',   'select', '["All","Oily","Dry","Combination","Sensitive","Normal"]', 0, 0, 1, 2),
(6, 'Volume / Weight','volume',     'text',   NULL, 0, 0, 0, 3),
(6, 'SPF',           'spf',         'select', '["None","SPF 15","SPF 30","SPF 50","SPF 50+"]', 0, 0, 1, 4),
(6, 'Ingredients',   'ingredients', 'textarea', NULL, 0, 0, 0, 5),
(6, 'Gender',        'gender',      'select', '["Men","Women","Unisex"]', 0, 0, 1, 6);

-- GROCERY & FMCG (type_id = 7)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(7, 'MFG Date',        'mfg_date',     'date',   NULL, 0, 0, 0, 1),
(7, 'Expiry Date',     'expiry_date',  'date',   NULL, 0, 0, 0, 2),
(7, 'Batch No',        'batch_no',     'text',   NULL, 0, 0, 0, 3),
(7, 'Manufacturer',    'manufacturer', 'text',   NULL, 0, 0, 1, 4),
(7, 'Pack Size',       'pack_size',    'select', '["Single","3-Pack","6-Pack","12-Pack","24-Pack","Carton"]', 1, 0, 1, 5),
(7, 'Weight',          'weight',       'text',   NULL, 0, 0, 0, 6),
(7, 'Diet Type',       'diet_type',    'select', '["Regular","Halal","Organic","Sugar Free","Gluten Free"]', 0, 0, 1, 7);

-- HARDWARE & AUTO (type_id = 8)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(8, 'Material',        'material',       'select', '["Steel","Iron","Brass","Copper","Aluminum","Plastic","Wood","Rubber"]', 0, 0, 1, 1),
(8, 'Size / Spec',     'size_spec',      'text',   NULL, 1, 0, 1, 2),
(8, 'Grade',           'grade',          'text',   NULL, 0, 0, 0, 3),
(8, 'Compatibility',   'compatibility',  'text',   NULL, 0, 0, 0, 4),
(8, 'Weight',          'weight',         'text',   NULL, 0, 0, 0, 5);

-- JEWELRY & ACCESSORIES (type_id = 9)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(9, 'Material',     'material',   'select', '["Gold","Silver","Platinum","Artificial","Rose Gold","Steel","Pearl"]', 0, 1, 1, 1),
(9, 'Karat',        'karat',      'select', '["24K","22K","18K","14K","N/A"]', 0, 0, 1, 2),
(9, 'Stone Type',   'stone_type', 'select', '["None","Diamond","Ruby","Emerald","Sapphire","Zircon","Pearl","Crystal"]', 0, 0, 1, 3),
(9, 'Size',         'size',       'text',   NULL, 1, 0, 0, 4),
(9, 'Weight (grams)','weight_g',  'number', NULL, 0, 0, 0, 5),
(9, 'Gender',       'gender',     'select', '["Men","Women","Unisex"]', 0, 0, 1, 6);

-- STATIONERY & BOOKS (type_id = 10)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(10, 'Type',          'type',       'select', '["Notebook","Pen","Pencil","Eraser","Ruler","Marker","File","Book","Art Supply","Other"]', 0, 0, 1, 1),
(10, 'Paper Size',    'paper_size', 'select', '["A4","A5","B5","Legal","Letter","N/A"]', 0, 0, 1, 2),
(10, 'Color',         'color',      'color',  NULL, 1, 0, 1, 3),
(10, 'Pack Count',    'pack_count', 'select', '["Single","3-Pack","6-Pack","12-Pack","Box"]', 1, 0, 1, 4);

-- MEDICAL & PHARMACY (type_id = 11)
INSERT IGNORE INTO type_attributes (item_type_id, attribute_name, attribute_key, field_type, options, is_variant, is_required, is_filterable, sort_order) VALUES
(11, 'Generic Name',     'generic_name',  'text',   NULL, 0, 0, 1, 1),
(11, 'Dosage Form',      'dosage_form',   'select', '["Tablet","Capsule","Syrup","Injection","Cream","Ointment","Drops","Inhaler","Sachet","Suppository"]', 0, 0, 1, 2),
(11, 'Strength',         'strength',      'text',   NULL, 1, 0, 1, 3),
(11, 'Pack Size',        'pack_size',     'select', '["Strip","Bottle","Box","Tube","Single"]', 1, 0, 1, 4),
(11, 'Manufacturer',     'manufacturer',  'text',   NULL, 0, 0, 1, 5),
(11, 'Expiry Date',      'expiry_date',   'date',   NULL, 0, 1, 0, 6),
(11, 'Batch No',         'batch_no',      'text',   NULL, 0, 0, 0, 7),
(11, 'Prescription Req', 'rx_required',   'boolean', NULL, 0, 0, 1, 8);

-- -----------------------------------------------------------
-- 29. SYNC LOG (for hybrid cloud sync tracking)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL COMMENT 'sales, products, customers, etc.',
    action ENUM('push', 'pull') NOT NULL,
    record_count INT NOT NULL DEFAULT 0,
    status ENUM('synced', 'failed', 'partial') NOT NULL DEFAULT 'synced',
    error_message TEXT,
    synced_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 30. ACTIVE PACKAGES (license-linked feature sets)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS store_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO store_config (config_key, config_value) VALUES
('active_package', 'O1'),
('active_item_types', '[1]'),
('store_setup_complete', '0'),
('first_run', '1');

SET FOREIGN_KEY_CHECKS = 1;
