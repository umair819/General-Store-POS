/**
 * Tijarat PRO Storefront — Core Interactive JavaScript
 */

// Theme Management
function initTheme() {
    const savedTheme = localStorage.getItem('tijarat_store_theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
}

function toggleStoreTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('tijarat_store_theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('storeThemeIcon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
}

// Toast Notifications
function showStoreToast(message, type = 'success') {
    let container = document.getElementById('storeToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'storeToastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    const iconColor = type === 'success' ? '#10b981' : '#ef4444';
    
    toast.innerHTML = `<i class="fa-solid ${icon}" style="color: ${iconColor};"></i> <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

// Cart State Management via AJAX
async function addToCart(productId, variantId = null, qty = 1, options = {}) {
    try {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);
        if (variantId) formData.append('variant_id', variantId);
        formData.append('qty', qty);

        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            updateCartBadge(data.cart_count);
            showStoreToast(data.message || 'Added to shopping bag!');
            if (options.redirectToCart) {
                window.location.href = 'cart.php';
            }
        } else {
            showStoreToast(data.message || 'Could not add item to bag', 'error');
        }
    } catch (err) {
        console.error('Add to cart error:', err);
        showStoreToast('Network error while updating bag', 'error');
    }
}

async function updateCartItem(itemKey, qty) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_cart');
        formData.append('item_key', itemKey);
        formData.append('qty', qty);

        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            updateCartBadge(data.cart_count);
            if (window.location.pathname.endsWith('cart.php')) {
                window.location.reload();
            }
        } else {
            showStoreToast(data.message || 'Error updating quantity', 'error');
        }
    } catch (err) {
        console.error('Update cart error:', err);
    }
}

async function removeCartItem(itemKey) {
    try {
        const formData = new FormData();
        formData.append('action', 'remove_from_cart');
        formData.append('item_key', itemKey);

        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            updateCartBadge(data.cart_count);
            showStoreToast('Item removed from bag');
            if (window.location.pathname.endsWith('cart.php')) {
                window.location.reload();
            }
        }
    } catch (err) {
        console.error('Remove cart error:', err);
    }
}

function updateCartBadge(count) {
    const badges = document.querySelectorAll('.cart-count-badge');
    badges.forEach(b => {
        b.textContent = count;
        b.style.display = count > 0 ? 'flex' : 'none';
    });
}

// 1-Click WhatsApp Direct Ordering
function orderOnWhatsApp(shopPhone, productName, variantLabel = '', price = '', productUrl = '') {
    // Normalize phone number (Pakistani format 03xx -> 923xx)
    let cleanPhone = shopPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('03')) {
        cleanPhone = '92' + cleanPhone.substring(1);
    }
    
    let text = `Hello! I would like to order the following item:\n\n` +
               `🛍️ *Product:* ${productName}\n`;
    if (variantLabel) {
        text += `✨ *Variant / Size:* ${variantLabel}\n`;
    }
    if (price) {
        text += `💰 *Price:* ${price}\n`;
    }
    if (productUrl) {
        text += `🔗 *Link:* ${productUrl}\n`;
    }
    text += `\nPlease let me know availability and delivery options to my city. Thank you!`;

    const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(text)}`;
    window.open(waUrl, '_blank');
}

// Initialize theme on load
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
});
