<?php
/**
 * Reports Button
 * Botón y modal para reportar problemas en cursos
 * 
 * @package CourseSystem
 * @subpackage Reports
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode del botón de reporte
 * Uso: [boton_reporte]
 */
function course_report_button_shortcode($atts) {
    // Verificar que el usuario esté logueado
    if (!is_user_logged_in()) {
        return '';
    }
    
    global $product;
    
    // Validar que product sea un objeto válido
    if (!is_object($product) || !method_exists($product, 'get_id')) {
        $product = wc_get_product(get_the_ID());
    }
    
    if (!$product) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="course-report-wrapper">
        <!-- Botón de reporte -->
        <button type="button" class="course-report-btn" id="openReportModal" 
                style="background: transparent; 
                       border: 1px solid rgba(255, 255, 255, 0.15); 
                       color: rgba(255, 255, 255, 0.5); 
                       padding: 4px 10px; 
                       border-radius: 4px; 
                       font-size: 11px; 
                       cursor: pointer; 
                       transition: all 0.3s; 
                       display: inline-flex; 
                       align-items: center; 
                       gap: 5px; 
                       margin: 5px 0;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span class="report-btn-text">Reportar problema</span>
        </button>
        
        <!-- Modal de Reporte -->
        <div id="reportModal" class="report-modal" style="display: none;">
            <div class="report-modal-overlay"></div>
            <div class="report-modal-content">
                <div class="report-modal-header">
                    <h3 style="margin: 0; font-size: 18px; color: #fff;">⚠️ Reportar un problema con este curso</h3>
                    <button type="button" class="report-modal-close" id="closeReportModal">&times;</button>
                </div>
                
                <form id="courseReportForm" style="padding: 20px;">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                    <input type="hidden" name="action" value="submit_course_report">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('course_report_nonce'); ?>">
                    
                    <!-- Tipo de problema -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #e0e0e0;">¿Qué tipo de problema encontraste?</label>
                        <select name="report_type" required 
                                style="width: 100%; padding: 10px; border: 1px solid #3a3a3a; border-radius: 4px; background: #2a2a2a; color: #e0e0e0; font-size: 14px;">
                            <option value="">Selecciona una opción</option>
                            <option value="outdated">El curso está desactualizado</option>
                            <option value="error">Hay un error en el curso</option>
                            <option value="broken_link">Enlace roto o no funciona</option>
                            <option value="wrong_info">Información incorrecta</option>
                            <option value="other">Otro problema</option>
                        </select>
                    </div>
                    
                    <!-- Mensaje -->
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #e0e0e0;">Describe el problema</label>
                        <textarea name="report_message" rows="4" 
                                  placeholder="Cuéntanos más detalles sobre el problema..." 
                                  style="width: 100%; padding: 10px; border: 1px solid #3a3a3a; border-radius: 4px; resize: vertical; background: #2a2a2a; color: #e0e0e0; font-size: 14px;"></textarea>
                    </div>
                    
                    <!-- Response message -->
                    <div id="reportResponse" style="margin-bottom: 15px; display: none; padding: 10px; border-radius: 4px;"></div>
                    
                    <!-- Botones -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="cancelReport" 
                                style="padding: 10px 20px; background: #3a3a3a; color: #e0e0e0; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                            Cancelar
                        </button>
                        <button type="submit" 
                                style="padding: 10px 20px; background: #da0480; color: #000; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                            Enviar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
    .course-report-btn:hover {
        border-color: rgba(255, 255, 255, 0.3);
        color: rgba(255, 255, 255, 0.8);
    }
    
    /* Modal */
    .report-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .report-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
    }
    
    .report-modal-content {
        position: relative;
        background: #1a1f2e;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(218, 4, 128, 0.3);
    }
    
    .report-modal-header {
        padding: 20px;
        border-bottom: 1px solid rgba(218, 4, 128, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .report-modal-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        transition: color 0.2s;
    }
    
    .report-modal-close:hover {
        color: #da0480;
    }
    
    .report-success {
        background: #10b981;
        color: #fff;
    }
    
    .report-error {
        background: #ef4444;
        color: #fff;
    }
    
    button:hover {
        background: #c00370;
        color: #fff;
    }
    
    #cancelReport:hover {
        background: #4d1a1a;
        color: #fc7c7c;
        border: 1px solid #6a2d2d;
    }
    
    #cancelReport:hover {
        background: #4a4a4a;
    }
    
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #da0480;
    }
    
    /* RESPONSIVE - Ocultar texto en tablets y móviles */
    @media (max-width: 1024px) {
        .course-report-btn .report-btn-text {
            display: none;
        }
        
        .course-report-btn {
            padding: 6px;
        }
        
        .report-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 20px;
        }
        
        .report-modal-content {
            max-width: 100%;
        }
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        const modal = $('#reportModal');
        const openBtn = $('#openReportModal');
        const closeBtn = $('#closeReportModal');
        const cancelBtn = $('#cancelReport');
        const form = $('#courseReportForm');
        const response = $('#reportResponse');
        
        // Abrir modal
        openBtn.on('click', function() {
            modal.fadeIn(200);
            $('body').css('overflow', 'hidden');
        });
        
        // Cerrar modal
        function closeModal() {
            modal.fadeOut(200);
            $('body').css('overflow', '');
            form[0].reset();
            response.hide();
        }
        
        closeBtn.on('click', closeModal);
        cancelBtn.on('click', closeModal);
        
        // Cerrar al hacer clic en el overlay
        $('.report-modal-overlay').on('click', closeModal);
        
        // Enviar formulario
        form.on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Enviando...');
            response.hide();
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success) {
                        response.removeClass('report-error').addClass('report-success')
                               .html('Reporte enviado correctamente. ¡Gracias por tu ayuda!')
                               .fadeIn();
                        
                        setTimeout(function() {
                            closeModal();
                        }, 2000);
                    } else {
                        response.removeClass('report-success').addClass('report-error')
                               .html(res.data + ' - Error al enviar el reporte')
                               .fadeIn();
                    }
                },
                error: function() {
                    response.removeClass('report-success').addClass('report-error')
                           .html('Error de conexión. Intenta nuevamente.')
                           .fadeIn();
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Enviar Reporte');
                }
            });
        });
    });
    </script>
    <?php
    
    return ob_get_clean();
}
add_shortcode('boton_reporte', 'course_report_button_shortcode');
?>