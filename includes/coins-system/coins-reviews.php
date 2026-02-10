<?php
/**
 * Recompensas por reseñas de productos
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar 1 coin por reseña aprobada de curso premium
 */
add_action('comment_approved', 'coins_recompensa_por_resena', 10, 2);

function coins_recompensa_por_resena($comment_ID, $comment_approved) {
    if ($comment_approved != 1) {
        return;
    }
    
    $comment = get_comment($comment_ID);
    if (!$comment) {
        return;
    }
    
    // Solo reseñas de productos (WooCommerce)
    if ($comment->comment_type !== 'review' && $comment->comment_type !== '') {
        return;
    }
    
    $product_id = $comment->comment_post_ID;
    $user_id    = $comment->user_id;
    
    // Solo usuarios registrados
    if (!$user_id) {
        return;
    }
    
    // Verificar que el producto es premium
    if (!has_term('premium', 'product_cat', $product_id)) {
        return;
    }
    
    global $wpdb;
    $tabla = $wpdb->prefix . 'coins_reviews_rewarded';
    
    // Verificar si ya se otorgó coin por esta reseña
    $ya_otorgado = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $tabla WHERE comment_id = %d",
        $comment_ID
    ));
    
    if ($ya_otorgado) {
        return;
    }
    
    // Verificar que el usuario compró el producto
    if (!wc_customer_bought_product(get_userdata($user_id)->user_email, $user_id, $product_id)) {
        return;
    }
    
    // Otorgar 1 coin
    $coins = 1;
    $descripcion = 'Coin por reseña del curso ID ' . $product_id;
    
    if (coins_manager()->agregar_coins($user_id, $coins, $descripcion, null)) {
        // Registrar en tabla de reviews recompensadas
        $wpdb->insert(
            $tabla,
            array(
                'user_id'        => $user_id,
                'comment_id'     => $comment_ID,
                'product_id'     => $product_id,
                'coins_otorgados'=> $coins,
                'fecha'          => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%f', '%s')
        );
    }
}
