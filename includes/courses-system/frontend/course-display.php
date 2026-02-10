<?php
/**
 * Display de Curso en Frontend
 * Muestra el contenido del curso con sus lecciones
 * 
 * @package CoursesSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mostrar información del curso en single
 */
function courses_single_course_content($content) {
    if (!is_singular('course') || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    
    global $post;
    
    $course_manager = Course_Manager::get_instance();
    $user_id = get_current_user_id();
    $has_access = $user_id ? $course_manager->user_has_access($user_id, $post->ID) : false;
    
    ob_start();
    
    // Encabezado del curso
    courses_render_course_header($post->ID, $has_access);
    
    // Contenido original
    echo '<div class="course-description">' . $content . '</div>';
    
    // Lecciones del curso
    courses_render_course_lessons($post->ID, $has_access);
    
    // Botón de acción
    if (!$has_access) {
        courses_render_access_button($post->ID);
    }
    
    return ob_get_clean();
}
add_filter('the_content', 'courses_single_course_content');

/**
 * Renderizar header del curso
 */
function courses_render_course_header($course_id, $has_access) {
    $course_manager = Course_Manager::get_instance();
    $stats = $course_manager->get_stats($course_id);
    $total_lessons = $course_manager->count_lessons($course_id);
    $level = get_post_meta($course_id, '_course_level', true);
    $duration = get_post_meta($course_id, '_course_duration', true);
    
    $user_id = get_current_user_id();
    $progress = $user_id ? courses_get_user_progress($user_id, $course_id) : null;
    
    ?>
    <div class="course-header" style="margin-bottom: 30px; padding: 25px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.2);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            
            <div class="course-meta-item" style="text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">📝</div>
                <div style="font-size: 24px; font-weight: 700; color: #1e40af;"><?php echo $total_lessons; ?></div>
                <div style="font-size: 13px; color: #6b7280;">Lecciones</div>
            </div>
            
            <?php if ($level) : ?>
            <div class="course-meta-item" style="text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">🎯</div>
                <div style="font-size: 16px; font-weight: 700; color: #1e40af; text-transform: capitalize;"><?php echo esc_html($level); ?></div>
                <div style="font-size: 13px; color: #6b7280;">Nivel</div>
            </div>
            <?php endif; ?>
            
            <?php if ($duration) : ?>
            <div class="course-meta-item" style="text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">⏱️</div>
                <div style="font-size: 24px; font-weight: 700; color: #1e40af;"><?php echo $duration; ?>h</div>
                <div style="font-size: 13px; color: #6b7280;">Duración</div>
            </div>
            <?php endif; ?>
            
            <div class="course-meta-item" style="text-align: center;">
                <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
                <div style="font-size: 24px; font-weight: 700; color: #1e40af;"><?php echo $stats['total_students']; ?></div>
                <div style="font-size: 13px; color: #6b7280;">Estudiantes</div>
            </div>
        </div>
        
        <?php if ($has_access && $progress) : ?>
            <div style="margin-top: 25px;">
                <?php courses_render_progress_bar($user_id, $course_id); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Renderizar lista de lecciones
 */
function courses_render_course_lessons($course_id, $has_access) {
    $course_manager = Course_Manager::get_instance();
    $lesson_manager = Lesson_Manager::get_instance();
    $lessons = $course_manager->get_lessons($course_id);
    
    if (empty($lessons)) {
        return;
    }
    
    $user_id = get_current_user_id();
    
    ?>
    <div class="course-lessons" style="margin: 30px 0;">
        <h2 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 20px;">📖 Contenido del Curso</h2>
        
        <div class="lessons-list" style="background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <?php foreach ($lessons as $index => $lesson) : 
                $is_completed = $user_id ? $lesson_manager->is_completed($user_id, $lesson->ID) : false;
                $lesson_duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                $border_style = $index < count($lessons) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '';
            ?>
                <div class="lesson-item <?php echo $is_completed ? 'completed' : ''; ?>" style="padding: 20px; display: flex; align-items: center; gap: 15px; <?php echo $border_style; ?> transition: all 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    
                    <div class="lesson-number" style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $is_completed ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'rgba(59, 130, 246, 0.1)'; ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; color: <?php echo $is_completed ? '#fff' : '#1e40af'; ?>; flex-shrink: 0;">
                        <?php echo $is_completed ? '✔' : ($index + 1); ?>
                    </div>
                    
                    <div class="lesson-content" style="flex: 1;">
                        <?php if ($has_access) : ?>
                            <a href="<?php echo get_permalink($lesson->ID); ?>" style="font-size: 16px; font-weight: 600; color: #1f2937; text-decoration: none; display: block; margin-bottom: 4px;">
                                <?php echo esc_html($lesson->post_title); ?>
                            </a>
                        <?php else : ?>
                            <span style="font-size: 16px; font-weight: 600; color: #6b7280; display: block; margin-bottom: 4px;">
                                <?php echo esc_html($lesson->post_title); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($lesson_duration) : ?>
                            <span style="font-size: 13px; color: #9ca3af;">
                                ⏱️ <?php echo $lesson_duration; ?> min
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$has_access) : ?>
                        <div class="lesson-lock" style="color: #9ca3af; font-size: 20px;">
                            🔒
                        </div>
                    <?php elseif ($is_completed) : ?>
                        <div class="lesson-check" style="color: #10b981; font-size: 20px;">
                            ✅
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Renderizar botón de acceso
 */
function courses_render_access_button($course_id) {
    $product_id = get_post_meta($course_id, '_course_product_id', true);
    $is_free = get_post_meta($course_id, '_course_is_free', true);
    
    ?>
    <div class="course-access-action" style="margin: 40px 0; padding: 30px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(218, 4, 128, 0.3); text-align: center;">
        <?php if ($is_free) : ?>
            <h3 style="margin: 0 0 15px 0; color: #da0480; font-size: 22px;">🎁 Curso Gratuito</h3>
            <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 16px;">Este curso es completamente gratis. ¡Comienza ahora!</p>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo add_query_arg('enroll', '1', get_permalink($course_id)); ?>" class="course-enroll-btn" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 30px; font-weight: 700; font-size: 16px; text-decoration: none; box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4); transition: all 0.3s;">
                    Inscribirme Gratis
                </a>
            <?php else : ?>
                <a href="<?php echo wp_login_url(get_permalink($course_id)); ?>" class="course-login-btn" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 30px; font-weight: 700; font-size: 16px; text-decoration: none; box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4); transition: all 0.3s;">
                    Iniciar Sesión para Comenzar
                </a>
            <?php endif; ?>
        <?php elseif ($product_id && class_exists('WooCommerce')) : 
            $product = wc_get_product($product_id);
            if ($product) :
        ?>
            <h3 style="margin: 0 0 15px 0; color: #da0480; font-size: 22px;">🚀 Accede a Este Curso</h3>
            <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 16px;">Compra el curso para desbloquear todo el contenido</p>
            <div style="font-size: 36px; font-weight: 800; color: #da0480; margin-bottom: 20px;">
                <?php echo $product->get_price_html(); ?>
            </div>
            <a href="<?php echo $product->add_to_cart_url(); ?>" class="course-buy-btn" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 30px; font-weight: 700; font-size: 16px; text-decoration: none; box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4); transition: all 0.3s;">
                🛒 Comprar Curso Ahora
            </a>
            <?php endif; ?>
        <?php else : ?>
            <h3 style="margin: 0 0 15px 0; color: #da0480; font-size: 22px;">🔒 Acceso Restringido</h3>
            <p style="margin: 0; color: #6b7280; font-size: 16px;">Este curso requiere inscripción.</p>
        <?php endif; ?>
    </div>
    
    <style>
        .course-enroll-btn:hover,
        .course-login-btn:hover,
        .course-buy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 4, 128, 0.6);
        }
    </style>
    <?php
}

/**
 * Manejar inscripción gratuita
 */
function courses_handle_free_enrollment() {
    if (!isset($_GET['enroll']) || !is_singular('course')) {
        return;
    }
    
    if (!is_user_logged_in()) {
        return;
    }
    
    global $post;
    $course_id = $post->ID;
    $is_free = get_post_meta($course_id, '_course_is_free', true);
    
    if (!$is_free) {
        return;
    }
    
    $user_id = get_current_user_id();
    $course_manager = Course_Manager::get_instance();
    
    // Verificar si ya tiene acceso
    if ($course_manager->user_has_access($user_id, $course_id)) {
        return;
    }
    
    // Otorgar acceso
    $course_manager->grant_access($user_id, $course_id, 0, 0, 'free');
    
    // Redirigir a la primera lección
    $lesson_manager = Lesson_Manager::get_instance();
    $first_lesson = $lesson_manager->get_first_lesson($course_id);
    
    if ($first_lesson) {
        wp_safe_redirect(get_permalink($first_lesson->ID));
        exit;
    }
}
add_action('template_redirect', 'courses_handle_free_enrollment');
?>