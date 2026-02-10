<?php
/**
 * Hub Child Theme - Functions (LIMPIO Y ORGANIZADO)
 * 
 * IMPORTANTE: Este es el nuevo functions.php limpio y organizado.
 * Todas las funcionalidades están ahora en carpetas modulares en /includes/
 * 
 * Versión: 2.0.0
 * Última actualización: Febrero 2026
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================
 * ESTILOS DEL THEME
 * ============================================
 */

// Encolar estilos del theme padre y child
function hub_child_enqueue_styles() {
    $parent_style = 'parent-style';
    
    // Estilo del theme padre
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    
    // Estilo del child theme
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'hub_child_enqueue_styles');

/**
 * ============================================
 * MÓDULOS PERSONALIZADOS
 * ============================================
 */

// Cargar sistema de coins
if (file_exists(get_stylesheet_directory() . '/includes/coins-system/loader.php')) {
    require_once get_stylesheet_directory() . '/includes/coins-system/loader.php';
}

// Cargar sistema de cursos
if (file_exists(get_stylesheet_directory() . '/includes/course-system/loader.php')) {
    require_once get_stylesheet_directory() . '/includes/course-system/loader.php';
}

// Cargar personalización de checkout
if (file_exists(get_stylesheet_directory() . '/includes/checkout-customization/loader.php')) {
    require_once get_stylesheet_directory() . '/includes/checkout-customization/loader.php';
}

// Cargar integración con Dokan
if (file_exists(get_stylesheet_directory() . '/includes/dokan-integration.php')) {
    require_once get_stylesheet_directory() . '/includes/dokan-integration.php';
}

/**
 * ============================================
 * SHORTCODES
 * ============================================
 */

// Cargar TODOS los shortcodes organizados
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes-loader.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes-loader.php';
}

/**
 * ============================================
 * SCRIPTS GLOBALES
 * ============================================
 */

// Script del dashboard (si es necesario)
add_action('wp_footer', function() {
    if (!is_page('dashboard')) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scripts específicos del dashboard
        console.log('Dashboard cargado');
    });
    </script>
    <?php
});

/**
 * ============================================
 * CONFIGURACIÓN DE WOOCOMMERCE
 * ============================================
 */

// Configuraciones básicas de WooCommerce
add_filter('woocommerce_enqueue_styles', '__return_false');

// Soporte para miniaturas de productos
add_theme_support('woocommerce');
add_theme_support('wc-product-gallery-zoom');
add_theme_support('wc-product-gallery-lightbox');
add_theme_support('wc-product-gallery-slider');

/**
 * ============================================
 * FUNCIONES AUXILIARES
 * ============================================
 */

/**
 * Obtener URL del avatar de un usuario
 */
function get_user_avatar_url($user_id, $size = 96) {
    return get_avatar_url($user_id, array('size' => $size));
}

/**
 * Verificar si el usuario compró un producto
 */
function user_has_bought_product($product_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    return wc_customer_bought_product($user->user_email, $user_id, $product_id);
}

/**
 * Formatear precio con símbolo de moneda
 */
function format_price($price) {
    return wc_price($price);
}

/**
 * ============================================
 * SEGURIDAD Y OPTIMIZACIÓN
 * ============================================
 */

// Remover version de WordPress del head
remove_action('wp_head', 'wp_generator');

// Deshabilitar XML-RPC si no es necesario
add_filter('xmlrpc_enabled', '__return_false');

// Limitar revisiones de posts
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

/**
 * ============================================
 * FIN DEL ARCHIVO
 * ============================================
 * 
 * NOTA: Este archivo ahora solo tiene ~150 líneas en lugar de 5,000+
 * Todo el código está organizado en carpetas modulares.
 * 
 * Estructura de carpetas:
 * /includes/
 *   ├── coins-system/          (Sistema de monedas)
 *   ├── course-system/         (Sistema de cursos)
 *   ├── checkout-customization/ (Personalización checkout)
 *   ├── dokan-integration.php (Integración Dokan)
 *   ├── shortcodes/            (Todos los shortcodes)
 *   └── shortcodes-loader.php  (Cargador de shortcodes)
 */
?>