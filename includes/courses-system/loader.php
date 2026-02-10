<?php
/**
 * Sistema de Cursos - Loader Principal
 * Carga todos los módulos del sistema de cursos de forma organizada
 * 
 * @package CoursesSystem
 * @version 1.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Constantes del sistema
define('COURSES_VERSION', '1.0.0');
define('COURSES_PATH', get_stylesheet_directory() . '/includes/courses-system/');
define('COURSES_URL', get_stylesheet_directory_uri() . '/includes/courses-system/');

/**
 * Clase principal del Sistema de Cursos
 */
class Courses_System_Loader {
    
    /**
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Obtener instancia
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        // 1. Base de datos
        require_once COURSES_PATH . 'database/tables.php';
        
        // 2. Core (clases principales)
        require_once COURSES_PATH . 'core/class-course-manager.php';
        require_once COURSES_PATH . 'core/class-lesson-manager.php';
        require_once COURSES_PATH . 'core/progress.php';
        
        // 3. Custom Post Types
        require_once COURSES_PATH . 'post-types/course-post-type.php';
        require_once COURSES_PATH . 'post-types/lesson-post-type.php';
        
        // 4. Admin (solo en backend)
        if (is_admin()) {
            require_once COURSES_PATH . 'admin/course-metabox.php';
            require_once COURSES_PATH . 'admin/lesson-metabox.php';
            require_once COURSES_PATH . 'admin/columns.php';
        }
        
        // 5. Frontend (solo en frontend)
        if (!is_admin()) {
            require_once COURSES_PATH . 'frontend/course-display.php';
            require_once COURSES_PATH . 'frontend/lesson-display.php';
            require_once COURSES_PATH . 'frontend/progress-bar.php';
            require_once COURSES_PATH . 'frontend/navigation.php';
        }
        
        // 6. Shortcodes (siempre)
        require_once COURSES_PATH . 'shortcodes/course-list.php';
        require_once COURSES_PATH . 'shortcodes/course-single.php';
        require_once COURSES_PATH . 'shortcodes/user-courses.php';
        require_once COURSES_PATH . 'shortcodes/lesson-content.php';
        
        // 7. Integración WooCommerce
        if (class_exists('WooCommerce')) {
            require_once COURSES_PATH . 'integration/woocommerce-integration.php';
        }
        
        // 8. AJAX
        require_once COURSES_PATH . 'ajax/course-ajax.php';
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Activación del sistema
        add_action('after_switch_theme', array($this, 'activate'));
        
        // Cargar estilos y scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Activación del sistema
     */
    public function activate() {
        // Crear tablas
        courses_create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Cargar scripts del frontend
     */
    public function enqueue_scripts() {
        // CSS principal
        wp_enqueue_style(
            'courses-system',
            COURSES_URL . 'assets/courses.css',
            array(),
            COURSES_VERSION
        );
        
        // JavaScript principal
        wp_enqueue_script(
            'courses-system',
            COURSES_URL . 'assets/courses.js',
            array('jquery'),
            COURSES_VERSION,
            true
        );
        
        // Pasar variables a JS
        wp_localize_script('courses-system', 'coursesData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('courses_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'strings' => array(
                'loading' => 'Cargando...',
                'error' => 'Ocurrió un error',
                'success' => 'Éxito',
                'lesson_completed' => '¡Lección completada!',
                'course_completed' => '¡Felicidades! Has completado el curso'
            )
        ));
    }
    
    /**
     * Cargar scripts del admin
     */
    public function enqueue_admin_scripts($hook) {
        // Solo en páginas de cursos/lecciones
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        global $post;
        if (!$post || !in_array($post->post_type, array('course', 'lesson'))) {
            return;
        }
        
        wp_enqueue_style(
            'courses-admin',
            COURSES_URL . 'assets/admin.css',
            array(),
            COURSES_VERSION
        );
        
        wp_enqueue_script(
            'courses-admin',
            COURSES_URL . 'assets/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            COURSES_VERSION,
            true
        );
    }
}

// Inicializar el sistema
function courses_system_init() {
    return Courses_System_Loader::get_instance();
}
add_action('plugins_loaded', 'courses_system_init', 1);

/**
 * Funciones helper globales
 */

/**
 * Obtener nombre de tabla con prefijo
 */
function courses_get_table_name($table) {
    global $wpdb;
    $tables = array(
        'progress' => $wpdb->prefix . 'course_progress',
        'lessons_completed' => $wpdb->prefix . 'lessons_completed',
        'course_access' => $wpdb->prefix . 'course_access'
    );
    
    return isset($tables[$table]) ? $tables[$table] : '';
}

/**
 * Verificar si un usuario tiene acceso a un curso
 */
function courses_user_has_access($user_id, $course_id) {
    $course_manager = Course_Manager::get_instance();
    return $course_manager->user_has_access($user_id, $course_id);
}

/**
 * Obtener progreso de un usuario en un curso
 */
function courses_get_user_progress($user_id, $course_id) {
    return courses_get_progress_percentage($user_id, $course_id);
}

/**
 * Marcar lección como completada
 */
function courses_complete_lesson($user_id, $lesson_id, $course_id) {
    $lesson_manager = Lesson_Manager::get_instance();
    return $lesson_manager->mark_as_completed($user_id, $lesson_id, $course_id);
}

/**
 * Verificar si una lección está completada
 */
function courses_is_lesson_completed($user_id, $lesson_id) {
    $lesson_manager = Lesson_Manager::get_instance();
    return $lesson_manager->is_completed($user_id, $lesson_id);
}
?>