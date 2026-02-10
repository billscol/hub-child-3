<?php
/**
 * Reviews Form
 * Formulario personalizado de reseñas con estrellas
 * 
 * @package CourseSystem
 * @subpackage Reviews
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Procesar formulario de reseña
 */
function course_process_review_submission() {
    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // Verificar que tenga el nonce
    if (!isset($_POST['review_nonce'])) {
        return;
    }
    
    // Verificar nonce
    if (!wp_verify_nonce($_POST['review_nonce'], 'submit_review_' . $_POST['product_id'])) {
        wp_die('Error de seguridad');
    }
    
    // Verificar que el usuario esté autenticado
    if (!is_user_logged_in()) {
        wp_die('Debes estar logueado para dejar una reseña');
    }
    
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $author = sanitize_text_field($_POST['author']);
    $email = sanitize_email($_POST['email']);
    $comment = sanitize_textarea_field($_POST['comment']);
    
    // Validar datos
    if (empty($comment) || empty($author) || empty($email) || $rating < 1 || $rating > 5) {
        wp_die('Por favor completa todos los campos correctamente');
    }
    
    // Preparar datos del comentario
    $comment_data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $author,
        'comment_author_email' => $email,
        'comment_author_url' => '',
        'comment_content' => $comment,
        'comment_type' => 'review',
        'comment_approved' => 0, // Pendiente de aprobación
        'user_id' => get_current_user_id()
    );
    
    // Insertar comentario
    $comment_id = wp_insert_comment($comment_data);
    
    if ($comment_id) {
        // Guardar la calificación como meta
        update_comment_meta($comment_id, 'rating', $rating);
        
        // Redirigir con mensaje
        wp_redirect(add_query_arg('review_added', 'success', get_permalink($product_id)));
        exit;
    } else {
        wp_die('Error al guardar la reseña');
    }
}
add_action('init', 'course_process_review_submission');

/**
 * Mostrar mensaje de éxito
 */
function course_show_review_success_message() {
    if (isset($_GET['review_added']) && $_GET['review_added'] === 'success') {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("Gracias por tu reseña! Aparecerá después de ser revisada por el administrador.");
        });
        </script>';
    }
}
add_action('wp_head', 'course_show_review_success_message');
?>