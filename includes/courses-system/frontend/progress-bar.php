<?php
/**
 * Barra de Progreso
 * Muestra visualmente el progreso del usuario en un curso
 * 
 * @package CoursesSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderizar barra de progreso
 */
function courses_render_progress_bar($user_id, $course_id, $args = array()) {
    $defaults = array(
        'show_percentage' => true,
        'show_lessons' => true,
        'height' => '12px',
        'color' => '#10b981',
        'bg_color' => '#e5e7eb',
        'border_radius' => '20px',
        'show_label' => true
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $progress = courses_get_user_progress($user_id, $course_id);
    $percentage = $progress['percentage'];
    $completed = $progress['completed'];
    $total = $progress['total'];
    
    ?>
    <div class="course-progress-bar-wrapper" style="margin: 0;">
        <?php if ($args['show_label']) : ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 14px; font-weight: 600; color: #1f2937;">
                    Tu Progreso
                </span>
                <?php if ($args['show_percentage']) : ?>
                    <span style="font-size: 20px; font-weight: 800; color: <?php echo esc_attr($args['color']); ?>;">
                        <?php echo number_format($percentage, 0); ?>%
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="progress-bar-bg" style="width: 100%; height: <?php echo esc_attr($args['height']); ?>; background: <?php echo esc_attr($args['bg_color']); ?>; border-radius: <?php echo esc_attr($args['border_radius']); ?>; overflow: hidden; position: relative; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);">
            <div class="progress-bar-fill" style="height: 100%; width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, <?php echo esc_attr($args['color']); ?> 0%, <?php echo courses_darken_color($args['color'], 20); ?> 100%); border-radius: <?php echo esc_attr($args['border_radius']); ?>; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);"></div>
        </div>
        
        <?php if ($args['show_lessons'] && $total > 0) : ?>
            <div style="margin-top: 8px; font-size: 13px; color: #6b7280; text-align: right;">
                <?php echo $completed; ?> de <?php echo $total; ?> lecciones completadas
            </div>
        <?php endif; ?>
        
        <?php if ($percentage >= 100) : ?>
            <div style="margin-top: 15px; padding: 15px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3); text-align: center;">
                <span style="font-size: 24px; margin-right: 8px;">🎉</span>
                <span style="color: #047857; font-weight: 700; font-size: 15px;">¡Felicitaciones! Has completado este curso</span>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Oscurecer un color hexadecimal
 */
function courses_darken_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Barra de progreso compacta (para widgets/shortcodes)
 */
function courses_render_compact_progress($user_id, $course_id) {
    $progress = courses_get_user_progress($user_id, $course_id);
    $percentage = $progress['percentage'];
    
    ?>
    <div class="course-compact-progress" style="display: flex; align-items: center; gap: 12px;">
        <div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
            <div style="height: 100%; width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius: 10px; transition: width 0.6s ease;"></div>
        </div>
        <span style="font-size: 12px; font-weight: 700; color: #10b981; white-space: nowrap;">
            <?php echo number_format($percentage, 0); ?>%
        </span>
    </div>
    <?php
}

/**
 * Progreso circular (para dashboards)
 */
function courses_render_circular_progress($user_id, $course_id, $size = 120) {
    $progress = courses_get_user_progress($user_id, $course_id);
    $percentage = $progress['percentage'];
    
    $radius = ($size / 2) - 10;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
    
    ?>
    <div class="circular-progress" style="width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; position: relative; display: inline-flex; align-items: center; justify-content: center;">
        <svg width="<?php echo $size; ?>" height="<?php echo $size; ?>" style="transform: rotate(-90deg);">
            <!-- Fondo -->
            <circle 
                cx="<?php echo $size / 2; ?>" 
                cy="<?php echo $size / 2; ?>" 
                r="<?php echo $radius; ?>" 
                fill="none" 
                stroke="#e5e7eb" 
                stroke-width="10"
            />
            <!-- Progreso -->
            <circle 
                cx="<?php echo $size / 2; ?>" 
                cy="<?php echo $size / 2; ?>" 
                r="<?php echo $radius; ?>" 
                fill="none" 
                stroke="url(#gradient-<?php echo $course_id; ?>)" 
                stroke-width="10"
                stroke-dasharray="<?php echo $circumference; ?>" 
                stroke-dashoffset="<?php echo $offset; ?>"
                stroke-linecap="round"
                style="transition: stroke-dashoffset 0.6s ease;"
            />
            <defs>
                <linearGradient id="gradient-<?php echo $course_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
        <div style="position: absolute; text-align: center;">
            <div style="font-size: <?php echo $size / 3.5; ?>px; font-weight: 800; color: #1f2937;">
                <?php echo number_format($percentage, 0); ?>%
            </div>
            <div style="font-size: <?php echo $size / 12; ?>px; color: #6b7280; margin-top: 2px;">
                Completado
            </div>
        </div>
    </div>
    <?php
}

/**
 * Widget de progreso en sidebar
 */
function courses_render_sidebar_progress($user_id, $course_id) {
    $progress = courses_get_user_progress($user_id, $course_id);
    $course = get_post($course_id);
    
    ?>
    <div class="sidebar-course-progress" style="padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb;">
        <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #1f2937;">
            🎯 Tu Progreso
        </h4>
        
        <div style="margin-bottom: 15px;">
            <?php courses_render_compact_progress($user_id, $course_id); ?>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
            <div style="padding: 12px; background: #f0fdf4; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: 800; color: #10b981;">
                    <?php echo $progress['completed']; ?>
                </div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                    Completadas
                </div>
            </div>
            <div style="padding: 12px; background: #fef3c7; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b;">
                    <?php echo $progress['total'] - $progress['completed']; ?>
                </div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                    Pendientes
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>