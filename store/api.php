<?php
// ============================================================
// Tijarat PRO — Storefront Public API (AJAX Handler)
// ============================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/init.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // ----------------------------------------------------
    // ADD TO CART
    // ----------------------------------------------------
    case 'add_to_cart':
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $qty       = max(1, (int)($_POST['qty'] ?? 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product selected']);
            exit;
        }

        // Fetch product
        $product = dbFind('products', $productId);
        if (!$product || (isset($product['is_active']) && (int)$product['is_active'] === 0)) {
            echo json_encode(['success' => false, 'message' => 'Product is currently unavailable']);
            exit;
        }

        $variantLabel = null;
        $unitPrice    = (float)($product['sale_price'] ?? 0.0);
        $itemImage    = !empty($product['image_url']) ? $product['image_url'] : '';

        // If variant specified or product has variants
        if ($variantId) {
            $variant = dbFind('product_variants', $variantId);
            if ($variant && (int)$variant['product_id'] === $productId) {
                $variantLabel = $variant['variant_label'];
                $unitPrice    = (float)$variant['sale_price'];
                if (!empty($variant['image_url'])) {
                    $itemImage = $variant['image_url'];
                }
            }
        } elseif (!empty($product['has_variants'])) {
            // Find default or first active variant
            $firstVariant = dbQuery("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1", [$productId]);
            if (!empty($firstVariant)) {
                $variantId    = (int)$firstVariant[0]['id'];
                $variantLabel = $firstVariant[0]['variant_label'];
                $unitPrice    = (float)$firstVariant[0]['sale_price'];
                if (!empty($firstVariant[0]['image_url'])) {
                    $itemImage = $firstVariant[0]['image_url'];
                }
            }
        }

        $cartKey = makeCartKey($productId, $variantId);

        if (isset($_SESSION['store_cart'][$cartKey])) {
            $_SESSION['store_cart'][$cartKey]['qty'] += $qty;
        } else {
            $_SESSION['store_cart'][$cartKey] = [
                'product_id'    => $productId,
                'variant_id'    => $variantId,
                'name'          => $product['name'],
                'variant_label' => $variantLabel,
                'price'         => $unitPrice,
                'image'         => $itemImage,
                'qty'           => $qty
            ];
        }

        echo json_encode([
            'success'    => true,
            'message'    => "Added '{$product['name']}' to your shopping bag",
            'cart_count' => getStoreCartCount(),
            'subtotal'   => getStoreCartSubtotal(),
            'formatted_subtotal' => formatStorePrice(getStoreCartSubtotal())
        ]);
        exit;

    // ----------------------------------------------------
    // UPDATE CART QUANTITY
    // ----------------------------------------------------
    case 'update_cart':
        $itemKey = trim($_POST['item_key'] ?? '');
        $qty     = (int)($_POST['qty'] ?? 1);

        if (!isset($_SESSION['store_cart'][$itemKey])) {
            echo json_encode(['success' => false, 'message' => 'Item not found in shopping bag']);
            exit;
        }

        if ($qty <= 0) {
            unset($_SESSION['store_cart'][$itemKey]);
        } else {
            $_SESSION['store_cart'][$itemKey]['qty'] = $qty;
        }

        echo json_encode([
            'success'    => true,
            'cart_count' => getStoreCartCount(),
            'subtotal'   => getStoreCartSubtotal(),
            'formatted_subtotal' => formatStorePrice(getStoreCartSubtotal())
        ]);
        exit;

    // ----------------------------------------------------
    // REMOVE FROM CART
    // ----------------------------------------------------
    case 'remove_from_cart':
        $itemKey = trim($_POST['item_key'] ?? '');
        if (isset($_SESSION['store_cart'][$itemKey])) {
            unset($_SESSION['store_cart'][$itemKey]);
        }

        echo json_encode([
            'success'    => true,
            'cart_count' => getStoreCartCount(),
            'subtotal'   => getStoreCartSubtotal(),
            'formatted_subtotal' => formatStorePrice(getStoreCartSubtotal())
        ]);
        exit;

    // ----------------------------------------------------
    // CLEAR CART
    // ----------------------------------------------------
    case 'clear_cart':
        $_SESSION['store_cart'] = [];
        echo json_encode([
            'success'    => true,
            'cart_count' => 0,
            'subtotal'   => 0
        ]);
        exit;

    // ----------------------------------------------------
    // GET CART STATE
    // ----------------------------------------------------
    case 'get_cart':
        echo json_encode([
            'success'    => true,
            'cart'       => $_SESSION['store_cart'],
            'cart_count' => getStoreCartCount(),
            'subtotal'   => getStoreCartSubtotal(),
            'formatted_subtotal' => formatStorePrice(getStoreCartSubtotal())
        ]);
        exit;

    // ----------------------------------------------------
    // TRACK ORDER
    // ----------------------------------------------------
    case 'track_order':
        $query = trim($_REQUEST['query'] ?? '');
        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'Please provide an Order Number or Phone Number']);
            exit;
        }

        // Search by order_no or shipping_phone
        $orders = dbQuery("SELECT * FROM orders WHERE order_no = ? OR shipping_phone = ? ORDER BY id DESC LIMIT 5", [$query, $query]);
        if (empty($orders)) {
            echo json_encode(['success' => false, 'message' => 'No orders found matching your search.']);
            exit;
        }

        $results = [];
        foreach ($orders as $ord) {
            $items = dbQuery("SELECT * FROM order_items WHERE order_id = ?", [$ord['id']]);
            $results[] = [
                'order_no'       => $ord['order_no'],
                'created_at'     => $ord['created_at'],
                'order_status'   => $ord['order_status'],
                'payment_method' => $ord['payment_method'],
                'payment_status' => $ord['payment_status'],
                'total'          => $ord['total'],
                'formatted_total'=> formatStorePrice($ord['total']),
                'tracking_no'    => $ord['tracking_no'],
                'items'          => $items
            ];
        }

        echo json_encode(['success' => true, 'orders' => $results]);
        exit;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown API action requested']);
        exit;
}
?>
