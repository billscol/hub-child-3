<?php
/**
 * Curriculum Display
 * Display automático del curriculum en productos
 * 
 * @package CourseSystem
 * @subpackage Curriculum
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mostrar curriculum en página de producto
 */
function course_display_curriculum() {
    global $product;
    
    $curriculum = get_post_meta($product->get_id(), '_course_curriculum', true);
    
    if (empty($curriculum)) {
        return;
    }
    
    // Verificar si el usuario compró el producto
    $current_user = wp_get_current_user();
    $has_bought = false;
    
    if ($current_user->ID > 0) {
        $has_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
    }
    
    // Calcular totales
    $total_modules = count($curriculum);
    $total_lessons = 0;
    
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    
    ?>
    <style>
    .course-curriculum-section {
        margin: 50px 0;
        padding: 35px;
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(218, 4, 128, 0.03) 100%);
        border: 2px solid rgba(218, 4, 128, 0.25);
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(218, 4, 128, 0.1);
    }
    
    .curriculum-main-title {
        font-size: 28px;
        font-weight: 700;
        color: #da0480;
        margin: 0 0 25px 0;
        text-align: center;
    }
    
    .curriculum-stats {
        display: flex;
        gap: 25px;
        margin-bottom: 30px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .curriculum-stat-box {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(218, 4, 128, 0.1);
        padding: 15px 25px;
        border-radius: 12px;
        border: 1px solid rgba(218, 4, 128, 0.3);
        box-shadow: 0 4px 12px rgba(218, 4, 128, 0.08);
    }
    
    .curriculum-stat-box svg {
        flex-shrink: 0;
        color: #da0480;
    }
    
    .curriculum-stat-box span {
        font-size: 16px;
        font-weight: 700;
        color: #da0480;
        letter-spacing: 0.5px;
    }
    
    .curriculum-accordion {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    
    .curriculum-module {
        border: 2px solid rgba(218, 4, 128, 0.25);
        border-radius: 14px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(218, 4, 128, 0.08);
    }
    
    .curriculum-module:hover {
        border-color: rgba(218, 4, 128, 0.5);
        box-shadow: 0 8px 20px rgba(218, 4, 128, 0.18);
        transform: translateY(-3px);
    }
    
    .module-header {
        padding: 20px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.12) 0%, rgba(218, 4, 128, 0.08) 100%);
        transition: all 0.3s;
        border-bottom: 1px solid rgba(218, 4, 128, 0.15);
    }
    
    .module-header:hover {
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.18) 0%, rgba(218, 4, 128, 0.12) 100%);
    }
    
    .module-title {
        font-size: 17px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .module-number {
        background: #da0480;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }
    
    .chevron-icon {
        transition: transform 0.3s;
        flex-shrink: 0;
    }
    
    .module-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-in-out;
    }
    
    .module-lessons {
        padding: 20px 25px;
        background: rgba(218, 4, 128, 0.03);
    }
    
    .lesson-item-display {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        color: #374151;
        font-size: 15px;
        font-weight: 500;
        border-bottom: 1px solid rgba(218, 4, 128, 0.12);
        transition: all 0.2s;
    }
    
    .lesson-item-display:last-child {
        border-bottom: none;
    }
    
    .lesson-item-display:hover {
        padding-left: 8px;
        color: #da0480;
    }
    
    .lesson-item-display svg {
        flex-shrink: 0;
        color: #da0480;
    }
    
    .lesson-icon-wrapper {
        width: 36px;
        height: 36px;
        background: rgba(218, 4, 128, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .locked-content {
        padding: 50px 25px;
        text-align: center;
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.05) 0%, rgba(218, 4, 128, 0.02) 100%);
    }
    
    .locked-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: rgba(218, 4, 128, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(218, 4, 128, 0.3);
    }
    
    .locked-content h4 {
        color: #da0480;
        margin: 0 0 12px 0;
        font-size: 24px;
        font-weight: 800;
    }
    
    .locked-content p {
        color: #6b7280;
        margin: 10px 0;
        font-size: 16px;
        line-height: 1.6;
    }
    
    .locked-lesson-count {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        padding: 10px 20px;
        background: rgba(218, 4, 128, 0.1);
        border-radius: 20px;
        color: #da0480;
        font-weight: 600;
        font-size: 14px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .course-curriculum-section {
            padding: 25px 20px;
            margin: 35px 0;
            border-radius: 16px;
        }
        
        .curriculum-main-title {
            font-size: 22px;
            margin-bottom: 20px;
        }
        
        .curriculum-stats {
            flex-direction: column;
            gap: 12px;
        }
        
        .curriculum-stat-box {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
        }
        
        .module-header {
            padding: 16px 18px;
        }
        
        .module-title {
            font-size: 15px;
        }
        
        .module-number {
            width: 28px;
            height: 28px;
            font-size: 13px;
        }
        
        .lesson-item-display {
            font-size: 14px;
            padding: 12px 0;
        }
        
        .lesson-icon-wrapper {
            width: 32px;
            height: 32px;
        }
        
        .locked-content {
            padding: 40px 20px;
        }
        
        .locked-icon-wrapper {
            width: 70px;
            height: 70px;
        }
        
        .locked-content h4 {
            font-size: 20px;
        }
        
        .locked-content p {
            font-size: 15px;
        }
    }
    </style>
    
    <div class="course-curriculum-section">
        <h3 class="curriculum-main-title">📚 Contenido del Curso</h3>
        
        <!-- Estadísticas -->
        <div class="curriculum-stats">
            <div class="curriculum-stat-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                </svg>
                <span><?php echo $total_modules; ?> MÓDULOS</span>
            </div>
            <div class="curriculum-stat-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span><?php echo $total_lessons; ?> LECCIONES</span>
            </div>
        </div>
        
        <!-- Acordeón de módulos -->
        <div class="curriculum-accordion">
            <?php foreach ($curriculum as $index => $module) : 
                $is_locked = isset($module['locked']) ? $module['locked'] : false;
                $show_content = !$is_locked || $has_bought;
            ?>
                <div class="curriculum-module">
                    <div class="module-header" onclick="toggleModule(this)">
                        <div class="module-title">
                            <span class="module-number"><?php echo $index + 1; ?></span>
                            <?php if ($is_locked && !$has_bought) : ?>
                                <span>🔒</span>
                            <?php endif; ?>
                            <span><?php echo esc_html($module['name']); ?></span>
                        </div>
                        <svg class="chevron-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    
                    <div class="module-content">
                        <div class="module-lessons">
                            <?php if ($show_content) : // MOSTRAR CONTENIDO ?>
                                <?php if (!empty($module['lessons'])) : ?>
                                    <?php foreach ($module['lessons'] as $lesson) : ?>
                                        <div class="lesson-item-display">
                                            <div class="lesson-icon-wrapper">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                                </svg>
                                            </div>
                                            <span><?php echo esc_html($lesson); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php else : // MÓDULO BLOQUEADO ?>
                                <div class="locked-content">
                                    <div class="locked-icon-wrapper">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                    </div>
                                    <h4>🔒 Contenido Privado</h4>
                                    <p>Este módulo está disponible solo para nuestros alumnos.<br>Adquiere el curso para desbloquear todo el contenido.</p>
                                    <?php if (!empty($module['lessons'])) : ?>
                                        <div class="locked-lesson-count">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg>
                                            <span><?php echo count($module['lessons']); ?> lecciones en este módulo</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
    function toggleModule(header) {
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
}
add_action('woocommerce_after_single_product_summary', 'course_display_curriculum', 15);
?>