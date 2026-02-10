<?php
/**
 * Custom Post Type: Course (Curso)
 * Define el CPT de cursos con sus taxonomías
 * 
 * @package CoursesSystem
 * @subpackage PostTypes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar CPT Course
 */
function courses_register_course_post_type() {
    $labels = array(
        'name' => 'Cursos',
        'singular_name' => 'Curso',
        'menu_name' => 'Cursos',
        'add_new' => 'Añadir Curso',
        'add_new_item' => 'Añadir Nuevo Curso',
        'edit_item' => 'Editar Curso',
        'new_item' => 'Nuevo Curso',
        'view_item' => 'Ver Curso',
        'view_items' => 'Ver Cursos',
        'search_items' => 'Buscar Cursos',
        'not_found' => 'No se encontraron cursos',
        'not_found_in_trash' => 'No hay cursos en la papelera',
        'all_items' => 'Todos los Cursos',
        'archives' => 'Archivo de Cursos',
        'attributes' => 'Atributos del Curso',
        'insert_into_item' => 'Insertar en curso',
        'uploaded_to_this_item' => 'Subido a este curso'
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'curso'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-book-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest' => true,
        'taxonomies' => array('course_category', 'course_tag')
    );
    
    register_post_type('course', $args);
}
add_action('init', 'courses_register_course_post_type');

/**
 * Registrar taxonomía: Categorías de Curso
 */
function courses_register_course_category() {
    $labels = array(
        'name' => 'Categorías de Curso',
        'singular_name' => 'Categoría de Curso',
        'search_items' => 'Buscar Categorías',
        'all_items' => 'Todas las Categorías',
        'parent_item' => 'Categoría Padre',
        'parent_item_colon' => 'Categoría Padre:',
        'edit_item' => 'Editar Categoría',
        'update_item' => 'Actualizar Categoría',
        'add_new_item' => 'Añadir Nueva Categoría',
        'new_item_name' => 'Nombre de Nueva Categoría',
        'menu_name' => 'Categorías'
    );
    
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'categoria-curso'),
        'show_in_rest' => true
    );
    
    register_taxonomy('course_category', array('course'), $args);
}
add_action('init', 'courses_register_course_category');

/**
 * Registrar taxonomía: Etiquetas de Curso
 */
function courses_register_course_tag() {
    $labels = array(
        'name' => 'Etiquetas de Curso',
        'singular_name' => 'Etiqueta de Curso',
        'search_items' => 'Buscar Etiquetas',
        'all_items' => 'Todas las Etiquetas',
        'edit_item' => 'Editar Etiqueta',
        'update_item' => 'Actualizar Etiqueta',
        'add_new_item' => 'Añadir Nueva Etiqueta',
        'new_item_name' => 'Nombre de Nueva Etiqueta',
        'menu_name' => 'Etiquetas'
    );
    
    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'etiqueta-curso'),
        'show_in_rest' => true
    );
    
    register_taxonomy('course_tag', array('course'), $args);
}
add_action('init', 'courses_register_course_tag');

/**
 * Modificar título de entrada en admin
 */
function courses_change_title_text($title) {
    $screen = get_current_screen();
    
    if ('course' === $screen->post_type) {
        $title = 'Escribe el título del curso';
    }
    
    return $title;
}
add_filter('enter_title_here', 'courses_change_title_text');

/**
 * Mensajes personalizados
 */
function courses_updated_messages($messages) {
    global $post, $post_ID;
    
    $permalink = get_permalink($post_ID);
    
    $messages['course'] = array(
        0 => '',
        1 => sprintf('Curso actualizado. <a href="%s">Ver curso</a>', esc_url($permalink)),
        2 => 'Campo personalizado actualizado.',
        3 => 'Campo personalizado eliminado.',
        4 => 'Curso actualizado.',
        5 => isset($_GET['revision']) ? sprintf('Curso restaurado a revisión del %s', wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => sprintf('Curso publicado. <a href="%s">Ver curso</a>', esc_url($permalink)),
        7 => 'Curso guardado.',
        8 => sprintf('Curso enviado. <a target="_blank" href="%s">Previsualizar curso</a>', esc_url(add_query_arg('preview', 'true', $permalink))),
        9 => sprintf('Curso programado para: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Previsualizar curso</a>', date_i18n('M j, Y @ G:i', strtotime($post->post_date)), esc_url($permalink)),
        10 => sprintf('Borrador de curso actualizado. <a target="_blank" href="%s">Previsualizar curso</a>', esc_url(add_query_arg('preview', 'true', $permalink)))
    );
    
    return $messages;
}
add_filter('post_updated_messages', 'courses_updated_messages');

/**
 * Agregar contadores en el menú
 */
function courses_add_course_count() {
    global $menu;
    
    $count_courses = wp_count_posts('course');
    
    if ($count_courses && $count_courses->publish) {
        foreach ($menu as $key => $val) {
            if ($val[2] == 'edit.php?post_type=course') {
                $menu[$key][0] .= ' <span class="update-plugins count-' . $count_courses->publish . '"><span class="plugin-count">' . $count_courses->publish . '</span></span>';
                break;
            }
        }
    }
}
add_action('admin_menu', 'courses_add_course_count');

/**
 * Agregar soporte para excerpt en editor de bloques
 */
function courses_enable_excerpt_on_course() {
    add_post_type_support('course', 'excerpt');
}
add_action('init', 'courses_enable_excerpt_on_course');
?>