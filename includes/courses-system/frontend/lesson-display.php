<?php
/**
 * Display de Lección en Frontend
 * Muestra el contenido de la lección con navegación
 * 
 * @package CoursesSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mostrar contenido de la lección
 */
function courses_single_lesson_content($content) {
    if (!is_singular('lesson') || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    
    global $post;
    
    $lesson_manager = Lesson_Manager::get_instance();
    $course_id = $lesson_manager->get_course_id($post->ID);
    
    if (!$course_id) {
        return '<div class="lesson-error" style="padding: 20px; background: #fee2e2; border-radius: 8px; border: 1px solid #dc2626; color: #991b1b;">⚠️ Esta lección no está asociada a ningún curso.</div>';
    }
    
    $user_id = get_current_user_id();
    $course_manager = Course_Manager::get_instance();
    $has_access = $user_id ? $course_manager->user_has_access($user_id, $course_id) : false;
    
    if (!$has_access) {
        return courses_render_lesson_restricted($course_id);
    }
    
    ob_start();
    
    // Breadcrumb
    courses_render_lesson_breadcrumb($post->ID, $course_id);
    
    // Video si existe
    $video_url = get_post_meta($post->ID, '_lesson_video_url', true);
    if ($video_url) {
        courses_render_lesson_video($video_url);
    }
    
    // Contenido
    echo '<div class="lesson-content">' . $content . '</div>';
    
    // Recursos
    $resources = get_post_meta($post->ID, '_lesson_resources', true);
    if ($resources) {
        courses_render_lesson_resources($resources);
    }
    
    // Botón de completar
    courses_render_complete_button($post->ID, $course_id);
    
    // Navegación
    courses_render_lesson_navigation($post->ID, $course_id);
    
    return ob_get_clean();
}
add_filter('the_content', 'courses_single_lesson_content');

/**
 * Renderizar breadcrumb
 */
function courses_render_lesson_breadcrumb($lesson_id, $course_id) {
    $course = get_post($course_id);
    
    ?>
    <div class="lesson-breadcrumb" style="margin-bottom: 25px; padding: 15px 20px; background: #f9fafb; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
        <a href="<?php echo get_permalink($course_id); ?>" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
            📖 <?php echo esc_html($course->post_title); ?>
        </a>
        <span style="color: #9ca3af;">/</span>
        <span style="color: #6b7280;"><?php echo get_the_title($lesson_id); ?></span>
    </div>
    <?php
}

/**
 * Renderizar video
 */
function courses_render_lesson_video($video_url) {
    $embed_code = wp_oembed_get($video_url, array('width' => 800));
    
    if ($embed_code) {
        ?>
        <div class="lesson-video" style="margin: 25px 0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
            <?php echo $embed_code; ?>
        </div>
        <?php
    } else {
        ?>
        <div class="lesson-video-link" style="margin: 25px 0; padding: 20px; background: #dbeafe; border-radius: 10px; border-left: 4px solid #3b82f6;">
            <p style="margin: 0; color: #1e40af;">
                🎥 <a href="<?php echo esc_url($video_url); ?>" target="_blank" style="color: #1e40af; font-weight: 600; text-decoration: underline;">Ver video de la lección</a>
            </p>
        </div>
        <?php
    }
}

/**
 * Renderizar recursos
 */
function courses_render_lesson_resources($resources) {
    $resources_array = explode("\n", trim($resources));
    
    if (empty($resources_array)) {
        return;
    }
    
    ?>
    <div class="lesson-resources" style="margin: 30px 0; padding: 25px; background: #f0fdf4; border-radius: 12px; border: 1px solid #86efac;">
        <h3 style="margin: 0 0 15px 0; color: #166534; font-size: 18px; font-weight: 700;">📁 Recursos de esta Lección</h3>
        <ul style="margin: 0; padding-left: 20px; list-style: none;">
            <?php foreach ($resources_array as $resource) : 
                $resource = trim($resource);
                if (empty($resource)) continue;
                
                // Detectar si es URL
                if (filter_var($resource, FILTER_VALIDATE_URL)) {
                    $filename = basename(parse_url($resource, PHP_URL_PATH));
                    ?>
                    <li style="margin: 10px 0;">
                        <a href="<?php echo esc_url($resource); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #059669; text-decoration: none; font-weight: 600;">
                            🔗 <?php echo esc_html($filename); ?>
                        </a>
                    </li>
                    <?php
                } else {
                    ?>
                    <li style="margin: 10px 0; color: #047857;">
                        • <?php echo esc_html($resource); ?>
                    </li>
                    <?php
                }
            endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * Renderizar botón de completar
 */
function courses_render_complete_button($lesson_id, $course_id) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }
    
    $lesson_manager = Lesson_Manager::get_instance();
    $is_completed = $lesson_manager->is_completed($user_id, $lesson_id);
    
    ?>
    <div class="lesson-complete-section" style="margin: 40px 0; padding: 25px; background: <?php echo $is_completed ? 'rgba(16, 185, 129, 0.1)' : 'rgba(59, 130, 246, 0.1)'; ?>; border-radius: 12px; border: 1px solid <?php echo $is_completed ? '#10b981' : '#3b82f6'; ?>; text-align: center;">
        <?php if ($is_completed) : ?>
            <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
            <h3 style="margin: 0 0 10px 0; color: #047857; font-size: 20px;">¡Lección Completada!</h3>
            <p style="margin: 0 0 15px 0; color: #6b7280;">Has terminado esta lección exitosamente.</p>
            <button 
                onclick="coursesMarkIncomplete(<?php echo $lesson_id; ?>, <?php echo $course_id; ?>)"
                class="lesson-incomplete-btn"
                style="padding: 12px 28px; background: #fff; color: #047857; border: 2px solid #10b981; border-radius: 25px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s;">
                Marcar como Incompleta
            </button>
        <?php else : ?>
            <div style="font-size: 48px; margin-bottom: 10px;">🎯</div>
            <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 20px;">¿Completaste esta lección?</h3>
            <p style="margin: 0 0 15px 0; color: #6b7280;">Marca como completada para actualizar tu progreso.</p>
            <button 
                onclick="coursesMarkComplete(<?php echo $lesson_id; ?>, <?php echo $course_id; ?>)"
                class="lesson-complete-btn"
                style="padding: 14px 32px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: #fff; border: none; border-radius: 25px; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4); transition: all 0.3s;">
                ✅ Marcar como Completada
            </button>
        <?php endif; ?>
    </div>
    
    <style>
        .lesson-complete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
        }
        
        .lesson-incomplete-btn:hover {
            background: #f0fdf4;
        }
    </style>
    <?php
}

/**
 * Contenido restringido
 */
function courses_render_lesson_restricted($course_id) {
    $course = get_post($course_id);
    
    ob_start();
    ?>
    <div class="lesson-restricted" style="padding: 40px; text-align: center; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(239, 68, 68, 0.3);">
        <div style="font-size: 64px; margin-bottom: 20px;">🔒</div>
        <h2 style="margin: 0 0 15px 0; color: #991b1b; font-size: 28px;">Contenido Restringido</h2>
        <p style="margin: 0 0 25px 0; color: #6b7280; font-size: 16px;">
            Esta lección es parte del curso: <strong><?php echo esc_html($course->post_title); ?></strong>
        </p>
        <a href="<?php echo get_permalink($course_id); ?>" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 30px; font-weight: 700; font-size: 16px; text-decoration: none; box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4); transition: all 0.3s;">
            Ver Detalles del Curso
        </a>
    </div>
    <?php
    return ob_get_clean();
}
?>