<?php
/**
 * Shortcode: Progreso del Usuario
 * [user_progress] - Muestra dashboard con progreso de cursos
 * 
 * @package CoursesSystem
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode de progreso del usuario
 * 
 * Atributos:
 * - user_id: ID del usuario (default: usuario actual)
 * - show_stats: Mostrar estadísticas globales (yes/no)
 * - show_courses: Mostrar cursos en progreso (yes/no)
 */
function user_progress_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
        'show_stats' => 'yes',
        'show_courses' => 'yes'
    ), $atts, 'user_progress');
    
    $user_id = intval($atts['user_id']);
    
    if (!$user_id) {
        return '<div style="padding: 30px; text-align: center; background: #fef3c7; border-radius: 12px; border: 1px solid #f59e0b;">
            <p style="margin: 0; color: #92400e;">🔒 Debes iniciar sesión para ver tu progreso.</p>
        </div>';
    }
    
    $stats = courses_get_user_stats($user_id);
    $courses = courses_get_user_courses($user_id, 'all');
    
    ob_start();
    ?>
    <div class="user-progress-dashboard" style="margin: 30px 0;">
        
        <?php if ($atts['show_stats'] === 'yes') : ?>
            <div class="user-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
                
                <div class="stat-card" style="padding: 25px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.3); text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">📚</div>
                    <div style="font-size: 36px; font-weight: 800; color: #1e40af; margin-bottom: 5px;">
                        <?php echo $stats['total_courses']; ?>
                    </div>
                    <div style="font-size: 14px; color: #6b7280; font-weight: 600;">
                        Cursos Inscritos
                    </div>
                </div>
                
                <div class="stat-card" style="padding: 25px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(16, 185, 129, 0.3); text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
                    <div style="font-size: 36px; font-weight: 800; color: #047857; margin-bottom: 5px;">
                        <?php echo $stats['completed_courses']; ?>
                    </div>
                    <div style="font-size: 14px; color: #6b7280; font-weight: 600;">
                        Cursos Completados
                    </div>
                </div>
                
                <div class="stat-card" style="padding: 25px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(245, 158, 11, 0.3); text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🔥</div>
                    <div style="font-size: 36px; font-weight: 800; color: #b45309; margin-bottom: 5px;">
                        <?php echo $stats['in_progress_courses']; ?>
                    </div>
                    <div style="font-size: 14px; color: #6b7280; font-weight: 600;">
                        En Progreso
                    </div>
                </div>
                
                <div class="stat-card" style="padding: 25px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(139, 92, 246, 0.3); text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🎓</div>
                    <div style="font-size: 36px; font-weight: 800; color: #6d28d9; margin-bottom: 5px;">
                        <?php echo $stats['total_lessons_completed']; ?>
                    </div>
                    <div style="font-size: 14px; color: #6b7280; font-weight: 600;">
                        Lecciones Completadas
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($atts['show_courses'] === 'yes' && !empty($courses)) : ?>
            <div class="user-courses-section">
                <h2 style="font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 20px;">
                    📚 Mis Cursos
                </h2>
                
                <div style="display: grid; gap: 20px;">
                    <?php foreach ($courses as $course) : 
                        $progress = courses_get_user_progress($user_id, $course->ID);
                        $total_lessons = Course_Manager::get_instance()->count_lessons($course->ID);
                    ?>
                        <div class="user-course-item" style="padding: 25px; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                            <div style="display: flex; gap: 20px; align-items: start; flex-wrap: wrap;">
                                
                                <?php if (has_post_thumbnail($course->ID)) : ?>
                                    <div style="flex-shrink: 0; width: 120px; height: 80px; border-radius: 8px; overflow: hidden;">
                                        <a href="<?php echo get_permalink($course->ID); ?>">
                                            <?php echo get_the_post_thumbnail($course->ID, 'thumbnail', array('style' => 'width: 100%; height: 100%; object-fit: cover;')); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="flex: 1; min-width: 200px;">
                                    <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">
                                        <a href="<?php echo get_permalink($course->ID); ?>" style="color: #1f2937; text-decoration: none;">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <?php courses_render_compact_progress($user_id, $course->ID); ?>
                                    </div>
                                    
                                    <div style="display: flex; gap: 15px; flex-wrap: wrap; font-size: 13px; color: #6b7280;">
                                        <span>📝 <?php echo $progress['completed']; ?>/<?php echo $total_lessons; ?> lecciones</span>
                                        <?php if ($progress['status'] === 'completed') : ?>
                                            <span style="color: #10b981; font-weight: 600;">✅ Completado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div style="flex-shrink: 0;">
                                    <a href="<?php echo get_permalink($course->ID); ?>" style="display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: #fff; border-radius: 20px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap;">
                                        <?php echo $progress['status'] === 'completed' ? '🎓 Ver Certificado' : '🚀 Continuar'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($atts['show_courses'] === 'yes') : ?>
            <div style="padding: 40px; text-align: center; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                <div style="font-size: 64px; margin-bottom: 15px;">📚</div>
                <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 20px;">Aún no tienes cursos</h3>
                <p style="margin: 0 0 20px 0; color: #6b7280;">Explora nuestro catálogo y comienza tu aprendizaje</p>
                <a href="/cursos" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 25px; text-decoration: none; font-weight: 700;">
                    Ver Cursos Disponibles
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('user_progress', 'user_progress_shortcode');
?>