<?php
/**
 * Columnas Personalizadas en Admin
 * Agrega columnas de coins en listados de productos y usuarios
 * 
 * @package CoinsSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==================================================
 * COLUMNAS EN PRODUCTOS
 * ==================================================
 */

/**
 * Agregar columna de coins en listado de productos
 */
function coins_add_product_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Insertar después de la columna de precio
        if ($key === 'price') {
            $new_columns['coins_cost'] = '🪙 Coins';
        }
    }
    
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'coins_add_product_column');

/**
 * Mostrar contenido de columna de coins
 */
function coins_show_product_column_content($column, $post_id) {
    if ($column === 'coins_cost') {
        $coins_manager = Coins_Manager::get_instance();
        $costo = $coins_manager->get_costo_coins_producto($post_id);
        
        if ($costo > 0) {
            echo '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: rgba(218, 4, 128, 0.1); border-radius: 20px; color: #da0480; font-weight: 600; font-size: 13px;">';
            echo $coins_manager->format_coins($costo);
            echo ' 🪙';
            echo '</span>';
        } else {
            echo '<span style="color: #999; font-size: 13px;">—</span>';
        }
    }
}
add_action('manage_product_posts_custom_column', 'coins_show_product_column_content', 10, 2);

/**
 * Hacer columna ordenable
 */
function coins_make_product_column_sortable($columns) {
    $columns['coins_cost'] = 'coins_cost';
    return $columns;
}
add_filter('manage_edit-product_sortable_columns', 'coins_make_product_column_sortable');

/**
 * Ordenar por columna de coins
 */
function coins_product_column_orderby($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ($orderby === 'coins_cost') {
        $query->set('meta_key', '_costo_coins');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'coins_product_column_orderby');

/**
 * ==================================================
 * COLUMNAS EN USUARIOS
 * ==================================================
 */

/**
 * Agregar columna de coins en listado de usuarios
 */
function coins_add_user_column($columns) {
    $columns['user_coins'] = '🪙 Coins';
    return $columns;
}
add_filter('manage_users_columns', 'coins_add_user_column');

/**
 * Mostrar contenido de columna de coins de usuario
 */
function coins_show_user_column_content($value, $column_name, $user_id) {
    if ($column_name === 'user_coins') {
        $coins_manager = Coins_Manager::get_instance();
        $saldo = $coins_manager->get_coins($user_id);
        
        return '<span style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05)); border-radius: 20px; color: #da0480; font-weight: 700; font-size: 14px; border: 1px solid rgba(218, 4, 128, 0.2);">' . 
               $coins_manager->format_coins($saldo) . 
               ' 🪙</span>';
    }
    
    return $value;
}
add_filter('manage_users_custom_column', 'coins_show_user_column_content', 10, 3);

/**
 * Hacer columna de usuarios ordenable
 */
function coins_make_user_column_sortable($columns) {
    $columns['user_coins'] = 'user_coins';
    return $columns;
}
add_filter('manage_users_sortable_columns', 'coins_make_user_column_sortable');

/**
 * Ordenar por columna de coins de usuario
 */
function coins_user_column_orderby($query) {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    
    if ($screen && $screen->id === 'users') {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';
        
        if ($orderby === 'user_coins') {
            $query->set('meta_key', '_user_coins');
            $query->set('orderby', 'meta_value_num');
        }
    }
}
add_action('pre_get_users', 'coins_user_column_orderby');

/**
 * ==================================================
 * FILTROS EN ADMIN
 * ==================================================
 */

/**
 * Agregar filtro de productos por coins
 */
function coins_add_product_filter() {
    global $typenow;
    
    if ($typenow === 'product') {
        $selected = isset($_GET['coins_filter']) ? $_GET['coins_filter'] : '';
        
        echo '<select name="coins_filter" style="margin-left: 10px;">';
        echo '<option value="">Todos los productos</option>';
        echo '<option value="with_coins"' . selected($selected, 'with_coins', false) . '>Con costo en coins</option>';
        echo '<option value="without_coins"' . selected($selected, 'without_coins', false) . '>Sin costo en coins</option>';
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'coins_add_product_filter');

/**
 * Aplicar filtro de productos por coins
 */
function coins_apply_product_filter($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if (isset($_GET['coins_filter'])) {
        $filter = $_GET['coins_filter'];
        
        if ($filter === 'with_coins') {
            $meta_query = array(
                array(
                    'key' => '_costo_coins',
                    'value' => '0',
                    'compare' => '>',
                    'type' => 'NUMERIC'
                )
            );
            $query->set('meta_query', $meta_query);
        } elseif ($filter === 'without_coins') {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => '_costo_coins',
                    'value' => '0',
                    'compare' => '=',
                    'type' => 'NUMERIC'
                ),
                array(
                    'key' => '_costo_coins',
                    'compare' => 'NOT EXISTS'
                )
            );
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'coins_apply_product_filter');

/**
 * ==================================================
 * BULK ACTIONS
 * ==================================================
 */

/**
 * Agregar acción masiva para establecer coins
 */
function coins_add_bulk_action($actions) {
    $actions['set_coins'] = 'Establecer costo en coins';
    return $actions;
}
add_filter('bulk_actions-edit-product', 'coins_add_bulk_action');

/**
 * Manejar acción masiva
 */
function coins_handle_bulk_action($redirect_to, $action, $post_ids) {
    if ($action !== 'set_coins') {
        return $redirect_to;
    }
    
    // Aquí se podría agregar un formulario modal para establecer el costo
    // Por ahora solo redireccionamos con un mensaje
    
    $redirect_to = add_query_arg('coins_bulk', count($post_ids), $redirect_to);
    return $redirect_to;
}
add_filter('handle_bulk_actions-edit-product', 'coins_handle_bulk_action', 10, 3);

/**
 * Mostrar mensaje después de acción masiva
 */
function coins_bulk_action_notice() {
    if (!empty($_GET['coins_bulk'])) {
        $count = intval($_GET['coins_bulk']);
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . sprintf('Se procesaron %d productos.', $count) . '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'coins_bulk_action_notice');
?>