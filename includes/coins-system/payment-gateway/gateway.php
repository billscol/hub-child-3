<?php
/**
 * Pasarela de Pago con Coins para WooCommerce
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase de la pasarela de pago con Coins
 */
class WC_Gateway_Coins extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'coins';
        $this->method_title = 'Coins';
        $this->method_description = 'Permite a los usuarios pagar con coins';
        $this->has_fields = false;
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Activar/Desactivar',
                'type' => 'checkbox',
                'label' => 'Activar pago con Coins',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que verá el usuario en el checkout',
                'default' => 'Pagar con Coins',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción del método de pago',
                'default' => 'Usa tus coins para pagar este curso'
            )
        );
    }
    
    public function is_available() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Verificar si hay productos canjeables en el carrito
        $cart = WC()->cart;
        $tiene_canjeables = false;
        
        foreach ($cart->get_cart() as $cart_item) {
            if (producto_es_canjeable_coins($cart_item['product_id'])) {
                $tiene_canjeables = true;
                break;
            }
        }
        
        if (!$tiene_canjeables) {
            return false;
        }
        
        // Verificar saldo suficiente
        $user_id = get_current_user_id();
        $total_coins_necesarios = $this->calcular_coins_necesarios();
        
        return coins_manager()->has_sufficient_balance($user_id, $total_coins_necesarios);
    }
    
    private function calcular_coins_necesarios() {
        $total = 0;
        $cart = WC()->cart;
        
        foreach ($cart->get_cart() as $cart_item) {
            if (producto_es_canjeable_coins($cart_item['product_id'])) {
                $coins = get_coins_requeridos_producto($cart_item['product_id']);
                $total += $coins * $cart_item['quantity'];
            }
        }
        
        return $total;
    }
    
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        $total_coins = $this->calcular_coins_necesarios();
        
        // Verificar saldo
        if (!coins_manager()->has_sufficient_balance($user_id, $total_coins)) {
            wc_add_notice('Saldo de coins insuficiente', 'error');
            return array('result' => 'failure');
        }
        
        // Restar coins
        if (coins_manager()->subtract_coins(
            $user_id,
            $total_coins,
            'Pago de pedido #' . $order_id,
            $order_id
        )) {
            // Marcar pedido como completado
            $order->payment_complete();
            $order->update_meta_data('_paid_with_coins', $total_coins);
            $order->save();
            
            // Vaciar carrito
            WC()->cart->empty_cart();
            
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }
        
        wc_add_notice('Error al procesar el pago con coins', 'error');
        return array('result' => 'failure');
    }
}
?>