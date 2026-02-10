<?php
/**
 * Recompensas por Compras
 * Otorga coins cuando un usuario compra un curso premium
 * 
 * @package CoinsSystem
 * @subpackage Rewards
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins al completar una compra
 * Hook: woocommerce_order_status_completed
 */
function coins_reward_on_purchase($order_id) {
    // Evitar procesar dos veces el mismo pedido
    if (get_post_meta($order_id, '_coins_rewarded', true)) {
        return;
    }
    
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    // Solo otorgar si hay un usuario válido
    if (!$user_id) {
        return;
    }
    
    // No otorgar coins si el pago fue con coins
    if ($order->get_payment_method() === 'coins') {
        update_post_meta($order_id, '_coins_rewarded', 'skipped_coins_payment');
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $total_coins_to_add = 0;
    $productos_premiados = array();
    
    // Recorrer productos del pedido
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        
        if (!$product) {
            continue;
        }
        
        // Solo otorgar coins por productos de pago (no gratuitos)
        if ($product->get_price() > 0) {
            $quantity = $item->get_quantity();
            
            // 1 coin por cada curso premium comprado
            $coins_per_product = 1;
            $coins_to_add = $coins_per_product * $quantity;
            
            $total_coins_to_add += $coins_to_add;
            $productos_premiados[] = $product->get_name() . ' (x' . $quantity . ')';
        }
    }
    
    // Otorgar coins si hay productos válidos
    if ($total_coins_to_add > 0) {
        $descripcion = 'Compra de curso premium: ' . implode(', ', $productos_premiados);
        
        $coins_manager->add_coins(
            $user_id,
            $total_coins_to_add,
            'compra',
            $descripcion,
            $order_id
        );
        
        // Marcar pedido como procesado
        update_post_meta($order_id, '_coins_rewarded', 'yes');
        update_post_meta($order_id, '_coins_amount', $total_coins_to_add);
        
        // Agregar nota al pedido
        $order->add_order_note(
            sprintf(
                '🪙 Se otorgaron %s coins al cliente por esta compra.',
                $coins_manager->format_coins($total_coins_to_add)
            )
        );
        
        // Hook para extensiones
        do_action('coins_purchase_rewarded', $user_id, $total_coins_to_add, $order_id);
    } else {
        // Marcar como procesado pero sin coins (productos gratuitos)
        update_post_meta($order_id, '_coins_rewarded', 'no_eligible_products');
    }
}
add_action('woocommerce_order_status_completed', 'coins_reward_on_purchase');

/**
 * Mostrar coins ganados en la página de agradecimiento
 */
function coins_show_earned_on_thankyou($order_id) {
    $coins_rewarded = get_post_meta($order_id, '_coins_rewarded', true);
    
    if ($coins_rewarded !== 'yes') {
        return;
    }
    
    $coins_amount = get_post_meta($order_id, '_coins_amount', true);
    
    if (!$coins_amount) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $user_id = get_current_user_id();
    $current_balance = coins_get_balance($user_id);
    
    ?>
    <div class="coins-earned-notice" style="margin: 20px 0; padding: 20px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3);">
        <h3 style="color: #da0480; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 24px;">🎉</span>
            ¡Has ganado coins!
        </h3>
        <p style="margin: 8px 0; font-size: 15px;">
            Has recibido <strong style="color: #da0480;"><?php echo esc_html($coins_manager->format_coins($coins_amount)); ?> coins</strong> por esta compra.
        </p>
        <p style="margin: 8px 0; font-size: 14px; color: #6b7280;">
            Tu saldo actual: <strong style="color: #da0480;"><?php echo esc_html($coins_manager->format_coins($current_balance)); ?> coins</strong>
        </p>
        <p style="margin: 12px 0 0 0; padding: 12px; background: rgba(218, 4, 128, 0.08); border-radius: 8px; font-size: 14px; color: #374151;">
            💡 <strong>Tip:</strong> Puedes usar tus coins para canjear cursos gratuitos. ¡Sigue acumulando!
        </p>
    </div>
    <?php
}
add_action('woocommerce_thankyou', 'coins_show_earned_on_thankyou', 20);

/**
 * Revertir coins si el pedido es cancelado o reembolsado
 */
function coins_revert_on_order_cancelled($order_id) {
    $coins_rewarded = get_post_meta($order_id, '_coins_rewarded', true);
    
    if ($coins_rewarded !== 'yes') {
        return;
    }
    
    $coins_amount = get_post_meta($order_id, '_coins_amount', true);
    
    if (!$coins_amount) {
        return;
    }
    
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    
    // Restar coins otorgados
    $coins_manager->subtract_coins(
        $user_id,
        $coins_amount,
        'reversion',
        'Reversión de coins por cancelación/reembolso del pedido #' . $order_id,
        $order_id
    );
    
    // Actualizar meta
    update_post_meta($order_id, '_coins_rewarded', 'reverted');
    
    // Nota al pedido
    $order->add_order_note(
        sprintf(
            '❌ Se revirtieron %s coins del cliente por cancelación/reembolso.',
            $coins_manager->format_coins($coins_amount)
        )
    );
}
add_action('woocommerce_order_status_cancelled', 'coins_revert_on_order_cancelled');
add_action('woocommerce_order_status_refunded', 'coins_revert_on_order_cancelled');

/**
 * Mostrar coins ganados en el email de pedido completado
 */
function coins_add_to_order_email($order, $sent_to_admin, $plain_text, $email) {
    // Solo mostrar en email de pedido completado al cliente
    if ($sent_to_admin || $email->id !== 'customer_completed_order') {
        return;
    }
    
    $coins_rewarded = get_post_meta($order->get_id(), '_coins_rewarded', true);
    
    if ($coins_rewarded !== 'yes') {
        return;
    }
    
    $coins_amount = get_post_meta($order->get_id(), '_coins_amount', true);
    
    if (!$coins_amount) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    
    if ($plain_text) {
        echo "\n\n";
        echo "========================================\n";
        echo "🪙 HAS GANADO COINS\n";
        echo "========================================\n";
        echo "Has recibido " . $coins_manager->format_coins($coins_amount) . " coins por esta compra.\n";
        echo "Puedes usar tus coins para canjear cursos gratuitos.\n";
    } else {
        ?>
        <div style="margin: 30px 0; padding: 25px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3);">
            <h2 style="color: #da0480; margin: 0 0 15px 0;">🎉 ¡Has ganado coins!</h2>
            <p style="font-size: 15px; margin: 10px 0;">Has recibido <strong style="color: #da0480;"><?php echo esc_html($coins_manager->format_coins($coins_amount)); ?> coins</strong> por esta compra.</p>
            <p style="font-size: 14px; margin: 10px 0; color: #6b7280;">Puedes usar tus coins para canjear cursos gratuitos en nuestra plataforma.</p>
        </div>
        <?php
    }
}
add_action('woocommerce_email_after_order_table', 'coins_add_to_order_email', 20, 4);
?>