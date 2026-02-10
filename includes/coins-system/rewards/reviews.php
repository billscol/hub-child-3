<?php
/**
 * Recompensas por Reseñas
 * Otorga coins cuando un usuario deja una reseña verificada
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins por reseña verificada
 */
function otorgar_coins_por_resena($comment_id, $comment_approved) {
    // Solo otorgar si la reseña es aprobada
    if ($comment_approved !== 1 && $comment_approved !== 'approve') {
        return;
    }
    
    global $wpdb;
    
    $comment = get_comment($comment_id);
    
    if (!$comment || $comment->comment_type !== 'review') {
        return;
    }
    
    $user_id = $comment->user_id;
    $product_id = $comment->comment_post_ID;
    
    if (!$user_id) {
        return;
    }
    
    // Verificar que el usuario compró el producto
    if (!wc_customer_bought_product('', $user_id, $product_id)) {
        return;
    }
    
    // Verificar que no se hayan otorgado coins ya por esta reseña
    $tabla_reviews = $wpdb->prefix . 'coins_reviews_rewarded';
    $ya_recompensado = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $tabla_reviews WHERE comment_id = %d",
        $comment_id
    ));
    
    if ($ya_recompensado) {
        return;
    }
    
    // Otorgar coins
    $coins_a_otorgar = apply_filters('coins_por_resena', 1, $comment, $product_id);
    
    if (coins_manager()->add_coins(
        $user_id,
        $coins_a_otorgar,
        'Recompensa por reseña verificada'
    )) {
        // Registrar en tabla de recompensas
        $wpdb->insert(
            $tabla_reviews,
            array(
                'user_id' => $user_id,
                'comment_id' => $comment_id,
                'product_id' => $product_id,
                'coins_otorgados' => $coins_a_otorgar,
                'fecha' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%f', '%s')
        );
    }
}

// Otorgar coins cuando se aprueba una reseña
add_action('comment_approved_to_approved', 'otorgar_coins_por_resena', 10, 2);
add_action('comment_unapproved_to_approved', 'otorgar_coins_por_resena', 10, 2);

/**
 * Mostrar notificación de coins ganados después de enviar reseña
 */
function notificar_coins_por_resena() {
    if (!is_product() || !is_user_logged_in()) {
        return;
    }
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Interceptar envío de reseña
        $('#commentform').on('submit', function() {
            // Mostrar notificación después de enviar
            setTimeout(function() {
                if ($('.comment-form-comment').length === 0) {
                    // Formulario desapareció, reseña enviada
                    var notice = $('<div class="coins-review-notice" style="background: #d4edda; border: 2px solid #28a745; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">' +
                        '<strong>🎉 ¡Gracias por tu reseña!</strong><br>' +
                        'Una vez aprobada, ganarás <strong>1 coin</strong> como recompensa.' +
                        '</div>');
                    
                    $('#review_form_wrapper').prepend(notice);
                    
                    setTimeout(function() {
                        notice.fadeOut();
                    }, 5000);
                }
            }, 1000);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'notificar_coins_por_resena');
?>