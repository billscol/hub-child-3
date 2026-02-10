<?php
/**
 * Recompensas por Reseñas
 * Otorga coins cuando un usuario deja una reseña verificada
 * 
 * @package CoinsSystem
 * @subpackage Rewards
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins cuando se aprueba una reseña
 */
function coins_reward_on_review_approved($comment_id, $comment_approved) {
    // Solo si la reseña fue aprobada
    if ($comment_approved !== 1 && $comment_approved !== 'approve') {
        return;
    }
    
    $comment = get_comment($comment_id);
    
    if (!$comment || $comment->comment_type !== 'review') {
        return;
    }
    
    $user_id = $comment->user_id;
    $product_id = $comment->comment_post_ID;
    
    // Solo si el usuario está registrado
    if (!$user_id) {
        return;
    }
    
    global $wpdb;
    $table = coins_get_table_name('reviews');
    
    // Verificar si ya se otorgaron coins por esta reseña
    $ya_recompensado = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table WHERE comment_id = %d",
            $comment_id
        )
    );
    
    if ($ya_recompensado) {
        return; // Ya se otorgaron coins por esta reseña
    }
    
    // Verificar que el usuario haya comprado el producto
    $user = get_userdata($user_id);
    $ha_comprado = wc_customer_bought_product($user->user_email, $user_id, $product_id);
    
    if (!$ha_comprado) {
        return; // Solo dar coins por reseñas verificadas (compra confirmada)
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $coins_por_resena = 1; // 1 coin por reseña verificada
    
    // Otorgar coins
    $coins_manager->add_coins(
        $user_id,
        $coins_por_resena,
        'resena',
        'Recompensa por reseña verificada - Producto #' . $product_id
    );
    
    // Registrar en tabla de reseñas recompensadas
    $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'comment_id' => $comment_id,
            'product_id' => $product_id,
            'coins_otorgados' => $coins_por_resena,
            'fecha' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%f', '%s')
    );
    
    // Agregar meta al comentario
    update_comment_meta($comment_id, '_coins_otorgados', $coins_por_resena);
    
    // Enviar notificación
    coins_send_reward_notification($user_id, $coins_por_resena, 'resena');
}
add_action('comment_approved_review', 'coins_reward_on_review_approved', 10, 2);
add_action('transition_comment_status', 'coins_reward_on_review_status_change', 10, 3);

/**
 * Manejar cambio de estado de reseña
 */
function coins_reward_on_review_status_change($new_status, $old_status, $comment) {
    if ($new_status === 'approved' && $old_status !== 'approved') {
        coins_reward_on_review_approved($comment->comment_ID, 1);
    }
}

/**
 * Revertir coins si se elimina o desaprueba una reseña
 */
function coins_revert_on_review_unapproved($comment_id) {
    $comment = get_comment($comment_id);
    
    if (!$comment || $comment->comment_type !== 'review') {
        return;
    }
    
    $user_id = $comment->user_id;
    
    if (!$user_id) {
        return;
    }
    
    global $wpdb;
    $table = coins_get_table_name('reviews');
    
    // Buscar si se otorgaron coins
    $registro = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE comment_id = %d",
            $comment_id
        )
    );
    
    if ($registro) {
        $coins_manager = Coins_Manager::get_instance();
        
        // Restar los coins
        $coins_manager->subtract_coins(
            $user_id,
            $registro->coins_otorgados,
            'reversion',
            'Reversión de coins por reseña eliminada/desaprobada'
        );
        
        // Eliminar registro
        $wpdb->delete(
            $table,
            array('comment_id' => $comment_id),
            array('%d')
        );
    }
}
add_action('delete_comment', 'coins_revert_on_review_unapproved');
add_action('spam_comment', 'coins_revert_on_review_unapproved');
add_action('trash_comment', 'coins_revert_on_review_unapproved');

/**
 * Obtener reseñas recompensadas de un usuario
 */
function coins_get_rewarded_reviews($user_id) {
    global $wpdb;
    $table = coins_get_table_name('reviews');
    
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY fecha DESC",
            $user_id
        )
    );
}

/**
 * Contar reseñas recompensadas
 */
function coins_count_rewarded_reviews($user_id) {
    global $wpdb;
    $table = coins_get_table_name('reviews');
    
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d",
            $user_id
        )
    );
}
?>