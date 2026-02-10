<?php
/**
 * Integración con WooCommerce
 * Conecta productos con cursos y otorga acceso automático
 * 
 * @package CoursesSystem
 * @subpackage Integrations
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verificar si WooCommerce está activo
 */
if (!class_exists('WooCommerce')) {
    return;
}

/**
 * Otorgar acceso al curso al completar compra
 */
function courses_grant_access_on_purchase($order_id) {
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    $course_manager = Course_Manager::get_instance();
    
    // Recorrer items del pedido
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        
        // Buscar curso asociado a este producto
        $course = courses_get_course_by_product($product_id);
        
        if ($course) {
            // Verificar si ya tiene acceso
            if (!$course_manager->user_has_access($user_id, $course->ID)) {
                // Otorgar acceso
                $course_manager->grant_access(
                    $user_id, 
                    $course->ID, 
                    $product_id, 
                    $order_id,
                    'purchase'
                );
                
                // Log
                $order->add_order_note(
                    sprintf('Acceso otorgado al curso: %s (ID: %d)', $course->post_title, $course->ID)
                );
            }
        }
    }
}
add_action('woocommerce_order_status_completed', 'courses_grant_access_on_purchase');
add_action('woocommerce_order_status_processing', 'courses_grant_access_on_purchase');

/**
 * Obtener curso por ID de producto
 */
function courses_get_course_by_product($product_id) {
    $args = array(
        'post_type' => 'course',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_course_product_id',
                'value' => $product_id,
                'compare' => '='
            )
        )
    );
    
    $courses = get_posts($args);
    
    return !empty($courses) ? $courses[0] : null;
}

/**
 * Revocar acceso al cancelar/reembolsar pedido
 */
function courses_revoke_access_on_cancel($order_id) {
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    $course_manager = Course_Manager::get_instance();
    
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $course = courses_get_course_by_product($product_id);
        
        if ($course) {
            $course_manager->revoke_access($user_id, $course->ID);
            
            $order->add_order_note(
                sprintf('Acceso revocado al curso: %s (ID: %d)', $course->post_title, $course->ID)
            );
        }
    }
}
add_action('woocommerce_order_status_cancelled', 'courses_revoke_access_on_cancel');
add_action('woocommerce_order_status_refunded', 'courses_revoke_access_on_cancel');
add_action('woocommerce_order_status_failed', 'courses_revoke_access_on_cancel');

/**
 * Agregar metabox de curso en producto
 */
function courses_add_product_course_metabox() {
    add_meta_box(
        'course_product_link',
        '🎓 Curso Asociado',
        'courses_render_product_course_metabox',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'courses_add_product_course_metabox');

/**
 * Renderizar metabox de curso en producto
 */
function courses_render_product_course_metabox($post) {
    // Buscar curso asociado
    $course = courses_get_course_by_product($post->ID);
    
    ?>
    <div style="padding: 10px 0;">
        <?php if ($course) : ?>
            <div style="padding: 15px; background: #dcfce7; border-radius: 8px; border-left: 4px solid #10b981;">
                <p style="margin: 0 0 10px 0; color: #166534; font-weight: 600;">
                    ✅ Este producto está vinculado al curso:
                </p>
                <p style="margin: 0; color: #166534;">
                    <strong><?php echo esc_html($course->post_title); ?></strong>
                </p>
                <p style="margin: 10px 0 0 0;">
                    <a href="<?php echo get_edit_post_link($course->ID); ?>" class="button button-small">
                        Editar Curso
                    </a>
                </p>
            </div>
        <?php else : ?>
            <div style="padding: 15px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <p style="margin: 0 0 10px 0; color: #92400e; font-weight: 600;">
                    ⚠️ Este producto no tiene un curso asociado
                </p>
                <p style="margin: 0 0 10px 0; color: #92400e; font-size: 12px;">
                    Para vincular un curso, ve a editar el curso y selecciona este producto en la configuración.
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Agregar tab de curso en página de producto
 */
function courses_add_product_course_tab($tabs) {
    global $post;
    
    $course = courses_get_course_by_product($post->ID);
    
    if ($course) {
        $tabs['course_content'] = array(
            'title' => '📚 Contenido del Curso',
            'priority' => 20,
            'callback' => 'courses_render_product_course_tab'
        );
    }
    
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'courses_add_product_course_tab');

/**
 * Renderizar tab de contenido del curso
 */
function courses_render_product_course_tab() {
    global $post;
    
    $course = courses_get_course_by_product($post->ID);
    
    if (!$course) {
        return;
    }
    
    $course_manager = Course_Manager::get_instance();
    $lessons = $course_manager->get_lessons($course->ID);
    $total_lessons = count($lessons);
    $level = get_post_meta($course->ID, '_course_level', true);
    $duration = get_post_meta($course->ID, '_course_duration', true);
    
    ?>
    <div class="woocommerce-course-content" style="padding: 20px 0;">
        <h2 style="margin-bottom: 20px;">🎓 <?php echo esc_html($course->post_title); ?></h2>
        
        <?php if ($course->post_excerpt) : ?>
            <div style="margin-bottom: 25px; padding: 20px; background: #f9fafb; border-radius: 8px;">
                <?php echo wpautop($course->post_excerpt); ?>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="padding: 15px; background: #dbeafe; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 5px;">📝</div>
                <div style="font-size: 24px; font-weight: 700; color: #1e40af;"><?php echo $total_lessons; ?></div>
                <div style="font-size: 13px; color: #6b7280;">Lecciones</div>
            </div>
            
            <?php if ($level) : ?>
            <div style="padding: 15px; background: #fef3c7; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 5px;">🎯</div>
                <div style="font-size: 16px; font-weight: 700; color: #92400e; text-transform: capitalize;"><?php echo $level; ?></div>
                <div style="font-size: 13px; color: #6b7280;">Nivel</div>
            </div>
            <?php endif; ?>
            
            <?php if ($duration) : ?>
            <div style="padding: 15px; background: #dcfce7; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 5px;">⏱️</div>
                <div style="font-size: 24px; font-weight: 700; color: #166534;"><?php echo $duration; ?>h</div>
                <div style="font-size: 13px; color: #6b7280;">Duración</div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($lessons)) : ?>
            <h3 style="margin-bottom: 15px;">📖 Temario del Curso</h3>
            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                <?php foreach ($lessons as $index => $lesson) : 
                    $lesson_duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                ?>
                    <div style="padding: 15px 20px; display: flex; align-items: center; gap: 12px; <?php echo $index < count($lessons) - 1 ? 'border-bottom: 1px solid #f3f4f6;' : ''; ?>">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1e40af; flex-shrink: 0; font-size: 12px;">
                            <?php echo $index + 1; ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1f2937;">
                                <?php echo esc_html($lesson->post_title); ?>
                            </div>
                            <?php if ($lesson_duration) : ?>
                                <div style="font-size: 12px; color: #9ca3af; margin-top: 2px;">
                                    ⏱️ <?php echo $lesson_duration; ?> min
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Agregar información de acceso en email de compra
 */
function courses_add_course_info_to_email($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin) {
        return;
    }
    
    if ($email->id !== 'customer_completed_order' && $email->id !== 'customer_processing_order') {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    $courses_purchased = array();
    
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $course = courses_get_course_by_product($product_id);
        
        if ($course) {
            $courses_purchased[] = $course;
        }
    }
    
    if (empty($courses_purchased)) {
        return;
    }
    
    ?>
    <h2 style="color: #3b82f6; margin-top: 30px;">🎓 Tus Cursos</h2>
    <p>Has obtenido acceso a los siguientes cursos:</p>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($courses_purchased as $course) : ?>
            <li style="margin: 10px 0; padding: 15px; background: #f9fafb; border-radius: 8px;">
                <strong><?php echo esc_html($course->post_title); ?></strong><br>
                <a href="<?php echo get_permalink($course->ID); ?>" style="color: #3b82f6;">
                    Comenzar Curso →
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}
add_action('woocommerce_email_order_details', 'courses_add_course_info_to_email', 20, 4);

/**
 * Agregar columna de curso en lista de productos
 */
function courses_add_product_course_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'name') {
            $new_columns['linked_course'] = '🎓 Curso';
        }
    }
    
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'courses_add_product_course_column');

/**
 * Mostrar contenido de columna de curso
 */
function courses_show_product_course_column($column, $post_id) {
    if ($column === 'linked_course') {
        $course = courses_get_course_by_product($post_id);
        
        if ($course) {
            echo '<a href="' . get_edit_post_link($course->ID) . '" style="color: #10b981; font-weight: 600;">';
            echo '✅ ' . esc_html($course->post_title);
            echo '</a>';
        } else {
            echo '<span style="color: #9ca3af;">—</span>';
        }
    }
}
add_action('manage_product_posts_custom_column', 'courses_show_product_course_column', 10, 2);
?>