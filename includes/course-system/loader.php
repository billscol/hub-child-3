<?php
/**
 * Course System Loader
 * Carga todos los módulos del sistema de cursos
 * 
 * @package CourseSystem
 * @version 1.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('COURSE_SYSTEM_VERSION', '1.0.0');
define('COURSE_SYSTEM_PATH', get_stylesheet_directory() . '/includes/course-system');
define('COURSE_SYSTEM_URL', get_stylesheet_directory_uri() . '/includes/course-system');

/**
 * Cargar Sistema de Cursos
 */
function load_course_system() {
    // 1. CURRICULUM - Módulos y Lecciones
    require_once COURSE_SYSTEM_PATH . '/curriculum/metabox.php';
    require_once COURSE_SYSTEM_PATH . '/curriculum/display.php';
    require_once COURSE_SYSTEM_PATH . '/curriculum/shortcode.php';
    
    // 2. REVIEWS - Sistema de reseñas
    require_once COURSE_SYSTEM_PATH . '/reviews/form.php';
    require_once COURSE_SYSTEM_PATH . '/reviews/display.php';
    require_once COURSE_SYSTEM_PATH . '/reviews/shortcode.php';
    
    // 3. REPORTS - Sistema de reportes
    require_once COURSE_SYSTEM_PATH . '/reports/cpt.php';
    require_once COURSE_SYSTEM_PATH . '/reports/button.php';
    require_once COURSE_SYSTEM_PATH . '/reports/handler.php';
    
    // 4. SUPPORT - Tickets de soporte
    require_once COURSE_SYSTEM_PATH . '/support/cpt.php';
    require_once COURSE_SYSTEM_PATH . '/support/endpoint.php';
    require_once COURSE_SYSTEM_PATH . '/support/template.php';
    
    // 5. DASHBOARD - Personalización Mi Cuenta
    require_once COURSE_SYSTEM_PATH . '/dashboard/customization.php';
    require_once COURSE_SYSTEM_PATH . '/dashboard/styles.php';
    
    // 6. SHORTCODES - Shortcodes personalizados
    require_once COURSE_SYSTEM_PATH . '/shortcodes/filtros-cursos.php';
    require_once COURSE_SYSTEM_PATH . '/shortcodes/grid-cursos.php';
    require_once COURSE_SYSTEM_PATH . '/shortcodes/video-producto.php';
    
    // 7. INTEGRATION - Integraciones
    if (class_exists('WeDevs_Dokan')) {
        require_once COURSE_SYSTEM_PATH . '/integration/dokan.php';
    }
}

// Cargar después de que WordPress esté listo
add_action('after_setup_theme', 'load_course_system', 20);

/**
 * Log de carga exitosa
 */
add_action('init', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('✅ Sistema de Cursos cargado - v' . COURSE_SYSTEM_VERSION);
    }
}, 999);
?>