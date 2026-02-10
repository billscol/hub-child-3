<?php
/**
 * Gateway de pago con coins
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Coins extends WC_Payment_Gateway {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'coins';
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = 'Pagar con Coins';
        $this->method_description = 'Permite a los usuarios pagar con coins acumulados';
        
        // Cargar configuración
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        // Guardar configuración
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }
    
    /**
     * Campos de configuración del gateway
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Activar/Desactivar',
                'type' => 'checkbox',
                'label' => 'Habilitar pago con coins',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que ve el usuario al pagar',
                'default' => 'Pagar con Coins',
                'desc_tip' => true
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción del método de pago',
                'default' => 'Usa tus coins acumulados para obtener cursos gratis',
                'desc_tip' => true
            )
        );
    }
    
    /**
     * Verificar si el gateway está disponible
     */
    public function is_available() {
        // Solo disponible si el usuario está logueado
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Verificar que el carrito exista
        if (!function_exists('WC') || !WC()->cart) {
            return false;
        }
        
        // Solo disponible si hay cursos gratis en el carrito
        $tiene_gratis = false;
        $tiene_premium = false;
        
        foreach (WC()->cart->get_cart() as $item) {
            $product_id = $item['product_id'];
            
            if (has_term('gratis', 'product_cat', $product_id)) {
                $tiene_gratis = true;
            } else {
                $tiene_premium = true;
            }
        }
        
        // Solo mostrar si SOLO hay cursos gratis
        if (!$tiene_gratis || $tiene_premium) {
            return false;
        }
        
        // Verificar que tenga coins suficientes
        $coins_necesarios = coins_manager()->calcular_coins_carrito();
        $coins_usuario = coins_manager()->get_coins(get_current_user_id());
        
        if ($coins_usuario < $coins_necesarios) {
            return false;
        }
        
        return parent::is_available();
    }
    
    /**
     * Descripción con información de coins
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wptautop($this->description));
        }
        
        $user_id = get_current_user_id();
        $coins_disponibles = coins_manager()->get_coins($user_id);
        $coins_necesarios = coins_manager()->calcular_coins_carrito();
        $coins_restantes = $coins_disponibles - $coins_necesarios;
        
        echo '<div class="coins-info" style="background: #f7f7f7; padding: 15px; border-radius: 5px; margin-top: 10px;">';
        echo '<p style="margin: 5px 0;"><strong>🪙 Coins disponibles:</strong> ' . coins_manager()->format_coins($coins_disponibles) . '</p>';
        echo '<p style="margin: 5px 0;"><strong>💰 Coins necesarios:</strong> ' . coins_manager()->format_coins($coins_necesarios) . '</p>';
        echo '<p style="margin: 5px 0;"><strong>📊 Coins restantes:</strong> ' . coins_manager()->format_coins($coins_restantes) . '</p>';
        echo '</div>';
    }
    
    /**
     * Procesar el pago
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        // Calcular coins necesarios
        $coins_necesarios = 0;
        $productos = array();
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_name = $item->get_name();
            
            if (has_term('gratis', 'product_cat', $product_id)) {
                $cantidad = $item->get_quantity();
                $costo_unitario = coins_manager()->get_costo_coins_producto($product_id);
                $costo_total = $costo_unitario * $cantidad;
                
                $coins_necesarios += $costo_total;
                $productos[] = $product_name . ' (x' . $cantidad . ' = ' . coins_manager()->format_coins($costo_total) . ' coins)';
            }
        }
        
        // Verificar y descontar coins
        if (coins_manager()->tiene_coins($user_id, $coins_necesarios)) {
            $descripcion = 'Canje de cursos: ' . implode(', ', $productos);
            
            if (coins_manager()->descontar_coins($user_id, $coins_necesarios, $descripcion, $order_id)) {
                // Marcar orden como completada
                $order->payment_complete();
                $order->add_order_note(sprintf(
                    'Pagado con %s coins. Cursos canjeados: %s',
                    coins_manager()->format_coins($coins_necesarios),
                    implode(', ', $productos)
                ));
                
                // Reducir stock si aplica
                wc_reduce_stock_levels($order_id);
                
                // Vaciar carrito
                WC()->cart->empty_cart();
                
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            }
        }
        
        // Error: no hay suficientes coins
        wc_add_notice('No tienes suficientes coins para completar esta compra', 'error');
        
        return array(
            'result' => 'fail',
            'redirect' => ''
        );
    }
}
