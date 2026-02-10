<?php
/**
 * Sistema de Canje de Coins
 * Permite canjear coins por cursos gratuitos
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verificar si un producto es canjeable con coins
 */
function producto_es_canjeable_coins($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return false;
    }
    
    // Verificar si el producto tiene configurado el precio en coins
    $coins_requeridos = get_post_meta($product_id, '_coins_requeridos', true);
    
    return !empty($coins_requeridos) && $coins_requeridos > 0;
}

/**
 * Obtener coins requeridos para un producto
 */
function get_coins_requeridos_producto($product_id) {
    $coins = get_post_meta($product_id, '_coins_requeridos', true);
    return $coins ? intval($coins) : 0;
}

/**
 * Procesar canje de coins por producto
 */
function procesar_canje_coins($user_id, $product_id) {
    // Validar usuario
    if (!$user_id) {
        return array('success' => false, 'message' => 'Usuario no válido');
    }
    
    // Validar producto
    if (!producto_es_canjeable_coins($product_id)) {
        return array('success' => false, 'message' => 'Este producto no es canjeable con coins');
    }
    
    // Obtener coins requeridos
    $coins_requeridos = get_coins_requeridos_producto($product_id);
    
    // Verificar saldo
    if (!coins_manager()->has_sufficient_balance($user_id, $coins_requeridos)) {
        return array(
            'success' => false,
            'message' => 'No tienes suficientes coins',
            'coins_necesarios' => $coins_requeridos,
            'coins_actuales' => coins_manager()->get_balance($user_id)
        );
    }
    
    // Verificar que el usuario no haya comprado ya el producto
    if (wc_customer_bought_product('', $user_id, $product_id)) {
        return array('success' => false, 'message' => 'Ya posees este curso');
    }
    
    // Restar coins
    if (!coins_manager()->subtract_coins(
        $user_id,
        $coins_requeridos,
        'Canje de curso - Producto #' . $product_id,
        $product_id
    )) {
        return array('success' => false, 'message' => 'Error al procesar el canje');
    }
    
    // Crear pedido gratis
    $order = wc_create_order(array('customer_id' => $user_id));
    $order->add_product(wc_get_product($product_id), 1);
    $order->set_total(0);
    $order->update_meta_data('_canje_coins', $coins_requeridos);
    $order->update_meta_data('_payment_method', 'coins');
    $order->update_meta_data('_payment_method_title', 'Coins');
    $order->set_status('completed');
    $order->save();
    
    return array(
        'success' => true,
        'message' => '¡Canje exitoso! El curso ya está disponible',
        'order_id' => $order->get_id()
    );
}

/**
 * AJAX: Canjear producto con coins
 */
function ajax_canjear_producto_coins() {
    check_ajax_referer('canje_coins_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Debes iniciar sesión'));
    }
    
    $user_id = get_current_user_id();
    $product_id = intval($_POST['product_id']);
    
    $resultado = procesar_canje_coins($user_id, $product_id);
    
    if ($resultado['success']) {
        wp_send_json_success($resultado);
    } else {
        wp_send_json_error($resultado);
    }
}
add_action('wp_ajax_canjear_producto_coins', 'ajax_canjear_producto_coins');
?>