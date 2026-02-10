<?php
/**
 * Shortcode: [boton_reporte]
 * Botón para reportar problemas en cursos
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('course_report_button_shortcode')) {
    add_shortcode('boton_reporte', 'course_report_button_shortcode');
    function course_report_button_shortcode($atts) {
        // Verificar si el usuario está logueado
        if (!is_user_logged_in()) {
            return '';
        }
        
        global $product;
        
        // Validar que $product sea un objeto válido
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            $product = wc_get_product(get_the_ID());
        }
        
        if (!$product) {
            return '';
        }
        
        $product_id = $product->get_id();
        $unique_id = 'report_modal_' . $product_id . '_' . uniqid();
        
        ob_start();
        ?>
        
        <style>
            .report-button-container {
                margin: 20px 0;
            }
            
            .report-button {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05));
                color: #da0480;
                border: 2px solid rgba(218, 4, 128, 0.3);
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
            }
            
            .report-button:hover {
                background: linear-gradient(135deg, rgba(218, 4, 128, 0.2), rgba(218, 4, 128, 0.1));
                border-color: #da0480;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(218, 4, 128, 0.2);
            }
            
            .report-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 999999;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(5px);
            }
            
            .report-modal-content {
                background: linear-gradient(135deg, #1a1f2e, #0f1419);
                padding: 30px;
                border-radius: 16px;
                max-width: 500px;
                width: 90%;
                border: 2px solid rgba(218, 4, 128, 0.3);
                box-shadow: 0 10px 40px rgba(218, 4, 128, 0.2);
                position: relative;
            }
            
            .report-modal h3 {
                color: #da0480;
                margin: 0 0 20px 0;
                font-size: 22px;
                font-weight: 700;
            }
            
            .report-form-group {
                margin-bottom: 20px;
            }
            
            .report-form-group label {
                display: block;
                color: #e2e8f0;
                margin-bottom: 8px;
                font-weight: 600;
                font-size: 14px;
            }
            
            .report-form-group select,
            .report-form-group textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid rgba(218, 4, 128, 0.3);
                border-radius: 8px;
                background: rgba(26, 38, 64, 0.8);
                color: #fff;
                font-size: 14px;
                font-family: inherit;
            }
            
            .report-form-group textarea {
                min-height: 100px;
                resize: vertical;
            }
            
            .report-form-group select:focus,
            .report-form-group textarea:focus {
                outline: none;
                border-color: #da0480;
                box-shadow: 0 0 0 4px rgba(218, 4, 128, 0.1);
            }
            
            .report-buttons {
                display: flex;
                gap: 12px;
                margin-top: 25px;
            }
            
            .report-submit-btn {
                flex: 1;
                background: linear-gradient(135deg, #da0480, #b00368);
                color: white;
                border: none;
                padding: 14px 20px;
                border-radius: 8px;
                font-weight: 700;
                font-size: 15px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .report-submit-btn:hover {
                background: linear-gradient(135deg, #b00368, #8a0252);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(218, 4, 128, 0.4);
            }
            
            .report-cancel-btn {
                flex: 1;
                background: rgba(255, 255, 255, 0.1);
                color: #e2e8f0;
                border: 2px solid rgba(255, 255, 255, 0.2);
                padding: 14px 20px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 15px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .report-cancel-btn:hover {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.3);
            }
            
            .report-close-x {
                position: absolute;
                top: 15px;
                right: 15px;
                background: rgba(218, 4, 128, 0.2);
                color: #da0480;
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s;
            }
            
            .report-close-x:hover {
                background: #da0480;
                color: white;
                transform: rotate(90deg);
            }
            
            @media (max-width: 768px) {
                .report-modal-content {
                    padding: 25px 20px;
                }
                
                .report-buttons {
                    flex-direction: column;
                }
                
                .report-button {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
        
        <div class="report-button-container">
            <button class="report-button" onclick="openReportModal<?php echo $unique_id; ?>()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                Reportar un problema
            </button>
        </div>
        
        <div id="report-modal-<?php echo $unique_id; ?>" class="report-modal">
            <div class="report-modal-content">
                <button class="report-close-x" onclick="closeReportModal<?php echo $unique_id; ?>()">×</button>
                
                <h3>⚠️ Reportar Problema</h3>
                
                <form id="report-form-<?php echo $unique_id; ?>" method="post">
                    <div class="report-form-group">
                        <label>¿Qué tipo de problema encontraste? *</label>
                        <select name="report_type" required>
                            <option value="">Selecciona una opción</option>
                            <option value="contenido_incompleto">Contenido Incompleto</option>
                            <option value="error_tecnico">Error Técnico</option>
                            <option value="informacion_incorrecta">Información Incorrecta</option>
                            <option value="problema_acceso">Problema de Acceso</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="report-form-group">
                        <label>Describe el problema *</label>
                        <textarea name="report_description" placeholder="Por favor describe el problema que encontraste..." required></textarea>
                    </div>
                    
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                    <input type="hidden" name="action" value="submit_course_report">
                    <?php wp_nonce_field('submit_course_report_' . $product_id, 'report_nonce'); ?>
                    
                    <div class="report-buttons">
                        <button type="submit" class="report-submit-btn">Enviar Reporte</button>
                        <button type="button" class="report-cancel-btn" onclick="closeReportModal<?php echo $unique_id; ?>()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        function openReportModal<?php echo $unique_id; ?>() {
            document.getElementById('report-modal-<?php echo $unique_id; ?>').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeReportModal<?php echo $unique_id; ?>() {
            document.getElementById('report-modal-<?php echo $unique_id; ?>').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Cerrar al hacer clic fuera del modal
        document.getElementById('report-modal-<?php echo $unique_id; ?>').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReportModal<?php echo $unique_id; ?>();
            }
        });
        
        // Manejar envío del formulario con AJAX
        jQuery(document).ready(function($) {
            $('#report-form-<?php echo $unique_id; ?>').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('✅ Tu reporte ha sido enviado. Gracias por ayudarnos a mejorar.');
                            closeReportModal<?php echo $unique_id; ?>();
                            $('#report-form-<?php echo $unique_id; ?>')[0].reset();
                        } else {
                            alert('❌ Error: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('❌ Ocurrió un error. Por favor inténtalo de nuevo.');
                    }
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
}

// Procesar el formulario de reporte via AJAX
function process_course_report_ajax() {
    // Verificar nonce
    $product_id = intval($_POST['product_id']);
    
    if (!isset($_POST['report_nonce']) || !wp_verify_nonce($_POST['report_nonce'], 'submit_course_report_' . $product_id)) {
        wp_send_json_error(array('message' => 'Error de seguridad'));
    }
    
    // Verificar usuario logueado
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Debes estar logueado'));
    }
    
    $report_type = sanitize_text_field($_POST['report_type']);
    $description = sanitize_textarea_field($_POST['report_description']);
    $user_id = get_current_user_id();
    
    // Crear el reporte
    $report_types = array(
        'contenido_incompleto' => 'Contenido Incompleto',
        'error_tecnico' => 'Error Técnico',
        'informacion_incorrecta' => 'Información Incorrecta',
        'problema_acceso' => 'Problema de Acceso',
        'otro' => 'Otro'
    );
    
    $post_data = array(
        'post_title'    => isset($report_types[$report_type]) ? $report_types[$report_type] : 'Reporte',
        'post_type'     => 'course_report',
        'post_status'   => 'publish'
    );
    
    $report_id = wp_insert_post($post_data);
    
    if ($report_id) {
        update_post_meta($report_id, '_report_type', $report_type);
        update_post_meta($report_id, '_product_id', $product_id);
        update_post_meta($report_id, '_user_id', $user_id);
        update_post_meta($report_id, '_report_description', $description);
        update_post_meta($report_id, '_report_status', 'pending');
        update_post_meta($report_id, '_report_date', current_time('mysql'));
        
        wp_send_json_success(array('message' => 'Reporte enviado correctamente'));
    } else {
        wp_send_json_error(array('message' => 'Error al guardar el reporte'));
    }
}
add_action('wp_ajax_submit_course_report', 'process_course_report_ajax');
?>