<?php
/**
 * Loader del Sistema de Coins
 * Carga todos los módulos del sistema de monedas virtuales
 * 
 * @package CoinsSystem
 * @version 2.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    return;
}

/**
 * Definir constantes del sistema de coins
 */
if (!defined('COINS_VERSION')) {
    define('COINS_VERSION', '2.0.0');
}

if (!defined('COINS_PATH')) {
    define('COINS_PATH', get_stylesheet_directory() . '/includes/coins-system');
}

if (!defined('COINS_URL')) {
    define('COINS_URL', get_stylesheet_directory_uri() . '/includes/coins-system');
}

/**
 * Cargar archivos del sistema
 */

// 1. Database - Tablas de base de datos
if (file_exists(COINS_PATH . '/database/tables.php')) {
    require_once COINS_PATH . '/database/tables.php';
}

// 2. Core - Funcionalidades centrales
if (file_exists(COINS_PATH . '/core/class-coins-manager.php')) {
    require_once COINS_PATH . '/core/class-coins-manager.php';
}

if (file_exists(COINS_PATH . '/core/balance.php')) {
    require_once COINS_PATH . '/core/balance.php';
}

if (file_exists(COINS_PATH . '/core/transactions.php')) {
    require_once COINS_PATH . '/core/transactions.php';
}

// 3. Gateway - Pasarela de pago
if (file_exists(COINS_PATH . '/gateway/class-coins-gateway.php')) {
    require_once COINS_PATH . '/gateway/class-coins-gateway.php';
}

// 4. Rewards - Sistema de recompensas
if (file_exists(COINS_PATH . '/rewards/purchases.php')) {
    require_once COINS_PATH . '/rewards/purchases.php';
}

if (file_exists(COINS_PATH . '/rewards/reviews.php')) {
    require_once COINS_PATH . '/rewards/reviews.php';
}

if (file_exists(COINS_PATH . '/rewards/social-shares.php')) {
    require_once COINS_PATH . '/rewards/social-shares.php';
}

// 5. Admin - Funcionalidades del backend
if (is_admin()) {
    if (file_exists(COINS_PATH . '/admin/metabox.php')) {
        require_once COINS_PATH . '/admin/metabox.php';
    }
    
    if (file_exists(COINS_PATH . '/admin/columns.php')) {
        require_once COINS_PATH . '/admin/columns.php';
    }
}

// 6. Frontend - Funcionalidades del frontend
if (file_exists(COINS_PATH . '/frontend/display.php')) {
    require_once COINS_PATH . '/frontend/display.php';
}

if (file_exists(COINS_PATH . '/frontend/modal.php')) {
    require_once COINS_PATH . '/frontend/modal.php';
}

if (file_exists(COINS_PATH . '/frontend/user-dropdown.php')) {
    require_once COINS_PATH . '/frontend/user-dropdown.php';
}

// 7. Integration - Integraciones con WooCommerce
if (file_exists(COINS_PATH . '/integration/woocommerce-hooks.php')) {
    require_once COINS_PATH . '/integration/woocommerce-hooks.php';
}

/**
 * Inicializar el sistema de coins
 */
function coins_system_init() {
    // Registrar el gateway de coins en WooCommerce
    add_filter('woocommerce_payment_gateways', 'coins_register_gateway');
}
add_action('plugins_loaded', 'coins_system_init');

/**
 * Registrar gateway de coins
 */
function coins_register_gateway($gateways) {
    if (class_exists('WC_Gateway_Coins')) {
        $gateways[] = 'WC_Gateway_Coins';
    }
    return $gateways;
}
?>