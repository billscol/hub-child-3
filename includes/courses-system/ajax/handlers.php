<?php
/**
 * AJAX Handlers
 * Maneja todas las peticiones AJAX del sistema de cursos
 * 
 * @package CoursesSystem
 * @subpackage AJAX
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Marcar lección como completada (AJAX)
 */
function courses_ajax_mark_lesson_complete() {
    // Verificar nonce
    check_ajax_referer('courses_ajax_nonce', 'nonce');
    
    // Verificar usuario logueado
    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => 'Debes iniciar sesión'
        ));
    }
    
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    
    if (!$lesson_id || !$course_id) {
        wp_send_json_error(array(
            'message' => 'Datos inválidos'
        ));
    }
    
    $user_id = get_current_user_id();
    
    // Verificar acceso al curso
    $course_manager = Course_Manager::get_instance();
    if (!$course_manager->user_has_access($user_id, $course_id)) {
        wp_send_json_error(array(
            'message' => 'No tienes acceso a este curso'
        ));
    }
    
    // Marcar como completada
    $lesson_manager = Lesson_Manager::get_instance();
    $result = $lesson_manager->mark_as_completed($user_id, $lesson_id, $course_id);
    
    if ($result) {
        // Obtener progreso actualizado
        $progress = courses_get_user_progress($user_id, $course_id);
        
        wp_send_json_success(array(
            'message' => '¡Lección completada!',
            'progress' => $progress
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Error al marcar lección'
        ));
    }
}
add_action('wp_ajax_courses_mark_complete', 'courses_ajax_mark_lesson_complete');

/**
 * Marcar lección como incompleta (AJAX)
 */
function courses_ajax_mark_lesson_incomplete() {
    check_ajax_referer('courses_ajax_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => 'Debes iniciar sesión'
        ));
    }
    
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    
    if (!$lesson_id || !$course_id) {
        wp_send_json_error(array(
            'message' => 'Datos inválidos'
        ));
    }
    
    $user_id = get_current_user_id();
    
    $lesson_manager = Lesson_Manager::get_instance();
    $result = $lesson_manager->mark_as_incomplete($user_id, $lesson_id);
    
    if ($result) {
        $progress = courses_get_user_progress($user_id, $course_id);
        
        wp_send_json_success(array(
            'message' => 'Lección marcada como incompleta',
            'progress' => $progress
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Error al actualizar lección'
        ));
    }
}
add_action('wp_ajax_courses_mark_incomplete', 'courses_ajax_mark_lesson_incomplete');

/**
 * Obtener progreso de curso (AJAX)
 */
function courses_ajax_get_progress() {
    check_ajax_referer('courses_ajax_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => 'Debes iniciar sesión'
        ));
    }
    
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    
    if (!$course_id) {
        wp_send_json_error(array(
            'message' => 'ID de curso inválido'
        ));
    }
    
    $user_id = get_current_user_id();
    $progress = courses_get_user_progress($user_id, $course_id);
    
    wp_send_json_success(array(
        'progress' => $progress
    ));
}
add_action('wp_ajax_courses_get_progress', 'courses_ajax_get_progress');

/**
 * Resetear progreso de curso (AJAX)
 */
function courses_ajax_reset_progress() {
    check_ajax_referer('courses_ajax_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => 'Debes iniciar sesión'
        ));
    }
    
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    
    if (!$course_id) {
        wp_send_json_error(array(
            'message' => 'ID de curso inválido'
        ));
    }
    
    $user_id = get_current_user_id();
    
    // Verificar acceso
    $course_manager = Course_Manager::get_instance();
    if (!$course_manager->user_has_access($user_id, $course_id)) {
        wp_send_json_error(array(
            'message' => 'No tienes acceso a este curso'
        ));
    }
    
    $result = courses_reset_progress($user_id, $course_id);
    
    if ($result) {
        wp_send_json_success(array(
            'message' => 'Progreso reseteado exitosamente'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Error al resetear progreso'
        ));
    }
}
add_action('wp_ajax_courses_reset_progress', 'courses_ajax_reset_progress');

/**
 * Enqueue scripts AJAX
 */
function courses_enqueue_ajax_scripts() {
    if (!is_singular(array('course', 'lesson'))) {
        return;
    }
    
    ?>
    <script>
        var coursesAjax = {
            ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('courses_ajax_nonce'); ?>'
        };
        
        // Marcar lección como completada
        function coursesMarkComplete(lessonId, courseId) {
            if (!confirm('¿Marcar esta lección como completada?')) {
                return;
            }
            
            var btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Procesando...';
            
            fetch(coursesAjax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'courses_mark_complete',
                    nonce: coursesAjax.nonce,
                    lesson_id: lessonId,
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.data.message || 'Error al marcar lección');
                    btn.disabled = false;
                    btn.textContent = '✅ Marcar como Completada';
                }
            })
            .catch(error => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.textContent = '✅ Marcar como Completada';
            });
        }
        
        // Marcar lección como incompleta
        function coursesMarkIncomplete(lessonId, courseId) {
            if (!confirm('¿Marcar esta lección como incompleta?')) {
                return;
            }
            
            var btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Procesando...';
            
            fetch(coursesAjax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'courses_mark_incomplete',
                    nonce: coursesAjax.nonce,
                    lesson_id: lessonId,
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.data.message || 'Error al actualizar lección');
                    btn.disabled = false;
                    btn.textContent = 'Marcar como Incompleta';
                }
            })
            .catch(error => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.textContent = 'Marcar como Incompleta';
            });
        }
    </script>
    <?php
}
add_action('wp_footer', 'courses_enqueue_ajax_scripts');
?>