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
 * Procesar compartido en redes sociales via AJAX
 */
function coins_process_social_share() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'coins_social_share')) {
        wp_send_json_error(array('message' => 'Error de seguridad'));
    }
    
    // Verificar usuario logueado
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Debes estar logueado'));
    }
    
    $user_id = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $platform = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
    
    if (!$product_id || !$platform) {
        wp_send_json_error(array('message' => 'Datos incompletos'));
    }
    
    // Plataformas permitidas
    $allowed_platforms = array('facebook', 'twitter', 'whatsapp', 'telegram', 'linkedin');
    
    if (!in_array($platform, $allowed_platforms)) {
        wp_send_json_error(array('message' => 'Plataforma no válida'));
    }
    
    global $wpdb;
    $table = coins_get_table_name('shares');
    
    // Verificar si ya compartió hoy en esta plataforma
    $ya_compartido_hoy = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE user_id = %d 
             AND product_id = %d 
             AND platform = %s 
             AND DATE(fecha) = CURDATE()",
            $user_id,
            $product_id,
            $platform
        )
    );
    
    if ($ya_compartido_hoy > 0) {
        wp_send_json_error(array('message' => 'Ya compartiste en esta red hoy'));
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $coins_por_compartir = 0.5; // 0.5 coins por compartir
    
    // Otorgar coins
    $coins_manager->add_coins(
        $user_id,
        $coins_por_compartir,
        'compartir',
        'Compartir producto #' . $product_id . ' en ' . ucfirst($platform)
    );
    
    // Registrar el compartido
    $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'product_id' => $product_id,
            'platform' => $platform,
            'coins_otorgados' => $coins_por_compartir,
            'fecha' => current_time('mysql')
        ),
        array('%d', '%d', '%s', '%f', '%s')
    );
    
    $nuevo_saldo = $coins_manager->get_coins($user_id);
    
    wp_send_json_success(array(
        'message' => '¡Gracias por compartir! Ganaste ' . $coins_manager->format_coins($coins_por_compartir) . ' coins',
        'coins_earned' => $coins_por_compartir,
        'new_balance' => $nuevo_saldo
    ));
}
add_action('wp_ajax_coins_social_share', 'coins_process_social_share');

/**
 * Agregar botones de compartir en producto
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
    <div class="coins-social-share" style="margin: 20px 0; padding: 20px; background: rgba(218, 4, 128, 0.05); border-radius: 10px; border: 1px solid rgba(218, 4, 128, 0.2);">
        <h4 style="margin: 0 0 15px 0; color: #da0480; font-size: 16px;">🪙 Comparte y gana 0.5 coins</h4>
        <div class="social-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="social-share-btn" data-platform="facebook" data-product="<?php echo esc_attr($product_id); ?>" style="flex: 1; min-width: 100px; padding: 10px 15px; background: #1877f2; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                📘 Facebook
            </button>
            <button class="social-share-btn" data-platform="twitter" data-product="<?php echo esc_attr($product_id); ?>" style="flex: 1; min-width: 100px; padding: 10px 15px; background: #1da1f2; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                🐦 Twitter
            </button>
            <button class="social-share-btn" data-platform="whatsapp" data-product="<?php echo esc_attr($product_id); ?>" style="flex: 1; min-width: 100px; padding: 10px 15px; background: #25d366; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                💬 WhatsApp
            </button>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.social-share-btn').on('click', function() {
            var button = $(this);
            var platform = button.data('platform');
            var productId = button.data('product');
            var productUrl = '<?php echo esc_js($product_url); ?>';
            var productTitle = '<?php echo esc_js($product_title); ?>';
            
            // Abrir ventana de compartir
            var shareUrl = '';
            
            switch(platform) {
                case 'facebook':
                    shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(productUrl);
                    break;
                case 'twitter':
                    shareUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(productTitle) + '&url=' + encodeURIComponent(productUrl);
                    break;
                case 'whatsapp':
                    shareUrl = 'https://wa.me/?text=' + encodeURIComponent(productTitle + ' ' + productUrl);
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
            
            // Registrar el compartido para ganar coins
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'coins_social_share',
                    product_id: productId,
                    platform: platform,
                    nonce: '<?php echo wp_create_nonce('coins_social_share'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data.message);
                        button.prop('disabled', true).css('opacity', '0.5');
                    } else {
                        alert('⚠️ ' + response.data.message);
                    }
                }
            });
        });
    });
    </script>
    <?php
}
add_action('woocommerce_after_single_product_summary', 'coins_add_social_share_buttons', 25);

/**
 * Obtener compartidos de un usuario
 */
function coins_get_user_shares($user_id, $limit = 50) {
    global $wpdb;
    $table = coins_get_table_name('shares');
    
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY fecha DESC LIMIT %d",
            $user_id,
            $limit
        )
    );
}
?>