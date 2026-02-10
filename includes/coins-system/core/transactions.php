<?php
/**
 * Historial de Transacciones
 * Funciones para registrar y consultar transacciones de coins
 * 
 * @package CoinsSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar transacción al historial
 * 
 * @param int $user_id ID del usuario
 * @param string $tipo Tipo de transacción
 * @param float $cantidad Cantidad (positivo = suma, negativo = resta)
 * @param float $saldo_anterior Saldo antes de la transacción
 * @param float $saldo_nuevo Saldo después de la transacción
 * @param string $descripcion Descripción de la transacción
 * @param int $order_id ID del pedido (opcional)
 * @return int|false ID de la transacción o false si falló
 */
function coins_add_transaction($user_id, $tipo, $cantidad, $saldo_anterior, $saldo_nuevo, $descripcion = '', $order_id = null) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $data = array(
        'user_id' => absint($user_id),
        'tipo' => sanitize_text_field($tipo),
        'cantidad' => floatval($cantidad),
        'saldo_anterior' => floatval($saldo_anterior),
        'saldo_nuevo' => floatval($saldo_nuevo),
        'descripcion' => sanitize_text_field($descripcion),
        'order_id' => $order_id ? absint($order_id) : null,
        'fecha' => current_time('mysql')
    );
    
    $format = array('%d', '%s', '%f', '%f', '%f', '%s', '%d', '%s');
    
    $result = $wpdb->insert($table, $data, $format);
    
    if ($result) {
        return $wpdb->insert_id;
    }
    
    return false;
}

/**
 * Obtener transacciones de un usuario
 * 
 * @param int $user_id ID del usuario
 * @param int $limit Límite de resultados
 * @param int $offset Offset para paginación
 * @return array Array de transacciones
 */
function coins_get_user_transactions($user_id, $limit = 50, $offset = 0) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table 
         WHERE user_id = %d 
         ORDER BY fecha DESC 
         LIMIT %d OFFSET %d",
        $user_id,
        $limit,
        $offset
    );
    
    return $wpdb->get_results($query);
}

/**
 * Contar transacciones de un usuario
 * 
 * @param int $user_id ID del usuario
 * @return int Número de transacciones
 */
function coins_count_transactions($user_id) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d",
            $user_id
        )
    );
}

/**
 * Obtener total ganado por un usuario
 * 
 * @param int $user_id ID del usuario
 * @return float Total ganado
 */
function coins_get_total_earned($user_id) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $total = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(cantidad) FROM $table 
             WHERE user_id = %d AND cantidad > 0",
            $user_id
        )
    );
    
    return $total ? floatval($total) : 0;
}

/**
 * Obtener total gastado por un usuario
 * 
 * @param int $user_id ID del usuario
 * @return float Total gastado (valor absoluto)
 */
function coins_get_total_spent($user_id) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $total = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(ABS(cantidad)) FROM $table 
             WHERE user_id = %d AND cantidad < 0",
            $user_id
        )
    );
    
    return $total ? floatval($total) : 0;
}

/**
 * Obtener transacciones por tipo
 * 
 * @param int $user_id ID del usuario
 * @param string $tipo Tipo de transacción
 * @param int $limit Límite de resultados
 * @return array Array de transacciones
 */
function coins_get_transactions_by_type($user_id, $tipo, $limit = 50) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table 
         WHERE user_id = %d AND tipo = %s 
         ORDER BY fecha DESC 
         LIMIT %d",
        $user_id,
        $tipo,
        $limit
    );
    
    return $wpdb->get_results($query);
}

/**
 * Obtener transacciones recientes del sistema
 * 
 * @param int $limit Límite de resultados
 * @return array Array de transacciones
 */
function coins_get_recent_transactions($limit = 20) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table 
         ORDER BY fecha DESC 
         LIMIT %d",
        $limit
    );
    
    return $wpdb->get_results($query);
}

/**
 * Eliminar transacciones antiguas
 * 
 * @param int $days Días de antigüedad
 * @return int Número de transacciones eliminadas
 */
function coins_delete_old_transactions($days = 365) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $date = date('Y-m-d H:i:s', strtotime("-$days days"));
    
    return $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $table WHERE fecha < %s",
            $date
        )
    );
}

/**
 * Obtener estadísticas de transacciones por tipo
 * 
 * @param int $user_id ID del usuario
 * @return array Estadísticas por tipo
 */
function coins_get_transaction_stats_by_type($user_id) {
    global $wpdb;
    
    $table = coins_get_table_name('historial');
    
    $query = $wpdb->prepare(
        "SELECT 
            tipo,
            COUNT(*) as cantidad_transacciones,
            SUM(cantidad) as total_coins
         FROM $table 
         WHERE user_id = %d 
         GROUP BY tipo",
        $user_id
    );
    
    return $wpdb->get_results($query);
}

/**
 * Exportar historial de un usuario a CSV
 * 
 * @param int $user_id ID del usuario
 * @return string Contenido CSV
 */
function coins_export_transactions_csv($user_id) {
    $transactions = coins_get_user_transactions($user_id, 9999);
    
    $csv = "Fecha,Tipo,Cantidad,Saldo Anterior,Saldo Nuevo,Descripci\u00f3n\n";
    
    foreach ($transactions as $t) {
        $csv .= sprintf(
            "%s,%s,%s,%s,%s,\"%s\"\n",
            $t->fecha,
            $t->tipo,
            $t->cantidad,
            $t->saldo_anterior,
            $t->saldo_nuevo,
            str_replace('"', '""', $t->descripcion)
        );
    }
    
    return $csv;
}
?>