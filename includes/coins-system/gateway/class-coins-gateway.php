<?php
/**
 * Gateway de Pago con Coins para WooCommerce
 * Permite pagar productos con coins
 * 
 * @package CoinsSystem
 * @subpackage Gateway
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase WC_Gateway_Coins
 * Extiende WC_Payment_Gateway para crear una pasarela de pago con coins
 */
class WC_Gateway_Coins extends WC_Payment_Gateway {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'coins';
        $this->icon = COINS_URL . '/assets/coin-icon.png';
        $this->has_fields = false;
        $this->method_title = 'Pago con Coins';
        $this->method_description = 'Permite a los usuarios pagar con coins ganados en el sistema.';
        
        // Cargar configuración
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener valores de configuración
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
    }
    
    /**
     * Inicializar campos de configuración
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Habilitar/Deshabilitar',
                'type' => 'checkbox',
                'label' => 'Habilitar pago con Coins',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout.',
                'default' => 'Canjear con Coins',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout.',
                'default' => 'Canjea este curso usando tus coins acumulados.',
                'desc_tip' => true
            )
        );
    }
    
    /**
     * Verificar si el gateway está disponible
     */
    public function is_available() {
        // Gateway solo disponible si el usuario está logueado
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Verificar si el carrito tiene productos canjeables con coins
        if (!$this->cart_has_coin_products()) {
            return false;
        }
        
        // Verificar si el usuario tiene suficientes coins
        $coins_needed = $this->get_total_coins_needed();
        $user_coins = coins_get_balance(get_current_user_id());
        
        if ($user_coins < $coins_needed) {
            return false;
        }
        
        return parent::is_available();
    }
    
    /**
     * Verificar si el carrito tiene productos canjeables
     */
    private function cart_has_coin_products() {
        if (!WC()->cart) {
            return false;
        }
        
        $coins_manager = Coins_Manager::get_instance();
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if ($coins_manager->is_coin_redeemable($product_id)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Obtener total de coins necesarios
     */
    private function get_total_coins_needed() {
        if (!WC()->cart) {
            return 0;
        }
        
        $coins_manager = Coins_Manager::get_instance();
        $total_coins = 0;
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            
            $coins_per_product = $coins_manager->get_costo_coins_producto($product_id);
            $total_coins += $coins_per_product * $quantity;
        }
        
        return $total_coins;
    }
    
    /**
     * Mostrar descripción del método de pago
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }
        
        $coins_needed = $this->get_total_coins_needed();
        $user_coins = coins_get_balance(get_current_user_id());
        $coins_manager = Coins_Manager::get_instance();
        
        echo '<div class="coins-payment-info" style="padding: 15px; background: rgba(218, 4, 128, 0.1); border-radius: 8px; margin: 10px 0; border: 1px solid rgba(218, 4, 128, 0.3);">';
        echo '<p style="margin: 0 0 10px 0; color: #da0480; font-weight: 600;"><strong>🪙 Resumen de Coins:</strong></p>';
        echo '<p style="margin: 5px 0; font-size: 14px;">Coins necesarios: <strong>' . $coins_manager->format_coins($coins_needed) . '</strong></p>';
        echo '<p style="margin: 5px 0; font-size: 14px;">Tus coins: <strong>' . $coins_manager->format_coins($user_coins) . '</strong></p>';
        echo '<p style="margin: 10px 0 0 0; font-size: 14px; color: #6b7280;">Después del canje tendrás: <strong>' . $coins_manager->format_coins($user_coins - $coins_needed) . '</strong> coins</p>';
        echo '</div>';
    }
    
    /**
     * Procesar el pago
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) {
            wc_add_notice('Error: No se pudo identificar al usuario.', 'error');
            return array(
                'result' => 'failure'
            );
        }
        
        $coins_manager = Coins_Manager::get_instance();
        $coins_needed = 0;
        
        // Calcular coins necesarios para cada producto del pedido
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            
            $coins_per_product = $coins_manager->get_costo_coins_producto($product_id);
            $coins_needed += $coins_per_product * $quantity;
        }
        
        // Verificar que tenga suficientes coins
        if (!coins_has_sufficient_balance($user_id, $coins_needed)) {
            wc_add_notice('No tienes suficientes coins para completar esta compra.', 'error');
            return array(
                'result' => 'failure'
            );
        }
        
        // Restar coins
        $result = $coins_manager->subtract_coins(
            $user_id,
            $coins_needed,
            'canje',
            'Canje de producto: ' . $order->get_id(),
            $order_id
        );
        
        if (is_wp_error($result)) {
            wc_add_notice('Error al procesar el pago con coins: ' . $result->get_error_message(), 'error');
            return array(
                'result' => 'failure'
            );
        }
        
        // Marcar el pedido como completado
        $order->payment_complete();
        $order->add_order_note(
            sprintf(
                'Pago completado con %s coins. Saldo anterior: %s, Saldo nuevo: %s',
                $coins_manager->format_coins($coins_needed),
                $coins_manager->format_coins(coins_get_balance($user_id) + $coins_needed),
                $coins_manager->format_coins(coins_get_balance($user_id))
            )
        );
        
        // Reducir stock
        wc_reduce_stock_levels($order_id);
        
        // Vaciar carrito
        WC()->cart->empty_cart();
        
        // Redirigir a página de agradecimiento
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
    
    /**
     * Página de agradecimiento
     */
    public function thankyou_page($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) {
            return;
        }
        
        $coins_manager = Coins_Manager::get_instance();
        $current_balance = coins_get_balance($user_id);
        
        echo '<div class="woocommerce-order-coins-info" style="margin: 30px 0; padding: 25px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3);">';
        echo '<h2 style="color: #da0480; margin: 0 0 15px 0;">🎉 ¡Canje Exitoso!</h2>';
        echo '<p style="font-size: 15px; margin: 8px 0;">Has canjeado este curso con tus coins.</p>';
        echo '<p style="font-size: 15px; margin: 8px 0; color: #6b7280;">Saldo actual: <strong style="color: #da0480;">' . $coins_manager->format_coins($current_balance) . '</strong> coins</p>';
        echo '<p style="font-size: 14px; margin: 15px 0 0 0; color: #9ca3af;">✨ Recuerda que puedes ganar más coins comprando cursos premium y dejando reseñas.</p>';
        echo '</div>';
    }
}
?>