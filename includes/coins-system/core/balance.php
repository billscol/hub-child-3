<?php
/**
 * Funciones de Gestión de Saldo de Coins
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mostrar saldo de coins del usuario actual
 */
function display_user_coins_balance($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return '0';
    }
    
    $balance = coins_manager()->get_balance($user_id);
    return number_format($balance, 0, ',', '.');
}

/**
 * Shortcode para mostrar saldo de coins
 * Uso: [mi_saldo_coins]
 */
function shortcode_mi_saldo_coins($atts) {
    if (!is_user_logged_in()) {
        return '<span class="coins-balance-guest">Inicia sesión para ver tus coins</span>';
    }
    
    $balance = display_user_coins_balance();
    
    ob_start();
    ?>
    <div class="coins-balance-display">
        <span class="coins-icon">🪙</span>
        <span class="coins-amount"><?php echo $balance; ?></span>
        <span class="coins-label">Coins</span>
    </div>
    
    <style>
        .coins-balance-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1), rgba(218, 4, 128, 0.05));
            padding: 12px 20px;
            border-radius: 30px;
            border: 2px solid rgba(218, 4, 128, 0.3);
            font-weight: 600;
            color: #da0480;
        }
        
        .coins-icon {
            font-size: 24px;
        }
        
        .coins-amount {
            font-size: 20px;
            font-weight: 700;
        }
        
        .coins-label {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .coins-balance-guest {
            color: #666;
            font-style: italic;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('mi_saldo_coins', 'shortcode_mi_saldo_coins');

/**
 * Agregar coins al usuario via AJAX (admin)
 */
function ajax_admin_add_coins() {
    check_ajax_referer('admin_coins_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permisos insuficientes'));
    }
    
    $user_id = intval($_POST['user_id']);
    $cantidad = floatval($_POST['cantidad']);
    $descripcion = sanitize_text_field($_POST['descripcion']);
    
    if (coins_manager()->add_coins($user_id, $cantidad, $descripcion)) {
        wp_send_json_success(array(
            'message' => 'Coins agregados exitosamente',
            'new_balance' => coins_manager()->get_balance($user_id)
        ));
    } else {
        wp_send_json_error(array('message' => 'Error al agregar coins'));
    }
}
add_action('wp_ajax_admin_add_coins', 'ajax_admin_add_coins');
?>