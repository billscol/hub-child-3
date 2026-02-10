<?php
/**
 * Página de Configuración del Sistema de Coins
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar menú de configuración
 */
function add_coins_settings_menu() {
    add_submenu_page(
        'woocommerce',
        'Configuración de Coins',
        'Coins Settings',
        'manage_options',
        'coins-settings',
        'render_coins_settings_page'
    );
}
add_action('admin_menu', 'add_coins_settings_menu');

/**
 * Renderizar página de configuración
 */
function render_coins_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Guardar configuración
    if (isset($_POST['coins_settings_nonce']) && wp_verify_nonce($_POST['coins_settings_nonce'], 'coins_settings')) {
        update_option('coins_por_compra', intval($_POST['coins_por_compra']));
        update_option('coins_por_resena', floatval($_POST['coins_por_resena']));
        update_option('coins_por_compartir', floatval($_POST['coins_por_compartir']));
        
        echo '<div class="notice notice-success"><p>✅ Configuración guardada correctamente</p></div>';
    }
    
    // Obtener configuración actual
    $coins_por_compra = get_option('coins_por_compra', 1);
    $coins_por_resena = get_option('coins_por_resena', 1);
    $coins_por_compartir = get_option('coins_por_compartir', 0.5);
    
    // Obtener estadísticas
    $stats = coins_manager()->get_statistics();
    
    ?>
    <div class="wrap">
        <h1>🪙 Configuración del Sistema de Coins</h1>
        
        <!-- Estadísticas -->
        <div class="coins-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Total en Circulación</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 700; color: #da0480;"><?php echo number_format($stats['total_circulation'], 0); ?></p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Total Ganados</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 700; color: #28a745;"><?php echo number_format($stats['total_earned'], 0); ?></p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Total Gastados</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 700; color: #dc3545;"><?php echo number_format($stats['total_spent'], 0); ?></p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Usuarios Activos</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 700; color: #0073aa;"><?php echo number_format($stats['active_users'], 0); ?></p>
            </div>
        </div>
        
        <!-- Formulario de Configuración -->
        <form method="post" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <?php wp_nonce_field('coins_settings', 'coins_settings_nonce'); ?>
            
            <h2>Configuración de Recompensas</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Coins por Compra Premium</th>
                    <td>
                        <input type="number" name="coins_por_compra" value="<?php echo esc_attr($coins_por_compra); ?>" min="0" step="1" style="width: 100px;">
                        <p class="description">Cantidad de coins que gana un usuario al comprar un curso premium.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Coins por Reseña Verificada</th>
                    <td>
                        <input type="number" name="coins_por_resena" value="<?php echo esc_attr($coins_por_resena); ?>" min="0" step="0.1" style="width: 100px;">
                        <p class="description">Cantidad de coins que gana un usuario al dejar una reseña verificada.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Coins por Compartir</th>
                    <td>
                        <input type="number" name="coins_por_compartir" value="<?php echo esc_attr($coins_por_compartir); ?>" min="0" step="0.1" style="width: 100px;">
                        <p class="description">Cantidad de coins que gana un usuario al compartir un curso en redes sociales.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary button-large">💾 Guardar Configuración</button>
            </p>
        </form>
    </div>
    <?php
}
?>