<?php
/**
 * Recompensas por Compartir en Redes Sociales
 * Otorga coins cuando un usuario comparte un producto
 * 
 * @package CoinsSystem
 * @subpackage Rewards
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar compartido en redes sociales (vía AJAX)
 */
function coins_register_social_share() {
    // Verificar nonce
    check_ajax_referer('coins_social_share', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Debes estar logueado'));
    }
    
    $user_id = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $platform = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
    
    if (!$product_id || !$platform) {
        wp_send_json_error(array('message' => 'Datos inválidos'));
    }
    
    // Validar plataforma
    $allowed_platforms = array('facebook', 'twitter', 'whatsapp', 'telegram', 'linkedin');
    
    if (!in_array($platform, $allowed_platforms)) {
        wp_send_json_error(array('message' => 'Plataforma no válida'));
    }
    
    global $wpdb;
    $table_shares = coins_get_table_name('shares');
    
    // Verificar si ya compartió hoy en esta plataforma
    $today_start = date('Y-m-d 00:00:00');
    $already_shared = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_shares 
             WHERE user_id = %d 
             AND product_id = %d 
             AND platform = %s 
             AND fecha >= %s",
            $user_id,
            $product_id,
            $platform,
            $today_start
        )
    );
    
    if ($already_shared) {
        wp_send_json_error(array('message' => 'Ya compartiste en esta plataforma hoy'));
    }
    
    // Otorgar coins (0.5 coins por compartir)
    $coins_to_add = 0.5;
    $coins_manager = Coins_Manager::get_instance();
    
    $product = wc_get_product($product_id);
    $product_name = $product ? $product->get_name() : 'Producto #' . $product_id;
    
    $coins_manager->add_coins(
        $user_id,
        $coins_to_add,
        'compartir',
        'Compartido en ' . ucfirst($platform) . ': ' . $product_name,
        null
    );
    
    // Registrar en la tabla
    $wpdb->insert(
        $table_shares,
        array(
            'user_id' => $user_id,
            'product_id' => $product_id,
            'platform' => $platform,
            'coins_otorgados' => $coins_to_add,
            'fecha' => current_time('mysql')
        ),
        array('%d', '%d', '%s', '%f', '%s')
    );
    
    wp_send_json_success(array(
        'message' => '¡Has ganado ' . $coins_manager->format_coins($coins_to_add) . ' coins!',
        'new_balance' => coins_get_balance($user_id)
    ));
}
add_action('wp_ajax_coins_social_share', 'coins_register_social_share');

/**
 * Agregar botones de compartir en página de producto
 */
function coins_add_social_share_buttons() {
    if (!is_user_logged_in()) {
        return;
    }
    
    global $product;
    
    if (!$product) {
        return;
    }
    
    $product_id = $product->get_id();
    $product_url = get_permalink($product_id);
    $product_title = $product->get_name();
    
    ?>
    <div class="coins-social-share" style="margin: 25px 0; padding: 20px; background: linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(218, 4, 128, 0.03) 100%); border-radius: 12px; border: 1px solid rgba(218, 4, 128, 0.2);">
        <h4 style="margin: 0 0 12px 0; color: #da0480; font-size: 16px; font-weight: 700;">
            📱 Comparte y gana coins
        </h4>
        <p style="margin: 0 0 15px 0; font-size: 14px; color: #6b7280;">
            Gana <strong style="color: #da0480;">0.5 coins</strong> por cada plataforma que compartas (1 vez al día).
        </p>
        <div class="social-share-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="coinsShareOn('facebook', <?php echo $product_id; ?>, '<?php echo esc_js($product_url); ?>', '<?php echo esc_js($product_title); ?>')" 
                    class="share-btn" data-platform="facebook" 
                    style="padding: 10px 16px; background: #1877f2; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                <span style="margin-right: 6px;">👍</span> Facebook
            </button>
            
            <button onclick="coinsShareOn('twitter', <?php echo $product_id; ?>, '<?php echo esc_js($product_url); ?>', '<?php echo esc_js($product_title); ?>')" 
                    class="share-btn" data-platform="twitter" 
                    style="padding: 10px 16px; background: #1da1f2; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                <span style="margin-right: 6px;">🐦</span> Twitter
            </button>
            
            <button onclick="coinsShareOn('whatsapp', <?php echo $product_id; ?>, '<?php echo esc_js($product_url); ?>', '<?php echo esc_js($product_title); ?>')" 
                    class="share-btn" data-platform="whatsapp" 
                    style="padding: 10px 16px; background: #25d366; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                <span style="margin-right: 6px;">💬</span> WhatsApp
            </button>
            
            <button onclick="coinsShareOn('telegram', <?php echo $product_id; ?>, '<?php echo esc_js($product_url); ?>', '<?php echo esc_js($product_title); ?>')" 
                    class="share-btn" data-platform="telegram" 
                    style="padding: 10px 16px; background: #0088cc; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                <span style="margin-right: 6px;">✈️</span> Telegram
            </button>
        </div>
    </div>
    
    <style>
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .share-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    
    <script>
    function coinsShareOn(platform, productId, url, title) {
        // Abrir ventana de compartir según plataforma
        let shareUrl = '';
        
        switch(platform) {
            case 'facebook':
                shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
                break;
            case 'twitter':
                shareUrl = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
                break;
            case 'whatsapp':
                shareUrl = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
                break;
            case 'telegram':
                shareUrl = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
            
            // Registrar compartido y otorgar coins
            setTimeout(function() {
                coinsRegisterShare(platform, productId);
            }, 2000); // Esperar 2 segundos antes de otorgar coins
        }
    }
    
    function coinsRegisterShare(platform, productId) {
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'coins_social_share',
                nonce: '<?php echo wp_create_nonce('coins_social_share'); ?>',
                platform: platform,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    alert('🎉 ' + response.data.message);
                    
                    // Deshabilitar botón
                    jQuery('.share-btn[data-platform="' + platform + '"]').prop('disabled', true);
                    
                    // Actualizar balance si hay display
                    if (typeof updateCoinsDisplay === 'function') {
                        updateCoinsDisplay(response.data.new_balance);
                    }
                } else {
                    alert('⚠️ ' + response.data.message);
                }
            }
        });
    }
    </script>
    <?php
}
add_action('woocommerce_after_single_product_summary', 'coins_add_social_share_buttons', 25);

/**
 * Obtener estadísticas de compartidos de un usuario
 */
function coins_get_user_share_stats($user_id) {
    global $wpdb;
    $table_shares = coins_get_table_name('shares');
    
    $stats = array(
        'total_shares' => 0,
        'total_coins_earned' => 0,
        'shares_by_platform' => array()
    );
    
    // Total general
    $totals = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT COUNT(*) as total, SUM(coins_otorgados) as total_coins 
             FROM $table_shares 
             WHERE user_id = %d",
            $user_id
        )
    );
    
    if ($totals) {
        $stats['total_shares'] = (int) $totals->total;
        $stats['total_coins_earned'] = (float) $totals->total_coins;
    }
    
    // Por plataforma
    $by_platform = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT platform, COUNT(*) as count 
             FROM $table_shares 
             WHERE user_id = %d 
             GROUP BY platform",
            $user_id
        )
    );
    
    foreach ($by_platform as $row) {
        $stats['shares_by_platform'][$row->platform] = (int) $row->count;
    }
    
    return $stats;
}
?>