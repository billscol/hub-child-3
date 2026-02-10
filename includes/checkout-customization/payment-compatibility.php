<?php
/**
 * Compatibilidad de Checkout
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Desactivar campos de envío para productos digitales
 */
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');
add_filter('woocommerce_cart_needs_shipping', '__return_false');

/**
 * Remover campos de dirección innecesarios del formulario
 */
add_filter('woocommerce_checkout_fields', 'child_theme_remove_address_fields_for_digital', 999);
function child_theme_remove_address_fields_for_digital($fields) {
    $needs_shipping = false;
    
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $_product = $cart_item['data'];
            if ($_product && !$_product->is_virtual() && !$_product->is_downloadable()) {
                $needs_shipping = true;
                break;
            }
        }
    }
    
    if (!$needs_shipping) {
        // Remover shipping
        if (isset($fields['shipping'])) {
            $fields['shipping'] = [];
        }
    }
    
    return $fields;
}

/**
 * Limpiar dirección de la sesión
 */
add_action('woocommerce_after_calculate_totals', 'child_theme_clear_shipping_from_cart', 999);
function child_theme_clear_shipping_from_cart($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    $needs_shipping = false;
    foreach ($cart->get_cart() as $cart_item) {
        $_product = $cart_item['data'];
        if ($_product && !$_product->is_virtual() && !$_product->is_downloadable()) {
            $needs_shipping = true;
            break;
        }
    }
    
    if (!$needs_shipping && WC()->customer) {
        WC()->customer->set_shipping_address_1('');
        WC()->customer->set_shipping_address_2('');
        WC()->customer->set_shipping_city('');
        WC()->customer->set_shipping_state('');
        WC()->customer->set_shipping_postcode('');
        WC()->customer->set_shipping_country('');
        WC()->customer->save();
    }
}
