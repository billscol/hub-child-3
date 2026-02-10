<?php
/**
 * Módulo: Personalización de Checkout WooCommerce
 * 
 * archivo index.php
 * /home/bills/dominios/cursobarato/wp-content/themes/hub-child/includes/checkout-customization/index.php
 * Este archivo carga todos los componentes del módulo de checkout
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    return;
}

// Definir constante del módulo
define('CHECKOUT_MODULE_DIR', CHILD_THEME_DIR . '/includes/checkout-customization');

/**
 * Cargar componentes del módulo
 */

// 1. Estilos personalizados (CSS)
require_once CHECKOUT_MODULE_DIR . '/styles.php';

// 2. Scripts personalizados (JavaScript)
require_once CHECKOUT_MODULE_DIR . '/scripts.php';

// 3. Configuración de campos
require_once CHECKOUT_MODULE_DIR . '/fields.php';

// 4. Validaciones del servidor
require_once CHECKOUT_MODULE_DIR . '/validation.php';

// 5. Compatibilidad de métodos de pago con bloques 
require_once CHECKOUT_MODULE_DIR . '/payment-compatibility.php';

/**
 * Log de módulo cargado (solo para debug)
 * Comentar en producción
 */
// error_log('✅ Módulo Checkout Customization cargado correctamente');
