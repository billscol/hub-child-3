<?php
/**
 * Metabox para editar reportes en el admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Agregar metabox
function add_course_report_metabox() {
    add_meta_box(
        'course_report_details',
        'Detalles del Reporte',
        'render_course_report_metabox',
        'course_report',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_course_report_metabox');

// Renderizar metabox
function render_course_report_metabox($post) {
    wp_nonce_field('course_report_metabox', 'course_report_metabox_nonce');
    
    $report_type = get_post_meta($post->ID, '_report_type', true);
    $product_id = get_post_meta($post->ID, '_product_id', true);
    $user_id = get_post_meta($post->ID, '_user_id', true);
    $description = get_post_meta($post->ID, '_report_description', true);
    $status = get_post_meta($post->ID, '_report_status', true);
    $report_date = get_post_meta($post->ID, '_report_date', true);
    
    $product = wc_get_product($product_id);
    $user = get_userdata($user_id);
    
    $report_types = array(
        'contenido_incompleto' => 'Contenido Incompleto',
        'error_tecnico' => 'Error Técnico',
        'informacion_incorrecta' => 'Información Incorrecta',
        'problema_acceso' => 'Problema de Acceso',
        'otro' => 'Otro'
    );
    ?>
    
    <style>
        .report-field {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #da0480;
            border-radius: 4px;
        }
        .report-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .report-field select,
        .report-field textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .report-field textarea {
            min-height: 120px;
            font-family: inherit;
        }
        .report-info {
            background: #e8f4f8;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .report-info strong {
            color: #0073aa;
        }
    </style>
    
    <div class="report-info">
        <p style="margin: 0 0 8px 0;">
            <strong>Tipo de reporte:</strong>
            <?php echo isset($report_types[$report_type]) ? $report_types[$report_type] : $report_type; ?>
        </p>
        
        <p style="margin: 0 0 8px 0;">
            <strong>Curso reportado:</strong>
            <?php if ($product) {
                echo '<a href="' . get_edit_post_link($product_id) . '">' . $product->get_name() . '</a>';
            } ?>
        </p>
        
        <p style="margin: 0 0 8px 0;">
            <strong>Reportado por:</strong>
            <?php if ($user) {
                echo $user->display_name . '<br>';
                echo '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
            } ?>
        </p>
        
        <p style="margin: 0;">
            <strong>Fecha:</strong>
            <?php echo date('d/m/Y H:i', strtotime($report_date)); ?>
        </p>
    </div>
    
    <div class="report-field">
        <label>Descripción del problema:</label>
        <textarea readonly><?php echo esc_textarea($description); ?></textarea>
    </div>
    
    <div class="report-field">
        <label>Estado del reporte:</label>
        <select name="report_status">
            <option value="pending" <?php selected($status, 'pending'); ?>>⏳ Pendiente</option>
            <option value="resolved" <?php selected($status, 'resolved'); ?>>✅ Resuelto</option>
        </select>
        <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;">
            Al cambiar a "Resuelto", se enviará un email al usuario.
        </p>
    </div>
    <?php
}

// Guardar metabox
function save_course_report_metabox($post_id) {
    if (!isset($_POST['course_report_metabox_nonce']) || 
        !wp_verify_nonce($_POST['course_report_metabox_nonce'], 'course_report_metabox')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['report_status'])) {
        $old_status = get_post_meta($post_id, '_report_status', true);
        $new_status = sanitize_text_field($_POST['report_status']);
        
        update_post_meta($post_id, '_report_status', $new_status);
        
        // Si cambió a "resuelto", enviar email
        if ($old_status !== 'resolved' && $new_status === 'resolved') {
            send_report_resolved_email($post_id);
        }
    }
}
add_action('save_post_course_report', 'save_course_report_metabox');
?>