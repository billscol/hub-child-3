<?php
/**
 * Custom Post Type: Reportes de Cursos
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// PASO 1: Registrar Custom Post Type
function register_course_reports_cpt() {
    $labels = array(
        'name'               => 'Reportes de Cursos',
        'singular_name'      => 'Reporte',
        'menu_name'          => 'Reportes de Cursos',
        'add_new'            => 'Agregar Reporte',
        'add_new_item'       => 'Agregar Nuevo Reporte',
        'edit_item'          => 'Editar Reporte',
        'new_item'           => 'Nuevo Reporte',
        'view_item'          => 'Ver Reporte',
        'search_items'       => 'Buscar Reportes',
        'not_found'          => 'No se encontraron reportes',
        'not_found_in_trash' => 'No hay reportes en la papelera'
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-warning',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'),
        'menu_position'       => 26,
        'show_in_admin_bar'   => false,
    );
    
    register_post_type('course_report', $args);
}
add_action('init', 'register_course_reports_cpt');
?>