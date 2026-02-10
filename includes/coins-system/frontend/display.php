<?php
/**
 * Display de Coins en Frontend
 * Muestra el saldo de coins del usuario en diferentes partes del sitio
 * 
 * @package CoinsSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para mostrar saldo de coins
 * Uso: [coins_balance]
 */
function coins_balance_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p style="color: #6b7280; font-size: 14px;">Inicia sesión para ver tus coins.</p>';
    }
    
    $atts = shortcode_atts(array(
        'style' => 'default' // default, minimal, detailed
    ), $atts);
    
    $user_id = get_current_user_id();
    $coins_manager = Coins_Manager::get_instance();
    $balance = coins_get_balance($user_id);
    $stats = $coins_manager->get_stats($user_id);
    
    ob_start();
    
    if ($atts['style'] === 'minimal') {
        ?>
        <div class="coins-balance-minimal" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: linear-gradient(135deg, #da0480 0%, #b00368 100%); color: #fff; border-radius: 20px; font-weight: 600;">
            <span style="font-size: 18px;">🪙</span>
            <span><?php echo esc_html($coins_manager->format_coins($balance)); ?></span>
        </div>
        <?php
    } elseif ($atts['style'] === 'detailed') {
        ?>
        <div class="coins-balance-detailed" style="padding: 25px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 16px; border: 1px solid rgba(218, 4, 128, 0.3);">
            <h3 style="margin: 0 0 20px 0; color: #da0480; font-size: 20px; font-weight: 700;">🪙 Tus Coins</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="padding: 15px; background: rgba(218, 4, 128, 0.08); border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: #da0480;"><?php echo esc_html($coins_manager->format_coins($balance)); ?></div>
                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Saldo Actual</div>
                </div>
                
                <div style="padding: 15px; background: rgba(16, 185, 129, 0.08); border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?php echo esc_html($coins_manager->format_coins($stats['total_ganado'])); ?></div>
                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Total Ganado</div>
                </div>
                
                <div style="padding: 15px; background: rgba(239, 68, 68, 0.08); border-radius: 12px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: #ef4444;"><?php echo esc_html($coins_manager->format_coins($stats['total_gastado'])); ?></div>
                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Total Gastado</div>
                </div>
            </div>
            
            <div style="padding: 15px; background: rgba(59, 130, 246, 0.08); border-radius: 10px; border-left: 4px solid #3b82f6;">
                <p style="margin: 0; font-size: 14px; color: #374151;">
                    <strong style="color: #1e40af;">💡 Cómo ganar más coins:</strong><br>
                    • Compra cursos premium<br>
                    • Deja reseñas verificadas<br>
                    • Comparte en redes sociales
                </p>
            </div>
        </div>
        <?php
    } else {
        // Estilo default
        ?>
        <div class="coins-balance-default" style="display: inline-flex; align-items: center; gap: 12px; padding: 12px 20px; background: rgba(218, 4, 128, 0.1); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3);">
            <span style="font-size: 24px;">🪙</span>
            <div>
                <div style="font-size: 12px; color: #6b7280; margin-bottom: 2px;">Tus Coins</div>
                <div style="font-size: 20px; font-weight: 700; color: #da0480;"><?php echo esc_html($coins_manager->format_coins($balance)); ?></div>
            </div>
        </div>
        <?php
    }
    
    return ob_get_clean();
}
add_shortcode('coins_balance', 'coins_balance_shortcode');

/**
 * Mostrar precio en coins en productos
 */
function coins_show_product_coin_price() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($product->get_id());
    
    if ($costo_coins <= 0) {
        return;
    }
    
    ?>
    <div class="product-coin-price" style="margin: 20px 0; padding: 18px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.3);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <span style="font-size: 32px;">🪙</span>
            <div>
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 3px;">O cánjea por</div>
                <div style="font-size: 26px; font-weight: 800; color: #da0480;">
                    <?php echo esc_html($coins_manager->format_coins($costo_coins)); ?> <span style="font-size: 18px; font-weight: 600;">coins</span>
                </div>
            </div>
        </div>
        
        <?php if (is_user_logged_in()) : 
            $user_coins = coins_get_balance(get_current_user_id());
            $tiene_coins = $user_coins >= $costo_coins;
        ?>
            <div style="padding: 12px; background: rgba(<?php echo $tiene_coins ? '16, 185, 129' : '239, 68, 68'; ?>, 0.1); border-radius: 8px; margin-top: 12px;">
                <p style="margin: 0; font-size: 14px; color: #374151;">
                    <?php if ($tiene_coins) : ?>
                        <strong style="color: #10b981;">✅ Tienes suficientes coins</strong><br>
                        Saldo actual: <strong><?php echo esc_html($coins_manager->format_coins($user_coins)); ?> coins</strong>
                    <?php else : ?>
                        <strong style="color: #ef4444;">⚠️ Te faltan coins</strong><br>
                        Tienes: <?php echo esc_html($coins_manager->format_coins($user_coins)); ?> | Necesitas: <?php echo esc_html($coins_manager->format_coins($costo_coins - $user_coins)); ?> más
                    <?php endif; ?>
                </p>
            </div>
        <?php else : ?>
            <p style="margin: 10px 0 0 0; font-size: 13px; color: #6b7280;">
                <a href="<?php echo wp_login_url(get_permalink()); ?>" style="color: #da0480; font-weight: 600; text-decoration: underline;">Inicia sesión</a> para ver tu saldo de coins.
            </p>
        <?php endif; ?>
    </div>
    <?php
}
add_action('woocommerce_single_product_summary', 'coins_show_product_coin_price', 25);

/**
 * Widget de coins en sidebar
 */
function coins_register_widget() {
    register_sidebar(array(
        'name' => 'Coins Widget',
        'id' => 'coins-widget',
        'before_widget' => '<div class="coins-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
}
add_action('widgets_init', 'coins_register_widget');

/**
 * CSS para displays de coins
 */
function coins_enqueue_display_styles() {
    ?>
    <style>
        /* Responsive para displays de coins */
        @media (max-width: 768px) {
            .coins-balance-detailed > div {
                grid-template-columns: 1fr !important;
            }
            
            .product-coin-price {
                padding: 15px !important;
            }
            
            .product-coin-price > div:first-child {
                flex-direction: column;
                text-align: center;
            }
        }
        
        /* Animación de coins */
        @keyframes coins-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .coins-balance-minimal:hover,
        .coins-balance-default:hover {
            animation: coins-pulse 0.6s ease-in-out;
        }
    </style>
    <?php
}
add_action('wp_head', 'coins_enqueue_display_styles');
?>