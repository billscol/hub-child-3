<?php
/**
 * Shortcode: Lista de Cursos
 * [courses_list] - Muestra grid de cursos disponibles
 * 
 * @package CoursesSystem
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode de lista de cursos
 * 
 * Atributos:
 * - limit: Número de cursos a mostrar (default: -1, todos)
 * - category: Slug de categoría a filtrar
 * - orderby: Campo para ordenar (title, date, menu_order)
 * - order: ASC o DESC
 * - columns: Número de columnas (2, 3, 4)
 */
function courses_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,
        'category' => '',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'columns' => 3
    ), $atts, 'courses_list');
    
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish'
    );
    
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'course_category',
                'field' => 'slug',
                'terms' => $atts['category']
            )
        );
    }
    
    $courses = new WP_Query($args);
    
    if (!$courses->have_posts()) {
        return '<p style="text-align: center; padding: 40px; color: #6b7280;">No se encontraron cursos.</p>';
    }
    
    $columns = max(1, min(4, intval($atts['columns'])));
    $user_id = get_current_user_id();
    $course_manager = Course_Manager::get_instance();
    
    ob_start();
    ?>
    <div class="courses-list-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(<?php echo (300 / $columns) * 2; ?>px, 1fr)); gap: 25px; margin: 30px 0;">
        <?php while ($courses->have_posts()) : $courses->the_post(); 
            $course_id = get_the_ID();
            $has_access = $user_id ? $course_manager->user_has_access($user_id, $course_id) : false;
            $total_lessons = $course_manager->count_lessons($course_id);
            $level = get_post_meta($course_id, '_course_level', true);
            $duration = get_post_meta($course_id, '_course_duration', true);
            $is_free = get_post_meta($course_id, '_course_is_free', true);
            $product_id = get_post_meta($course_id, '_course_product_id', true);
            
            $progress = $user_id ? courses_get_user_progress($user_id, $course_id) : null;
        ?>
            <div class="course-card" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 8px 24px rgba(0, 0, 0, 0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.08)';">
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="course-thumbnail" style="position: relative; padding-top: 60%; overflow: hidden;">
                        <a href="<?php the_permalink(); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                            <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: 100%; object-fit: cover;')); ?>
                        </a>
                        <?php if ($is_free) : ?>
                            <span style="position: absolute; top: 12px; left: 12px; padding: 6px 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                                🎁 Gratis
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div style="padding: 20px;">
                    <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; line-height: 1.3;">
                        <a href="<?php the_permalink(); ?>" style="color: #1f2937; text-decoration: none;">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <?php if (has_excerpt()) : ?>
                        <p style="margin: 0 0 15px 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 15px; font-size: 13px; color: #6b7280;">
                        <span>📝 <?php echo $total_lessons; ?> lecciones</span>
                        <?php if ($duration) : ?>
                            <span>⏱️ <?php echo $duration; ?>h</span>
                        <?php endif; ?>
                        <?php if ($level) : ?>
                            <span style="text-transform: capitalize;">🎯 <?php echo $level; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($has_access && $progress) : ?>
                        <div style="margin-bottom: 15px;">
                            <?php courses_render_compact_progress($user_id, $course_id); ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php the_permalink(); ?>" class="course-card-btn" style="display: block; text-align: center; padding: 12px 20px; background: <?php echo $has_access ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)'; ?>; color: #fff; border-radius: 25px; text-decoration: none; font-weight: 700; font-size: 14px; transition: all 0.3s;">
                        <?php echo $has_access ? '🚀 Continuar Curso' : ($is_free ? '🎁 Ver Curso Gratis' : '📚 Ver Detalles'); ?>
                    </a>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    
    <style>
        .course-card-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('courses_list', 'courses_list_shortcode');
?>