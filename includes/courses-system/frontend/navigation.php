<?php
/**
 * Navegación de Lecciones
 * Sistema de navegación entre lecciones de un curso
 * 
 * @package CoursesSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderizar navegación de lecciones
 */
function courses_render_lesson_navigation($lesson_id, $course_id) {
    $lesson_manager = Lesson_Manager::get_instance();
    
    $previous = $lesson_manager->get_previous_lesson($course_id, $lesson_id);
    $next = $lesson_manager->get_next_lesson($course_id, $lesson_id);
    
    ?>
    <div class="lesson-navigation" style="margin: 40px 0; display: grid; grid-template-columns: <?php echo ($previous && $next) ? '1fr 1fr' : '1fr'; ?>; gap: 20px;">
        
        <?php if ($previous) : ?>
            <a href="<?php echo get_permalink($previous->ID); ?>" class="nav-lesson prev" style="display: flex; align-items: center; gap: 15px; padding: 20px; background: #fff; border: 2px solid #e5e7eb; border-radius: 12px; text-decoration: none; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                <div style="font-size: 32px; color: #3b82f6;">←</div>
                <div style="flex: 1; text-align: left;">
                    <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                        Anterior
                    </div>
                    <div style="font-size: 15px; font-weight: 600; color: #1f2937; line-height: 1.3;">
                        <?php echo esc_html($previous->post_title); ?>
                    </div>
                </div>
            </a>
        <?php else : ?>
            <?php if ($next) : ?>
                <div></div> <!-- Spacer para mantener el grid -->
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($next) : ?>
            <a href="<?php echo get_permalink($next->ID); ?>" class="nav-lesson next" style="display: flex; align-items: center; gap: 15px; padding: 20px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border: 2px solid #3b82f6; border-radius: 12px; text-decoration: none; transition: all 0.3s; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);">
                <div style="flex: 1; text-align: right;">
                    <div style="font-size: 12px; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; font-weight: 600;">
                        Siguiente
                    </div>
                    <div style="font-size: 15px; font-weight: 600; color: #1e40af; line-height: 1.3;">
                        <?php echo esc_html($next->post_title); ?>
                    </div>
                </div>
                <div style="font-size: 32px; color: #3b82f6;">→</div>
            </a>
        <?php endif; ?>
    </div>
    
    <style>
        .nav-lesson.prev:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
            transform: translateX(-4px);
        }
        
        .nav-lesson.next:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.08) 100%);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
            transform: translateX(4px);
        }
    </style>
    
    <?php
    
    // Botón volver al curso
    ?>
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?php echo get_permalink($course_id); ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 25px; color: #6b7280; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#3b82f6';" onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='#6b7280';">
            📖 Volver al Curso
        </a>
    </div>
    <?php
}

/**
 * Índice de lecciones (sidebar o modal)
 */
function courses_render_lessons_index($course_id, $current_lesson_id = 0) {
    $course_manager = Course_Manager::get_instance();
    $lesson_manager = Lesson_Manager::get_instance();
    $lessons = $course_manager->get_lessons($course_id);
    
    if (empty($lessons)) {
        return;
    }
    
    $user_id = get_current_user_id();
    
    ?>
    <div class="lessons-index" style="background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
        <div style="padding: 20px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
            <h3 style="margin: 0; color: #fff; font-size: 18px; font-weight: 700;">
                📖 Contenido del Curso
            </h3>
        </div>
        
        <div style="max-height: 500px; overflow-y: auto;">
            <?php foreach ($lessons as $index => $lesson) : 
                $is_completed = $user_id ? $lesson_manager->is_completed($user_id, $lesson->ID) : false;
                $is_current = ($lesson->ID == $current_lesson_id);
                $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
            ?>
                <a href="<?php echo get_permalink($lesson->ID); ?>" style="display: flex; align-items: center; gap: 12px; padding: 16px 20px; text-decoration: none; border-bottom: 1px solid #f3f4f6; background: <?php echo $is_current ? 'rgba(59, 130, 246, 0.1)' : '#fff'; ?>; transition: all 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='<?php echo $is_current ? 'rgba(59, 130, 246, 0.1)' : '#fff'; ?>'">
                    
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: <?php echo $is_completed ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : ($is_current ? '#3b82f6' : 'rgba(156, 163, 175, 0.2)'); ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; color: <?php echo ($is_completed || $is_current) ? '#fff' : '#6b7280'; ?>; flex-shrink: 0; font-size: 12px;">
                        <?php echo $is_completed ? '✔' : ($index + 1); ?>
                    </div>
                    
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: <?php echo $is_current ? '700' : '600'; ?>; color: <?php echo $is_current ? '#1e40af' : '#1f2937'; ?>; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo esc_html($lesson->post_title); ?>
                        </div>
                        <?php if ($duration) : ?>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                ⏱️ <?php echo $duration; ?> min
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_completed) : ?>
                        <div style="color: #10b981; font-size: 18px;">✅</div>
                    <?php elseif ($is_current) : ?>
                        <div style="color: #3b82f6; font-size: 18px;">▶️</div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Botón flotante de índice (para móvil)
 */
function courses_render_floating_index_button($course_id) {
    ?>
    <button 
        onclick="toggleLessonsIndex()"
        class="floating-index-btn"
        style="position: fixed; bottom: 80px; right: 20px; width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: #fff; border: none; font-size: 24px; cursor: pointer; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4); z-index: 999; transition: all 0.3s;"
        onmouseover="this.style.transform='scale(1.1)'"
        onmouseout="this.style.transform='scale(1)'">
        📖
    </button>
    
    <div id="mobile-lessons-index" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000;" onclick="toggleLessonsIndex()">
        <div style="position: absolute; bottom: 0; left: 0; right: 0; max-height: 70vh; background: #fff; border-radius: 20px 20px 0 0; padding: 20px; overflow-y: auto;" onclick="event.stopPropagation();">
            <?php courses_render_lessons_index($course_id, get_the_ID()); ?>
            <button onclick="toggleLessonsIndex()" style="width: 100%; margin-top: 15px; padding: 12px; background: #f3f4f6; border: none; border-radius: 8px; font-weight: 600; color: #6b7280; cursor: pointer;">
                Cerrar
            </button>
        </div>
    </div>
    
    <script>
        function toggleLessonsIndex() {
            var index = document.getElementById('mobile-lessons-index');
            if (index.style.display === 'none') {
                index.style.display = 'block';
            } else {
                index.style.display = 'none';
            }
        }
    </script>
    <?php
}

/**
 * Agregar botón flotante en single lesson
 */
function courses_add_floating_index() {
    if (!is_singular('lesson')) {
        return;
    }
    
    global $post;
    $lesson_manager = Lesson_Manager::get_instance();
    $course_id = $lesson_manager->get_course_id($post->ID);
    
    if (!$course_id) {
        return;
    }
    
    courses_render_floating_index_button($course_id);
}
add_action('wp_footer', 'courses_add_floating_index');
?>