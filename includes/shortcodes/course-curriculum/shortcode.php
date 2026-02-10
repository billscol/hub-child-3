<?php
/**
 * Shortcode: [course_curriculum]
 * Muestra el currículum del curso con módulos y lecciones
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('course_curriculum', 'course_curriculum_shortcode');
function course_curriculum_shortcode($atts) {
    global $product;
    
    // Si no hay producto, intentar obtenerlo del post actual
    if (!$product) {
        global $post;
        if ($post) {
            $product = wc_get_product($post->ID);
        }
    }
    
    if (!$product) {
        return '';
    }
    
    $curriculum = get_post_meta($product->get_id(), '_course_curriculum', true);
    
    if (empty($curriculum)) {
        return '';
    }
    
    // Verificar si el usuario actual ha comprado el producto
    $current_user = wp_get_current_user();
    $has_bought = false;
    
    if ($current_user->ID > 0) {
        $has_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
    }
    
    $total_modules = count($curriculum);
    $total_lessons = 0;
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    
    ob_start();
    ?>
    <div class="course-curriculum-section" style="margin:40px 0;padding:30px;background:linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e;border:1px solid rgba(218, 4, 128, 0.3);border-radius:16px;box-shadow:0 4px 20px rgba(218, 4, 128, 0.1);">
        <div class="curriculum-stats" style="display:flex;gap:30px;margin-bottom:25px;color:#9ca3af;font-size:15px;flex-wrap:wrap;justify-content:center;">
            <div style="display:flex;align-items:center;gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span style="color:#da0480;"><strong><?php echo $total_modules; ?> MÓDULOS</strong></span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#da0480" stroke="#da0480" stroke-width="1">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span style="color:#da0480;"><strong><?php echo $total_lessons; ?> LECCIONES</strong></span>
            </div>
        </div>
        
        <div class="curriculum-accordion">
            <?php foreach ($curriculum as $index => $module) { 
                $is_locked = isset($module['locked']) ? $module['locked'] : false;
                $show_content = !$is_locked || $has_bought;
                ?>
            <div class="curriculum-module" style="border:1px solid rgba(218, 4, 128, 0.3);border-radius:12px;margin-bottom:15px;overflow:hidden;transition:all 0.3s;">
                <div class="module-header" style="padding:18px 20px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg, rgba(218, 4, 128, 0.15) 0%, rgba(30, 35, 45, 0.8) 100%);transition:all 0.3s;border-bottom:1px solid rgba(218, 4, 128, 0.2);" onclick="toggleModuleShortcode(this)" onmouseover="this.style.background='linear-gradient(135deg, rgba(218, 4, 128, 0.25) 0%, rgba(30, 35, 45, 0.9) 100%)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(218, 4, 128, 0.15) 0%, rgba(30, 35, 45, 0.8) 100%)'">
                    <span style="color:#fff;font-size:16px;font-weight:600;">
                        <?php if ($is_locked && !$has_bought) { ?>
                            🔒 
                        <?php } ?>
                        <?php echo $index + 1; ?>. <?php echo esc_html($module['name']); ?>
                    </span>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5" style="transition:transform 0.3s;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="module-content" style="max-height:0;overflow:hidden;transition:max-height 0.4s ease-in-out;">
                    <div class="module-lessons" style="padding:15px 20px;background:rgba(218, 4, 128, 0.05);">
                        <?php if ($show_content) { 
                            // MOSTRAR CONTENIDO - Módulo desbloqueado o usuario compró
                            if (!empty($module['lessons'])) {
                                foreach ($module['lessons'] as $lesson) { ?>
                                <div class="lesson-item-display" style="display:flex;align-items:center;gap:12px;padding:10px 0;color:#9ca3af;font-size:15px;border-bottom:1px solid rgba(218, 4, 128, 0.1);transition:all 0.2s;" onmouseover="this.style.paddingLeft='8px';this.style.color='#da0480'" onmouseout="this.style.paddingLeft='0';this.style.color='#9ca3af'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#da0480" stroke="#da0480" stroke-width="0">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span><?php echo esc_html($lesson); ?></span>
                                </div>
                            <?php }
                            }
                        } else { 
                            // MÓDULO BLOQUEADO - Mostrar mensaje
                            ?>
                            <div class="locked-content" style="padding:40px 30px;text-align:center;background:linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(20, 25, 35, 0.6) 100%);">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2" style="margin:0 auto 15px;display:block;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <h4 style="color:#da0480;margin:0 0 10px 0;font-size:18px;font-weight:700;">Contenido Privado</h4>
                                <p style="color:#9ca3af;margin:0;font-size:15px;">Disponible solo para nuestros alumnos.</p>
                                <p style="color:#6b7280;margin:10px 0 0 0;font-size:14px;">
                                    <?php 
                                    if (!empty($module['lessons'])) {
                                        echo '▶ ' . count($module['lessons']) . ' lecciones en este módulo';
                                    }
                                    ?>
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <style>
        @media (max-width: 768px) {
            .course-curriculum-section {
                padding: 20px !important;
                margin: 30px 0 !important;
            }
            .curriculum-stats {
                gap: 20px !important;
                font-size: 13px !important;
            }
            .curriculum-stats > div {
                gap: 6px !important;
            }
            .curriculum-stats svg {
                width: 18px !important;
                height: 18px !important;
            }
            .curriculum-stats span {
                font-size: 13px !important;
            }
            .module-header {
                padding: 15px !important;
            }
            .module-header span {
                font-size: 14px !important;
            }
            .lesson-item-display {
                font-size: 14px !important;
            }
            .locked-content {
                padding: 30px 20px !important;
            }
        }
    </style>
    
    <script>
    function toggleModuleShortcode(header) {
        var module = header.closest('.curriculum-module');
        var content = module.querySelector('.module-content');
        var chevron = module.querySelector('.chevron-icon');
        var isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
        
        if (isOpen) {
            content.style.maxHeight = '0';
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            chevron.style.transform = 'rotate(180deg)';
        }
    }
    </script>
    <?php
    
    return ob_get_clean();
}

?>