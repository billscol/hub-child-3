<?php
/**
 * Recompensas por Compras
 * Otorga coins cuando un usuario compra un curso premium
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins al completar una compra
 */
function otorgar_coins_por_compra($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    // Verificar que no se hayan otorgado coins ya
    if ($order->get_meta('_coins_otorgados')) {
        return;
    }
    
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    // Calcular coins a otorgar
    $coins_a_otorgar = 0;
    
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        
        if (!$product) {
            continue;
        }
        
        // Solo otorgar coins por productos premium (no gratuitos)
        if ($product->get_price() > 0) {
            // Otorgar 1 coin por cada compra de curso premium
            $coins_por_producto = apply_filters('coins_por_compra_producto', 1, $product, $order);
            $coins_a_otorgar += $coins_por_producto;
        }
    }
    
    if ($coins_a_otorgar > 0) {
        coins_manager()->add_coins(
            $user_id,
            $coins_a_otorgar,
            'Recompensa por compra - Pedido #' . $order_id,
            $order_id
        );
        
        // Marcar que ya se otorgaron coins
        $order->update_meta_data('_coins_otorgados', $coins_a_otorgar);
        $order->save();
        
        // Agregar nota al pedido
        $order->add_order_note(
            sprintf(
                '🪙 Se otorgaron %d coins al cliente por esta compra.',
                $coins_a_otorgar
            )
        );
    }
}

// Otorgar coins cuando el pedido se completa
add_action('woocommerce_order_status_completed', 'otorgar_coins_por_compra');

// También otorgar cuando se procesa (para productos digitales)
add_action('woocommerce_order_status_processing', 'otorgar_coins_por_compra');

/**
 * Mostrar coins ganados en la página de agradecimiento
 */
function mostrar_coins_ganados_thank_you($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    $coins_otorgados = $order->get_meta('_coins_otorgados');
    
    if ($coins_otorgados && $coins_otorgados > 0) {
        ?>
        <div class="coins-earned-notice" style="background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05)); border: 2px solid rgba(218, 4, 128, 0.3); border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">🎉</div>
            <h3 style="color: #da0480; margin: 0 0 10px 0;">¡Felicidades!</h3>
            <p style="margin: 0; font-size: 16px;">
                Has ganado <strong style="color: #da0480; font-size: 24px;"><?php echo $coins_otorgados; ?> coins</strong> con esta compra.
            </p>
            <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">
                Usa tus coins para canjear más cursos gratis.
            </p>
        </div>
        <?php
    }
}
add_action('woocommerce_thankyou', 'mostrar_coins_ganados_thank_you', 20);

/**
 * Mostrar coins potenciales en la página del producto
 */
function mostrar_coins_potenciales_producto() {
    global $product;
    
    if (!$product || $product->get_price() <= 0) {
        return;
    }
    
    $coins_potenciales = apply_filters('coins_por_compra_producto', 1, $product, null);
    
    ?>
    <div class="product-coins-badge" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(218, 4, 128, 0.1); color: #da0480; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 10px 0;">
        <span>🪙</span>
        <span>Gana <?php echo $coins_potenciales; ?> coin<?php echo $coins_potenciales > 1 ? 's' : ''; ?> con esta compra</span>
    </div>
    <?php
}
add_action('woocommerce_single_product_summary', 'mostrar_coins_potenciales_producto', 25);
?>