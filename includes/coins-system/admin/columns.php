<?php
/**
 * Columnas Personalizadas en Admin
 * Agrega columnas de coins en listados de admin
 * 
 * @package CoinsSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar columna de coins en lista de productos
 */
function coins_add_product_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Añadir columna de coins después del precio
        if ($key === 'price') {
            $new_columns['coins_cost'] = '🪙 Coins';
        }
    }
    
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'coins_add_product_column');

/**
 * Mostrar contenido de la columna de coins
 */
function coins_show_product_column_content($column, $post_id) {
    if ($column === 'coins_cost') {
        $coins_manager = Coins_Manager::get_instance();
        $costo_coins = $coins_manager->get_costo_coins_producto($post_id);
        
        if ($costo_coins > 0) {
            echo '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 20px; font-size: 12px; font-weight: 600;">';
            echo '<span>🪙</span> ' . esc_html($coins_manager->format_coins($costo_coins));
            echo '</span>';
        } else {
            echo '<span style="color: #9ca3af; font-size: 12px;">—</span>';
        }
    }
}
add_action('manage_product_posts_custom_column', 'coins_show_product_column_content', 10, 2);

/**
 * Hacer la columna sorteable
 */
function coins_make_product_column_sortable($columns) {
    $columns['coins_cost'] = 'coins_cost';
    return $columns;
}
add_filter('manage_edit-product_sortable_columns', 'coins_make_product_column_sortable');

/**
 * Query para ordenar por coins
 */
function coins_product_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('orderby') === 'coins_cost') {
        $query->set('meta_key', '_costo_coins');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'coins_product_column_orderby');

/**
 * Agregar columna de coins en lista de usuarios
 */
function coins_add_user_column($columns) {
    $columns['user_coins'] = '🪙 Coins';
    return $columns;
}
add_filter('manage_users_columns', 'coins_add_user_column');

/**
 * Mostrar contenido de columna de coins en usuarios
 */
function coins_show_user_column_content($value, $column_name, $user_id) {
    if ($column_name === 'user_coins') {
        $coins_manager = Coins_Manager::get_instance();
        $user_coins = coins_get_balance($user_id);
        
        if ($user_coins > 0) {
            return '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 20px; font-size: 12px; font-weight: 600;">' . 
                   '<span>🪙</span> ' . esc_html($coins_manager->format_coins($user_coins)) . 
                   '</span>';
        } else {
            return '<span style="color: #9ca3af; font-size: 12px;">0</span>';
        }
    }
    
    return $value;
}
add_filter('manage_users_custom_column', 'coins_show_user_column_content', 10, 3);

/**
 * Hacer columna de usuarios sorteable
 */
function coins_make_user_column_sortable($columns) {
    $columns['user_coins'] = 'user_coins';
    return $columns;
}
add_filter('manage_users_sortable_columns', 'coins_make_user_column_sortable');

/**
 * Query para ordenar usuarios por coins
 */
function coins_user_column_orderby($query) {
    if (!is_admin()) {
        return;
    }
    
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'user_coins') {
        $query->set('meta_key', '_user_coins');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_users', 'coins_user_column_orderby');

/**
 * Agregar columna de coins en pedidos
 */
function coins_add_order_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Añadir después del total
        if ($key === 'order_total') {
            $new_columns['coins_info'] = '🪙 Coins';
        }
    }
    
    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'coins_add_order_column');

/**
 * Mostrar info de coins en pedidos
 */
function coins_show_order_column_content($column, $post_id) {
    if ($column === 'coins_info') {
        $order = wc_get_order($post_id);
        
        if (!$order) {
            echo '<span style="color: #9ca3af;">—</span>';
            return;
        }
        
        // Ver si fue pago con coins
        if ($order->get_payment_method() === 'coins') {
            echo '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; background: #da0480; color: #fff; border-radius: 6px; font-size: 11px; font-weight: 600;">';
            echo '🪙 Canje';
            echo '</span>';
        }
        
        // Ver si se otorgaron coins
        $coins_rewarded = get_post_meta($post_id, '_coins_rewarded', true);
        $coins_amount = get_post_meta($post_id, '_coins_amount', true);
        
        if ($coins_rewarded === 'yes' && $coins_amount) {
            $coins_manager = Coins_Manager::get_instance();
            echo '<br><span style="display: inline-flex; align-items: center; gap: 5px; margin-top: 4px; padding: 4px 8px; background: #10b981; color: #fff; border-radius: 6px; font-size: 11px; font-weight: 600;">';
            echo '+' . esc_html($coins_manager->format_coins($coins_amount));
            echo '</span>';
        }
        
        if ($column === 'coins_info' && !$order->get_payment_method() === 'coins' && $coins_rewarded !== 'yes') {
            echo '<span style="color: #9ca3af; font-size: 12px;">—</span>';
        }
    }
}
add_action('manage_shop_order_posts_custom_column', 'coins_show_order_column_content', 10, 2);

/**
 * Filtro rápido para productos canjeables
 */
function coins_add_product_filter() {
    global $typenow;
    
    if ($typenow === 'product') {
        $current_filter = isset($_GET['coins_filter']) ? $_GET['coins_filter'] : 'all';
        ?>
        <select name="coins_filter">
            <option value="all" <?php selected($current_filter, 'all'); ?>>Todos los productos</option>
            <option value="redeemable" <?php selected($current_filter, 'redeemable'); ?>>Canjeables con coins</option>
            <option value="not_redeemable" <?php selected($current_filter, 'not_redeemable'); ?>>No canjeables</option>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'coins_add_product_filter');

/**
 * Aplicar filtro de productos canjeables
 */
function coins_apply_product_filter($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'product' && isset($_GET['coins_filter'])) {
        $filter = $_GET['coins_filter'];
        
        if ($filter === 'redeemable') {
            $query->set('meta_query', array(
                array(
                    'key' => '_costo_coins',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC'
                )
            ));
        } elseif ($filter === 'not_redeemable') {
            $query->set('meta_query', array(
                'relation' => 'OR',
                array(
                    'key' => '_costo_coins',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_costo_coins',
                    'value' => 0,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                )
            ));
        }
    }
}
add_action('pre_get_posts', 'coins_apply_product_filter');
?>