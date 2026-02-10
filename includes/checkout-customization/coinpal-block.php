<?php
/**
 * Coinpal - Soporte de Bloques
 * 
 * Wrapper para hacer que Coinpal funcione con bloques
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Child_Theme_Coinpal_Blocks extends AbstractPaymentMethodType {
    
    protected $name = 'coinpal';
    private $gateway;
    
    public function initialize() {
        $this->settings = get_option('woocommerce_coinpal_settings', []);
        $gateways = WC()->payment_gateways()->payment_gateways();
        $this->gateway = isset($gateways['coinpal']) ? $gateways['coinpal'] : null;
    }
    
    public function is_active() {
        return !is_null($this->gateway) && $this->gateway->is_available();
    }
    
    public function get_payment_method_script_handles() {
        return [];
    }
    
    public function get_payment_method_data() {
        if (!$this->gateway) {
            return [];
        }
        
        return [
            'title' => $this->gateway->get_title(),
            'description' => $this->gateway->get_description(),
            'icon' => $this->gateway->get_icon(),
            'supports' => [
                'products',
            ],
        ];
    }
}
