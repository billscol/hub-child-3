<?php
/**
 * Course System - Inicializador
 * Carga todos los mиоdulos del sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('COURSE_SYSTEM_PATH', get_stylesheet_directory() . '/includes/course-system/');
define('COURSE_SYSTEM_URL', get_stylesheet_directory_uri() . '/includes/course-system/');

// Cargar mиоdulos
require_once COURSE_SYSTEM_PATH . 'cache.php';
require_once COURSE_SYSTEM_PATH . 'taxonomy.php';
require_once COURSE_SYSTEM_PATH . 'admin.php';
require_once COURSE_SYSTEM_PATH . 'meta-boxes.php';
require_once COURSE_SYSTEM_PATH . 'shortcodes.php';
require_once COURSE_SYSTEM_PATH . 'styles.php';

// Template override para archivo de autor - CON PRIORIDAD ALTA
add_filter('template_include', 'course_author_template_override', 99);
function course_author_template_override($template) {
    if (is_tax('course_author')) {
        $custom_template = COURSE_SYSTEM_PATH . 'templates/archive-author.php';
        
        // Debug: descomentar para verificar
        // error_log('Intentando cargar template: ' . $custom_template);
        // error_log('Template existe: ' . (file_exists($custom_template) ? 'SI' : 'NO'));
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}


// Al final de init.php, agregar:
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once COURSE_SYSTEM_PATH . 'debug-template.php';
}


