<?php
/**
 * Metabox de Curso
 * Configuración y opciones de cursos en el admin
 * 
 * @package CoursesSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox de curso
 */
function courses_add_course_metabox() {
    add_meta_box(
        'course_settings',
        '⚙️ Configuración del Curso',
        'courses_render_course_metabox',
        'course',
        'side',
        'high'
    );
    
    add_meta_box(
        'course_stats',
        '📊 Estadísticas del Curso',
        'courses_render_stats_metabox',
        'course',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'courses_add_course_metabox');

/**
 * Renderizar metabox de configuración
 */
function courses_render_course_metabox($post) {
    wp_nonce_field('courses_save_course_meta', 'courses_course_nonce');
    
    $product_id = get_post_meta($post->ID, '_course_product_id', true);
    $level = get_post_meta($post->ID, '_course_level', true);
    $duration = get_post_meta($post->ID, '_course_duration', true);
    $is_free = get_post_meta($post->ID, '_course_is_free', true);
    
    ?>
    <style>
        .course-meta-field {
            margin-bottom: 20px;
        }
        
        .course-meta-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
        }
        
        .course-meta-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .course-meta-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .course-meta-help {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .course-meta-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .course-meta-checkbox input {
            width: auto;
        }
    </style>
    
    <div class="course-meta-fields">
        <!-- Producto WooCommerce -->
        <div class="course-meta-field">
            <label class="course-meta-label" for="course_product_id">
                🛒 Producto WooCommerce
            </label>
            <select id="course_product_id" name="course_product_id" class="course-meta-input">
                <option value="0">Seleccionar producto...</option>
                <?php
                if (class_exists('WooCommerce')) {
                    $products = get_posts(array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ));
                    
                    foreach ($products as $product) {
                        printf(
                            '<option value="%d"%s>%s</option>',
                            $product->ID,
                            selected($product_id, $product->ID, false),
                            esc_html($product->post_title)
                        );
                    }
                }
                ?>
            </select>
            <p class="course-meta-help">
                Al comprar este producto, se otorga acceso automático al curso.
            </p>
        </div>
        
        <!-- Curso gratuito -->
        <div class="course-meta-field">
            <label class="course-meta-checkbox">
                <input 
                    type="checkbox" 
                    name="course_is_free" 
                    value="1"
                    <?php checked($is_free, '1'); ?>
                />
                <span>🎁 Curso gratuito (acceso para todos)</span>
            </label>
        </div>
        
        <!-- Nivel -->
        <div class="course-meta-field">
            <label class="course-meta-label" for="course_level">
                🎯 Nivel del Curso
            </label>
            <select id="course_level" name="course_level" class="course-meta-input">
                <option value="" <?php selected($level, ''); ?>>Seleccionar nivel...</option>
                <option value="principiante" <?php selected($level, 'principiante'); ?>>Principiante</option>
                <option value="intermedio" <?php selected($level, 'intermedio'); ?>>Intermedio</option>
                <option value="avanzado" <?php selected($level, 'avanzado'); ?>>Avanzado</option>
                <option value="todos" <?php selected($level, 'todos'); ?>>Todos los niveles</option>
            </select>
        </div>
        
        <!-- Duración estimada -->
        <div class="course-meta-field">
            <label class="course-meta-label" for="course_duration">
                ⏱️ Duración Estimada (horas)
            </label>
            <input 
                type="number" 
                id="course_duration" 
                name="course_duration" 
                value="<?php echo esc_attr($duration); ?>" 
                min="0"
                step="0.5"
                class="course-meta-input"
                placeholder="Ej: 5"
            />
            <p class="course-meta-help">
                Tiempo estimado para completar el curso.
            </p>
        </div>
    </div>
    <?php
}

/**
 * Renderizar metabox de estadísticas
 */
function courses_render_stats_metabox($post) {
    $course_manager = Course_Manager::get_instance();
    $stats = $course_manager->get_stats($post->ID);
    $total_lessons = $course_manager->count_lessons($post->ID);
    
    ?>
    <style>
        .course-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .course-stat-box {
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .course-stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .course-stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .course-stat-box.primary {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }
        
        .course-stat-box.primary .course-stat-value {
            color: #1e40af;
        }
        
        .course-stat-box.success {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .course-stat-box.success .course-stat-value {
            color: #047857;
        }
        
        .course-stat-box.warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
        }
        
        .course-stat-box.warning .course-stat-value {
            color: #b45309;
        }
    </style>
    
    <div class="course-stats-grid">
        <div class="course-stat-box primary">
            <div class="course-stat-value"><?php echo $total_lessons; ?></div>
            <div class="course-stat-label">Lecciones</div>
        </div>
        
        <div class="course-stat-box success">
            <div class="course-stat-value"><?php echo $stats['total_students']; ?></div>
            <div class="course-stat-label">Estudiantes</div>
        </div>
        
        <div class="course-stat-box warning">
            <div class="course-stat-value"><?php echo $stats['active_students']; ?></div>
            <div class="course-stat-label">Activos</div>
        </div>
        
        <div class="course-stat-box success">
            <div class="course-stat-value"><?php echo $stats['completed_students']; ?></div>
            <div class="course-stat-label">Completados</div>
        </div>
        
        <div class="course-stat-box primary">
            <div class="course-stat-value"><?php echo $stats['average_progress']; ?>%</div>
            <div class="course-stat-label">Progreso Promedio</div>
        </div>
    </div>
    
    <?php if ($total_lessons === 0) : ?>
        <div style="padding: 15px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; margin-top: 15px;">
            <p style="margin: 0; color: #92400e;">
                <strong>⚠️ Atención:</strong> Este curso no tiene lecciones aún. 
                <a href="<?php echo admin_url('post-new.php?post_type=lesson&course=' . $post->ID); ?>">Añade la primera lección</a>
            </p>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Guardar metabox de curso
 */
function courses_save_course_metabox($post_id) {
    // Verificar nonce
    if (!isset($_POST['courses_course_nonce']) || 
        !wp_verify_nonce($_POST['courses_course_nonce'], 'courses_save_course_meta')) {
        return;
    }
    
    // Verificar autoguardado
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar producto ID
    if (isset($_POST['course_product_id'])) {
        update_post_meta($post_id, '_course_product_id', intval($_POST['course_product_id']));
    }
    
    // Guardar si es gratuito
    $is_free = isset($_POST['course_is_free']) ? '1' : '0';
    update_post_meta($post_id, '_course_is_free', $is_free);
    
    // Guardar nivel
    if (isset($_POST['course_level'])) {
        update_post_meta($post_id, '_course_level', sanitize_text_field($_POST['course_level']));
    }
    
    // Guardar duración
    if (isset($_POST['course_duration'])) {
        update_post_meta($post_id, '_course_duration', floatval($_POST['course_duration']));
    }
}
add_action('save_post_course', 'courses_save_course_metabox');
?>