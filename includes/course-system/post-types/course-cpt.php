<?php
/**
 * Custom Post Type: Cursos
 * 
 * @package Hub_Child_Theme
 * @subpackage Course_System
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar Custom Post Type de Cursos
 */
function register_course_post_type() {
    $labels = array(
        'name'                  => 'Cursos',
        'singular_name'         => 'Curso',
        'menu_name'             => 'Cursos',
        'name_admin_bar'        => 'Curso',
        'add_new'               => 'Añadir Nuevo',
        'add_new_item'          => 'Añadir Nuevo Curso',
        'new_item'              => 'Nuevo Curso',
        'edit_item'             => 'Editar Curso',
        'view_item'             => 'Ver Curso',
        'all_items'             => 'Todos los Cursos',
        'search_items'          => 'Buscar Cursos',
        'not_found'             => 'No se encontraron cursos',
        'not_found_in_trash'    => 'No hay cursos en la papelera'
    );
    
    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array('slug' => 'curso'),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-welcome-learn-more',
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'          => true, // Para Gutenberg
    );
    
    register_post_type('course', $args);
}

/**
 * Configurar capacidades de cursos
 */
function setup_course_capabilities() {
    $role = get_role('administrator');
    
    if ($role) {
        $role->add_cap('edit_course');
        $role->add_cap('read_course');
        $role->add_cap('delete_course');
        $role->add_cap('edit_courses');
        $role->add_cap('edit_others_courses');
        $role->add_cap('publish_courses');
        $role->add_cap('read_private_courses');
    }
}
?>