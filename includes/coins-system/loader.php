<?php
/**
 * Loader del Sistema de Coins
 * Carga todos los módulos del sistema de monedas virtuales
 * 
 * @package Hub_Child_Theme
 * @subpackage Coins_System
 * @version 2.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constante del path
if (!defined('COINS_PATH')) {
    define('COINS_PATH', get_stylesheet_directory() . '/includes/coins-system/');
}

/**
 * ============================================
 * CARGAR MÓDULOS DEL SISTEMA DE COINS
 * ============================================
 */

// 1. Base de datos
if (file_exists(COINS_PATH . 'database/tables.php')) {
    require_once COINS_PATH . 'database/tables.php';
}

// 2. Clase principal de gestión de coins
if (file_exists(COINS_PATH . 'core/class-coins-manager.php')) {
    require_once COINS_PATH . 'core/class-coins-manager.php';
}

// 3. Funciones auxiliares
if (file_exists(COINS_PATH . 'core/coins-functions.php')) {
    require_once COINS_PATH . 'core/coins-functions.php';
}

// 4. Gateway de pago con coins
if (file_exists(COINS_PATH . 'payment/class-coins-gateway.php')) {
    require_once COINS_PATH . 'payment/class-coins-gateway.php';
}

// 5. Sistema de recompensas
if (file_exists(COINS_PATH . 'rewards/purchase-rewards.php')) {
    require_once COINS_PATH . 'rewards/purchase-rewards.php';
}

if (file_exists(COINS_PATH . 'rewards/review-rewards.php')) {
    require_once COINS_PATH . 'rewards/review-rewards.php';
}

if (file_exists(COINS_PATH . 'rewards/social-rewards.php')) {
    require_once COINS_PATH . 'rewards/social-rewards.php';
}

// 6. Hooks y filtros
if (file_exists(COINS_PATH . 'hooks/coins-hooks.php')) {
    require_once COINS_PATH . 'hooks/coins-hooks.php';
}

// 7. Panel de administración
if (file_exists(COINS_PATH . 'admin/coins-metabox.php')) {
    require_once COINS_PATH . 'admin/coins-metabox.php';
}

if (file_exists(COINS_PATH . 'admin/coins-admin.php')) {
    require_once COINS_PATH . 'admin/coins-admin.php';
}

// 8. Frontend (widgets, displays)
if (file_exists(COINS_PATH . 'frontend/coins-display.php')) {
    require_once COINS_PATH . 'frontend/coins-display.php';
}

/**
 * ============================================
 * BACKWARDS COMPATIBILITY
 * ============================================
 * Mantener compatibilidad con archivos antiguos
 * TODO: Migrar código a la nueva estructura
 */

// Archivos legacy (si existen)
$legacy_files = [
    'coins-functions.php',
    'class-coins-manager.php',
    'class-coins-gateway.php',
    'coins-hooks.php',
    'coins-metabox.php',
    'coins-reviews.php',
    'coins-social.php',
    'coins-admin.php'
];

foreach ($legacy_files as $legacy_file) {
    $file_path = COINS_PATH . $legacy_file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

/**
 * Inicializar sistema de coins
 */
function init_coins_system() {
    // Registrar gateway de coins en WooCommerce
    if (function_exists('registrar_gateway_coins')) {
        add_filter('woocommerce_payment_gateways', 'registrar_gateway_coins');
    }
    
    // Crear tablas si es necesario
    if (function_exists('crear_tablas_coins')) {
        add_action('after_switch_theme', 'crear_tablas_coins');
    }
}
add_action('plugins_loaded', 'init_coins_system');
?>