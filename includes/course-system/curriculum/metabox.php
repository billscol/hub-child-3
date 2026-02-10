<?php
/**
 * Curriculum Metabox
 * Metabox para gestionar módulos y lecciones en el backend
 * 
 * @package CourseSystem
 * @subpackage Curriculum
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox de Módulos y Lecciones en productos
 */
function course_add_curriculum_metabox() {
    add_meta_box(
        'course_curriculum',
        '📚 Currículum del Curso (Módulos y Lecciones)',
        'course_render_curriculum_metabox',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'course_add_curriculum_metabox');

/**
 * Renderizar metabox de curriculum
 */
function course_render_curriculum_metabox($post) {
    wp_nonce_field('save_course_curriculum', 'course_curriculum_nonce');
    
    $curriculum = get_post_meta($post->ID, '_course_curriculum', true);
    
    if (empty($curriculum)) {
        $curriculum = array();
    }
    
    // Calcular totales
    $total_modules = count($curriculum);
    $total_lessons = 0;
    
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    
    ?>
    <style>
    /* Contenedor principal */
    .course-curriculum-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
    }
    
    /* Header con estadísticas */
    .curriculum-header {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px;
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%);
        border: 1px solid rgba(218, 4, 128, 0.3);
        border-radius: 10px;
        margin-bottom: 25px;
    }
    
    .curriculum-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        padding: 12px 18px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    
    .curriculum-stat label {
        font-weight: 600;
        color: #da0480;
        margin: 0;
        font-size: 14px;
    }
    
    .curriculum-stat input {
        width: 55px !important;
        text-align: center;
        font-weight: 700;
        color: #333;
        border: 2px solid rgba(218, 4, 128, 0.3) !important;
        border-radius: 6px;
        padding: 6px !important;
    }
    
    /* Items de módulo */
    .module-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #da0480;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }
    
    .module-item:hover {
        box-shadow: 0 4px 16px rgba(218, 4, 128, 0.2);
        transform: translateY(-2px);
    }
    
    .module-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .module-header h4 {
        margin: 0;
        color: #da0480;
        font-size: 17px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .module-item input[type="text"] {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: inherit;
    }
    
    .module-item input[type="text"]:focus {
        border-color: #da0480;
        outline: none;
        box-shadow: 0 0 0 4px rgba(218, 4, 128, 0.1);
    }
    
    /* Sección de bloqueo */
    .locked-section {
        background: rgba(218, 4, 128, 0.06);
        padding: 14px;
        border-left: 4px solid #da0480;
        border-radius: 8px;
        margin: 18px 0;
    }
    
    .locked-section label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        margin: 0;
    }
    
    .locked-section input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #da0480;
    }
    
    .locked-section small {
        display: block;
        margin-top: 10px;
        margin-left: 32px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }
    
    /* Contenedor de lecciones */
    .lessons-container {
        margin-top: 18px;
        padding: 18px;
        background: rgba(218, 4, 128, 0.03);
        border-radius: 8px;
        border: 1px solid rgba(218, 4, 128, 0.1);
    }
    
    .lessons-container > label {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 15px;
    }
    
    .lesson-item {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
        align-items: center;
    }
    
    .lesson-item::before {
        content: '▶';
        color: #da0480;
        font-size: 11px;
        flex-shrink: 0;
    }
    
    .lesson-item input {
        flex: 1;
        padding: 10px 12px !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 7px;
    }
    
    .lesson-item input:focus {
        border-color: #da0480 !important;
        box-shadow: 0 0 0 3px rgba(218, 4, 128, 0.1);
    }
    
    /* Botones */
    .button {
        border-radius: 8px !important;
        padding: 10px 18px !important;
        font-weight: 600 !important;
        transition: all 0.3s !important;
        border: none !important;
        cursor: pointer !important;
    }
    
    .button-primary {
        background: linear-gradient(135deg, #da0480 0%, #b00368 100%) !important;
        border-color: #da0480 !important;
        text-shadow: none !important;
        box-shadow: 0 3px 8px rgba(218, 4, 128, 0.35) !important;
    }
    
    .button-primary:hover {
        background: linear-gradient(135deg, #b00368 0%, #8a0252 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(218, 4, 128, 0.5) !important;
    }
    
    .remove-module {
        background: #ef4444 !important;
        color: #fff !important;
    }
    
    .remove-module:hover {
        background: #dc2626 !important;
        transform: translateY(-1px);
    }
    
    .remove-lesson {
        background: #f87171 !important;
        color: #fff !important;
        padding: 8px 14px !important;
        font-size: 13px !important;
    }
    
    .remove-lesson:hover {
        background: #ef4444 !important;
    }
    
    .add-lesson {
        background: #fff !important;
        color: #da0480 !important;
        border: 2px solid #da0480 !important;
    }
    
    .add-lesson:hover {
        background: #da0480 !important;
        color: #fff !important;
    }
    
    /* Responsive */
    @media (max-width: 782px) {
        .curriculum-header {
            flex-direction: column;
        }
        
        .curriculum-stat {
            width: 100%;
            justify-content: space-between;
        }
        
        .module-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .module-header .remove-module {
            width: 100%;
        }
        
        .lesson-item {
            flex-wrap: wrap;
        }
        
        .lesson-item input {
            width: 100%;
        }
        
        .remove-lesson {
            width: 100%;
        }
    }
    </style>
    
    <div id="course-curriculum-container">
        <!-- Header con estadísticas -->
        <div class="curriculum-header">
            <div class="curriculum-stat">
                <label>Total Módulos:</label>
                <input type="number" id="total-modules" value="<?php echo $total_modules; ?>" readonly>
            </div>
            <div class="curriculum-stat">
                <label>Total Lecciones:</label>
                <input type="number" id="total-lessons" value="<?php echo $total_lessons; ?>" readonly>
            </div>
        </div>
        
        <!-- Lista de módulos -->
        <div id="modules-list">
            <?php if (!empty($curriculum)) : ?>
                <?php foreach ($curriculum as $index => $module) : 
                    $is_locked = isset($module['locked']) ? $module['locked'] : false;
                ?>
                    <div class="module-item" data-index="<?php echo $index; ?>">
                        <div class="module-header">
                            <h4>📚 Módulo <?php echo $index + 1; ?></h4>
                            <button type="button" class="button remove-module">❌ Eliminar</button>
                        </div>
                        
                        <p style="margin-bottom: 16px;">
                            <label style="font-weight:600; display:block; margin-bottom:10px; color:#374151;">Nombre del Módulo</label>
                            <input 
                                type="text" 
                                name="curriculum[<?php echo $index; ?>][name]" 
                                value="<?php echo esc_attr($module['name']); ?>" 
                                placeholder="Ej: Mentalidad del Trafficker"
                            >
                        </p>
                        
                        <div class="locked-section">
                            <label>
                                <input 
                                    type="checkbox" 
                                    name="curriculum[<?php echo $index; ?>][locked]" 
                                    value="1" 
                                    <?php checked($is_locked, true); ?>
                                >
                                <span style="font-weight:600; font-size:14px;">🔒 Bloquear este módulo (solo visible para compradores)</span>
                            </label>
                            <small>Si está marcado, los visitantes verán "Contenido Privado" en lugar de las lecciones.</small>
                        </div>
                        
                        <div class="lessons-container">
                            <label>📝 Lecciones del Módulo</label>
                            <div class="lessons-list">
                                <?php if (!empty($module['lessons'])) : ?>
                                    <?php foreach ($module['lessons'] as $l_index => $lesson) : ?>
                                        <div class="lesson-item">
                                            <input 
                                                type="text" 
                                                name="curriculum[<?php echo $index; ?>][lessons][<?php echo $l_index; ?>]" 
                                                value="<?php echo esc_attr($lesson); ?>" 
                                                placeholder="Nombre de la lección"
                                            >
                                            <button type="button" class="button remove-lesson">❌</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button add-lesson">➕ Agregar Lección</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" class="button button-primary" id="add-module">🎓 Agregar Nuevo Módulo</button>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var moduleIndex = <?php echo count($curriculum); ?>;
        
        // Agregar nuevo módulo
        $('#add-module').on('click', function() {
            var html = `
            <div class="module-item" data-index="${moduleIndex}">
                <div class="module-header">
                    <h4>📚 Módulo ${moduleIndex + 1}</h4>
                    <button type="button" class="button remove-module">❌ Eliminar</button>
                </div>
                
                <p style="margin-bottom: 16px;">
                    <label style="font-weight:600; display:block; margin-bottom:10px; color:#374151;">Nombre del Módulo</label>
                    <input type="text" name="curriculum[${moduleIndex}][name]" placeholder="Ej: Configuraciones Iniciales">
                </p>
                
                <div class="locked-section">
                    <label>
                        <input type="checkbox" name="curriculum[${moduleIndex}][locked]" value="1">
                        <span style="font-weight:600; font-size:14px;">🔒 Bloquear este módulo (solo visible para compradores)</span>
                    </label>
                    <small>Si está marcado, los visitantes verán "Contenido Privado" en lugar de las lecciones.</small>
                </div>
                
                <div class="lessons-container">
                    <label>📝 Lecciones del Módulo</label>
                    <div class="lessons-list"></div>
                    <button type="button" class="button add-lesson">➕ Agregar Lección</button>
                </div>
            </div>
            `;
            
            $('#modules-list').append(html);
            moduleIndex++;
            updateTotals();
        });
        
        // Agregar lección
        $(document).on('click', '.add-lesson', function() {
            var moduleItem = $(this).closest('.module-item');
            var moduleIdx = moduleItem.data('index');
            var lessonCount = moduleItem.find('.lesson-item').length;
            
            var html = `
            <div class="lesson-item">
                <input type="text" name="curriculum[${moduleIdx}][lessons][${lessonCount}]" placeholder="Nombre de la lección">
                <button type="button" class="button remove-lesson">❌</button>
            </div>
            `;
            
            moduleItem.find('.lessons-list').append(html);
            updateTotals();
        });
        
        // Eliminar módulo
        $(document).on('click', '.remove-module', function() {
            if (confirm('¿Estás seguro de eliminar este módulo completo?')) {
                $(this).closest('.module-item').fadeOut(300, function() {
                    $(this).remove();
                    updateTotals();
                    renumberModules();
                });
            }
        });
        
        // Eliminar lección
        $(document).on('click', '.remove-lesson', function() {
            $(this).closest('.lesson-item').fadeOut(200, function() {
                $(this).remove();
                updateTotals();
            });
        });
        
        // Actualizar totales
        function updateTotals() {
            var totalModules = $('.module-item').length;
            var totalLessons = $('.lesson-item').length;
            
            $('#total-modules').val(totalModules);
            $('#total-lessons').val(totalLessons);
        }
        
        // Renumerar módulos
        function renumberModules() {
            $('.module-item').each(function(index) {
                $(this).find('.module-header h4').text('📚 Módulo ' + (index + 1));
            });
        }
    });
    </script>
    <?php
}

/**
 * Guardar datos del currículum
 */
function course_save_curriculum($post_id) {
    // Verificar nonce
    if (!isset($_POST['course_curriculum_nonce']) || 
        !wp_verify_nonce($_POST['course_curriculum_nonce'], 'save_course_curriculum')) {
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
    
    // Guardar curriculum
    if (isset($_POST['curriculum'])) {
        $curriculum = array();
        
        foreach ($_POST['curriculum'] as $module) {
            if (!empty($module['name'])) {
                $lessons = array();
                
                if (!empty($module['lessons'])) {
                    foreach ($module['lessons'] as $lesson) {
                        if (!empty($lesson)) {
                            $lessons[] = sanitize_text_field($lesson);
                        }
                    }
                }
                
                $curriculum[] = array(
                    'name' => sanitize_text_field($module['name']),
                    'lessons' => $lessons,
                    'locked' => isset($module['locked']) ? true : false
                );
            }
        }
        
        update_post_meta($post_id, '_course_curriculum', $curriculum);
    }
}
add_action('save_post_product', 'course_save_curriculum');
?>