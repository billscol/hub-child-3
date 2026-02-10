<?php
/**
 * Metabox de Lección
 * Configuración y opciones de lecciones en el admin
 * 
 * @package CoursesSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox de lección
 */
function courses_add_lesson_metabox() {
    add_meta_box(
        'lesson_settings',
        '⚙️ Configuración de la Lección',
        'courses_render_lesson_metabox',
        'lesson',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'courses_add_lesson_metabox');

/**
 * Renderizar metabox de lección
 */
function courses_render_lesson_metabox($post) {
    wp_nonce_field('courses_save_lesson_meta', 'courses_lesson_nonce');
    
    $duration = get_post_meta($post->ID, '_lesson_duration', true);
    $video_url = get_post_meta($post->ID, '_lesson_video_url', true);
    $resources = get_post_meta($post->ID, '_lesson_resources', true);
    
    ?>
    <style>
        .lesson-meta-field {
            margin-bottom: 20px;
        }
        
        .lesson-meta-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
        }
        
        .lesson-meta-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .lesson-meta-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .lesson-meta-help {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .lesson-info-box {
            padding: 12px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 20px;
        }
        
        .lesson-info-box p {
            margin: 5px 0;
            font-size: 12px;
            color: #1e40af;
        }
    </style>
    
    <div class="lesson-meta-fields">
        <?php if ($post->post_parent) : 
            $course = get_post($post->post_parent);
        ?>
            <div class="lesson-info-box">
                <p><strong>📚 Curso:</strong> <?php echo esc_html($course->post_title); ?></p>
                <p><strong>🔢 Orden:</strong> <?php echo $post->menu_order; ?></p>
            </div>
        <?php else : ?>
            <div style="padding: 12px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 20px;">
                <p style="margin: 0; color: #92400e; font-size: 12px;">
                    <strong>⚠️ Atención:</strong> Esta lección no tiene un curso asignado. 
                    Selecciona un curso en "Atributos de la página" en el panel derecho.
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Duración -->
        <div class="lesson-meta-field">
            <label class="lesson-meta-label" for="lesson_duration">
                ⏱️ Duración (minutos)
            </label>
            <input 
                type="number" 
                id="lesson_duration" 
                name="lesson_duration" 
                value="<?php echo esc_attr($duration); ?>" 
                min="0"
                step="1"
                class="lesson-meta-input"
                placeholder="Ej: 15"
            />
            <p class="lesson-meta-help">
                Tiempo estimado para completar esta lección.
            </p>
        </div>
        
        <!-- Video URL -->
        <div class="lesson-meta-field">
            <label class="lesson-meta-label" for="lesson_video_url">
                🎥 URL del Video
            </label>
            <input 
                type="url" 
                id="lesson_video_url" 
                name="lesson_video_url" 
                value="<?php echo esc_url($video_url); ?>" 
                class="lesson-meta-input"
                placeholder="https://youtube.com/watch?v=..."
            />
            <p class="lesson-meta-help">
                YouTube, Vimeo, u otro servicio de video.
            </p>
        </div>
        
        <!-- Recursos -->
        <div class="lesson-meta-field">
            <label class="lesson-meta-label" for="lesson_resources">
                📁 Recursos / Enlaces
            </label>
            <textarea 
                id="lesson_resources" 
                name="lesson_resources" 
                class="lesson-meta-input"
                rows="4"
                placeholder="Un enlace por línea..."><?php echo esc_textarea($resources); ?></textarea>
            <p class="lesson-meta-help">
                Archivos descargables, enlaces útiles, etc. (uno por línea)
            </p>
        </div>
    </div>
    <?php
}

/**
 * Guardar metabox de lección
 */
function courses_save_lesson_metabox($post_id) {
    // Verificar nonce
    if (!isset($_POST['courses_lesson_nonce']) || 
        !wp_verify_nonce($_POST['courses_lesson_nonce'], 'courses_save_lesson_meta')) {
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
    
    // Guardar duración
    if (isset($_POST['lesson_duration'])) {
        update_post_meta($post_id, '_lesson_duration', intval($_POST['lesson_duration']));
    }
    
    // Guardar video URL
    if (isset($_POST['lesson_video_url'])) {
        update_post_meta($post_id, '_lesson_video_url', esc_url_raw($_POST['lesson_video_url']));
    }
    
    // Guardar recursos
    if (isset($_POST['lesson_resources'])) {
        update_post_meta($post_id, '_lesson_resources', sanitize_textarea_field($_POST['lesson_resources']));
    }
}
add_action('save_post_lesson', 'courses_save_lesson_metabox');

/**
 * Agregar quick edit para orden de lección
 */
function courses_lesson_quick_edit_custom_box($column_name, $post_type) {
    if ($column_name !== 'order' || $post_type !== 'lesson') {
        return;
    }
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title">Orden</span>
                <span class="input-text-wrap">
                    <input type="number" name="menu_order" class="ptitle" value="" />
                </span>
            </label>
        </div>
    </fieldset>
    <?php
}
add_action('quick_edit_custom_box', 'courses_lesson_quick_edit_custom_box', 10, 2);
?>