<?php
/**
 * Course Manager - Gestión de Cursos
 * Clase principal para gestionar cursos y accesos
 * 
 * @package CoursesSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Course_Manager (Singleton)
 */
class Course_Manager {
    
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
     * Constructor privado
     */
    private function __construct() {
        // Hooks
    }
    
    /**
     * Crear curso
     */
    public function create_course($args) {
        $defaults = array(
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'status' => 'publish',
            'product_id' => 0,
            'thumbnail_id' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $course_id = wp_insert_post(array(
            'post_title' => $args['title'],
            'post_content' => $args['content'],
            'post_excerpt' => $args['excerpt'],
            'post_status' => $args['status'],
            'post_type' => 'course'
        ));
        
        if (is_wp_error($course_id)) {
            return $course_id;
        }
        
        // Guardar meta
        if ($args['product_id']) {
            update_post_meta($course_id, '_course_product_id', $args['product_id']);
        }
        
        if ($args['thumbnail_id']) {
            set_post_thumbnail($course_id, $args['thumbnail_id']);
        }
        
        return $course_id;
    }
    
    /**
     * Verificar si usuario tiene acceso a un curso
     */
    public function user_has_access($user_id, $course_id) {
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'course_access';
        
        $access = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table 
                 WHERE user_id = %d 
                 AND course_id = %d
                 AND (expires_at IS NULL OR expires_at > NOW())",
                $user_id,
                $course_id
            )
        );
        
        if ($access) {
            return true;
        }
        
        // Verificar si el curso es gratuito
        $product_id = get_post_meta($course_id, '_course_product_id', true);
        
        if (!$product_id) {
            // Curso sin producto = gratuito
            return true;
        }
        
        // Verificar si compró el producto
        if (function_exists('wc_customer_bought_product')) {
            $user = get_user_by('id', $user_id);
            if ($user && wc_customer_bought_product($user->user_email, $user_id, $product_id)) {
                // Otorgar acceso automáticamente
                $this->grant_access($user_id, $course_id, $product_id);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Otorgar acceso a un curso
     */
    public function grant_access($user_id, $course_id, $product_id = 0, $order_id = 0, $access_type = 'purchase', $expires_at = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'course_access';
        
        // Verificar si ya tiene acceso
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE user_id = %d AND course_id = %d",
                $user_id,
                $course_id
            )
        );
        
        if ($existing) {
            return true; // Ya tiene acceso
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'product_id' => $product_id,
                'order_id' => $order_id,
                'access_type' => $access_type,
                'granted_at' => current_time('mysql'),
                'expires_at' => $expires_at
            ),
            array('%d', '%d', '%d', '%d', '%s', '%s', '%s')
        );
        
        if ($result) {
            do_action('courses_access_granted', $user_id, $course_id, $product_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Revocar acceso
     */
    public function revoke_access($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'course_access';
        
        return $wpdb->delete(
            $table,
            array(
                'user_id' => $user_id,
                'course_id' => $course_id
            ),
            array('%d', '%d')
        );
    }
    
    /**
     * Obtener lecciones de un curso
     */
    public function get_lessons($course_id, $published_only = true) {
        $args = array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'post_parent' => $course_id,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => $published_only ? 'publish' : 'any'
        );
        
        return get_posts($args);
    }
    
    /**
     * Contar lecciones de un curso
     */
    public function count_lessons($course_id) {
        return count($this->get_lessons($course_id));
    }
    
    /**
     * Obtener producto asociado
     */
    public function get_product_id($course_id) {
        return get_post_meta($course_id, '_course_product_id', true);
    }
    
    /**
     * Obtener estadísticas del curso
     */
    public function get_stats($course_id) {
        global $wpdb;
        $table_progress = $wpdb->prefix . 'course_progress';
        
        $stats = array(
            'total_students' => 0,
            'active_students' => 0,
            'completed_students' => 0,
            'average_progress' => 0,
            'total_lessons' => $this->count_lessons($course_id)
        );
        
        // Total de estudiantes
        $stats['total_students'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM $table_progress WHERE course_id = %d",
                $course_id
            )
        );
        
        // Estudiantes activos
        $stats['active_students'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_progress WHERE course_id = %d AND status = 'in_progress'",
                $course_id
            )
        );
        
        // Estudiantes completados
        $stats['completed_students'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_progress WHERE course_id = %d AND status = 'completed'",
                $course_id
            )
        );
        
        // Progreso promedio
        $avg = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(percentage) FROM $table_progress WHERE course_id = %d",
                $course_id
            )
        );
        
        $stats['average_progress'] = $avg ? round($avg, 2) : 0;
        
        return $stats;
    }
    
    /**
     * Obtener cursos de un usuario
     */
    public function get_user_courses($user_id, $status = 'all') {
        global $wpdb;
        $table_progress = $wpdb->prefix . 'course_progress';
        
        $where = "user_id = %d";
        $params = array($user_id);
        
        if ($status !== 'all') {
            $where .= " AND status = %s";
            $params[] = $status;
        }
        
        $course_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT course_id FROM $table_progress WHERE $where ORDER BY last_accessed DESC",
                $params
            )
        );
        
        if (empty($course_ids)) {
            return array();
        }
        
        return get_posts(array(
            'post_type' => 'course',
            'post__in' => $course_ids,
            'posts_per_page' => -1,
            'orderby' => 'post__in'
        ));
    }
}
?>