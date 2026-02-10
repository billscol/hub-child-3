<?php
/**
 * Tablas de Base de Datos del Sistema de Cursos
 * Crea y gestiona las tablas necesarias
 * 
 * @package CoursesSystem
 * @subpackage Database
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crear todas las tablas del sistema de cursos
 */
function courses_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Tabla 1: Progreso de cursos
    $table_progress = $wpdb->prefix . 'course_progress';
    
    $sql_progress = "CREATE TABLE $table_progress (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        course_id bigint(20) NOT NULL,
        lessons_completed int(11) DEFAULT 0,
        total_lessons int(11) DEFAULT 0,
        percentage decimal(5,2) DEFAULT 0.00,
        status varchar(20) DEFAULT 'in_progress',
        started_at datetime NOT NULL,
        completed_at datetime DEFAULT NULL,
        last_accessed datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_course (user_id, course_id),
        KEY user_id (user_id),
        KEY course_id (course_id),
        KEY status (status)
    ) $charset_collate;";
    
    dbDelta($sql_progress);
    
    // Tabla 2: Lecciones completadas
    $table_lessons = $wpdb->prefix . 'lessons_completed';
    
    $sql_lessons = "CREATE TABLE $table_lessons (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        lesson_id bigint(20) NOT NULL,
        course_id bigint(20) NOT NULL,
        completed_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_lesson (user_id, lesson_id),
        KEY user_id (user_id),
        KEY lesson_id (lesson_id),
        KEY course_id (course_id)
    ) $charset_collate;";
    
    dbDelta($sql_lessons);
    
    // Tabla 3: Acceso a cursos
    $table_access = $wpdb->prefix . 'course_access';
    
    $sql_access = "CREATE TABLE $table_access (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        course_id bigint(20) NOT NULL,
        product_id bigint(20) DEFAULT 0,
        order_id bigint(20) DEFAULT 0,
        access_type varchar(20) DEFAULT 'purchase',
        granted_at datetime NOT NULL,
        expires_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY user_course (user_id, course_id),
        KEY user_id (user_id),
        KEY course_id (course_id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    dbDelta($sql_access);
    
    // Guardar versión de DB
    update_option('courses_db_version', '1.0.0');
}

/**
 * Verificar si las tablas existen
 */
function courses_tables_exist() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'course_progress',
        $wpdb->prefix . 'lessons_completed',
        $wpdb->prefix . 'course_access'
    );
    
    foreach ($tables as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return false;
        }
    }
    
    return true;
}

/**
 * Limpiar datos antiguos (opcional)
 */
function courses_clean_old_data($days = 365) {
    global $wpdb;
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-$days days"));
    
    // Limpiar progreso de cursos no accedidos en X días
    $table_progress = $wpdb->prefix . 'course_progress';
    
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $table_progress 
             WHERE status = 'in_progress' 
             AND last_accessed < %s",
            $date_limit
        )
    );
}

/**
 * Obtener estadísticas de la base de datos
 */
function courses_get_db_stats() {
    global $wpdb;
    
    $stats = array();
    
    // Total de registros de progreso
    $table_progress = $wpdb->prefix . 'course_progress';
    $stats['total_progress'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_progress");
    
    // Total de lecciones completadas
    $table_lessons = $wpdb->prefix . 'lessons_completed';
    $stats['total_lessons_completed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_lessons");
    
    // Total de accesos otorgados
    $table_access = $wpdb->prefix . 'course_access';
    $stats['total_access'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_access");
    
    // Cursos activos
    $stats['active_courses'] = $wpdb->get_var(
        "SELECT COUNT(DISTINCT course_id) FROM $table_progress WHERE status = 'in_progress'"
    );
    
    return $stats;
}
?>