<?php
/**
 * Lesson Manager - Gestión de Lecciones
 * Clase para gestionar lecciones y su completado
 * 
 * @package CoursesSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Lesson_Manager (Singleton)
 */
class Lesson_Manager {
    
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
     * Crear lección
     */
    public function create_lesson($args) {
        $defaults = array(
            'title' => '',
            'content' => '',
            'course_id' => 0,
            'order' => 0,
            'status' => 'publish',
            'thumbnail_id' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        if (!$args['course_id']) {
            return new WP_Error('no_course', 'Debes especificar un curso padre');
        }
        
        $lesson_id = wp_insert_post(array(
            'post_title' => $args['title'],
            'post_content' => $args['content'],
            'post_status' => $args['status'],
            'post_type' => 'lesson',
            'post_parent' => $args['course_id'],
            'menu_order' => $args['order']
        ));
        
        if (is_wp_error($lesson_id)) {
            return $lesson_id;
        }
        
        if ($args['thumbnail_id']) {
            set_post_thumbnail($lesson_id, $args['thumbnail_id']);
        }
        
        return $lesson_id;
    }
    
    /**
     * Obtener curso padre de una lección
     */
    public function get_course_id($lesson_id) {
        $lesson = get_post($lesson_id);
        return $lesson ? $lesson->post_parent : 0;
    }
    
    /**
     * Verificar si una lección está completada
     */
    public function is_completed($user_id, $lesson_id) {
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lessons_completed';
        
        $completed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE user_id = %d AND lesson_id = %d",
                $user_id,
                $lesson_id
            )
        );
        
        return (bool) $completed;
    }
    
    /**
     * Marcar lección como completada
     */
    public function mark_as_completed($user_id, $lesson_id, $course_id = 0) {
        if (!$user_id || !$lesson_id) {
            return false;
        }
        
        // Si ya está completada, no hacer nada
        if ($this->is_completed($user_id, $lesson_id)) {
            return true;
        }
        
        // Obtener curso si no se proporciona
        if (!$course_id) {
            $course_id = $this->get_course_id($lesson_id);
        }
        
        if (!$course_id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'lessons_completed';
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'course_id' => $course_id,
                'completed_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s')
        );
        
        if ($result) {
            // Actualizar progreso del curso
            courses_update_progress($user_id, $course_id);
            
            // Hook
            do_action('courses_lesson_completed', $user_id, $lesson_id, $course_id);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Desmarcar lección como completada
     */
    public function mark_as_incomplete($user_id, $lesson_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lessons_completed';
        
        $course_id = $this->get_course_id($lesson_id);
        
        $result = $wpdb->delete(
            $table,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id
            ),
            array('%d', '%d')
        );
        
        if ($result && $course_id) {
            // Actualizar progreso del curso
            courses_update_progress($user_id, $course_id);
        }
        
        return (bool) $result;
    }
    
    /**
     * Obtener siguiente lección
     */
    public function get_next_lesson($course_id, $current_lesson_id) {
        $lessons = $this->get_course_lessons($course_id);
        
        $found_current = false;
        foreach ($lessons as $lesson) {
            if ($found_current) {
                return $lesson;
            }
            if ($lesson->ID == $current_lesson_id) {
                $found_current = true;
            }
        }
        
        return null;
    }
    
    /**
     * Obtener lección anterior
     */
    public function get_previous_lesson($course_id, $current_lesson_id) {
        $lessons = $this->get_course_lessons($course_id);
        
        $previous = null;
        foreach ($lessons as $lesson) {
            if ($lesson->ID == $current_lesson_id) {
                return $previous;
            }
            $previous = $lesson;
        }
        
        return null;
    }
    
    /**
     * Obtener primera lección del curso
     */
    public function get_first_lesson($course_id) {
        $lessons = $this->get_course_lessons($course_id);
        return !empty($lessons) ? $lessons[0] : null;
    }
    
    /**
     * Obtener última lección del curso
     */
    public function get_last_lesson($course_id) {
        $lessons = $this->get_course_lessons($course_id);
        return !empty($lessons) ? end($lessons) : null;
    }
    
    /**
     * Obtener lecciones del curso
     */
    private function get_course_lessons($course_id) {
        return get_posts(array(
            'post_type' => 'lesson',
            'post_parent' => $course_id,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
    }
    
    /**
     * Obtener lecciones completadas de un usuario en un curso
     */
    public function get_completed_lessons($user_id, $course_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lessons_completed';
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT lesson_id FROM $table WHERE user_id = %d AND course_id = %d",
                $user_id,
                $course_id
            )
        );
    }
    
    /**
     * Contar lecciones completadas
     */
    public function count_completed_lessons($user_id, $course_id) {
        return count($this->get_completed_lessons($user_id, $course_id));
    }
    
    /**
     * Obtener duración estimada de la lección
     */
    public function get_duration($lesson_id) {
        return get_post_meta($lesson_id, '_lesson_duration', true);
    }
    
    /**
     * Establecer duración de la lección
     */
    public function set_duration($lesson_id, $duration) {
        return update_post_meta($lesson_id, '_lesson_duration', $duration);
    }
}
?>