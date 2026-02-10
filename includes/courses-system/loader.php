<?php
/**
 * COURSES SYSTEM - LOADER
 * Sistema completo de gestión de cursos online
 * 
 * @package CoursesSystem
 * @version 1.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('COURSES_SYSTEM_VERSION', '1.0.0');
define('COURSES_SYSTEM_PATH', get_stylesheet_directory() . '/includes/courses-system/');
define('COURSES_SYSTEM_URL', get_stylesheet_directory_uri() . '/includes/courses-system/');

/**
 * CARGAR MÓDULOS DEL SISTEMA
 */

// 1. ACTIVADOR - Instalar y configurar sistema
require_once COURSES_SYSTEM_PATH . 'activator.php';

// 2. BASE DE DATOS
require_once COURSES_SYSTEM_PATH . 'database/tables.php';

// 3. CLASES PRINCIPALES
require_once COURSES_SYSTEM_PATH . 'core/class-course-manager.php';
require_once COURSES_SYSTEM_PATH . 'core/class-lesson-manager.php';
require_once COURSES_SYSTEM_PATH . 'core/progress.php';

// 4. CUSTOM POST TYPES
require_once COURSES_SYSTEM_PATH . 'post-types/course-post-type.php';
require_once COURSES_SYSTEM_PATH . 'post-types/lesson-post-type.php';

// 5. ADMIN
require_once COURSES_SYSTEM_PATH . 'admin/course-metabox.php';
require_once COURSES_SYSTEM_PATH . 'admin/lesson-metabox.php';
require_once COURSES_SYSTEM_PATH . 'admin/columns.php';

// 6. FRONTEND
require_once COURSES_SYSTEM_PATH . 'frontend/course-display.php';
require_once COURSES_SYSTEM_PATH . 'frontend/lesson-display.php';
require_once COURSES_SYSTEM_PATH . 'frontend/progress-bar.php';
require_once COURSES_SYSTEM_PATH . 'frontend/navigation.php';

// 7. SHORTCODES
require_once COURSES_SYSTEM_PATH . 'shortcodes/courses-list.php';
require_once COURSES_SYSTEM_PATH . 'shortcodes/user-progress.php';
require_once COURSES_SYSTEM_PATH . 'shortcodes/course-progress.php';
require_once COURSES_SYSTEM_PATH . 'shortcodes/latest-courses.php';

// 8. AJAX HANDLERS
require_once COURSES_SYSTEM_PATH . 'ajax/handlers.php';

// 9. INTEGRACIONES
require_once COURSES_SYSTEM_PATH . 'integrations/woocommerce.php';

/**
 * SISTEMA CARGADO EXITOSAMENTE
 */
add_action('init', function() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('✅ Courses System v' . COURSES_SYSTEM_VERSION . ' cargado exitosamente');
    }
});
?>