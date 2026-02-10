<?php
/**
 * Dropdown de Usuario con Coins
 * Integra los coins en el menú dropdown del usuario
 * 
 * @package CoinsSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar coins al dropdown del usuario (integración con [sp_auth])
 * Este código se integra con el shortcode sp_auth existente
 */
function coins_add_to_user_dropdown() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $user_id = get_current_user_id();
    $coins_manager = Coins_Manager::get_instance();
    $user_coins = coins_get_balance($user_id);
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Buscar el dropdown del usuario
        const userDropdown = $('.user-logged .dropdown-content');
        
        if (userDropdown.length) {
            // Crear elemento de coins
            const coinsItem = $('<div>').css({
                'padding': '12px 16px',
                'border-bottom': '1px solid rgba(218, 4, 128, 0.15)',
                'background': 'rgba(218, 4, 128, 0.05)'
            });
            
            const coinsContent = $('<div>').css({
                'display': 'flex',
                'align-items': 'center',
                'gap': '10px'
            });
            
            const coinsIcon = $('<img>').attr({
                'src': 'https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png',
                'alt': 'Coins'
            }).css({
                'width': '14px',
                'height': '14px',
                'object-fit': 'contain'
            });
            
            const coinsText = $('<span>').html(
                '<strong><?php echo esc_html($coins_manager->format_coins($user_coins)); ?></strong> coins disponibles'
            ).css({
                'font-size': '13px',
                'color': '#374151'
            });
            
            coinsContent.append(coinsIcon).append(coinsText);
            coinsItem.append(coinsContent);
            
            // Insertar al inicio del dropdown
            userDropdown.prepend(coinsItem);
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'coins_add_to_user_dropdown', 20);

/**
 * Agregar link de "Mis Coins" al menú de cuenta
 */
function coins_add_account_menu_item($items) {
    $new_items = array();
    
    foreach ($items as $key => $value) {
        $new_items[$key] = $value;
        
        // Añadir después de "Dashboard"
        if ($key === 'dashboard') {
            $new_items['coins'] = '🪙 Mis Coins';
        }
    }
    
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'coins_add_account_menu_item');

/**
 * Registrar endpoint de coins
 */
function coins_add_account_endpoint() {
    add_rewrite_endpoint('coins', EP_ROOT | EP_PAGES);
}
add_action('init', 'coins_add_account_endpoint');

/**
 * Contenido de la página "Mis Coins"
 */
function coins_account_page_content() {
    $user_id = get_current_user_id();
    $coins_manager = Coins_Manager::get_instance();
    $balance = coins_get_balance($user_id);
    $stats = $coins_manager->get_stats($user_id);
    $transactions = coins_get_user_transactions($user_id, 10);
    
    ?>
    <div class="woocommerce-coins-account">
        <h2 style="color: #da0480; margin-bottom: 25px;">🪙 Mis Coins</h2>
        
        <!-- Resumen -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="padding: 25px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3); text-align: center;">
                <div style="font-size: 36px; font-weight: 800; color: #da0480; margin-bottom: 8px;">
                    <?php echo esc_html($coins_manager->format_coins($balance)); ?>
                </div>
                <div style="font-size: 14px; color: #6b7280;">Saldo Actual</div>
            </div>
            
            <div style="padding: 25px; background: rgba(16, 185, 129, 0.08); border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3); text-align: center;">
                <div style="font-size: 36px; font-weight: 800; color: #10b981; margin-bottom: 8px;">
                    <?php echo esc_html($coins_manager->format_coins($stats['total_ganado'])); ?>
                </div>
                <div style="font-size: 14px; color: #6b7280;">Total Ganado</div>
            </div>
            
            <div style="padding: 25px; background: rgba(239, 68, 68, 0.08); border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.3); text-align: center;">
                <div style="font-size: 36px; font-weight: 800; color: #ef4444; margin-bottom: 8px;">
                    <?php echo esc_html($coins_manager->format_coins($stats['total_gastado'])); ?>
                </div>
                <div style="font-size: 14px; color: #6b7280;">Total Gastado</div>
            </div>
        </div>
        
        <!-- Cómo ganar coins -->
        <div style="padding: 20px; background: rgba(59, 130, 246, 0.08); border-radius: 12px; border-left: 4px solid #3b82f6; margin-bottom: 30px;">
            <h3 style="margin: 0 0 12px 0; color: #1e40af; font-size: 16px;">💡 Cómo ganar más coins</h3>
            <ul style="margin: 0; padding-left: 20px; color: #374151;">
                <li style="margin: 5px 0;">Compra cursos premium: <strong>+1 coin</strong> por curso</li>
                <li style="margin: 5px 0;">Deja reseñas verificadas: <strong>+1 coin</strong> por reseña</li>
                <li style="margin: 5px 0;">Comparte en redes sociales: <strong>+0.5 coins</strong> por plataforma</li>
            </ul>
        </div>
        
        <!-- Historial -->
        <div>
            <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px;">📊 Historial de Transacciones</h3>
            
            <?php if (empty($transactions)) : ?>
                <p style="color: #6b7280; text-align: center; padding: 40px;">No tienes transacciones aún.</p>
            <?php else : ?>
                <div style="background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
                    <?php foreach ($transactions as $index => $t) : 
                        $is_positive = $t->cantidad > 0;
                        $border_style = $index < count($transactions) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '';
                    ?>
                        <div style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; <?php echo $border_style; ?>">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                                    <?php 
                                    $tipo_icons = array(
                                        'compra' => '🛍️',
                                        'resena' => '⭐',
                                        'compartir' => '📱',
                                        'canje' => '🎁',
                                        'manual' => '⚙️',
                                        'reversion' => '↩️'
                                    );
                                    $icon = isset($tipo_icons[$t->tipo]) ? $tipo_icons[$t->tipo] : '🔹';
                                    echo esc_html($icon . ' ' . ucfirst($t->tipo));
                                    ?>
                                </div>
                                <div style="font-size: 13px; color: #6b7280;">
                                    <?php echo esc_html($t->descripcion); ?>
                                </div>
                                <div style="font-size: 12px; color: #9ca3af; margin-top: 4px;">
                                    <?php echo date('d/m/Y H:i', strtotime($t->fecha)); ?>
                                </div>
                            </div>
                            <div style="text-align: right; margin-left: 15px;">
                                <div style="font-size: 20px; font-weight: 700; color: <?php echo $is_positive ? '#10b981' : '#ef4444'; ?>;">
                                    <?php echo $is_positive ? '+' : ''; ?><?php echo esc_html($coins_manager->format_coins($t->cantidad)); ?>
                                </div>
                                <div style="font-size: 12px; color: #6b7280;">
                                    Balance: <?php echo esc_html($coins_manager->format_coins($t->saldo_nuevo)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($transactions) >= 10) : ?>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="#" onclick="coinsLoadMoreTransactions(); return false;" style="color: #da0480; font-weight: 600; text-decoration: underline;">
                            Ver más transacciones
                        </a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        @media (max-width: 768px) {
            .woocommerce-coins-account > div:first-of-type {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    <?php
}
add_action('woocommerce_account_coins_endpoint', 'coins_account_page_content');

/**
 * Flush rewrite rules al activar
 */
function coins_flush_rewrite_rules() {
    coins_add_account_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'coins_flush_rewrite_rules');
?>