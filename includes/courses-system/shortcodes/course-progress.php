<?php
/**
 * Shortcode: Progreso de Curso Específico
 * [course_progress id="123"] - Muestra progreso en un curso específico
 * 
 * @package CoursesSystem
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode de progreso de curso
 * 
 * Atributos:
 * - id: ID del curso (requerido)
 * - style: Estilo de la barra (bar, circular, compact)
 * - show_lessons: Mostrar lista de lecciones (yes/no)
 */
function course_progress_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
        'style' => 'bar',
        'show_lessons' => 'no'
    ), $atts, 'course_progress');
    
    $course_id = intval($atts['id']);
    $user_id = get_current_user_id();
    
    if (!$course_id) {
        return '<p style="color: #dc2626;">⚠️ Error: Debes especificar el ID del curso</p>';
    }
    
    if (!$user_id) {
        return '<p style="color: #6b7280;">🔒 Debes iniciar sesión para ver tu progreso</p>';
    }
    
    $course_manager = Course_Manager::get_instance();
    
    if (!$course_manager->user_has_access($user_id, $course_id)) {
        return '<p style="color: #6b7280;">🔒 No tienes acceso a este curso</p>';
    }
    
    ob_start();
    
    ?>
    <div class="course-progress-widget" style="margin: 20px 0;">
        <?php 
        switch ($atts['style']) {
            case 'circular':
                echo '<div style="text-align: center;">';
                courses_render_circular_progress($user_id, $course_id, 150);
                echo '</div>';
                break;
                
            case 'compact':
                courses_render_compact_progress($user_id, $course_id);
                break;
                
            case 'bar':
            default:
                courses_render_progress_bar($user_id, $course_id);
                break;
        }
        ?>
    </div>
    
    <?php if ($atts['show_lessons'] === 'yes') : 
        $lesson_manager = Lesson_Manager::get_instance();
        $lessons = $course_manager->get_lessons($course_id);
    ?>
        <div class="course-lessons-checklist" style="margin-top: 25px;">
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 15px;">📝 Lecciones</h3>
            <div style="background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
                <?php foreach ($lessons as $index => $lesson) : 
                    $is_completed = $lesson_manager->is_completed($user_id, $lesson->ID);
                ?>
                    <div style="padding: 15px 20px; display: flex; align-items: center; gap: 12px; <?php echo $index < count($lessons) - 1 ? 'border-bottom: 1px solid #f3f4f6;' : ''; ?>">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: <?php echo $is_completed ? '#10b981' : '#e5e7eb'; ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; flex-shrink: 0;">
                            <?php echo $is_completed ? '✔' : ''; ?>
                        </div>
                        <a href="<?php echo get_permalink($lesson->ID); ?>" style="flex: 1; color: <?php echo $is_completed ? '#6b7280' : '#1f2937'; ?>; text-decoration: none; font-weight: 600; font-size: 14px;<?php echo $is_completed ? ' text-decoration: line-through;' : ''; ?>">
                            <?php echo esc_html($lesson->post_title); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
    
    return ob_get_clean();
}
add_shortcode('course_progress', 'course_progress_shortcode');
?>