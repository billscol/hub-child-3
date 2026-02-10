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
 * Mostrar coins en el producto (single product)
 */
function coins_display_on_product() {
    if (!is_user_logged_in()) {
        return;
    }
    
    global $product;
    
    if (!$product) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($product->get_id());
    
    // Solo mostrar si el producto tiene costo en coins
    if ($costo_coins <= 0) {
        return;
    }
    
    $user_id = get_current_user_id();
    $user_coins = $coins_manager->get_coins($user_id);
    $tiene_suficientes = $user_coins >= $costo_coins;
    
    ?>
    <div class="product-coins-info" style="margin: 25px 0; padding: 20px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%); border-radius: 12px; border: 2px solid rgba(218, 4, 128, 0.3);">
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">
                    🪙 Costo en Coins:
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #da0480;">
                    <?php echo $coins_manager->format_coins($costo_coins); ?> coins
                </div>
            </div>
            
            <div style="flex: 1; min-width: 200px; padding-left: 20px; border-left: 2px solid rgba(218, 4, 128, 0.2);">
                <div style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">
                    Tu saldo:
                </div>
                <div style="font-size: 24px; font-weight: 700; color: <?php echo $tiene_suficientes ? '#10b981' : '#ef4444'; ?>;">
                    <?php echo $coins_manager->format_coins($user_coins); ?> coins
                </div>
            </div>
        </div>
        
        <?php if (!$tiene_suficientes): ?>
        <div style="margin-top: 15px; padding: 12px; background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; border-radius: 6px;">
            <p style="margin: 0; color: #ef4444; font-weight: 600; font-size: 14px;">
                ⚠️ Necesitas <?php echo $coins_manager->format_coins($costo_coins - $user_coins); ?> coins más para canjear este curso.
            </p>
        </div>
        <?php else: ?>
        <div style="margin-top: 15px; padding: 12px; background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; border-radius: 6px;">
            <p style="margin: 0; color: #10b981; font-weight: 600; font-size: 14px;">
                ✅ ¡Tienes suficientes coins! Puedes canjear este curso.
            </p>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action('woocommerce_single_product_summary', 'coins_display_on_product', 25);

/**
 * Shortcode para mostrar saldo de coins
 * Uso: [user_coins]
 */
function coins_shortcode_display($atts) {
    if (!is_user_logged_in()) {
        return '<span style="color: #9ca3af;">Inicia sesión para ver tus coins</span>';
    }
    
    $atts = shortcode_atts(array(
        'style' => 'default', // default, badge, large
        'show_icon' => 'yes',
        'label' => 'Tus coins:'
    ), $atts);
    
    $coins_manager = Coins_Manager::get_instance();
    $user_coins = $coins_manager->get_coins(get_current_user_id());
    $formatted_coins = $coins_manager->format_coins($user_coins);
    
    ob_start();
    
    switch ($atts['style']) {
        case 'badge':
            ?>
            <span class="coins-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: linear-gradient(135deg, #da0480, #b00368); color: white; border-radius: 20px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 12px rgba(218, 4, 128, 0.3);">
                <?php if ($atts['show_icon'] === 'yes'): ?>
                    🪙
                <?php endif; ?>
                <?php echo esc_html($formatted_coins); ?>
            </span>
            <?php
            break;
            
        case 'large':
            ?>
            <div class="coins-display-large" style="padding: 25px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05)); border-radius: 12px; text-align: center; border: 2px solid rgba(218, 4, 128, 0.3);">
                <div style="font-size: 14px; color: #6b7280; margin-bottom: 10px;">
                    <?php echo esc_html($atts['label']); ?>
                </div>
                <div style="font-size: 48px; font-weight: 800; color: #da0480;">
                    <?php if ($atts['show_icon'] === 'yes'): ?>
                        🪙
                    <?php endif; ?>
                    <?php echo esc_html($formatted_coins); ?>
                </div>
            </div>
            <?php
            break;
            
        default:
            ?>
            <span class="coins-display" style="display: inline-flex; align-items: center; gap: 8px; font-size: 16px; color: #1f2937;">
                <span style="color: #6b7280;"><?php echo esc_html($atts['label']); ?></span>
                <strong style="color: #da0480; font-weight: 700;">
                    <?php if ($atts['show_icon'] === 'yes'): ?>
                        🪙
                    <?php endif; ?>
                    <?php echo esc_html($formatted_coins); ?>
                </strong>
            </span>
            <?php
    }
    
    return ob_get_clean();
}
add_shortcode('user_coins', 'coins_shortcode_display');

/**
 * Widget de coins en sidebar
 */
function coins_sidebar_widget() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $user_id = get_current_user_id();
    $user_coins = $coins_manager->get_coins($user_id);
    $stats = $coins_manager->get_stats($user_id);
    
    ?>
    <div class="widget coins-widget" style="padding: 20px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05)); border-radius: 12px; margin-bottom: 30px; border: 2px solid rgba(218, 4, 128, 0.2);">
        <h3 class="widget-title" style="margin: 0 0 20px 0; color: #da0480; font-size: 18px; font-weight: 700;">
            🪙 Mis Coins
        </h3>
        
        <div class="coins-balance" style="text-align: center; padding: 20px; background: white; border-radius: 10px; margin-bottom: 15px;">
            <div style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">Saldo actual</div>
            <div style="font-size: 36px; font-weight: 800; color: #da0480;">
                <?php echo $coins_manager->format_coins($user_coins); ?>
            </div>
        </div>
        
        <div class="coins-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
            <div style="padding: 10px; background: white; border-radius: 8px; text-align: center;">
                <div style="color: #6b7280;">Ganado</div>
                <div style="font-weight: 700; color: #10b981;">
                    +<?php echo $coins_manager->format_coins($stats['total_ganado']); ?>
                </div>
            </div>
            <div style="padding: 10px; background: white; border-radius: 8px; text-align: center;">
                <div style="color: #6b7280;">Gastado</div>
                <div style="font-weight: 700; color: #ef4444;">
                    -<?php echo $coins_manager->format_coins($stats['total_gastado']); ?>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 15px; text-align: center;">
            <a href="<?php echo esc_url(site_url('/gana-coins')); ?>" style="display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #da0480, #b00368); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s;">
                ✨ Cómo ganar coins
            </a>
        </div>
    </div>
    <?php
}
add_action('woocommerce_sidebar', 'coins_sidebar_widget', 5);

/**
 * Enqueue scripts para display
 */
function coins_enqueue_display_scripts() {
    if (!is_user_logged_in()) {
        return;
    }
    
    wp_add_inline_style('woocommerce-general', '
        .coins-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(218, 4, 128, 0.4);
        }
        
        .coins-display-large {
            animation: coinsPulse 2s ease-in-out infinite;
        }
        
        @keyframes coinsPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    ');
}
add_action('wp_enqueue_scripts', 'coins_enqueue_display_scripts');
?>