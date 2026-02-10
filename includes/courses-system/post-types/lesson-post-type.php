<?php
/**
 * Custom Post Type: Lesson (Lección)
 * Define el CPT de lecciones
 * 
 * @package CoursesSystem
 * @subpackage PostTypes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar CPT Lesson
 */
function courses_register_lesson_post_type() {
    $labels = array(
        'name' => 'Lecciones',
        'singular_name' => 'Lección',
        'menu_name' => 'Lecciones',
        'add_new' => 'Añadir Lección',
        'add_new_item' => 'Añadir Nueva Lección',
        'edit_item' => 'Editar Lección',
        'new_item' => 'Nueva Lección',
        'view_item' => 'Ver Lección',
        'view_items' => 'Ver Lecciones',
        'search_items' => 'Buscar Lecciones',
        'not_found' => 'No se encontraron lecciones',
        'not_found_in_trash' => 'No hay lecciones en la papelera',
        'parent_item_colon' => 'Curso Padre:',
        'all_items' => 'Todas las Lecciones',
        'archives' => 'Archivo de Lecciones',
        'attributes' => 'Atributos de la Lección',
        'insert_into_item' => 'Insertar en lección',
        'uploaded_to_this_item' => 'Subido a esta lección'
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=course', // Submenu de cursos
        'query_var' => true,
        'rewrite' => array('slug' => 'leccion'),
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => true, // Permite padre (curso)
        'menu_position' => null,
        'menu_icon' => 'dashicons-media-text',
        'supports' => array('title', 'editor', 'thumbnail', 'page-attributes', 'comments'),
        'show_in_rest' => true
    );
    
    register_post_type('lesson', $args);
}
add_action('init', 'courses_register_lesson_post_type');

/**
 * Modificar título de entrada en admin
 */
function lessons_change_title_text($title) {
    $screen = get_current_screen();
    
    if ('lesson' === $screen->post_type) {
        $title = 'Escribe el título de la lección';
    }
    
    return $title;
}
add_filter('enter_title_here', 'lessons_change_title_text');

/**
 * Mensajes personalizados para lecciones
 */
function lessons_updated_messages($messages) {
    global $post, $post_ID;
    
    $permalink = get_permalink($post_ID);
    
    $messages['lesson'] = array(
        0 => '',
        1 => sprintf('Lección actualizada. <a href="%s">Ver lección</a>', esc_url($permalink)),
        2 => 'Campo personalizado actualizado.',
        3 => 'Campo personalizado eliminado.',
        4 => 'Lección actualizada.',
        5 => isset($_GET['revision']) ? sprintf('Lección restaurada a revisión del %s', wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => sprintf('Lección publicada. <a href="%s">Ver lección</a>', esc_url($permalink)),
        7 => 'Lección guardada.',
        8 => sprintf('Lección enviada. <a target="_blank" href="%s">Previsualizar lección</a>', esc_url(add_query_arg('preview', 'true', $permalink))),
        9 => sprintf('Lección programada para: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Previsualizar lección</a>', date_i18n('M j, Y @ G:i', strtotime($post->post_date)), esc_url($permalink)),
        10 => sprintf('Borrador de lección actualizado. <a target="_blank" href="%s">Previsualizar lección</a>', esc_url(add_query_arg('preview', 'true', $permalink)))
    );
    
    return $messages;
}
add_filter('post_updated_messages', 'lessons_updated_messages');

/**
 * Modificar columnas de admin de lecciones
 */
function lessons_modify_columns($columns) {
    // Reordenar columnas
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['course'] = 'Curso';
            $new_columns['order'] = 'Orden';
        } else if ($key !== 'date') {
            $new_columns[$key] = $value;
        }
    }
    
    $new_columns['date'] = 'Fecha';
    
    return $new_columns;
}
add_filter('manage_lesson_posts_columns', 'lessons_modify_columns');

/**
 * Mostrar contenido de columnas personalizadas
 */
function lessons_custom_columns($column, $post_id) {
    switch ($column) {
        case 'course':
            $lesson = get_post($post_id);
            if ($lesson->post_parent) {
                $course = get_post($lesson->post_parent);
                if ($course) {
                    echo '<a href="' . get_edit_post_link($course->ID) . '">' . esc_html($course->post_title) . '</a>';
                } else {
                    echo '<span style="color: #999;">Sin curso</span>';
                }
            } else {
                echo '<span style="color: #dc2626; font-weight: 600;">⚠️ Sin curso asignado</span>';
            }
            break;
            
        case 'order':
            $lesson = get_post($post_id);
            echo '<strong>' . esc_html($lesson->menu_order) . '</strong>';
            break;
    }
}
add_action('manage_lesson_posts_custom_column', 'lessons_custom_columns', 10, 2);

/**
 * Hacer columnas sortables
 */
function lessons_sortable_columns($columns) {
    $columns['course'] = 'parent';
    $columns['order'] = 'menu_order';
    return $columns;
}
add_filter('manage_edit-lesson_sortable_columns', 'lessons_sortable_columns');

/**
 * Modificar query para ordenamiento
 */
function lessons_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ('lesson' !== $query->get('post_type')) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('menu_order' === $orderby) {
        $query->set('orderby', 'menu_order');
    }
}
add_action('pre_get_posts', 'lessons_column_orderby');

/**
 * Agregar filtro por curso en admin
 */
function lessons_add_course_filter() {
    global $typenow;
    
    if ($typenow === 'lesson') {
        $courses = get_posts(array(
            'post_type' => 'course',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        $current_course = isset($_GET['course_filter']) ? intval($_GET['course_filter']) : 0;
        
        echo '<select name="course_filter">';
        echo '<option value="0">Todos los cursos</option>';
        
        foreach ($courses as $course) {
            printf(
                '<option value="%d"%s>%s</option>',
                $course->ID,
                selected($current_course, $course->ID, false),
                esc_html($course->post_title)
            );
        }
        
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'lessons_add_course_filter');

/**
 * Aplicar filtro por curso
 */
function lessons_apply_course_filter($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'lesson' && isset($_GET['course_filter'])) {
        $course_id = intval($_GET['course_filter']);
        
        if ($course_id > 0) {
            $query->set('post_parent', $course_id);
        }
    }
}
add_action('pre_get_posts', 'lessons_apply_course_filter');

/**
 * Aviso si la lección no tiene curso asignado
 */
function lessons_admin_notice() {
    global $post, $pagenow;
    
    if ($pagenow === 'post.php' && isset($post) && $post->post_type === 'lesson' && !$post->post_parent) {
        ?>
        <div class="notice notice-warning">
            <p><strong>⚠️ Atención:</strong> Esta lección no tiene un curso asignado. Por favor, selecciona un curso padre en el panel "Atributos de la página".</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'lessons_admin_notice');
?>