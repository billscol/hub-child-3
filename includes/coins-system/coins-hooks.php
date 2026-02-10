<?php
/**
 * Hooks y filtros del sistema de coins
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Otorgar coins al completar compra de curso premium
 */
add_action('woocommerce_order_status_completed', 'coins_otorgar_por_compra', 10, 1);

function coins_otorgar_por_compra($order_id) {
    $order   = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    $user_id = $order->get_user_id();
    
    if (!$user_id) {
        return;
    }
    
    if ($order->get_meta('_coins_otorgados')) {
        return;
    }
    
    $total_coins       = 0;
    $productos_premium = array();
    
    foreach ($order->get_items() as $item) {
        $product_id   = $item->get_product_id();
        $product_name = $item->get_name();
        $quantity     = $item->get_quantity();
        
        if (has_term('premium', 'product_cat', $product_id)) {
            /**
             * NUEVO:
             * - Para cursos premium leemos el meta _coins_ganados.
             * - Si está vacío o es 0, usamos 2 coins por defecto.
             * - Si por alguna razón la función helper no existe, hacemos fallback a 2.
             */
            if (function_exists('coins_get_coins_ganados_producto')) {
                $coins_por_producto = coins_get_coins_ganados_producto($product_id);
            } else {
                $valor_meta = get_post_meta($product_id, '_coins_ganados', true);
                $valor_meta = intval($valor_meta);
                $coins_por_producto = $valor_meta > 0 ? $valor_meta : 2;
            }

            $coins_item = $coins_por_producto * $quantity;
            
            $total_coins         += $coins_item;
            $productos_premium[]  = $product_name . ' (x' . $quantity . ') = ' . $coins_item . ' coins';
        }
    }
    
    if ($total_coins > 0) {
        $descripcion = 'Compra de curso premium: ' . implode(', ', $productos_premium);
        
        if (coins_manager()->agregar_coins($user_id, $total_coins, $descripcion, $order_id)) {
            $order->update_meta_data('_coins_otorgados', true);
            $order->save();
            
            $order->add_order_note(sprintf(
                'Se otorgaron %s coins al usuario por esta compra',
                coins_manager()->format_coins($total_coins)
            ));
            
            coins_enviar_notificacion_email($user_id, $total_coins, $order_id);
        }
    }
}

/**
 * Validar que no se mezclen productos premium con gratis en el carrito
 */
add_filter('woocommerce_add_to_cart_validation', 'coins_validar_mezcla_carrito', 10, 3);

function coins_validar_mezcla_carrito($passed, $product_id, $quantity) {
    if (!function_exists('WC') || !WC()->cart) {
        return $passed;
    }
    
    $tiene_premium = false;
    $tiene_gratis  = false;
    
    foreach (WC()->cart->get_cart() as $item) {
        if (has_term('premium', 'product_cat', $item['product_id'])) {
            $tiene_premium = true;
        }
        if (has_term('gratis', 'product_cat', $item['product_id'])) {
            $tiene_gratis = true;
        }
    }
    
    $es_premium = has_term('premium', 'product_cat', $product_id);
    $es_gratis  = has_term('gratis', 'product_cat', $product_id);
    
    if (($tiene_premium && $es_gratis) || ($tiene_gratis && $es_premium)) {
        wc_add_notice(
            'No puedes mezclar cursos premium con cursos gratis. Completa primero la compra actual.',
            'error'
        );
        return false;
    }
    
    if ($es_gratis && !is_user_logged_in()) {
        wc_add_notice(
            'Debes iniciar sesión para canjear cursos con coins.',
            'error'
        );
        return false;
    }
    
    if ($es_gratis && is_user_logged_in()) {
        $user_id            = get_current_user_id();
        $costo_unitario     = coins_manager()->get_costo_coins_producto($product_id);
        $coins_necesarios   = $costo_unitario * $quantity;
        $coins_en_carrito   = 0;
        
        foreach (WC()->cart->get_cart() as $item) {
            if (has_term('gratis', 'product_cat', $item['product_id'])) {
                $costo             = coins_manager()->get_costo_coins_producto($item['product_id']);
                $coins_en_carrito += ($costo * $item['quantity']);
            }
        }
        
        $total_coins_necesarios = $coins_en_carrito + $coins_necesarios;
        $coins_disponibles      = coins_manager()->get_coins($user_id);
        
        if ($coins_disponibles < $total_coins_necesarios) {
            wc_add_notice(
                sprintf(
                    'No tienes suficientes coins. Tienes %s, necesitas %s.',
                    coins_manager()->format_coins($coins_disponibles),
                    coins_manager()->format_coins($total_coins_necesarios)
                ),
                'error'
            );
            return false;
        }
    }
    
    return $passed;
}

/**
 * Ocultar métodos de pago normales cuando hay cursos gratis en el carrito
 */
add_filter('woocommerce_available_payment_gateways', 'coins_filtrar_metodos_pago', 100);

function coins_filtrar_metodos_pago($gateways) {
    if (is_admin() || !function_exists('WC') || !WC()->cart) {
        return $gateways;
    }
    
    if (WC()->cart->is_empty()) {
        return $gateways;
    }
    
    $solo_gratis   = true;
    $tiene_premium = false;
    
    foreach (WC()->cart->get_cart() as $item) {
        $product_id = $item['product_id'];
        
        if (has_term('gratis', 'product_cat', $product_id)) {
            continue;
        } else {
            $tiene_premium = true;
            $solo_gratis   = false;
            break;
        }
    }
    
    if ($solo_gratis && !empty(WC()->cart->get_cart())) {
        foreach ($gateways as $key => $gateway) {
            if ($key !== 'coins') {
                unset($gateways[$key]);
            }
        }
    }
    
    if ($tiene_premium && isset($gateways['coins'])) {
        unset($gateways['coins']);
    }
    
    return $gateways;
}

/**
 * Mostrar coins disponibles en el dashboard de la cuenta
 */
add_action('woocommerce_account_dashboard', 'coins_mostrar_en_dashboard');

function coins_mostrar_en_dashboard() {
    $user_id = get_current_user_id();
    if (!$user_id || !function_exists('coins_manager')) {
        return;
    }
    $coins = coins_manager()->get_coins($user_id);
    ?>
    <div class="woocommerce-coins-dashboard">
        <div class="coins-dash-icon">
            <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins">
        </div>
        <h3>Tus Coins</h3>
        <div class="coins-balance">
            <span class="coins-numero"><?php echo esc_html(coins_manager()->format_coins($coins)); ?></span>
            <span class="coins-label">coins disponibles</span>
        </div>
        <p class="coins-descripcion">
            Gana coins comprando cursos premium y dejando reseñas. Úsalos para canjear cursos gratis.
        </p>
        <div class="coins-actions">
            <?php if ($coins > 0): ?>
                <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>?filter_gratis=1" class="coins-btn primary">
                    Canjear coins
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('historial-coins')); ?>" class="coins-btn secondary">
                Ver historial
            </a>
        </div>
    </div>
    <?php
}

/**
 * Agregar endpoint personalizado para historial de coins
 */
add_action('init', 'coins_agregar_endpoint');

function coins_agregar_endpoint() {
    add_rewrite_endpoint('historial-coins', EP_ROOT | EP_PAGES);
}

/**
 * Agregar item al menú de Mi Cuenta
 */
add_filter('woocommerce_account_menu_items', 'coins_menu_item');

function coins_menu_item($items) {
    $new_items = array();
    
    foreach ($items as $key => $value) {
        $new_items[$key] = $value;
        
        if ($key === 'orders') {
            $new_items['historial-coins'] = 'Mis Coins';
        }
    }
    
    return $new_items;
}

/**
 * Contenido del endpoint de historial
 */
add_action('woocommerce_account_historial-coins_endpoint', 'coins_endpoint_content');

function coins_endpoint_content() {
    $user_id        = get_current_user_id();
    if (!$user_id || !function_exists('coins_manager')) {
        return;
    }
    $historial      = coins_manager()->get_historial($user_id, 50);
    $coins_actuales = coins_manager()->get_coins($user_id);
    ?>
    <div class="woocommerce-coins-historial">
        <div class="coins-hist-header">
            <div class="coins-hist-icon">
                <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins">
            </div>
            <div>
                <h2>Historial de Coins</h2>
                <p>Balance actual: <strong><?php echo esc_html(coins_manager()->format_coins($coins_actuales)); ?> coins</strong></p>
            </div>
        </div>
        
        <?php if (!empty($historial)): ?>
            <div class="coins-table-wrapper">
                <table class="coins-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $registro): ?>
                            <tr>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($registro->fecha))); ?></td>
                                <td>
                                    <?php if ($registro->tipo === 'suma'): ?>
                                        <span class="coins-tag tag-plus">Ganado</span>
                                    <?php else: ?>
                                        <span class="coins-tag tag-minus">Canjeado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($registro->descripcion); ?></td>
                                <td>
                                    <?php if ($registro->tipo === 'suma'): ?>
                                        <span class="coins-qty qty-plus">+<?php echo esc_html(coins_manager()->format_coins($registro->cantidad)); ?></span>
                                    <?php else: ?>
                                        <span class="coins-qty qty-minus">-<?php echo esc_html(coins_manager()->format_coins($registro->cantidad)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(coins_manager()->format_coins($registro->saldo_nuevo)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="coins-empty-state">
                <div class="coins-empty-icon">
                    <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins">
                </div>
                <h3>Aún no tienes movimientos</h3>
                <p>Compra un curso premium o deja una reseña para ganar tus primeros coins.</p>
                <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="coins-btn primary">
                    Ver cursos premium
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Mostrar badge de coins en productos gratis
 */
add_action('woocommerce_before_shop_loop_item_title', 'coins_badge_producto_gratis', 10);

function coins_badge_producto_gratis() {
    global $product;
    
    if (!$product || !function_exists('coins_manager')) {
        return;
    }
    
    if (has_term('gratis', 'product_cat', $product->get_id())) {
        $costo = coins_manager()->get_costo_coins_producto($product->get_id());
        echo '<span class="coins-badge coins-badge-gratis"><img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coin">' . esc_html(coins_manager()->format_coins($costo)) . ' ' . ($costo == 1 ? 'Coin' : 'Coins') . '</span>';
    }
}

/**
 * Mostrar badge de coins otorgados en productos premium
 */
add_action('woocommerce_before_shop_loop_item_title', 'coins_badge_producto_premium', 10);

function coins_badge_producto_premium() {
    global $product;
    
    if (!$product || !function_exists('coins_manager')) {
        return;
    }
    
    if (has_term('premium', 'product_cat', $product->get_id())) {
        $coins = coins_manager()->get_coins_producto($product->get_id());
        if ($coins > 0) {
            echo '<span class="coins-badge coins-badge-premium"><span class="premium-label">Ganas</span><strong>+' . esc_html(coins_manager()->format_coins($coins)) . '</strong> ' . ($coins == 1 ? 'Coin' : 'Coins') . '</span>';
        }
    }
}

/**
 * CSS para dashboard, historial y badges (estilo modal moderno)
 */
add_action('wp_head', 'coins_custom_css');

function coins_custom_css() {
    ?>
    <style>
        /* Dashboard Coins */
        .woocommerce-coins-dashboard {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            border: 1.5px solid rgba(218, 4, 128, .25);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 40px rgba(218,4,128,.1);
            text-align: center;
        }
        .woocommerce-coins-dashboard .coins-dash-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, rgba(218,4,128,.2), rgba(218,4,128,.1));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(218,4,128,.3);
        }
        .woocommerce-coins-dashboard .coins-dash-icon img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }
        .woocommerce-coins-dashboard h3 {
            margin: 0 0 12px;
            font-size: 24px;
            color: #fff;
            font-weight: 800;
        }
        .woocommerce-coins-dashboard .coins-balance {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 10px;
            margin: 16px 0;
        }
        .woocommerce-coins-dashboard .coins-numero {
            font-size: 48px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }
        .woocommerce-coins-dashboard .coins-label {
            font-size: 16px;
            color: #9ca3af;
        }
        .woocommerce-coins-dashboard .coins-descripcion {
            margin: 0 0 20px;
            color: #9ca3af;
            font-size: 14px;
        }
        .woocommerce-coins-dashboard .coins-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .coins-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all .3s;
            font-family: "Space Grotesk", sans-serif;
        }
        .coins-btn.primary {
            background: linear-gradient(135deg, #da0480, #b00368);
            color: #fff;
            box-shadow: 0 8px 24px rgba(218,4,128,.3);
        }
        .coins-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(218,4,128,.4);
        }
        .coins-btn.secondary {
            background: rgba(0,0,0,.3);
            border: 1.5px solid rgba(255,255,255,.08);
            color: #cbd5e1;
        }
        .coins-btn.secondary:hover {
            background: rgba(0,0,0,.4);
            border-color: #da0480;
            color: #da0480;
        }

        /* Historial Coins */
        .woocommerce-coins-historial {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            border: 1.5px solid rgba(218, 4, 128, .25);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 40px rgba(218,4,128,.1);
        }
        .woocommerce-coins-historial .coins-hist-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .woocommerce-coins-historial .coins-hist-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(218,4,128,.2), rgba(218,4,128,.1));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(218,4,128,.3);
            flex-shrink: 0;
        }
        .woocommerce-coins-historial .coins-hist-icon img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }
        .woocommerce-coins-historial h2 {
            margin: 0 0 4px;
            font-size: 24px;
            color: #fff;
            font-weight: 800;
        }
        .woocommerce-coins-historial p {
            margin: 0;
            color: #9ca3af;
            font-size: 14px;
        }
        .woocommerce-coins-historial strong {
            color: #fff;
        }
        .coins-table-wrapper {
            overflow-x: auto;
            background: rgba(0,0,0,.3);
            border-radius: 14px;
            border: 1.5px solid rgba(255,255,255,.08);
        }
        .coins-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .coins-table th,
        .coins-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        .coins-table th {
            font-weight: 700;
            font-size: 13px;
            color: #9ca3af;
            background: rgba(0,0,0,.2);
        }
        .coins-table td {
            color: #e5e7eb;
        }
        .coins-table tbody tr:hover {
            background: rgba(218,4,128,.05);
        }
        .coins-tag {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .tag-plus {
            background: rgba(34,197,94,.15);
            color: #4ade80;
        }
        .tag-minus {
            background: rgba(248,113,113,.15);
            color: #f87171;
        }
        .coins-qty {
            font-weight: 700;
        }
        .qty-plus {
            color: #4ade80;
        }
        .qty-minus {
            color: #f87171;
        }

        /* Empty State */
        .coins-empty-state {
            text-align: center;
            padding: 48px 24px;
            background: rgba(0,0,0,.3);
            border-radius: 14px;
            border: 1.5px solid rgba(255,255,255,.08);
        }
        .coins-empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, rgba(218,4,128,.2), rgba(218,4,128,.1));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(218,4,128,.3);
        }
        .coins-empty-icon img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .coins-empty-state h3 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #fff;
            font-weight: 800;
        }
        .coins-empty-state p {
            margin: 0 0 24px;
            color: #9ca3af;
            font-size: 14px;
        }

        /* Badges en productos */
        .coins-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
        }
        .coins-badge img {
            width: 18px;
            height: 18px;
            object-fit: contain;
        }
        .coins-badge-gratis {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            color: #da0480;
            border: 1.5px solid rgba(218,4,128,.4);
        }
        .coins-badge-premium {
            background: linear-gradient(135deg, #da0480, #b00368);
            color: #fff;
            border: 1.5px solid rgba(255,255,255,.2);
        }
        .coins-badge-premium .premium-label {
            font-size: 10px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .woocommerce-coins-dashboard,
            .woocommerce-coins-historial {
                padding: 20px;
            }
            .woocommerce-coins-dashboard .coins-numero {
                font-size: 36px;
            }
            .woocommerce-coins-historial .coins-hist-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .coins-table th,
            .coins-table td {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
    <?php
}
