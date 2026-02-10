<?php
/**
 * Clase Principal del Sistema de Coins
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Coins_Manager
 * Gestiona todas las operaciones del sistema de coins
 */
class Coins_Manager {
    
    private static $instance = null;
    
    /**
     * Singleton - Obtener instancia única
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Inicialización
    }
    
    /**
     * Obtener saldo de coins de un usuario
     */
    public function get_balance($user_id) {
        if (!$user_id) {
            return 0;
        }
        
        $balance = get_user_meta($user_id, '_coins_balance', true);
        return $balance ? floatval($balance) : 0;
    }
    
    /**
     * Agregar coins al saldo de un usuario
     */
    public function add_coins($user_id, $cantidad, $descripcion = '', $order_id = null) {
        if (!$user_id || $cantidad <= 0) {
            return false;
        }
        
        global $wpdb;
        
        $saldo_anterior = $this->get_balance($user_id);
        $saldo_nuevo = $saldo_anterior + floatval($cantidad);
        
        // Actualizar saldo
        update_user_meta($user_id, '_coins_balance', $saldo_nuevo);
        
        // Registrar en historial
        $tabla_historial = $wpdb->prefix . 'coins_historial';
        $wpdb->insert(
            $tabla_historial,
            array(
                'user_id' => $user_id,
                'tipo' => 'ganado',
                'cantidad' => $cantidad,
                'saldo_anterior' => $saldo_anterior,
                'saldo_nuevo' => $saldo_nuevo,
                'descripcion' => $descripcion,
                'order_id' => $order_id,
                'fecha' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%f', '%f', '%s', '%d', '%s')
        );
        
        // Hook para extensiones
        do_action('coins_added', $user_id, $cantidad, $saldo_nuevo, $descripcion);
        
        return true;
    }
    
    /**
     * Restar coins del saldo de un usuario
     */
    public function subtract_coins($user_id, $cantidad, $descripcion = '', $order_id = null) {
        if (!$user_id || $cantidad <= 0) {
            return false;
        }
        
        global $wpdb;
        
        $saldo_anterior = $this->get_balance($user_id);
        
        // Verificar que tenga suficiente saldo
        if ($saldo_anterior < $cantidad) {
            return false;
        }
        
        $saldo_nuevo = $saldo_anterior - floatval($cantidad);
        
        // Actualizar saldo
        update_user_meta($user_id, '_coins_balance', $saldo_nuevo);
        
        // Registrar en historial
        $tabla_historial = $wpdb->prefix . 'coins_historial';
        $wpdb->insert(
            $tabla_historial,
            array(
                'user_id' => $user_id,
                'tipo' => 'gastado',
                'cantidad' => $cantidad,
                'saldo_anterior' => $saldo_anterior,
                'saldo_nuevo' => $saldo_nuevo,
                'descripcion' => $descripcion,
                'order_id' => $order_id,
                'fecha' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%f', '%f', '%s', '%d', '%s')
        );
        
        // Hook para extensiones
        do_action('coins_subtracted', $user_id, $cantidad, $saldo_nuevo, $descripcion);
        
        return true;
    }
    
    /**
     * Verificar si el usuario tiene suficiente saldo
     */
    public function has_sufficient_balance($user_id, $cantidad_requerida) {
        $saldo = $this->get_balance($user_id);
        return $saldo >= $cantidad_requerida;
    }
    
    /**
     * Obtener historial de transacciones
     */
    public function get_transaction_history($user_id, $limit = 10, $offset = 0) {
        global $wpdb;
        
        $tabla_historial = $wpdb->prefix . 'coins_historial';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $tabla_historial 
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
     * Obtener total de coins en circulación
     */
    public function get_total_coins_in_circulation() {
        global $wpdb;
        
        $query = "SELECT SUM(meta_value) as total 
                 FROM {$wpdb->usermeta} 
                 WHERE meta_key = '_coins_balance'";
        
        $result = $wpdb->get_var($query);
        return $result ? floatval($result) : 0;
    }
    
    /**
     * Obtener estadísticas generales
     */
    public function get_statistics() {
        global $wpdb;
        
        $tabla_historial = $wpdb->prefix . 'coins_historial';
        
        return array(
            'total_circulation' => $this->get_total_coins_in_circulation(),
            'total_earned' => $wpdb->get_var("SELECT SUM(cantidad) FROM $tabla_historial WHERE tipo = 'ganado'"),
            'total_spent' => $wpdb->get_var("SELECT SUM(cantidad) FROM $tabla_historial WHERE tipo = 'gastado'"),
            'total_transactions' => $wpdb->get_var("SELECT COUNT(*) FROM $tabla_historial"),
            'active_users' => $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = '_coins_balance' AND meta_value > 0")
        );
    }
}

/**
 * Función helper para acceder al manager
 */
function coins_manager() {
    return Coins_Manager::get_instance();
}
?>