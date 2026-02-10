<?php
/**
 * PayPal Express Checkout - Soporte de Bloques
 * 
 * Wrapper para hacer que PayPal Express funcione con bloques
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Child_Theme_PayPal_Express_Blocks extends AbstractPaymentMethodType {
    
    protected $name = 'eh_paypal_express';
    private $gateway;
    
    public function initialize() {
        $this->settings = get_option('woocommerce_eh_paypal_express_settings', []);
        $gateways = WC()->payment_gateways()->payment_gateways();
        $this->gateway = isset($gateways['eh_paypal_express']) ? $gateways['eh_paypal_express'] : null;
    }
    
    public function is_active() {
        return !is_null($this->gateway) && $this->gateway->is_available();
    }
    
    public function get_payment_method_script_handles() {
        // No necesitamos scripts adicionales
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
