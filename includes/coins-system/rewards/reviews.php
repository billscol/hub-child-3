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
 * Otorgar coins cuando una reseña es aprobada
 * Hook: comment_approved
 */
function coins_reward_on_review_approved($comment_id, $comment_approved) {
    // Solo procesar si el comentario fue aprobado
    if ($comment_approved !== '1' && $comment_approved !== 1) {
        return;
    }
    
    $comment = get_comment($comment_id);
    
    if (!$comment) {
        return;
    }
    
    // Verificar que sea una reseña de producto
    if ($comment->comment_type !== 'review') {
        return;
    }
    
    $user_id = $comment->user_id;
    
    if (!$user_id) {
        return;
    }
    
    // Verificar que no se haya otorgado coins ya
    global $wpdb;
    $table_reviews = coins_get_table_name('reviews');
    
    $already_rewarded = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_reviews WHERE comment_id = %d",
            $comment_id
        )
    );
    
    if ($already_rewarded) {
        return; // Ya se otorgaron coins por esta reseña
    }
    
    $product_id = $comment->comment_post_ID;
    
    // Verificar que el usuario haya comprado el producto (reseña verificada)
    if (!coins_user_purchased_product($user_id, $product_id)) {
        return; // Solo recompensar reseñas verificadas
    }
    
    // Otorgar 1 coin por reseña verificada
    $coins_to_add = 1;
    $coins_manager = Coins_Manager::get_instance();
    
    $product = wc_get_product($product_id);
    $product_name = $product ? $product->get_name() : 'Producto #' . $product_id;
    
    $coins_manager->add_coins(
        $user_id,
        $coins_to_add,
        'resena',
        'Reseña verificada del curso: ' . $product_name,
        null
    );
    
    // Registrar en la tabla de reseñas premiadas
    $wpdb->insert(
        $table_reviews,
        array(
            'user_id' => $user_id,
            'comment_id' => $comment_id,
            'product_id' => $product_id,
            'coins_otorgados' => $coins_to_add,
            'fecha' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%f', '%s')
    );
    
    // Hook para extensiones
    do_action('coins_review_rewarded', $user_id, $coins_to_add, $comment_id, $product_id);
}
add_action('comment_approved', 'coins_reward_on_review_approved', 10, 2);

/**
 * También verificar cuando se cambia el estado de un comentario
 */
function coins_reward_on_review_status_change($new_status, $old_status, $comment) {
    // Si el comentario pasó de no aprobado a aprobado
    if ($new_status === 'approved' && $old_status !== 'approved') {
        coins_reward_on_review_approved($comment->comment_ID, 1);
    }
}
add_action('transition_comment_status', 'coins_reward_on_review_status_change', 10, 3);

/**
 * Verificar si un usuario compró un producto
 */
function coins_user_purchased_product($user_id, $product_id) {
    if (!$user_id) {
        return false;
    }
    
    $user = get_user_by('id', $user_id);
    
    if (!$user) {
        return false;
    }
    
    return wc_customer_bought_product($user->user_email, $user_id, $product_id);
}

/**
 * Mostrar badge de "reseña verificada con coins" en las reseñas
 */
function coins_add_verified_badge_to_review($comment_text, $comment) {
    if ($comment->comment_type !== 'review') {
        return $comment_text;
    }
    
    global $wpdb;
    $table_reviews = coins_get_table_name('reviews');
    
    $rewarded = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_reviews WHERE comment_id = %d",
            $comment->comment_ID
        )
    );
    
    if ($rewarded) {
        $badge = '<span class="coins-verified-review-badge" style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 20px; font-size: 12px; font-weight: 600; margin-left: 8px;">';
        $badge .= '<span style="font-size: 14px;">✔️</span> Verificada + 🪙';
        $badge .= '</span>';
        
        $comment_text = $badge . ' ' . $comment_text;
    }
    
    return $comment_text;
}
add_filter('comment_text', 'coins_add_verified_badge_to_review', 10, 2);

/**
 * Obtener estadísticas de reseñas de un usuario
 */
function coins_get_user_review_stats($user_id) {
    global $wpdb;
    $table_reviews = coins_get_table_name('reviews');
    
    $stats = array(
        'total_reviews' => 0,
        'total_coins_earned' => 0,
        'last_review_date' => null
    );
    
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT COUNT(*) as total, SUM(coins_otorgados) as total_coins, MAX(fecha) as last_date 
             FROM $table_reviews 
             WHERE user_id = %d",
            $user_id
        )
    );
    
    if ($results && $results[0]) {
        $stats['total_reviews'] = (int) $results[0]->total;
        $stats['total_coins_earned'] = (float) $results[0]->total_coins;
        $stats['last_review_date'] = $results[0]->last_date;
    }
    
    return $stats;
}

/**
 * Revertir coins si la reseña es eliminada o desaprobada
 */
function coins_revert_on_review_deleted($comment_id, $comment) {
    if ($comment->comment_type !== 'review') {
        return;
    }
    
    global $wpdb;
    $table_reviews = coins_get_table_name('reviews');
    
    $rewarded = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_reviews WHERE comment_id = %d",
            $comment_id
        )
    );
    
    if (!$rewarded) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    
    // Restar coins
    $coins_manager->subtract_coins(
        $rewarded->user_id,
        $rewarded->coins_otorgados,
        'reversion_resena',
        'Reversión de coins por reseña eliminada/desaprobada',
        null
    );
    
    // Eliminar registro
    $wpdb->delete(
        $table_reviews,
        array('comment_id' => $comment_id),
        array('%d')
    );
}
add_action('delete_comment', 'coins_revert_on_review_deleted', 10, 2);
add_action('wp_set_comment_status', function($comment_id, $comment_status) {
    if ($comment_status === 'unapproved' || $comment_status === 'spam' || $comment_status === 'trash') {
        $comment = get_comment($comment_id);
        if ($comment) {
            coins_revert_on_review_deleted($comment_id, $comment);
        }
    }
}, 10, 2);
?>