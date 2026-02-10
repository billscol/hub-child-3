<?php
/**
 * Funciones administrativas del sistema de coins
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar columna de coins en listado de usuarios
 */
add_filter('manage_users_columns', 'coins_agregar_columna_usuarios');

function coins_agregar_columna_usuarios($columns) {
    $columns['coins'] = 'Coins';
    return $columns;
}

/**
 * Mostrar coins en la columna
 */
add_filter('manage_users_custom_column', 'coins_mostrar_columna_usuarios', 10, 3);

function coins_mostrar_columna_usuarios($value, $column_name, $user_id) {
    if ($column_name === 'coins') {
        if (!function_exists('coins_manager')) {
            return $value;
        }
        $coins = coins_manager()->get_coins($user_id);
        return '<strong>' . coins_manager()->format_coins($coins) . '</strong>';
    }
    return $value;
}

/**
 * Agregar sección en el perfil de usuario para gestionar coins
 */
add_action('show_user_profile', 'coins_mostrar_en_perfil');
add_action('edit_user_profile', 'coins_mostrar_en_perfil');

function coins_mostrar_en_perfil($user) {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    if (!function_exists('coins_manager')) {
        return;
    }
    
    $coins     = coins_manager()->get_coins($user->ID);
    $historial = coins_manager()->get_historial($user->ID, 10);
    ?>
    
    <h2>Sistema de Coins</h2>
    
    <table class="form-table">
        <tr>
            <th><label>Balance de Coins</label></th>
            <td>
                <strong style="font-size: 24px; color: #2c3e50;">
                    <?php echo esc_html(coins_manager()->format_coins($coins)); ?>
                </strong> coins
            </td>
        </tr>
        
        <tr>
            <th><label for="coins_ajuste">Ajustar Coins</label></th>
            <td>
                <input type="number" name="coins_ajuste" id="coins_ajuste" 
                       class="regular-text" value="0" step="0.1" />
                <p class="description">
                    Ingresa un número positivo para agregar o negativo para descontar coins.
                </p>
            </td>
        </tr>
        
        <tr>
            <th><label for="coins_nota">Nota del Ajuste</label></th>
            <td>
                <input type="text" name="coins_nota" id="coins_nota" 
                       class="regular-text" placeholder="Ej: Ajuste manual por soporte" />
                <p class="description">
                    Descripción del motivo del ajuste (opcional).
                </p>
            </td>
        </tr>
    </table>
    
    <?php if (!empty($historial)): ?>
        <h3>Últimos 10 movimientos de coins</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Saldo</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $registro): ?>
                    <tr>
                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($registro->fecha))); ?></td>
                        <td><?php echo $registro->tipo === 'suma' ? '✓ Suma' : '− Resta'; ?></td>
                        <td>
                            <strong>
                                <?php
                                $signo = $registro->tipo === 'suma' ? '+' : '-';
                                echo esc_html($signo . coins_manager()->format_coins($registro->cantidad));
                                ?>
                            </strong>
                        </td>
                        <td><?php echo esc_html(coins_manager()->format_coins($registro->saldo_nuevo)); ?></td>
                        <td><?php echo esc_html($registro->descripcion); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php
}

/**
 * Guardar ajuste de coins desde el perfil
 */
add_action('personal_options_update', 'coins_guardar_ajuste_perfil');
add_action('edit_user_profile_update', 'coins_guardar_ajuste_perfil');

function coins_guardar_ajuste_perfil($user_id) {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    if (!function_exists('coins_manager')) {
        return;
    }
    
    if (isset($_POST['coins_ajuste']) && $_POST['coins_ajuste'] != 0) {
        $ajuste = floatval($_POST['coins_ajuste']);
        $nota   = isset($_POST['coins_nota']) ? sanitize_text_field($_POST['coins_nota']) : 'Ajuste manual';
        
        if ($ajuste > 0) {
            coins_manager()->agregar_coins($user_id, $ajuste, $nota, null);
            add_action('admin_notices', function() use ($ajuste) {
                ?>
                <div class="notice notice-success">
                    <p>Se agregaron <?php echo esc_html(coins_manager()->format_coins($ajuste)); ?> coins correctamente.</p>
                </div>
                <?php
            });
        } elseif ($ajuste < 0) {
            $descontar = abs($ajuste);
            if (coins_manager()->descontar_coins($user_id, $descontar, $nota, null)) {
                add_action('admin_notices', function() use ($descontar) {
                    ?>
                    <div class="notice notice-success">
                        <p>Se descontaron <?php echo esc_html(coins_manager()->format_coins($descontar)); ?> coins correctamente.</p>
                    </div>
                    <?php
                });
            } else {
                add_action('admin_notices', function() {
                    ?>
                    <div class="notice notice-error">
                        <p>Error: El usuario no tiene suficientes coins.</p>
                    </div>
                    <?php
                });
            }
        }
    }
}

/**
 * Agregar información de coins en el detalle de la orden
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'coins_info_en_orden');

function coins_info_en_orden($order) {
    $coins_otorgados = $order->get_meta('_coins_otorgados');
    $metodo_pago     = $order->get_payment_method();
    
    if ($coins_otorgados) {
        echo '<div class="order_data_column">';
        echo '<h3>Información de Coins</h3>';
        echo '<p><strong>✓ Coins otorgados por esta compra</strong></p>';
        echo '</div>';
    }
    
    if ($metodo_pago === 'coins') {
        echo '<div class="order_data_column">';
        echo '<h3>Información de Coins</h3>';
        echo '<p><strong>🪙 Pagado con coins</strong></p>';
        echo '</div>';
    }
}

/**
 * Agregar página de reportes en WooCommerce
 */
add_action('admin_menu', 'coins_agregar_menu_reportes', 99);

function coins_agregar_menu_reportes() {
    add_submenu_page(
        'woocommerce',
        'Reporte de Coins',
        'Coins',
        'manage_woocommerce',
        'coins-reportes',
        'coins_pagina_reportes'
    );
}

/**
 * Contenido de la página de reportes
 */
function coins_pagina_reportes() {
    global $wpdb;

    // Estadísticas generales basadas en usermeta 'user_coins'
    $meta_key = Coins_Manager::META_KEY; // 'user_coins'

    $total_usuarios_con_coins = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key = %s AND meta_value > 0",
             $meta_key
        )
    );
    
    $total_coins_circulacion = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(meta_value+0) FROM {$wpdb->usermeta} 
             WHERE meta_key = %s",
             $meta_key
        )
    );
    
    $tabla_historial = $wpdb->prefix . 'coins_historial';
    
    $total_coins_otorgados = $wpdb->get_var(
        "SELECT SUM(cantidad) FROM {$tabla_historial} WHERE tipo = 'suma'"
    );
    
    $total_coins_canjeados = $wpdb->get_var(
        "SELECT SUM(cantidad) FROM {$tabla_historial} WHERE tipo = 'resta'"
    );
    
    // Top usuarios con más coins
    $top_usuarios = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT user_id, meta_value as coins 
             FROM {$wpdb->usermeta} 
             WHERE meta_key = %s 
             ORDER BY (meta_value+0) DESC 
             LIMIT 10",
             $meta_key
        )
    );
    ?>
    <div class="wrap">
        <h1>📊 Reporte de Sistema de Coins</h1>
        
        <div class="coins-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0;">
            <div class="stat-box" style="background: white; padding: 20px; border-left: 4px solid #2c3e50;">
                <h3 style="margin: 0;">Usuarios con Coins</h3>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($total_usuarios_con_coins ?: 0); ?>
                </p>
            </div>
            
            <div class="stat-box" style="background: white; padding: 20px; border-left: 4px solid #27ae60;">
                <h3 style="margin: 0;">Coins en Circulación</h3>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #27ae60;">
                    <?php echo number_format($total_coins_circulacion ?: 0); ?>
                </p>
            </div>
            
            <div class="stat-box" style="background: white; padding: 20px; border-left: 4px solid #3498db%;">
                <h3 style="margin: 0;">Total Otorgados</h3>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #3498db;">
                    <?php echo number_format($total_coins_otorgados ?: 0); ?>
                </p>
            </div>
            
            <div class="stat-box" style="background: white; padding: 20px; border-left: 4px solid #e74c3c;">
                <h3 style="margin: 0;">Total Canjeados</h3>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #e74c3c;">
                    <?php echo number_format($total_coins_canjeados ?: 0); ?>
                </p>
            </div>
        </div>
        
        <div class="coins-top-usuarios" style="background: white; padding: 20px; margin: 20px 0;">
            <h2>Top 10 Usuarios con Más Coins</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Coins</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_usuarios)): ?>
                        <?php foreach ($top_usuarios as $usuario): ?>
                            <?php $user = get_userdata($usuario->user_id); ?>
                            <tr>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><strong><?php echo esc_html(coins_manager()->format_coins($usuario->coins)); ?></strong></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $usuario->user_id)); ?>">
                                        Ver perfil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay datos disponibles</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
