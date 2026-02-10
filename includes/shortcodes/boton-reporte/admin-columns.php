<?php
/**
 * Columnas personalizadas en el admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// PASO 2: Agregar columnas personalizadas
function course_reports_custom_columns($columns) {
    $new_columns = array(
        'cb'            => $columns['cb'],
        'title'         => 'Tipo de Reporte',
        'product'       => 'Curso',
        'user'          => 'Usuario',
        'status'        => 'Estado',
        'date'          => 'Fecha'
    );
    return $new_columns;
}
add_filter('manage_course_report_posts_columns', 'course_reports_custom_columns');

// PASO 3: Rellenar las columnas personalizadas
function course_reports_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'product':
            $product_id = get_post_meta($post_id, '_product_id', true);
            $product = wc_get_product($product_id);
            if ($product) {
                echo '<a href="' . get_edit_post_link($product_id) . '">' . $product->get_name() . '</a>';
            }
            break;
        
        case 'user':
            $user_id = get_post_meta($post_id, '_user_id', true);
            $user = get_userdata($user_id);
            if ($user) {
                echo $user->display_name;
            }
            break;
        
        case 'status':
            $status = get_post_meta($post_id, '_report_status', true);
            if ($status === 'resolved') {
                echo '<span style="color: #46b450; font-weight: 600;">✅ Resuelto</span>';
            } else {
                echo '<span style="color: #ffb900; font-weight: 600;">⏳ Pendiente</span>';
            }
            break;
    }
}
add_action('manage_course_report_posts_custom_column', 'course_reports_custom_column_content', 10, 2);
?>