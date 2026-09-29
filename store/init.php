<?php
// ============================================================
// Tijarat PRO — Storefront Initialization & Session Context
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db_config.php';

// Initialize session cart
if (!isset($_SESSION['store_cart']) || !is_array($_SESSION['store_cart'])) {
    $_SESSION['store_cart'] = [];
}

/**
 * Returns store metadata & settings
 */
function getStoreSettings() {
    global $global_config;
    return [
        'name'          => $global_config['shop_name'] ?? 'Tijarat Luxury Fragrances',
        'phone'         => $global_config['shop_phone'] ?? '03001234567',
        'address'       => $global_config['shop_address'] ?? 'Saddar, Karachi, Pakistan',
        'currency'      => $global_config['shop_currency'] ?? 'PKR',
        'whatsapp_mode' => $global_config['whatsapp_mode'] ?? 'link',
        'tagline'       => 'Artisanal Perfumes, Luxury Fragrances & Signature Essentials',
        'shipping_flat' => 250.0,
        'free_shipping_min' => 3500.0,
    ];
}

/**
 * Total number of units in shopping cart
 */
function getStoreCartCount() {
    $count = 0;
    if (!empty($_SESSION['store_cart'])) {
        foreach ($_SESSION['store_cart'] as $item) {
            $count += (int)($item['qty'] ?? 1);
        }
    }
    return $count;
}

/**
 * Subtotal of current shopping cart
 */
function getStoreCartSubtotal() {
    $subtotal = 0.0;
    if (!empty($_SESSION['store_cart'])) {
        foreach ($_SESSION['store_cart'] as $item) {
            $subtotal += ((float)$item['price']) * ((int)$item['qty']);
        }
    }
    return $subtotal;
}

/**
 * Formats amount into Pakistani Currency or configured currency
 */
function formatStorePrice($amount) {
    global $global_config;
    $currency = $global_config['shop_currency'] ?? 'PKR';
    return $currency . ' ' . number_format((float)$amount, 0);
}

/**
 * Generates item key for cart (e.g. prod_4_var_2)
 */
function makeCartKey($productId, $variantId = null) {
    return 'p' . (int)$productId . '_v' . (int)($variantId ?? 0);
}
?>
