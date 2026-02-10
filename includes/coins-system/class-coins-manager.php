<?php
/**
 * Clase principal para gestionar coins de usuarios
 */

if (!defined('ABSPATH')) {
    exit;
}

class Coins_Manager {
    
    /**
     * Instancia singleton
     */
    private static $instance = null;
    
    /**
     * Meta key para almacenar coins
     */
    const META_KEY = 'user_coins';
    
    /**
     * Obtener instancia
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Constructor privado para singleton
    }
    
    /**
     * Obtener coins de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return float Cantidad de coins
     */
    public function get_coins($user_id) {
        if (!$user_id) {
            return 0;
        }
        
        $coins = get_user_meta($user_id, self::META_KEY, true);
        return $coins ? (float) $coins : 0;
    }
    
    /**
     * Agregar coins a un usuario
     * 
     * @param int $user_id ID del usuario
     * @param float $cantidad Cantidad a agregar
     * @param string $descripcion Descripción de la transacción
     * @param int $order_id ID de la orden relacionada
     * @return bool
     */
    public function agregar_coins($user_id, $cantidad, $descripcion = '', $order_id = null) {
        if (!$user_id || $cantidad <= 0) {
            return false;
        }
        
        $saldo_anterior = $this->get_coins($user_id);
        $saldo_nuevo = $saldo_anterior + $cantidad;
        
        // Actualizar meta
        update_user_meta($user_id, self::META_KEY, $saldo_nuevo);
        
        // Registrar en historial
        $this->registrar_historial($user_id, 'suma', $cantidad, $saldo_anterior, $saldo_nuevo, $descripcion, $order_id);
        
        // Hook para otras acciones
        do_action('coins_agregados', $user_id, $cantidad, $saldo_nuevo);
        
        return true;
    }
    
    /**
     * Descontar coins de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param float $cantidad Cantidad a descontar
     * @param string $descripcion Descripción de la transacción
     * @param int $order_id ID de la orden relacionada
     * @return bool
     */
    public function descontar_coins($user_id, $cantidad, $descripcion = '', $order_id = null) {
        if (!$user_id || $cantidad <= 0) {
            return false;
        }
        
        $saldo_anterior = $this->get_coins($user_id);
        
        // Verificar que tenga suficientes coins
        if ($saldo_anterior < $cantidad) {
            return false;
        }
        
        $saldo_nuevo = $saldo_anterior - $cantidad;
        
        // Actualizar meta
        update_user_meta($user_id, self::META_KEY, $saldo_nuevo);
        
        // Registrar en historial
        $this->registrar_historial($user_id, 'resta', $cantidad, $saldo_anterior, $saldo_nuevo, $descripcion, $order_id);
        
        // Hook para otras acciones
        do_action('coins_descontados', $user_id, $cantidad, $saldo_nuevo);
        
        return true;
    }
    
    /**
     * Verificar si un usuario tiene suficientes coins
     * 
     * @param int $user_id ID del usuario
     * @param float $cantidad Cantidad requerida
     * @return bool
     */
    public function tiene_coins($user_id, $cantidad) {
        return $this->get_coins($user_id) >= $cantidad;
    }
    
    /**
     * Registrar transacción en el historial
     */
    private function registrar_historial($user_id, $tipo, $cantidad, $saldo_anterior, $saldo_nuevo, $descripcion, $order_id) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'coins_historial',
            array(
                'user_id' => $user_id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'saldo_anterior' => $saldo_anterior,
                'saldo_nuevo' => $saldo_nuevo,
                'descripcion' => $descripcion,
                'order_id' => $order_id,
                'fecha' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%f', '%f', '%s', '%d', '%s')
        );
    }
    
    /**
     * Obtener historial de coins de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_historial($user_id, $limit = 20) {
        global $wpdb;
        
        $tabla = $wpdb->prefix . 'coins_historial';
        
        $resultados = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $tabla WHERE user_id = %d ORDER BY fecha DESC LIMIT %d",
            $user_id,
            $limit
        ));
        
        return $resultados;
    }
    
    /**
     * Calcular coins que otorga un producto premium
     * 
     * @param int $product_id ID del producto
     * @return int Cantidad de coins (siempre 1 para premium)
     */
    public function get_coins_producto($product_id) {
        // Productos premium otorgan 1 coin
        if (has_term('premium', 'product_cat', $product_id)) {
            return apply_filters('coins_por_producto_premium', 1, $product_id);
        }
        
        return 0;
    }
    
    /**
     * Obtener costo en coins de un producto gratis
     * 
     * @param int $product_id ID del producto
     * @return float Costo en coins (default 1)
     */
    public function get_costo_coins_producto($product_id) {
        // Verificar si es un producto gratis
        if (!has_term('gratis', 'product_cat', $product_id)) {
            return 0;
        }
        
        // Obtener costo personalizado
        $costo = get_post_meta($product_id, '_coins_cost', true);
        
        // Si no tiene costo definido, default es 1
        return $costo ? (float) $costo : 1;
    }
    
    /**
     * Calcular coins necesarios para un carrito
     * 
     * @return float Total de coins necesarios
     */
    public function calcular_coins_carrito() {
        $total_coins = 0;
        
        if (!function_exists('WC') || !WC()->cart) {
            return 0;
        }
        
        foreach (WC()->cart->get_cart() as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Los cursos gratis cuestan X coins cada uno
            if (has_term('gratis', 'product_cat', $product_id)) {
                $costo_unitario = $this->get_costo_coins_producto($product_id);
                $total_coins += ($costo_unitario * $quantity);
            }
        }
        
        return $total_coins;
    }
    
    /**
     * Formatear cantidad de coins para mostrar
     * 
     * @param float $cantidad
     * @return string
     */
    public function format_coins($cantidad) {
        // Si es un número entero, no mostrar decimales
        if (floor($cantidad) == $cantidad) {
            return number_format($cantidad, 0, ',', '.');
        }
        
        // Si tiene decimales, mostrar 1 decimal
        return number_format($cantidad, 1, ',', '.');
    }
}

// Función helper para acceder al manager
function coins_manager() {
    return Coins_Manager::get_instance();
}
