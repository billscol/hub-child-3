<?php
/**
 * Configuración de campos del Checkout
 * Modificaciones en los campos del formulario
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cambiar etiquetas de campos
 */
add_filter('woocommerce_checkout_fields', 'customize_checkout_field_labels');
function customize_checkout_field_labels($fields) {
    // Cambiar "Teléfono" a "WhatsApp"
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['label'] = 'WhatsApp';
        $fields['billing']['billing_phone']['placeholder'] = 'Número de WhatsApp';
    }
    
    return $fields;
}

/**
 * Remover campos innecesarios de billing
 */
add_filter('woocommerce_billing_fields', 'remove_billing_fields');
function remove_billing_fields($fields) {
    // Comentar las líneas de los campos que quieras mantener
    unset($fields['billing_company']);
    unset($fields['billing_address_2']);
    
    return $fields;
}

/**
 * Ordenar campos del checkout
 */
add_filter('woocommerce_checkout_fields', 'reorder_checkout_fields');
function reorder_checkout_fields($fields) {
    // Orden de campos de billing
    if (isset($fields['billing'])) {
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_phone']['priority'] = 30;
        $fields['billing']['billing_email']['priority'] = 40;
        $fields['billing']['billing_address_1']['priority'] = 50;
        $fields['billing']['billing_city']['priority'] = 60;
        $fields['billing']['billing_state']['priority'] = 70;
        $fields['billing']['billing_postcode']['priority'] = 80;
        $fields['billing']['billing_country']['priority'] = 90;
    }
    
    return $fields;
}
