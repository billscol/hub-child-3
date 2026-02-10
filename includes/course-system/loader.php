<?php
/**
 * Loader del Sistema de Cursos
 * Carga todos los módulos del sistema de gestión de cursos
 * 
 * @package Hub_Child_Theme
 * @subpackage Course_System
 * @version 2.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constante del path
if (!defined('COURSE_SYSTEM_PATH')) {
    define('COURSE_SYSTEM_PATH', get_stylesheet_directory() . '/includes/course-system/');
}

/**
 * ============================================
 * CARGAR MÓDULOS DEL SISTEMA DE CURSOS
 * ============================================
 */

// 1. Custom Post Types y Taxonomías
if (file_exists(COURSE_SYSTEM_PATH . 'post-types/course-cpt.php')) {
    require_once COURSE_SYSTEM_PATH . 'post-types/course-cpt.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'post-types/taxonomies.php')) {
    require_once COURSE_SYSTEM_PATH . 'post-types/taxonomies.php';
}

// 2. Metaboxes del admin
if (file_exists(COURSE_SYSTEM_PATH . 'admin/metaboxes/course-info.php')) {
    require_once COURSE_SYSTEM_PATH . 'admin/metaboxes/course-info.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'admin/metaboxes/instructor-info.php')) {
    require_once COURSE_SYSTEM_PATH . 'admin/metaboxes/instructor-info.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'admin/metaboxes/course-settings.php')) {
    require_once COURSE_SYSTEM_PATH . 'admin/metaboxes/course-settings.php';
}

// 3. Columnas personalizadas en admin
if (file_exists(COURSE_SYSTEM_PATH . 'admin/admin-columns.php')) {
    require_once COURSE_SYSTEM_PATH . 'admin/admin-columns.php';
}

// 4. Templates frontend
if (file_exists(COURSE_SYSTEM_PATH . 'frontend/template-loader.php')) {
    require_once COURSE_SYSTEM_PATH . 'frontend/template-loader.php';
}

// 5. Sistema de progreso
if (file_exists(COURSE_SYSTEM_PATH . 'progress/course-progress.php')) {
    require_once COURSE_SYSTEM_PATH . 'progress/course-progress.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'progress/lesson-completion.php')) {
    require_once COURSE_SYSTEM_PATH . 'progress/lesson-completion.php';
}

// 6. Certificados
if (file_exists(COURSE_SYSTEM_PATH . 'certificates/certificate-generator.php')) {
    require_once COURSE_SYSTEM_PATH . 'certificates/certificate-generator.php';
}

// 7. Integraciones
if (file_exists(COURSE_SYSTEM_PATH . 'integration/woocommerce-integration.php')) {
    require_once COURSE_SYSTEM_PATH . 'integration/woocommerce-integration.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'integration/elementor-widgets.php')) {
    require_once COURSE_SYSTEM_PATH . 'integration/elementor-widgets.php';
}

// 8. Acceso y control
if (file_exists(COURSE_SYSTEM_PATH . 'access/enrollment.php')) {
    require_once COURSE_SYSTEM_PATH . 'access/enrollment.php';
}

if (file_exists(COURSE_SYSTEM_PATH . 'access/access-control.php')) {
    require_once COURSE_SYSTEM_PATH . 'access/access-control.php';
}

/**
 * ============================================
 * BACKWARDS COMPATIBILITY
 * ============================================
 * Archivo legacy init.php
 */
if (file_exists(COURSE_SYSTEM_PATH . 'init.php')) {
    require_once COURSE_SYSTEM_PATH . 'init.php';
}

/**
 * Inicializar sistema de cursos
 */
function init_course_system() {
    // Registrar custom post types
    if (function_exists('register_course_post_type')) {
        add_action('init', 'register_course_post_type');
    }
    
    // Registrar taxonomías
    if (function_exists('register_course_taxonomies')) {
        add_action('init', 'register_course_taxonomies');
    }
    
    // Configurar capacidades
    if (function_exists('setup_course_capabilities')) {
        add_action('init', 'setup_course_capabilities');
    }
}
add_action('after_setup_theme', 'init_course_system');
?>