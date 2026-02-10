<?php
/**
 * Validaciones del Sistema de Canje
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validar que el usuario pueda canjear
 */
function validar_puede_canjear($user_id, $product_id) {
    $errores = array();
    
    // Validar usuario logueado
    if (!$user_id) {
        $errores[] = 'Debes iniciar sesión';
    }
    
    // Validar producto existe
    $product = wc_get_product($product_id);
    if (!$product) {
        $errores[] = 'Producto no encontrado';
    }
    
    // Validar es canjeable
    if (!producto_es_canjeable_coins($product_id)) {
        $errores[] = 'Este producto no es canjeable';
    }
    
    // Validar no lo tiene ya
    if (wc_customer_bought_product('', $user_id, $product_id)) {
        $errores[] = 'Ya posees este curso';
    }
    
    // Validar saldo suficiente
    $coins_requeridos = get_coins_requeridos_producto($product_id);
    if (!coins_manager()->has_sufficient_balance($user_id, $coins_requeridos)) {
        $errores[] = 'Saldo insuficiente';
    }
    
    return array(
        'valido' => empty($errores),
        'errores' => $errores
    );
}
?>