<?php
/**
 * Reports CPT
 * Custom Post Type para reportes de cursos
 * 
 * @package CourseSystem
 * @subpackage Reports
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar Custom Post Type para reportes
 */
function course_register_reports_cpt() {
    $args = array(
        'labels' => array(
            'name' => 'Reportes de Cursos',
            'singular_name' => 'Reporte',
            'menu_name' => 'Reportes',
            'add_new' => 'Agregar Reporte',
            'add_new_item' => 'Agregar Nuevo Reporte',
            'edit_item' => 'Ver Reporte',
            'view_item' => 'Ver Reporte',
            'all_items' => 'Todos los Reportes'
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-warning',
        'menu_position' => 26,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow'
        ),
        'map_meta_cap' => true,
        'supports' => array('title', 'editor'),
        'show_in_rest' => false
    );
    
    register_post_type('course_report', $args);
}
add_action('init', 'course_register_reports_cpt');

/**
 * Agregar metaboxes para información del reporte
 */
function course_add_report_metaboxes() {
    add_meta_box(
        'report_details',
        'Detalles del Reporte',
        'course_render_report_details_metabox',
        'course_report',
        'side',
        'high'
    );
    
    add_meta_box(
        'report_status',
        'Estado del Reporte',
        'course_render_report_status_metabox',
        'course_report',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'course_add_report_metaboxes');

/**
 * Renderizar metabox de detalles
 */
function course_render_report_details_metabox($post) {
    $report_type = get_post_meta($post->ID, '_report_type', true);
    $product_id = get_post_meta($post->ID, '_product_id', true);
    $user_id = get_post_meta($post->ID, '_user_id', true);
    $report_date = get_post_meta($post->ID, '_report_date', true);
    
    $user = get_user_by('id', $user_id);
    $product = wc_get_product($product_id);
    
    $report_types = array(
        'outdated' => 'Versión desactualizada',
        'error' => 'Error en el curso',
        'broken_link' => 'Enlace roto',
        'wrong_info' => 'Información incorrecta',
        'other' => 'Otro problema'
    );
    
    echo '<div style="padding: 10px;">';
    echo '<p><strong>Tipo de reporte:</strong><br>';
    echo isset($report_types[$report_type]) ? $report_types[$report_type] : $report_type;
    echo '</p>';
    
    echo '<p><strong>Curso reportado:</strong><br>';
    if ($product) {
        echo '<a href="' . get_edit_post_link($product_id) . '" target="_blank">' . $product->get_name() . '</a>';
    }
    echo '</p>';
    
    echo '<p><strong>Reportado por:</strong><br>';
    if ($user) {
        echo $user->display_name . '<br>';
        echo '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
    }
    echo '</p>';
    
    echo '<p><strong>Fecha:</strong><br>';
    echo date('d/m/Y H:i', strtotime($report_date));
    echo '</p>';
    echo '</div>';
}

/**
 * Renderizar metabox de estado
 */
function course_render_report_status_metabox($post) {
    wp_nonce_field('save_report_status', 'report_status_nonce');
    
    $status = get_post_meta($post->ID, '_report_status', true);
    if (empty($status)) {
        $status = 'pending';
    }
    
    echo '<div style="padding: 10px;">';
    echo '<select name="report_status" style="width: 100%; padding: 8px; border-radius: 6px;">';
    echo '<option value="pending"' . selected($status, 'pending', false) . '>Pendiente</option>';
    echo '<option value="resolved"' . selected($status, 'resolved', false) . '>Resuelto</option>';
    echo '</select>';
    echo '<p style="margin: 10px 0 0 0; font-size: 13px; color: #6b7280;">Al marcar como "Resuelto", se enviará un email al usuario que reportó.</p>';
    echo '</div>';
}

/**
 * Guardar estado del reporte
 */
function course_save_report_status($post_id) {
    // Verificar nonce
    if (!isset($_POST['report_status_nonce']) || 
        !wp_verify_nonce($_POST['report_status_nonce'], 'save_report_status')) {
        return;
    }
    
    // Verificar autoguardado
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar estado
    if (isset($_POST['report_status'])) {
        $old_status = get_post_meta($post_id, '_report_status', true);
        $new_status = sanitize_text_field($_POST['report_status']);
        
        update_post_meta($post_id, '_report_status', $new_status);
        
        // Si cambió a resuelto, enviar email
        if ($old_status !== 'resolved' && $new_status === 'resolved') {
            course_send_report_resolved_email($post_id);
        }
    }
}
add_action('save_post_course_report', 'course_save_report_status');

/**
 * Enviar email de resolución
 */
function course_send_report_resolved_email($report_id) {
    $user_id = get_post_meta($report_id, '_user_id', true);
    $product_id = get_post_meta($report_id, '_product_id', true);
    $report_type = get_post_meta($report_id, '_report_type', true);
    
    $user = get_user_by('id', $user_id);
    $product = wc_get_product($product_id);
    
    if (!$user || !$product) {
        return;
    }
    
    $report_types = array(
        'outdated' => 'Versión desactualizada',
        'error' => 'Error en el curso',
        'broken_link' => 'Enlace roto',
        'wrong_info' => 'Información incorrecta',
        'other' => 'Otro problema'
    );
    
    $type_label = isset($report_types[$report_type]) ? $report_types[$report_type] : $report_type;
    
    $to = $user->user_email;
    $subject = 'Tu reporte ha sido resuelto';
    $message = sprintf(
        "Hola %s,\n\n" .
        "Te informamos que el reporte que realizaste sobre el curso \"%s\" ha sido revisado y resuelto.\n\n" .
        "Tipo de reporte: %s\n\n" .
        "Puedes volver a acceder al curso aquí: %s\n\n" .
        "Gracias por tu colaboración para mejorar nuestros cursos.\n\n" .
        "Saludos,\n%s",
        $user->display_name,
        $product->get_name(),
        $type_label,
        get_permalink($product_id),
        get_bloginfo('name')
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    wp_mail($to, $subject, $message, $headers);
}

/**
 * Personalizar columnas en listado de reportes
 */
function course_set_report_columns($columns) {
    return array(
        'cb' => $columns['cb'],
        'title' => 'Mensaje del Reporte',
        'report_type' => 'Tipo',
        'product' => 'Curso',
        'user' => 'Usuario',
        'status' => 'Estado',
        'date' => 'Fecha'
    );
}
add_filter('manage_course_report_posts_columns', 'course_set_report_columns');

/**
 * Rellenar columnas personalizadas
 */
function course_fill_report_columns($column, $post_id) {
    switch ($column) {
        case 'report_type':
            $type = get_post_meta($post_id, '_report_type', true);
            $icons = array(
                'outdated' => '📅',
                'error' => '❌',
                'broken_link' => '🔗',
                'wrong_info' => 'ℹ️',
                'other' => '🔧'
            );
            echo isset($icons[$type]) ? $icons[$type] : '🔹';
            break;
            
        case 'product':
            $product_id = get_post_meta($post_id, '_product_id', true);
            $product = wc_get_product($product_id);
            if ($product) {
                echo '<a href="' . get_edit_post_link($product_id) . '">' . $product->get_name() . '</a>';
            }
            break;
            
        case 'user':
            $user_id = get_post_meta($post_id, '_user_id', true);
            $user = get_user_by('id', $user_id);
            if ($user) {
                echo $user->display_name;
            }
            break;
            
        case 'status':
            $status = get_post_meta($post_id, '_report_status', true);
            if ($status === 'resolved') {
                echo '<span style="color: #46b450; font-weight: 600;">Resuelto</span>';
            } else {
                echo '<span style="color: #ffb900; font-weight: 600;">Pendiente</span>';
            }
            break;
    }
}
add_action('manage_course_report_posts_custom_column', 'course_fill_report_columns', 10, 2);
?>