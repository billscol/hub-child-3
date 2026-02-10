<?php
/**
 * Gestión de Saldo de Coins
 * Funciones para manejar el saldo de coins de usuarios
 * 
 * @package CoinsSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtener saldo de coins de un usuario
 * Función wrapper para fácil acceso
 * 
 * @param int $user_id ID del usuario
 * @return float Saldo de coins
 */
function coins_get_balance($user_id) {
    $coins_manager = Coins_Manager::get_instance();
    return $coins_manager->get_coins($user_id);
}

/**
 * Establecer saldo de coins de un usuario
 * 
 * @param int $user_id ID del usuario
 * @param float $amount Nueva cantidad de coins
 * @return bool True si se estableció correctamente
 */
function coins_set_balance($user_id, $amount) {
    $coins_manager = Coins_Manager::get_instance();
    return $coins_manager->set_coins($user_id, $amount);
}

/**
 * Verificar si un usuario tiene suficientes coins
 * 
 * @param int $user_id ID del usuario
 * @param float $amount Cantidad necesaria
 * @return bool True si tiene suficientes coins
 */
function coins_has_sufficient_balance($user_id, $amount) {
    $coins_manager = Coins_Manager::get_instance();
    return $coins_manager->user_has_coins($user_id, $amount);
}

/**
 * Obtener ranking de usuarios por coins
 * 
 * @param int $limit Límite de resultados
 * @return array Array de usuarios con sus coins
 */
function coins_get_user_ranking($limit = 10) {
    $args = array(
        'meta_key' => '_user_coins',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'number' => $limit,
        'fields' => array('ID', 'display_name')
    );
    
    $users = get_users($args);
    $ranking = array();
    
    foreach ($users as $user) {
        $ranking[] = array(
            'user_id' => $user->ID,
            'display_name' => $user->display_name,
            'coins' => coins_get_balance($user->ID)
        );
    }
    
    return $ranking;
}

/**
 * Obtener total de coins en el sistema
 * 
 * @return float Total de coins
 */
function coins_get_total_in_system() {
    global $wpdb;
    
    $total = $wpdb->get_var(
        "SELECT SUM(meta_value) 
         FROM {$wpdb->usermeta} 
         WHERE meta_key = '_user_coins'"
    );
    
    return $total ? floatval($total) : 0;
}

/**
 * Resetear coins de un usuario (CUIDADO)
 * 
 * @param int $user_id ID del usuario
 * @param string $razon Razón del reset
 * @return bool True si se resetó correctamente
 */
function coins_reset_balance($user_id, $razon = 'Reset manual') {
    $saldo_anterior = coins_get_balance($user_id);
    
    if ($saldo_anterior <= 0) {
        return false;
    }
    
    // Registrar transacción de reset
    coins_add_transaction(
        $user_id,
        'reset',
        -$saldo_anterior,
        $saldo_anterior,
        0,
        $razon
    );
    
    // Establecer saldo en 0
    return coins_set_balance($user_id, 0);
}

/**
 * Transferir coins entre usuarios
 * 
 * @param int $from_user_id ID del usuario que envía
 * @param int $to_user_id ID del usuario que recibe
 * @param float $amount Cantidad a transferir
 * @param string $razon Razón de la transferencia
 * @return bool|WP_Error True si se transfirió correctamente
 */
function coins_transfer($from_user_id, $to_user_id, $amount, $razon = '') {
    $coins_manager = Coins_Manager::get_instance();
    
    // Validar IDs
    if ($from_user_id === $to_user_id) {
        return new WP_Error('same_user', 'No puedes transferir coins a ti mismo');
    }
    
    // Verificar que el remitente tenga suficientes coins
    if (!coins_has_sufficient_balance($from_user_id, $amount)) {
        return new WP_Error('insufficient_balance', 'No tienes suficientes coins');
    }
    
    // Restar al remitente
    $subtract_result = $coins_manager->subtract_coins(
        $from_user_id,
        $amount,
        'transferencia_enviada',
        'Transferencia a usuario #' . $to_user_id . ': ' . $razon
    );
    
    if (is_wp_error($subtract_result)) {
        return $subtract_result;
    }
    
    // Agregar al destinatario
    $add_result = $coins_manager->add_coins(
        $to_user_id,
        $amount,
        'transferencia_recibida',
        'Transferencia de usuario #' . $from_user_id . ': ' . $razon
    );
    
    return $add_result;
}

/**
 * Obtener usuarios sin coins
 * 
 * @return array Array de IDs de usuarios
 */
function coins_get_users_with_zero_balance() {
    $args = array(
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => '_user_coins',
                'value' => '0',
                'compare' => '='
            ),
            array(
                'key' => '_user_coins',
                'compare' => 'NOT EXISTS'
            )
        ),
        'fields' => 'ID'
    );
    
    return get_users($args);
}
?>