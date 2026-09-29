<?php
require_once __DIR__ . '/db_config.php';

echo "Seeding Perfume Brands, Categories, Attributes & Products with Variants...\n";

// 1. Seed Brands
$brands_data = [
    ['Rasasi', 'rasasi', 'UAE', 'Dubai premier luxury perfumery established in 1979.'],
    ['Lattafa', 'lattafa', 'UAE', 'Leading Middle Eastern luxury fragrance house.'],
    ['Armaf', 'armaf', 'UAE', 'Signature niche-quality luxury perfumes.'],
    ['Dior', 'dior', 'France', 'World-renowned French haute couture and perfumery.'],
    ['J. Fragrances', 'j-fragrances', 'Pakistan', 'Pakistan foremost premium fragrance brand.'],
    ['Afnan', 'afnan', 'UAE', 'Artisanal oriental and contemporary perfumes.']
];

$brand_map = [];
foreach ($brands_data as $b) {
    $existing = dbQuery("SELECT id FROM brands WHERE slug = ?", [$b[1]]);
    if (empty($existing)) {
        $res = dbExecute("INSERT INTO brands (name, slug, country, description, is_active) VALUES (?, ?, ?, ?, 1)", $b);
        $brand_map[$b[1]] = $res['insertId'];
    } else {
        $brand_map[$b[1]] = $existing[0]['id'];
    }
}
echo "✔ Brands ready.\n";

// 2. Categories mapping
$categories_list = ['Men Perfumes', 'Women Perfumes', 'Unisex Perfumes', 'Designer Fragrances', 'Attar & Oils'];
$cat_map = [];
foreach ($categories_list as $catName) {
    $existing = dbQuery("SELECT id FROM categories WHERE name = ?", [$catName]);
    if (empty($existing)) {
        $res = dbExecute("INSERT INTO categories (name, description) VALUES (?, ?)", [$catName, 'Luxury fragrance category']);
        $cat_map[$catName] = $res['insertId'];
    } else {
        $cat_map[$catName] = $existing[0]['id'];
    }
}
echo "✔ Fragrance Categories ready.\n";

// 3. Perfume products data
$perfumes = [
    [
        'name' => 'Rasasi Hawas for Him',
        'slug' => 'rasasi-hawas-for-him',
        'brand_slug' => 'rasasi',
        'category' => 'Men Perfumes',
        'purchase_price' => 9000,
        'sale_price' => 12500,
        'stock_qty' => 45,
        'is_featured' => 1,
        'description' => 'Blends cinnamon, bergamot, orange blossom, grey amber and sandalwood to create an aquatic scent designed to embody masculine strength and vigor.',
        'attributes' => [
            'fragrance_family' => 'Fresh',
            'concentration' => 'EDP',
            'gender' => 'Men',
            'top_notes' => 'Apple, Bergamot, Lemon, Cinnamon',
            'middle_notes' => 'Watery Notes, Plum, Cardamom',
            'base_notes' => 'Ambergris, Musk, Patchouli, Driftwood',
            'country_origin' => 'UAE'
        ],
        'variants' => [
            ['variant_label' => '50ml Decant', 'sale_price' => 6500, 'purchase_price' => 4500, 'stock_qty' => 20, 'sku' => 'HAWAS-50ML'],
            ['variant_label' => '100ml Full Bottle', 'sale_price' => 12500, 'purchase_price' => 9000, 'stock_qty' => 25, 'sku' => 'HAWAS-100ML']
        ]
    ],
    [
        'name' => 'Lattafa Khamrah',
        'slug' => 'lattafa-khamrah',
        'brand_slug' => 'lattafa',
        'category' => 'Unisex Perfumes',
        'purchase_price' => 6500,
        'sale_price' => 8900,
        'stock_qty' => 30,
        'is_featured' => 1,
        'description' => 'A warm and decadent gourmand oriental fragrance with rich notes of dates, vanilla, cinnamon, and precious resins in a crystal decanter presentation.',
        'attributes' => [
            'fragrance_family' => 'Gourmand',
            'concentration' => 'EDP',
            'gender' => 'Unisex',
            'top_notes' => 'Cinnamon, Nutmeg, Bergamot',
            'middle_notes' => 'Dates, Praline, Tuberose, Mahonial',
            'base_notes' => 'Vanilla, Tonka Bean, Benzoin, Myrrh, Akigalawood',
            'country_origin' => 'UAE'
        ],
        'variants' => [
            ['variant_label' => '100ml Edition', 'sale_price' => 8900, 'purchase_price' => 6500, 'stock_qty' => 30, 'sku' => 'KHAMRAH-100ML']
        ]
    ],
    [
        'name' => 'Armaf Club De Nuit Intense Man',
        'slug' => 'armaf-club-de-nuit-intense-man',
        'brand_slug' => 'armaf',
        'category' => 'Men Perfumes',
        'purchase_price' => 7000,
        'sale_price' => 9500,
        'stock_qty' => 35,
        'is_featured' => 1,
        'description' => 'A provocative woody spicy scent that opens with fresh fruity notes of lemon, apple and blackcurrant leading to an opulent floral heart of rose and jasmine.',
        'attributes' => [
            'fragrance_family' => 'Woody',
            'concentration' => 'Parfum',
            'gender' => 'Men',
            'top_notes' => 'Lemon, Pineapple, Bergamot, Black Currant',
            'middle_notes' => 'Birch, Jasmine, Rose',
            'base_notes' => 'Ambergris, Musk, Patchouli, Vanilla',
            'country_origin' => 'France / UAE'
        ],
        'variants' => [
            ['variant_label' => '105ml Eau de Parfum', 'sale_price' => 9500, 'purchase_price' => 7000, 'stock_qty' => 20, 'sku' => 'CDNIM-105ML'],
            ['variant_label' => '200ml Pure Parfum', 'sale_price' => 16500, 'purchase_price' => 12000, 'stock_qty' => 15, 'sku' => 'CDNIM-200ML']
        ]
    ],
    [
        'name' => 'Dior Sauvage Eau de Parfum',
        'slug' => 'dior-sauvage-edp',
        'brand_slug' => 'dior',
        'category' => 'Designer Fragrances',
        'purchase_price' => 28000,
        'sale_price' => 38000,
        'stock_qty' => 18,
        'is_featured' => 1,
        'description' => 'The powerful and noble trail of Sauvage unfolds with fresh citrusy Calabrian bergamot and the sensuality of mysterious Papua New Guinean vanilla extract.',
        'attributes' => [
            'fragrance_family' => 'Fresh',
            'concentration' => 'EDP',
            'gender' => 'Men',
            'top_notes' => 'Calabrian Bergamot, Pepper',
            'middle_notes' => 'Sichuan Pepper, Lavender, Star Anise, Nutmeg',
            'base_notes' => 'Ambroxan, Papua New Guinean Vanilla',
            'country_origin' => 'France'
        ],
        'variants' => [
            ['variant_label' => '60ml', 'sale_price' => 26000, 'purchase_price' => 19500, 'stock_qty' => 8, 'sku' => 'SAUVAGE-60ML'],
            ['variant_label' => '100ml', 'sale_price' => 38000, 'purchase_price' => 28000, 'stock_qty' => 10, 'sku' => 'SAUVAGE-100ML']
        ]
    ],
    [
        'name' => 'J. Zarar Gold',
        'slug' => 'j-zarar-gold',
        'brand_slug' => 'j-fragrances',
        'category' => 'Men Perfumes',
        'purchase_price' => 4200,
        'sale_price' => 5800,
        'stock_qty' => 50,
        'is_featured' => 1,
        'description' => 'A royal scent starting with sparkling citrus notes, transitioning into rich spicy florals and drying down to warm amber and refined leather accords.',
        'attributes' => [
            'fragrance_family' => 'Oriental',
            'concentration' => 'EDP',
            'gender' => 'Men',
            'top_notes' => 'Grapefruit, Orange, Mandarin',
            'middle_notes' => 'Rose, Cinnamon, Spices',
            'base_notes' => 'Leather, Amber, Patchouli, Woody Notes',
            'country_origin' => 'Pakistan'
        ],
        'variants' => [
            ['variant_label' => '100ml', 'sale_price' => 5800, 'purchase_price' => 4200, 'stock_qty' => 50, 'sku' => 'ZARAR-GOLD-100']
        ]
    ],
    [
        'name' => 'Afnan 9PM',
        'slug' => 'afnan-9pm',
        'brand_slug' => 'afnan',
        'category' => 'Men Perfumes',
        'purchase_price' => 5600,
        'sale_price' => 7800,
        'stock_qty' => 40,
        'is_featured' => 1,
        'description' => 'An energetic night-out fragrance featuring a sweet blend of crisp green apple, spicy cinnamon, rich wild lavender, and warm tonka bean vanilla base.',
        'attributes' => [
            'fragrance_family' => 'Gourmand',
            'concentration' => 'EDP',
            'gender' => 'Men',
            'top_notes' => 'Apple, Cinnamon, Wild Lavender, Bergamot',
            'middle_notes' => 'Orange Blossom, Lily-of-the-Valley',
            'base_notes' => 'Vanilla, Tonka Bean, Amber, Patchouli',
            'country_origin' => 'UAE'
        ],
        'variants' => [
            ['variant_label' => '100ml', 'sale_price' => 7800, 'purchase_price' => 5600, 'stock_qty' => 40, 'sku' => 'AFNAN-9PM-100']
        ]
    ]
];

foreach ($perfumes as $p) {
    $brand_id = $brand_map[$p['brand_slug']] ?? null;
    $cat_id   = $cat_map[$p['category']] ?? null;
    $has_variants = count($p['variants']) > 1 ? 1 : 0;

    $existing = dbQuery("SELECT id FROM products WHERE slug = ?", [$p['slug']]);
    if (empty($existing)) {
        $res = dbExecute("
            INSERT INTO products (
                barcode, name, slug, brand_id, item_type_id, category_id, purchase_price, sale_price,
                unit, stock_qty, min_stock_threshold, is_featured, is_online, is_active, description, has_variants
            ) VALUES (?, ?, ?, ?, 2, ?, ?, ?, 'Piece', ?, 5, ?, 1, 1, ?, ?)
        ", [
            'BAR-' . strtoupper(substr(md5($p['slug']), 0, 8)),
            $p['name'],
            $p['slug'],
            $brand_id,
            $cat_id,
            $p['purchase_price'],
            $p['sale_price'],
            $p['stock_qty'],
            $p['is_featured'],
            $p['description'],
            $has_variants
        ]);
        $prodId = $res['insertId'];
    } else {
        $prodId = $existing[0]['id'];
    }

    if ($prodId > 0) {
        // Save attributes (Item Type 2 = Perfume)
        saveProductAttributes($prodId, 2, $p['attributes']);

        // Seed variants
        foreach ($p['variants'] as $v) {
            $existingVar = dbQuery("SELECT id FROM product_variants WHERE product_id = ? AND variant_label = ?", [$prodId, $v['variant_label']]);
            if (empty($existingVar)) {
                dbExecute("
                    INSERT INTO product_variants (
                        product_id, variant_label, sku, barcode, purchase_price, sale_price, stock_qty, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ", [
                    $prodId,
                    $v['variant_label'],
                    $v['sku'],
                    'VAR-' . strtoupper(substr(md5($v['sku']), 0, 8)),
                    $v['purchase_price'],
                    $v['sale_price'],
                    $v['stock_qty']
                ]);
            }
        }
    }
}

echo "✔ Successfully seeded luxury perfume catalog with attributes & variant matrix!\n";
