<?php
/**
 * Reports Handler
 * Procesamiento AJAX de reportes
 * 
 * @package CourseSystem
 * @subpackage Reports
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Procesar reporte via AJAX
 */
function course_handle_report_submission() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'course_report_nonce')) {
        wp_send_json_error('Seguridad inválida');
    }
    
    // Verificar usuario logueado
    if (!is_user_logged_in()) {
        wp_send_json_error('Debes iniciar sesión');
    }
    
    // Validar datos
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : '';
    $report_message = isset($_POST['report_message']) ? sanitize_textarea_field($_POST['report_message']) : '';
    
    if (!$product_id || !$report_type) {
        wp_send_json_error('Datos incompletos');
    }
    
    // Obtener información del producto
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error('Producto no encontrado');
    }
    
    $user = wp_get_current_user();
    
    // Crear el reporte
    $report_title = sprintf('Reporte: %s', $product->get_name());
    
    $report_id = wp_insert_post(array(
        'post_type' => 'course_report',
        'post_title' => $report_title,
        'post_content' => $report_message,
        'post_status' => 'publish'
    ));
    
    if ($report_id) {
        // Guardar metadatos
        update_post_meta($report_id, '_report_type', $report_type);
        update_post_meta($report_id, '_product_id', $product_id);
        update_post_meta($report_id, '_user_id', $user->ID);
        update_post_meta($report_id, '_report_date', current_time('mysql'));
        update_post_meta($report_id, '_report_status', 'pending');
        
        wp_send_json_success('Reporte enviado correctamente');
    } else {
        wp_send_json_error('Error al crear el reporte');
    }
}
add_action('wp_ajax_submit_course_report', 'course_handle_report_submission');
?>