<?php
/**
 * Sistema de Progreso de Cursos
 * Funciones para gestionar y calcular el progreso de usuarios
 * 
 * @package CoursesSystem
 * @subpackage Core
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Actualizar progreso de un usuario en un curso
 */
function courses_update_progress($user_id, $course_id) {
    if (!$user_id || !$course_id) {
        return false;
    }
    
    global $wpdb;
    $table_progress = $wpdb->prefix . 'course_progress';
    
    $course_manager = Course_Manager::get_instance();
    $lesson_manager = Lesson_Manager::get_instance();
    
    // Contar lecciones totales y completadas
    $total_lessons = $course_manager->count_lessons($course_id);
    $lessons_completed = $lesson_manager->count_completed_lessons($user_id, $course_id);
    
    // Calcular porcentaje
    $percentage = $total_lessons > 0 ? ($lessons_completed / $total_lessons) * 100 : 0;
    
    // Determinar estado
    $status = $percentage >= 100 ? 'completed' : 'in_progress';
    
    // Verificar si ya existe registro
    $existing = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_progress WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        )
    );
    
    $data = array(
        'lessons_completed' => $lessons_completed,
        'total_lessons' => $total_lessons,
        'percentage' => round($percentage, 2),
        'status' => $status,
        'last_accessed' => current_time('mysql')
    );
    
    if ($existing) {
        // Actualizar
        if ($status === 'completed' && $existing->status !== 'completed') {
            $data['completed_at'] = current_time('mysql');
            
            // Hook de curso completado
            do_action('courses_course_completed', $user_id, $course_id);
        }
        
        $wpdb->update(
            $table_progress,
            $data,
            array('id' => $existing->id),
            array('%d', '%d', '%f', '%s', '%s'),
            array('%d')
        );
    } else {
        // Insertar nuevo
        $data['user_id'] = $user_id;
        $data['course_id'] = $course_id;
        $data['started_at'] = current_time('mysql');
        
        if ($status === 'completed') {
            $data['completed_at'] = current_time('mysql');
        }
        
        $wpdb->insert(
            $table_progress,
            $data,
            array('%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s')
        );
        
        // Hook de curso iniciado
        do_action('courses_course_started', $user_id, $course_id);
    }
    
    return true;
}

/**
 * Obtener progreso de un usuario en un curso
 */
function courses_get_user_progress($user_id, $course_id) {
    if (!$user_id || !$course_id) {
        return array(
            'percentage' => 0,
            'completed' => 0,
            'total' => 0,
            'status' => 'not_started'
        );
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'course_progress';
    
    $progress = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ),
        ARRAY_A
    );
    
    if (!$progress) {
        // Si no hay progreso, calcular en tiempo real
        $course_manager = Course_Manager::get_instance();
        $lesson_manager = Lesson_Manager::get_instance();
        
        $total = $course_manager->count_lessons($course_id);
        $completed = $lesson_manager->count_completed_lessons($user_id, $course_id);
        
        return array(
            'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'completed' => $completed,
            'total' => $total,
            'status' => $completed > 0 ? 'in_progress' : 'not_started'
        );
    }
    
    return array(
        'percentage' => (float) $progress['percentage'],
        'completed' => (int) $progress['lessons_completed'],
        'total' => (int) $progress['total_lessons'],
        'status' => $progress['status'],
        'started_at' => $progress['started_at'],
        'completed_at' => $progress['completed_at'],
        'last_accessed' => $progress['last_accessed']
    );
}

/**
 * Obtener solo el porcentaje de progreso
 */
function courses_get_progress_percentage($user_id, $course_id) {
    $progress = courses_get_user_progress($user_id, $course_id);
    return $progress['percentage'];
}

/**
 * Verificar si un curso está completado
 */
function courses_is_course_completed($user_id, $course_id) {
    $progress = courses_get_user_progress($user_id, $course_id);
    return $progress['status'] === 'completed';
}

/**
 * Resetear progreso de un usuario en un curso
 */
function courses_reset_progress($user_id, $course_id) {
    global $wpdb;
    
    // Eliminar progreso
    $table_progress = $wpdb->prefix . 'course_progress';
    $wpdb->delete(
        $table_progress,
        array('user_id' => $user_id, 'course_id' => $course_id),
        array('%d', '%d')
    );
    
    // Eliminar lecciones completadas
    $table_lessons = $wpdb->prefix . 'lessons_completed';
    $wpdb->delete(
        $table_lessons,
        array('user_id' => $user_id, 'course_id' => $course_id),
        array('%d', '%d')
    );
    
    return true;
}

/**
 * Obtener todos los cursos de un usuario
 */
function courses_get_user_courses($user_id, $status = 'all') {
    $course_manager = Course_Manager::get_instance();
    return $course_manager->get_user_courses($user_id, $status);
}

/**
 * Obtener estadísticas globales de un usuario
 */
function courses_get_user_stats($user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'course_progress';
    
    $stats = array(
        'total_courses' => 0,
        'completed_courses' => 0,
        'in_progress_courses' => 0,
        'total_lessons_completed' => 0,
        'average_progress' => 0
    );
    
    // Total de cursos
    $stats['total_courses'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d",
            $user_id
        )
    );
    
    // Cursos completados
    $stats['completed_courses'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND status = 'completed'",
            $user_id
        )
    );
    
    // Cursos en progreso
    $stats['in_progress_courses'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND status = 'in_progress'",
            $user_id
        )
    );
    
    // Total de lecciones completadas
    $table_lessons = $wpdb->prefix . 'lessons_completed';
    $stats['total_lessons_completed'] = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_lessons WHERE user_id = %d",
            $user_id
        )
    );
    
    // Progreso promedio
    $avg = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(percentage) FROM $table WHERE user_id = %d",
            $user_id
        )
    );
    $stats['average_progress'] = $avg ? round($avg, 2) : 0;
    
    return $stats;
}

/**
 * Obtener tiempo estimado para completar un curso
 */
function courses_get_estimated_time($course_id) {
    $lesson_manager = Lesson_Manager::get_instance();
    $course_manager = Course_Manager::get_instance();
    
    $lessons = $course_manager->get_lessons($course_id);
    $total_minutes = 0;
    
    foreach ($lessons as $lesson) {
        $duration = $lesson_manager->get_duration($lesson->ID);
        if ($duration) {
            $total_minutes += intval($duration);
        }
    }
    
    return $total_minutes;
}

/**
 * Formatear tiempo estimado
 */
function courses_format_time($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($mins > 0) {
        return $hours . 'h ' . $mins . 'min';
    }
    
    return $hours . 'h';
}

/**
 * Obtener cursos más populares
 */
function courses_get_popular_courses($limit = 10) {
    global $wpdb;
    $table = $wpdb->prefix . 'course_progress';
    
    $popular_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT course_id, COUNT(*) as students 
             FROM $table 
             GROUP BY course_id 
             ORDER BY students DESC 
             LIMIT %d",
            $limit
        )
    );
    
    if (empty($popular_ids)) {
        return array();
    }
    
    return get_posts(array(
        'post_type' => 'course',
        'post__in' => $popular_ids,
        'posts_per_page' => $limit,
        'orderby' => 'post__in'
    ));
}
?>