<?php
/**
 * Validaciones del Checkout - DEPURACIÓN
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Limpiar y formatear datos antes de guardar
 */
add_filter('woocommerce_checkout_posted_data', 'clean_checkout_data');
function clean_checkout_data($data) {
    // Limpiar y formatear teléfono
    if (isset($data['billing_phone'])) {
        $data['billing_phone'] = preg_replace('/[^0-9+\-]/', '', $data['billing_phone']);
    }
    
    return $data;
}
