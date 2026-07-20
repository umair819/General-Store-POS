<?php
/**
 * TijaratPro - Demo Data Database Seeder
 * Seeds comprehensive store data including categories, products, customers, suppliers,
 * and a realistic 14-day history of sales and purchases to populate dashboards and charts.
 */

require_once __DIR__ . '/db_config.php';

echo "⏳ Starting Database Seeding...\n";

try {
    // Disable Foreign Key constraints temporarily to safely truncate
    $conn->exec("PRAGMA foreign_keys = OFF;");
    
    // Truncate tables
    $conn->exec("DELETE FROM sale_items;");
    $conn->exec("DELETE FROM sales;");
    $conn->exec("DELETE FROM purchase_items;");
    $conn->exec("DELETE FROM purchases;");
    $conn->exec("DELETE FROM customer_payments;");
    $conn->exec("DELETE FROM supplier_payments;");
    $conn->exec("DELETE FROM products;");
    $conn->exec("DELETE FROM categories;");
    $conn->exec("DELETE FROM customers;");
    $conn->exec("DELETE FROM suppliers;");
    
    // Re-enable Foreign Key constraints
    $conn->exec("PRAGMA foreign_keys = ON;");
    echo "✔ Cleaned old database tables.\n";

    // 1. Seed Categories
    $categories = [
        ['Atta, Grains & Pulses', 'Flour, rice, lentils, sugar'],
        ['Beverages & Soft Drinks', 'Cold drinks, juices, mineral water'],
        ['Snacks & Confectionery', 'Chips, biscuits, chocolates, candies'],
        ['Dairy & Bakery Products', 'Milk, yogurt, bread, butter, eggs'],
        ['Personal Care & Hygiene', 'Soaps, shampoos, toothpaste, detergents'],
        ['Spices, Ghee & Oil', 'Cooking oil, ghee, salt, red chili']
    ];
    
    $cat_ids = [];
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?);");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
        $cat_ids[$cat[0]] = $conn->lastInsertId();
    }
    echo "✔ Seeded Categories.\n";

    // 2. Seed Products
    $products = [
        // Atta & Grains
        ['1001', 'Sunridge Chakki Atta 10Kg', 'Atta, Grains & Pulses', 980, 1100, 'Packet', 25, 5],
        ['1002', 'Super Basmati Rice 5Kg', 'Atta, Grains & Pulses', 1450, 1650, 'Packet', 18, 4],
        ['1003', 'Fine Sugar 1Kg', 'Atta, Grains & Pulses', 110, 125, 'Kg', 120, 15],
        // Beverages
        ['2001', 'Coca Cola 1.5 Liter', 'Beverages & Soft Drinks', 145, 170, 'Piece', 5, 10], // Low stock!
        ['2002', 'Pepsi Soft Drink 1.5L', 'Beverages & Soft Drinks', 142, 170, 'Piece', 45, 8],
        ['2003', 'Nestle Pure Life 1.5L', 'Beverages & Soft Drinks', 70, 90, 'Piece', 80, 12],
        // Snacks
        ['3001', 'Lays Masala Chips 50g', 'Snacks & Confectionery', 42, 50, 'Piece', 150, 20],
        ['3002', 'Sooper Biscuits Family Pack', 'Snacks & Confectionery', 120, 140, 'Piece', 60, 10],
        ['3003', 'Dairy Milk Chocolate 30g', 'Snacks & Confectionery', 85, 100, 'Piece', 4, 15], // Low stock!
        // Dairy & Bakery
        ['4001', 'Nestle Milkpak 1 Liter', 'Dairy & Bakery Products', 245, 275, 'Piece', 95, 10],
        ['4002', 'Dawn Bread Large', 'Dairy & Bakery Products', 120, 140, 'Piece', 35, 5],
        ['4003', 'Farm Fresh Eggs (Dozen)', 'Dairy & Bakery Products', 260, 300, 'Dozen', 12, 5],
        // Personal Care
        ['5001', 'Sunsilk Shampoo 360ml', 'Personal Care & Hygiene', 460, 520, 'Piece', 28, 5],
        ['5002', 'Lifebuoy Soap 120g', 'Personal Care & Hygiene', 82, 95, 'Piece', 110, 10],
        // Spices & Oil
        ['6001', 'Dalda Cooking Oil 5 Liter', 'Spices, Ghee & Oil', 2650, 2850, 'Piece', 14, 3]
    ];

    $product_ids = [];
    $stmt = $conn->prepare("INSERT INTO products (barcode, name, category_id, purchase_price, sale_price, unit, stock_qty, min_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
    foreach ($products as $p) {
        $cat_id = $cat_ids[$p[2]] ?? null;
        $stmt->execute([$p[0], $p[1], $cat_id, $p[3], $p[4], $p[5], $p[6], $p[7]]);
        $product_ids[] = [
            'id' => $conn->lastInsertId(),
            'name' => $p[1],
            'purchase_price' => $p[3],
            'sale_price' => $p[4]
        ];
    }
    echo "✔ Seeded Products.\n";

    // 3. Seed Customers
    $customers = [
        ['Muhammad Bilal', '03112345678', 'Gulshan-e-Iqbal, Karachi', 12000, '1995-04-12', '2019-10-18'],
        ['Zeeshan Ahmed', '03223456789', 'Federal B Area, Karachi', 4500, '1990-11-20', null],
        ['Aisha Khan', '03334567890', 'Clifton, Karachi', 0, '1996-07-02', '2021-02-14'],
        ['Tariq Mahmood', '03445678901', 'North Nazimabad, Karachi', 8500, '1985-01-25', null],
        ['Kamran Shah', '03009876543', 'DHA Phase 6, Karachi', 1500, '1988-08-30', '2015-05-12']
    ];

    $customer_ids = [];
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address, balance, birthdate, anniversary) VALUES (?, ?, ?, ?, ?, ?);");
    foreach ($customers as $c) {
        $stmt->execute($c);
        $customer_ids[] = $conn->lastInsertId();
    }
    echo "✔ Seeded Customers.\n";

    // 4. Seed Suppliers
    $suppliers = [
        ['Metro Wholesale Karachi', '021111786786', 'SITEL Area, Karachi', -35000],
        ['Unilever Pakistan', '02135678901', 'Avari Plaza, Karachi', -15000],
        ['Nestle Pakistan Distributors', '04235789012', 'Ferozepur Road, Lahore', 0]
    ];

    $supplier_ids = [];
    $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, address, balance) VALUES (?, ?, ?, ?);");
    foreach ($suppliers as $s) {
        $stmt->execute($s);
        $supplier_ids[] = $conn->lastInsertId();
    }
    echo "✔ Seeded Suppliers.\n";

    // 5. Seed sales and purchases history for the last 14 days
    $sale_stmt = $conn->prepare("INSERT INTO sales (invoice_no, customer_id, user_id, subtotal, discount, total, paid_amount, balance_amount, payment_method, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
    $item_stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, purchase_price, sale_price, discount, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
    
    $purchase_stmt = $conn->prepare("INSERT INTO purchases (purchase_no, supplier_id, user_id, subtotal, discount, total, paid_amount, balance_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
    $p_item_stmt = $conn->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, purchase_price, total, created_at) VALUES (?, ?, ?, ?, ?, ?);");

    $tx_count = 0;
    
    for ($i = 14; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        // Generate 1 to 3 random sales for each day
        $sales_today = rand(1, 3);
        for ($s = 0; $s < $sales_today; $s++) {
            $hour = rand(9, 21);
            $minute = rand(10, 59);
            $datetime = "$date " . str_pad($hour, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minute, 2, '0', STR_PAD_LEFT) . ":00";
            
            // Pick random customer or Walk-in (30% Walk-in)
            $cust_id = (rand(1, 10) > 3) ? $customer_ids[array_rand($customer_ids)] : null;
            $invoice_no = "INV-" . date('Ymd', strtotime($date)) . "-" . str_pad($tx_count++, 4, '0', STR_PAD_LEFT);
            
            // Choose 1 to 4 random products for this invoice
            $sale_items = [];
            $subtotal = 0.0;
            $num_items = rand(1, 4);
            $picked_keys = array_rand($product_ids, $num_items);
            if (!is_array($picked_keys)) $picked_keys = [$picked_keys];
            
            foreach ($picked_keys as $key) {
                $p = $product_ids[$key];
                $qty = rand(1, 3);
                $total_p = $qty * $p['sale_price'];
                $subtotal += $total_p;
                
                $sale_items[] = [
                    'product_id' => $p['id'],
                    'qty' => $qty,
                    'purchase_price' => $p['purchase_price'],
                    'sale_price' => $p['sale_price'],
                    'total' => $total_p
                ];
            }
            
            $discount = (rand(1, 10) > 8) ? (float)rand(10, 50) : 0.0;
            $total = $subtotal - $discount;
            
            // Decide payment (Cash or Online or credit if customer is linked)
            $pay_method = (rand(1, 10) > 4) ? 'cash' : 'online';
            if ($cust_id && rand(1, 10) > 7) {
                // Credit sale (Udhaar)
                $paid = (float)rand(0, intval($total / 2));
                $balance = $total - $paid;
            } else {
                $paid = $total;
                $balance = 0.0;
            }
            
            // Insert Sale
            $sale_stmt->execute([$invoice_no, $cust_id, 1, $subtotal, $discount, $total, $paid, $balance, $pay_method, $datetime]);
            $sale_id = $conn->lastInsertId();
            
            // Insert Sale Items
            foreach ($sale_items as $item) {
                $item_stmt->execute([$sale_id, $item['product_id'], $item['qty'], $item['purchase_price'], $item['sale_price'], 0, $item['total'], $datetime]);
            }
        }

        // Generate 1 purchase every 3 days
        if ($i % 3 === 0) {
            $hour = rand(10, 16);
            $minute = rand(0, 59);
            $datetime = "$date " . str_pad($hour, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minute, 2, '0', STR_PAD_LEFT) . ":00";
            
            $supp_id = $supplier_ids[array_rand($supplier_ids)];
            $purchase_no = "PUR-" . date('Ymd', strtotime($date)) . "-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            // Buy Atta or Oil or Tea
            $subtotal = 0.0;
            $purchase_items = [];
            
            // Purchase Atta
            $p_atta = $product_ids[0]; // Atta
            $qty = rand(5, 10);
            $total_p = $qty * $p_atta['purchase_price'];
            $subtotal += $total_p;
            $purchase_items[] = ['product_id' => $p_atta['id'], 'qty' => $qty, 'price' => $p_atta['purchase_price'], 'total' => $total_p];
            
            // Purchase Cooking Oil
            $p_oil = $product_ids[count($product_ids) - 1]; // Oil
            $qty_oil = rand(2, 5);
            $total_oil = $qty_oil * $p_oil['purchase_price'];
            $subtotal += $total_oil;
            $purchase_items[] = ['product_id' => $p_oil['id'], 'qty' => $qty_oil, 'price' => $p_oil['purchase_price'], 'total' => $total_oil];

            $total = $subtotal;
            // 70% paid, 30% credit
            $paid = (rand(1, 10) > 3) ? $total : $total - 5000;
            $balance = $total - $paid;

            // Insert Purchase
            $purchase_stmt->execute([$purchase_no, $supp_id, 1, $subtotal, 0.0, $total, $paid, $balance, $datetime]);
            $purchase_id = $conn->lastInsertId();

            // Insert Purchase Items
            foreach ($purchase_items as $p_item) {
                $p_item_stmt->execute([$purchase_id, $p_item['product_id'], $p_item['qty'], $p_item['price'], $p_item['total'], $datetime]);
            }
        }
    }
    echo "✔ Seeded 14 Days History of POS Sales & Inventory Purchases.\n";

    // 6. Seed Customer & Supplier Payment logs
    $pay_stmt = $conn->prepare("INSERT INTO customer_payments (customer_id, amount, payment_method, note, created_at) VALUES (?, ?, ?, ?, ?);");
    $pay_stmt->execute([$customer_ids[0], 2500, 'cash', 'Khata partial recovery', date('Y-m-d H:i:s', strtotime('-4 days'))]);
    $pay_stmt->execute([$customer_ids[1], 1500, 'online', 'EasyPaisa transfer', date('Y-m-d H:i:s', strtotime('-1 days'))]);
    
    $supp_pay_stmt = $conn->prepare("INSERT INTO supplier_payments (supplier_id, amount, payment_method, note, created_at) VALUES (?, ?, ?, ?, ?);");
    $supp_pay_stmt->execute([$supplier_ids[0], 10000, 'online', 'Bank transfer settlement', date('Y-m-d H:i:s', strtotime('-6 days'))]);
    $supp_pay_stmt->execute([$supplier_ids[1], 5000, 'cash', 'Cash hand settlement', date('Y-m-d H:i:s', strtotime('-2 days'))]);
    echo "✔ Seeded Payment Logs.\n";

    echo "✅ Database Seeding Completed Successfully!\n";

} catch (PDOException $e) {
    echo "❌ Database Seeding Failed: " . $e->getMessage() . "\n";
}
