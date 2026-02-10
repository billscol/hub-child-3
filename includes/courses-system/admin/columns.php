<?php
/**
 * Columnas Personalizadas en Admin
 * Mejora los listados de cursos y lecciones con información útil
 * 
 * @package CoursesSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modificar columnas de cursos
 */
function courses_modify_course_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['lessons'] = '📝 Lecciones';
            $new_columns['students'] = '🎓 Estudiantes';
            $new_columns['product'] = '🛒 Producto';
        } elseif ($key === 'date') {
            $new_columns['level'] = '🎯 Nivel';
            $new_columns['status_col'] = '🟢 Estado';
            $new_columns[$key] = $value;
        } else {
            $new_columns[$key] = $value;
        }
    }
    
    return $new_columns;
}
add_filter('manage_course_posts_columns', 'courses_modify_course_columns');

/**
 * Mostrar contenido de columnas de cursos
 */
function courses_show_course_column_content($column, $post_id) {
    $course_manager = Course_Manager::get_instance();
    
    switch ($column) {
        case 'lessons':
            $total = $course_manager->count_lessons($post_id);
            
            if ($total > 0) {
                echo '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: 12px; font-weight: 600;">';
                echo '📝 ' . $total;
                echo '</span>';
            } else {
                echo '<span style="color: #dc2626; font-weight: 600;">⚠️ 0</span>';
            }
            break;
            
        case 'students':
            $stats = $course_manager->get_stats($post_id);
            $total = $stats['total_students'];
            $active = $stats['active_students'];
            
            if ($total > 0) {
                echo '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 12px; font-weight: 600;">';
                echo '🎓 ' . $total;
                echo '</span>';
                if ($active > 0) {
                    echo '<br><small style="color: #6b7280; margin-top: 4px;">Activos: ' . $active . '</small>';
                }
            } else {
                echo '<span style="color: #9ca3af; font-size: 12px;">—</span>';
            }
            break;
            
        case 'product':
            $product_id = get_post_meta($post_id, '_course_product_id', true);
            
            if ($product_id) {
                $product = get_post($product_id);
                if ($product) {
                    echo '<a href="' . get_edit_post_link($product_id) . '" style="color: #3b82f6; text-decoration: underline;">';
                    echo esc_html($product->post_title);
                    echo '</a>';
                } else {
                    echo '<span style="color: #dc2626;">⚠️ Producto eliminado</span>';
                }
            } else {
                $is_free = get_post_meta($post_id, '_course_is_free', true);
                if ($is_free) {
                    echo '<span style="color: #10b981; font-weight: 600;">🎁 Gratuito</span>';
                } else {
                    echo '<span style="color: #9ca3af;">—</span>';
                }
            }
            break;
            
        case 'level':
            $level = get_post_meta($post_id, '_course_level', true);
            
            if ($level) {
                $colors = array(
                    'principiante' => array('bg' => '#dcfce7', 'text' => '#166534'),
                    'intermedio' => array('bg' => '#fef3c7', 'text' => '#92400e'),
                    'avanzado' => array('bg' => '#fee2e2', 'text' => '#991b1b'),
                    'todos' => array('bg' => '#dbeafe', 'text' => '#1e40af')
                );
                
                $color = isset($colors[$level]) ? $colors[$level] : array('bg' => '#e5e7eb', 'text' => '#374151');
                
                echo '<span style="display: inline-flex; align-items: center; padding: 4px 10px; background: ' . $color['bg'] . '; color: ' . $color['text'] . '; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: capitalize;">';
                echo esc_html($level);
                echo '</span>';
            } else {
                echo '<span style="color: #9ca3af; font-size: 12px;">—</span>';
            }
            break;
            
        case 'status_col':
            $post_status = get_post_status($post_id);
            $total_lessons = $course_manager->count_lessons($post_id);
            $product_id = get_post_meta($post_id, '_course_product_id', true);
            $is_free = get_post_meta($post_id, '_course_is_free', true);
            
            $is_ready = ($post_status === 'publish' && $total_lessons > 0 && ($product_id || $is_free));
            
            if ($is_ready) {
                echo '<span style="display: inline-flex; align-items: center; gap: 5px; color: #10b981; font-weight: 600; font-size: 12px;">';
                echo '✅ Activo';
                echo '</span>';
            } else {
                echo '<span style="display: inline-flex; align-items: center; gap: 5px; color: #f59e0b; font-weight: 600; font-size: 12px;">';
                echo '⚠️ Incompleto';
                echo '</span>';
            }
            break;
    }
}
add_action('manage_course_posts_custom_column', 'courses_show_course_column_content', 10, 2);

/**
 * Hacer columnas sortables
 */
function courses_sortable_course_columns($columns) {
    $columns['lessons'] = 'lessons_count';
    $columns['students'] = 'students_count';
    $columns['level'] = 'level';
    return $columns;
}
add_filter('manage_edit-course_sortable_columns', 'courses_sortable_course_columns');

/**
 * Agregar filtros en listado de cursos
 */
function courses_add_course_filters() {
    global $typenow;
    
    if ($typenow === 'course') {
        // Filtro por nivel
        $current_level = isset($_GET['level_filter']) ? $_GET['level_filter'] : '';
        ?>
        <select name="level_filter">
            <option value="">Todos los niveles</option>
            <option value="principiante" <?php selected($current_level, 'principiante'); ?>>Principiante</option>
            <option value="intermedio" <?php selected($current_level, 'intermedio'); ?>>Intermedio</option>
            <option value="avanzado" <?php selected($current_level, 'avanzado'); ?>>Avanzado</option>
            <option value="todos" <?php selected($current_level, 'todos'); ?>>Todos los niveles</option>
        </select>
        
        <?php
        // Filtro por tipo
        $current_type = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
        ?>
        <select name="type_filter">
            <option value="">Todos los tipos</option>
            <option value="free" <?php selected($current_type, 'free'); ?>>Gratuitos</option>
            <option value="paid" <?php selected($current_type, 'paid'); ?>>De pago</option>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'courses_add_course_filters');

/**
 * Aplicar filtros de cursos
 */
function courses_apply_course_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'course') {
        // Filtro por nivel
        if (isset($_GET['level_filter']) && $_GET['level_filter'] !== '') {
            $query->set('meta_query', array(
                array(
                    'key' => '_course_level',
                    'value' => sanitize_text_field($_GET['level_filter']),
                    'compare' => '='
                )
            ));
        }
        
        // Filtro por tipo
        if (isset($_GET['type_filter'])) {
            if ($_GET['type_filter'] === 'free') {
                $query->set('meta_query', array(
                    array(
                        'key' => '_course_is_free',
                        'value' => '1',
                        'compare' => '='
                    )
                ));
            } elseif ($_GET['type_filter'] === 'paid') {
                $query->set('meta_query', array(
                    'relation' => 'AND',
                    array(
                        'key' => '_course_product_id',
                        'value' => '0',
                        'compare' => '>',
                        'type' => 'NUMERIC'
                    ),
                    array(
                        'key' => '_course_is_free',
                        'value' => '1',
                        'compare' => '!='
                    )
                ));
            }
        }
    }
}
add_action('pre_get_posts', 'courses_apply_course_filters');

/**
 * Acciones masivas personalizadas
 */
function courses_bulk_actions($bulk_actions) {
    $bulk_actions['reset_stats'] = 'Resetear estadísticas';
    return $bulk_actions;
}
add_filter('bulk_actions-edit-course', 'courses_bulk_actions');

/**
 * Manejar acciones masivas
 */
function courses_handle_bulk_actions($redirect_to, $action, $post_ids) {
    if ($action !== 'reset_stats') {
        return $redirect_to;
    }
    
    foreach ($post_ids as $post_id) {
        // Resetear estadísticas del curso
        global $wpdb;
        $table = $wpdb->prefix . 'course_progress';
        $wpdb->delete($table, array('course_id' => $post_id), array('%d'));
    }
    
    $redirect_to = add_query_arg('bulk_stats_reset', count($post_ids), $redirect_to);
    return $redirect_to;
}
add_filter('handle_bulk_actions-edit-course', 'courses_handle_bulk_actions', 10, 3);

/**
 * Mostrar aviso de acción masiva
 */
function courses_bulk_action_notices() {
    if (!empty($_REQUEST['bulk_stats_reset'])) {
        $count = intval($_REQUEST['bulk_stats_reset']);
        printf(
            '<div id="message" class="updated notice is-dismissible"><p>' .
            _n(
                'Se resetearon las estadísticas de %s curso.',
                'Se resetearon las estadísticas de %s cursos.',
                $count
            ) . '</p></div>',
            $count
        );
    }
}
add_action('admin_notices', 'courses_bulk_action_notices');

/**
 * Agregar estilos personalizados en admin
 */
function courses_admin_column_styles() {
    global $typenow;
    
    if ($typenow === 'course' || $typenow === 'lesson') {
        ?>
        <style>
            .widefat .column-lessons,
            .widefat .column-students,
            .widefat .column-product,
            .widefat .column-level,
            .widefat .column-status_col {
                width: 120px;
            }
            
            .widefat .column-course {
                width: 200px;
            }
            
            .widefat .column-order {
                width: 80px;
                text-align: center;
            }
        </style>
        <?php
    }
}
add_action('admin_head', 'courses_admin_column_styles');
?>