<?php
/**
 * Shortcode: [dual_buy_buttons]
 * Botones duales de compra: Comprar Ahora y Ver Carrito
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function custom_wc_dual_buttons_shortcode($atts) {
    global $product;
    
    if (!$product || !is_a($product, 'WC_Product')) {
        return '';
    }
    
    $product_id = $product->get_id();
    
    ob_start();
    ?>
    
    <div class="dual-buy-buttons-wrapper" data-product-id="<?php echo esc_attr($product_id); ?>">
        <!-- Botón Comprar Ahora -->
        <button type="button" class="buy-now-button" data-product-id="<?php echo esc_attr($product_id); ?>">
            <svg class="button-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span>Comprar Ahora</span>
        </button>
        
        <!-- Botón Ver Carrito -->
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="view-cart-button">
            <svg class="button-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <span>Ver Carrito</span>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dual_buy_buttons', 'custom_wc_dual_buttons_shortcode');
?>