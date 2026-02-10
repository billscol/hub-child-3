<?php
/**
 * Clase Principal del Sistema de Coins
 * Maneja todas las operaciones centrales de coins
 * 
 * @package CoinsSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Coins_Manager
 * Singleton para gestionar coins
 */
class Coins_Manager {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Versión
     */
    const VERSION = '2.0.0';
    
    /**
     * Meta key para almacenar coins
     */
    const META_KEY = '_user_coins';
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Constructor vacío
    }
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener coins de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return float Cantidad de coins
     */
    public function get_coins($user_id) {
        $coins = get_user_meta($user_id, self::META_KEY, true);
        return $coins ? floatval($coins) : 0;
    }
    
    /**
     * Establecer coins de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param float $amount Cantidad de coins
     * @return bool True si se guardó correctamente
     */
    public function set_coins($user_id, $amount) {
        $amount = floatval($amount);
        if ($amount < 0) {
            $amount = 0;
        }
        return update_user_meta($user_id, self::META_KEY, $amount);
    }
    
    /**
     * Agregar coins a un usuario
     * 
     * @param int $user_id ID del usuario
     * @param float $amount Cantidad a agregar
     * @param string $tipo Tipo de transacción
     * @param string $descripcion Descripción
     * @param int $order_id ID del pedido (opcional)
     * @return bool True si se agregó correctamente
     */
    public function add_coins($user_id, $amount, $tipo = 'manual', $descripcion = '', $order_id = null) {
        $amount = floatval($amount);
        
        if ($amount <= 0) {
            return false;
        }
        
        $saldo_anterior = $this->get_coins($user_id);
        $saldo_nuevo = $saldo_anterior + $amount;
        
        // Actualizar saldo
        $this->set_coins($user_id, $saldo_nuevo);
        
        // Registrar en historial
        coins_add_transaction(
            $user_id,
            $tipo,
            $amount,
            $saldo_anterior,
            $saldo_nuevo,
            $descripcion,
            $order_id
        );
        
        // Hook después de agregar coins
        do_action('coins_added', $user_id, $amount, $tipo, $saldo_nuevo);
        
        return true;
    }
    
    /**
     * Restar coins a un usuario
     * 
     * @param int $user_id ID del usuario
     * @param float $amount Cantidad a restar
     * @param string $tipo Tipo de transacción
     * @param string $descripcion Descripción
     * @param int $order_id ID del pedido (opcional)
     * @return bool|WP_Error True si se restó correctamente, WP_Error si no tiene suficientes coins
     */
    public function subtract_coins($user_id, $amount, $tipo = 'manual', $descripcion = '', $order_id = null) {
        $amount = floatval($amount);
        
        if ($amount <= 0) {
            return new WP_Error('invalid_amount', 'La cantidad debe ser mayor a 0');
        }
        
        $saldo_anterior = $this->get_coins($user_id);
        
        // Verificar que tenga suficientes coins
        if ($saldo_anterior < $amount) {
            return new WP_Error('insufficient_coins', 'No tiene suficientes coins');
        }
        
        $saldo_nuevo = $saldo_anterior - $amount;
        
        // Actualizar saldo
        $this->set_coins($user_id, $saldo_nuevo);
        
        // Registrar en historial
        coins_add_transaction(
            $user_id,
            $tipo,
            -$amount, // Negativo para indicar resta
            $saldo_anterior,
            $saldo_nuevo,
            $descripcion,
            $order_id
        );
        
        // Hook después de restar coins
        do_action('coins_subtracted', $user_id, $amount, $tipo, $saldo_nuevo);
        
        return true;
    }
    
    /**
     * Verificar si un usuario tiene suficientes coins
     * 
     * @param int $user_id ID del usuario
     * @param float $amount Cantidad necesaria
     * @return bool True si tiene suficientes coins
     */
    public function user_has_coins($user_id, $amount) {
        $user_coins = $this->get_coins($user_id);
        return $user_coins >= floatval($amount);
    }
    
    /**
     * Obtener costo en coins de un producto
     * 
     * @param int $product_id ID del producto
     * @return float Costo en coins
     */
    public function get_costo_coins_producto($product_id) {
        $costo = get_post_meta($product_id, '_costo_coins', true);
        return $costo ? floatval($costo) : 0;
    }
    
    /**
     * Establecer costo en coins de un producto
     * 
     * @param int $product_id ID del producto
     * @param float $costo Costo en coins
     * @return bool True si se guardó correctamente
     */
    public function set_costo_coins_producto($product_id, $costo) {
        $costo = floatval($costo);
        if ($costo < 0) {
            $costo = 0;
        }
        return update_post_meta($product_id, '_costo_coins', $costo);
    }
    
    /**
     * Formatear cantidad de coins para mostrar
     * 
     * @param float $amount Cantidad de coins
     * @return string Cantidad formateada
     */
    public function format_coins($amount) {
        return number_format(floatval($amount), 0, ',', '.');
    }
    
    /**
     * Verificar si un producto puede canjearse con coins
     * 
     * @param int $product_id ID del producto
     * @return bool True si puede canjearse
     */
    public function is_coin_redeemable($product_id) {
        $costo = $this->get_costo_coins_producto($product_id);
        return $costo > 0;
    }
    
    /**
     * Obtener estadísticas de coins
     * 
     * @param int $user_id ID del usuario
     * @return array Estadísticas
     */
    public function get_stats($user_id) {
        $stats = array(
            'saldo_actual' => $this->get_coins($user_id),
            'total_ganado' => coins_get_total_earned($user_id),
            'total_gastado' => coins_get_total_spent($user_id),
            'transacciones' => coins_count_transactions($user_id)
        );
        
        return $stats;
    }
}

/**
 * Función auxiliar para obtener la instancia
 */
function coins_manager() {
    return Coins_Manager::get_instance();
}
?>