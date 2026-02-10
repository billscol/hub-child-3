<?php
/**
 * Hooks de Integración con WooCommerce
 * Conecta el sistema de coins con WooCommerce
 * 
 * @package CoinsSystem
 * @subpackage Integration
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modificar precio de productos canjeables con coins en el carrito
 */
function coins_modify_cart_item_price($cart_item_data, $cart_item_key) {
    // Si el usuario seleccionó pagar con coins
    if (isset($_POST['payment_method']) && $_POST['payment_method'] === 'coins') {
        $product_id = $cart_item_data['product_id'];
        $coins_manager = Coins_Manager::get_instance();
        
        if ($coins_manager->is_coin_redeemable($product_id)) {
            // Establecer precio en 0 si se paga con coins
            $cart_item_data['data']->set_price(0);
        }
    }
    
    return $cart_item_data;
}
add_filter('woocommerce_get_cart_item_from_session', 'coins_modify_cart_item_price', 10, 2);

/**
 * Validar que productos gratuitos requieran coins
 */
function coins_validate_free_product_checkout($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return $passed;
    }
    
    // Si el producto es gratis Y requiere coins
    if ($product->get_price() == 0) {
        $coins_manager = Coins_Manager::get_instance();
        $costo_coins = $coins_manager->get_costo_coins_producto($product_id);
        
        if ($costo_coins > 0 && !is_user_logged_in()) {
            wc_add_notice('Debes iniciar sesión para canjear este producto con coins.', 'error');
            return false;
        }
    }
    
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'coins_validate_free_product_checkout', 10, 3);

/**
 * Mostrar aviso en checkout si hay productos canjeables
 */
function coins_checkout_notice() {
    if (!WC()->cart) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $has_coin_products = false;
    $total_coins_needed = 0;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        
        $costo_coins = $coins_manager->get_costo_coins_producto($product_id);
        if ($costo_coins > 0) {
            $has_coin_products = true;
            $total_coins_needed += $costo_coins * $quantity;
        }
    }
    
    if (!$has_coin_products) {
        return;
    }
    
    $user_coins = is_user_logged_in() ? coins_get_balance(get_current_user_id()) : 0;
    $has_enough = $user_coins >= $total_coins_needed;
    
    ?>
    <div class="woocommerce-info" style="background: rgba(218, 4, 128, 0.1); border-left-color: #da0480;">
        <strong>🪙 Puedes canjear este producto con coins</strong><br>
        Necesitas: <strong><?php echo esc_html($coins_manager->format_coins($total_coins_needed)); ?> coins</strong>
        <?php if (is_user_logged_in()) : ?>
            | Tienes: <strong><?php echo esc_html($coins_manager->format_coins($user_coins)); ?> coins</strong>
            <?php if (!$has_enough) : ?>
                <br><span style="color: #ef4444;">Te faltan <?php echo esc_html($coins_manager->format_coins($total_coins_needed - $user_coins)); ?> coins</span>
            <?php endif; ?>
        <?php else : ?>
            <br><a href="<?php echo wp_login_url(wc_get_checkout_url()); ?>" style="color: #da0480; text-decoration: underline;">Inicia sesión</a> para usar tus coins.
        <?php endif; ?>
    </div>
    <?php
}
add_action('woocommerce_before_checkout_form', 'coins_checkout_notice', 10);

/**
 * Añadir meta de coins a items del pedido
 */
function coins_add_order_item_meta($item_id, $values, $cart_item_key) {
    $product_id = $values['product_id'];
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($product_id);
    
    if ($costo_coins > 0) {
        wc_add_order_item_meta($item_id, '_costo_coins', $costo_coins);
    }
}
add_action('woocommerce_add_order_item_meta', 'coins_add_order_item_meta', 10, 3);

/**
 * Mostrar costo en coins en el pedido
 */
function coins_display_order_item_meta($item_id, $item, $order, $plain_text) {
    $costo_coins = wc_get_order_item_meta($item_id, '_costo_coins', true);
    
    if ($costo_coins) {
        $coins_manager = Coins_Manager::get_instance();
        
        if ($plain_text) {
            echo "\nCosto en Coins: " . $coins_manager->format_coins($costo_coins) . " coins";
        } else {
            echo '<br><small style="color: #da0480;">🪙 Costo: ' . esc_html($coins_manager->format_coins($costo_coins)) . ' coins</small>';
        }
    }
}
add_action('woocommerce_order_item_meta_end', 'coins_display_order_item_meta', 10, 4);

/**
 * Prevenir que productos con coins se agreguen con otros métodos de pago
 */
function coins_validate_cart_payment_method() {
    if (!WC()->cart || is_admin()) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $has_coin_only = false;
    $has_regular = false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);
        
        if (!$product) {
            continue;
        }
        
        $costo_coins = $coins_manager->get_costo_coins_producto($product_id);
        
        // Producto solo canjeable con coins (precio 0 y tiene costo coins)
        if ($product->get_price() == 0 && $costo_coins > 0) {
            $has_coin_only = true;
        } else {
            $has_regular = true;
        }
    }
    
    // Si hay productos de ambos tipos, mostrar aviso
    if ($has_coin_only && $has_regular) {
        wc_add_notice(
            'No puedes mezclar productos canjeables con coins y productos de pago regular. Por favor, realiza compras separadas.',
            'error'
        );
        
        // Redireccionar al carrito
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
add_action('woocommerce_checkout_process', 'coins_validate_cart_payment_method');

/**
 * Agregar clase CSS al body si hay productos canjeables
 */
function coins_add_body_class($classes) {
    if (is_product()) {
        global $product;
        if ($product) {
            $coins_manager = Coins_Manager::get_instance();
            if ($coins_manager->is_coin_redeemable($product->get_id())) {
                $classes[] = 'coin-redeemable-product';
            }
        }
    }
    
    if (is_user_logged_in()) {
        $user_coins = coins_get_balance(get_current_user_id());
        if ($user_coins > 0) {
            $classes[] = 'user-has-coins';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'coins_add_body_class');

/**
 * Agregar tab de coins en página de producto
 */
function coins_add_product_tab($tabs) {
    global $product;
    
    if (!$product) {
        return $tabs;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    
    if ($coins_manager->is_coin_redeemable($product->get_id())) {
        $tabs['coins_info'] = array(
            'title' => '🪙 Canje con Coins',
            'priority' => 50,
            'callback' => 'coins_product_tab_content'
        );
    }
    
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'coins_add_product_tab');

/**
 * Contenido del tab de coins
 */
function coins_product_tab_content() {
    global $product;
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($product->get_id());
    
    ?>
    <div class="coins-product-tab-content">
        <h3 style="color: #da0480;">🪙 Canjea este curso con Coins</h3>
        
        <p>Este curso puede ser tuyo por solo <strong style="color: #da0480; font-size: 18px;"><?php echo esc_html($coins_manager->format_coins($costo_coins)); ?> coins</strong>.</p>
        
        <div style="padding: 20px; background: rgba(218, 4, 128, 0.08); border-radius: 12px; border-left: 4px solid #da0480; margin: 20px 0;">
            <h4 style="margin: 0 0 12px 0; color: #1f2937;">💡 ¿Cómo funciona?</h4>
            <ol style="margin: 0; padding-left: 20px; color: #374151;">
                <li style="margin: 8px 0;">Gana coins comprando cursos premium, dejando reseñas o compartiendo en redes.</li>
                <li style="margin: 8px 0;">Acumula suficientes coins para canjear este curso.</li>
                <li style="margin: 8px 0;">Selecciona "Pagar con Coins" en el checkout.</li>
                <li style="margin: 8px 0;">¡Disfruta tu curso sin gastar dinero!</li>
            </ol>
        </div>
        
        <?php if (is_user_logged_in()) : 
            $user_coins = coins_get_balance(get_current_user_id());
            $tiene_coins = $user_coins >= $costo_coins;
        ?>
            <div style="padding: 18px; background: rgba(<?php echo $tiene_coins ? '16, 185, 129' : '239, 68, 68'; ?>, 0.1); border-radius: 10px; border: 1px solid rgba(<?php echo $tiene_coins ? '16, 185, 129' : '239, 68, 68'; ?>, 0.3);">
                <?php if ($tiene_coins) : ?>
                    <p style="margin: 0; color: #10b981; font-weight: 600;">✅ ¡Tienes suficientes coins para canjear este curso!</p>
                    <p style="margin: 10px 0 0 0; color: #374151;">Tu saldo: <strong><?php echo esc_html($coins_manager->format_coins($user_coins)); ?> coins</strong></p>
                <?php else : ?>
                    <p style="margin: 0; color: #ef4444; font-weight: 600;">⚠️ Te faltan coins</p>
                    <p style="margin: 10px 0 0 0; color: #374151;">
                        Tienes: <?php echo esc_html($coins_manager->format_coins($user_coins)); ?> coins | 
                        Necesitas: <?php echo esc_html($coins_manager->format_coins($costo_coins - $user_coins)); ?> más
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Ocultar precio normal si solo es canjeable con coins
 */
function coins_maybe_hide_price($price, $product) {
    if ($product->get_price() == 0) {
        $coins_manager = Coins_Manager::get_instance();
        $costo_coins = $coins_manager->get_costo_coins_producto($product->get_id());
        
        if ($costo_coins > 0) {
            return '<span style="color: #da0480; font-weight: 700;">🪙 Canjeable con ' . $coins_manager->format_coins($costo_coins) . ' coins</span>';
        }
    }
    
    return $price;
}
add_filter('woocommerce_get_price_html', 'coins_maybe_hide_price', 10, 2);
?>