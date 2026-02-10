<?php
/**
 * Loader del Sistema de Coins
 * Carga todos los módulos del sistema de monedas virtuales
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del sistema de coins
if (!defined('COINS_PATH')) {
    define('COINS_PATH', get_stylesheet_directory() . '/includes/coins-system/');
}

/**
 * Cargar archivos del sistema de coins en orden
 */
function cargar_sistema_coins() {
    // 1. Base de datos (tablas)
    if (file_exists(COINS_PATH . 'database/tables.php')) {
        require_once COINS_PATH . 'database/tables.php';
    }
    
    // 2. Funciones core
    if (file_exists(COINS_PATH . 'core/coins-manager.php')) {
        require_once COINS_PATH . 'core/coins-manager.php';
    }
    
    if (file_exists(COINS_PATH . 'core/balance.php')) {
        require_once COINS_PATH . 'core/balance.php';
    }
    
    if (file_exists(COINS_PATH . 'core/transactions.php')) {
        require_once COINS_PATH . 'core/transactions.php';
    }
    
    // 3. Sistema de recompensas
    if (file_exists(COINS_PATH . 'rewards/purchases.php')) {
        require_once COINS_PATH . 'rewards/purchases.php';
    }
    
    if (file_exists(COINS_PATH . 'rewards/reviews.php')) {
        require_once COINS_PATH . 'rewards/reviews.php';
    }
    
    if (file_exists(COINS_PATH . 'rewards/social-shares.php')) {
        require_once COINS_PATH . 'rewards/social-shares.php';
    }
    
    // 4. Sistema de canje
    if (file_exists(COINS_PATH . 'redemption/canje.php')) {
        require_once COINS_PATH . 'redemption/canje.php';
    }
    
    if (file_exists(COINS_PATH . 'redemption/validation.php')) {
        require_once COINS_PATH . 'redemption/validation.php';
    }
    
    // 5. Pasarela de pago (WooCommerce Gateway)
    if (file_exists(COINS_PATH . 'payment-gateway/gateway.php')) {
        require_once COINS_PATH . 'payment-gateway/gateway.php';
    }
    
    // 6. Panel de administración
    if (file_exists(COINS_PATH . 'admin/metabox.php')) {
        require_once COINS_PATH . 'admin/metabox.php';
    }
    
    if (file_exists(COINS_PATH . 'admin/columns.php')) {
        require_once COINS_PATH . 'admin/columns.php';
    }
    
    if (file_exists(COINS_PATH . 'admin/settings.php')) {
        require_once COINS_PATH . 'admin/settings.php';
    }
    
    // 7. Frontend (visualización)
    if (file_exists(COINS_PATH . 'frontend/display.php')) {
        require_once COINS_PATH . 'frontend/display.php';
    }
    
    if (file_exists(COINS_PATH . 'frontend/widgets.php')) {
        require_once COINS_PATH . 'frontend/widgets.php';
    }
    
    if (file_exists(COINS_PATH . 'frontend/ajax-handlers.php')) {
        require_once COINS_PATH . 'frontend/ajax-handlers.php';
    }
    
    if (file_exists(COINS_PATH . 'frontend/modal.php')) {
        require_once COINS_PATH . 'frontend/modal.php';
    }
    
    // 8. Integración con otros sistemas
    if (file_exists(COINS_PATH . 'integration/woocommerce.php')) {
        require_once COINS_PATH . 'integration/woocommerce.php';
    }
    
    // 9. API y webhooks
    if (file_exists(COINS_PATH . 'api/endpoints.php')) {
        require_once COINS_PATH . 'api/endpoints.php';
    }
}

// Cargar sistema de coins antes de todo
add_action('init', 'cargar_sistema_coins', 0);

/**
 * Registrar gateway de coins en WooCommerce
 */
function registrar_gateway_coins($gateways) {
    if (class_exists('WC_Gateway_Coins')) {
        $gateways[] = 'WC_Gateway_Coins';
    }
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'registrar_gateway_coins');
?>