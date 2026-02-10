<?php
/**
 * Recompensas por Compras
 * Otorga coins cuando un usuario completa una compra
 * 
 * @package CoinsSystem
 * @subpackage Rewards
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins cuando se completa un pedido
 */
function coins_reward_on_order_completed($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    // Solo si el usuario está registrado
    if (!$user_id) {
        return;
    }
    
    // Verificar si ya se otorgaron coins por este pedido
    $coins_otorgados = get_post_meta($order_id, '_coins_otorgados', true);
    if ($coins_otorgados) {
        return; // Ya se otorgaron coins
    }
    
    // Verificar que el pedido no haya sido pagado con coins
    $payment_method = $order->get_payment_method();
    if ($payment_method === 'coins') {
        return; // No dar coins por canjes con coins
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $total_coins = 0;
    
    // Contar productos premium (no gratuitos)
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $quantity = $item->get_quantity();
        
        // Solo dar coins por productos de pago
        if ($product && $product->get_price() > 0) {
            // 1 coin por cada producto premium
            $coins_por_producto = 1;
            $total_coins += $coins_por_producto * $quantity;
        }
    }
    
    // Si hay coins para otorgar
    if ($total_coins > 0) {
        // Otorgar coins
        $coins_manager->add_coins(
            $user_id,
            $total_coins,
            'compra',
            'Recompensa por compra - Pedido #' . $order_id,
            $order_id
        );
        
        // Marcar que ya se otorgaron coins
        update_post_meta($order_id, '_coins_otorgados', $total_coins);
        
        // Agregar nota al pedido
        $order->add_order_note(
            sprintf(
                'Se otorgaron %s coins al usuario por esta compra.',
                $coins_manager->format_coins($total_coins)
            )
        );
        
        // Enviar notificación al usuario
        coins_send_reward_notification($user_id, $total_coins, 'compra', $order_id);
    }
}
add_action('woocommerce_order_status_completed', 'coins_reward_on_order_completed');

/**
 * Enviar notificación de coins recibidos
 */
function coins_send_reward_notification($user_id, $coins, $tipo, $order_id = null) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $saldo_actual = $coins_manager->get_coins($user_id);
    
    $subject = '🪙 ¡Has ganado ' . $coins_manager->format_coins($coins) . ' coins!';
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #da0480, #b00368); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .coin-box { background: #fff; border: 2px solid #da0480; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
            .coin-amount { font-size: 36px; font-weight: bold; color: #da0480; }
            .balance { color: #666; font-size: 14px; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin: 0;">🎉 ¡Felicitaciones!</h1>
            </div>
            <div class="content">
                <p>Hola <strong>' . esc_html($user->display_name) . '</strong>,</p>
                
                <p>¡Excelente noticia! Has ganado coins por tu reciente compra.</p>
                
                <div class="coin-box">
                    <div class="coin-amount">+' . $coins_manager->format_coins($coins) . ' 🪙</div>
                    <div class="balance">Tu saldo actual: ' . $coins_manager->format_coins($saldo_actual) . ' coins</div>
                </div>
                
                <p>Puedes usar tus coins para canjear cursos gratuitos en nuestra plataforma.</p>
                
                <p><strong>¿Cómo ganar más coins?</strong></p>
                <ul>
                    <li>🛒 1 coin por cada curso premium que compras</li>
                    <li>⭐ 1 coin por cada reseña verificada</li>
                </ul>
                
                <p style="margin-top: 30px; color: #666; font-size: 13px;">
                    Saludos,<br>
                    El equipo de ' . get_bloginfo('name') . '
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    wp_mail($user->user_email, $subject, $message, $headers);
}

/**
 * Revertir coins si se cancela un pedido
 */
function coins_revert_on_order_cancelled($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    // Verificar si se otorgaron coins
    $coins_otorgados = get_post_meta($order_id, '_coins_otorgados', true);
    
    if ($coins_otorgados && $coins_otorgados > 0) {
        $coins_manager = Coins_Manager::get_instance();
        
        // Restar los coins que se habían otorgado
        $coins_manager->subtract_coins(
            $user_id,
            $coins_otorgados,
            'reversion',
            'Reversión por cancelación del pedido #' . $order_id,
            $order_id
        );
        
        // Marcar como revertido
        update_post_meta($order_id, '_coins_revertidos', true);
        
        $order->add_order_note(
            sprintf(
                'Se revirtieron %s coins del usuario por cancelación.',
                $coins_manager->format_coins($coins_otorgados)
            )
        );
    }
}
add_action('woocommerce_order_status_cancelled', 'coins_revert_on_order_cancelled');
add_action('woocommerce_order_status_refunded', 'coins_revert_on_order_cancelled');
add_action('woocommerce_order_status_failed', 'coins_revert_on_order_cancelled');
?>