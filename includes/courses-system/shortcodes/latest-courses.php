<?php
/**
 * Shortcode: Últimos Cursos
 * [latest_courses] - Muestra los cursos más recientes
 * 
 * @package CoursesSystem
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode de últimos cursos
 * 
 * Atributos:
 * - limit: Número de cursos (default: 6)
 * - style: Estilo (grid, slider, list)
 */
function latest_courses_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6,
        'style' => 'grid'
    ), $atts, 'latest_courses');
    
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish'
    );
    
    $courses = new WP_Query($args);
    
    if (!$courses->have_posts()) {
        return '';
    }
    
    $user_id = get_current_user_id();
    $course_manager = Course_Manager::get_instance();
    
    ob_start();
    ?>
    <div class="latest-courses-section" style="margin: 40px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 28px; font-weight: 800; color: #1f2937;">
                🎓 Últimos Cursos
            </h2>
            <a href="/cursos" style="color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 15px;">
                Ver todos →
            </a>
        </div>
        
        <div class="latest-courses-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
            <?php while ($courses->have_posts()) : $courses->the_post(); 
                $course_id = get_the_ID();
                $total_lessons = $course_manager->count_lessons($course_id);
                $is_free = get_post_meta($course_id, '_course_is_free', true);
                $level = get_post_meta($course_id, '_course_level', true);
            ?>
                <div class="latest-course-card" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0, 0, 0, 0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0, 0, 0, 0.08)';">
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div style="position: relative; padding-top: 60%; overflow: hidden;">
                            <a href="<?php the_permalink(); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: 100%; object-fit: cover;')); ?>
                            </a>
                            
                            <?php if ($is_free) : ?>
                                <span style="position: absolute; top: 10px; left: 10px; padding: 5px 12px; background: #10b981; color: #fff; border-radius: 15px; font-size: 11px; font-weight: 700;">
                                    GRATIS
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($level) : ?>
                                <span style="position: absolute; top: 10px; right: 10px; padding: 5px 12px; background: rgba(0, 0, 0, 0.7); color: #fff; border-radius: 15px; font-size: 11px; font-weight: 600; text-transform: capitalize;">
                                    <?php echo $level; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="padding: 18px;">
                        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 700; line-height: 1.3;">
                            <a href="<?php the_permalink(); ?>" style="color: #1f2937; text-decoration: none;">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 12px;">
                            📝 <?php echo $total_lessons; ?> lecciones
                        </div>
                        
                        <a href="<?php the_permalink(); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #f3f4f6; color: #374151; border-radius: 20px; text-decoration: none; font-weight: 600; font-size: 12px; transition: all 0.3s;" onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                            Ver Curso →
                        </a>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('latest_courses', 'latest_courses_shortcode');
?>