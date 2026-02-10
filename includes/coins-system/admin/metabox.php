<?php
/**
 * Metabox de Coins en Productos
 * Permite establecer el costo en coins de un producto
 * 
 * @package CoinsSystem
 * @subpackage Admin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox de coins en productos
 */
function coins_add_product_metabox() {
    add_meta_box(
        'coins_product_costo',
        '🪙 Coins para Canje',
        'coins_render_product_metabox',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'coins_add_product_metabox');

/**
 * Renderizar metabox
 */
function coins_render_product_metabox($post) {
    wp_nonce_field('coins_save_product_costo', 'coins_product_costo_nonce');
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($post->ID);
    
    ?>
    <style>
        .coins-metabox {
            padding: 10px 0;
        }
        
        .coins-field-group {
            margin-bottom: 15px;
        }
        
        .coins-field-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
            font-size: 13px;
        }
        
        .coins-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .coins-input:focus {
            outline: none;
            border-color: #da0480;
            box-shadow: 0 0 0 3px rgba(218, 4, 128, 0.1);
        }
        
        .coins-help-text {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .coins-info-box {
            padding: 12px;
            background: rgba(218, 4, 128, 0.08);
            border-radius: 8px;
            border-left: 4px solid #da0480;
            margin-top: 15px;
        }
        
        .coins-info-box p {
            margin: 5px 0;
            font-size: 12px;
            color: #374151;
        }
        
        .coins-info-box strong {
            color: #da0480;
        }
    </style>
    
    <div class="coins-metabox">
        <div class="coins-field-group">
            <label class="coins-field-label" for="costo_coins">
                Costo en Coins
            </label>
            <input 
                type="number" 
                id="costo_coins" 
                name="costo_coins" 
                value="<?php echo esc_attr($costo_coins); ?>" 
                min="0" 
                step="0.5"
                class="coins-input"
                placeholder="0"
            />
            <p class="coins-help-text">
                💡 Establece cuántos coins necesita un usuario para canjear este producto.
                Deja en <strong>0</strong> si no es canjeable con coins.
            </p>
        </div>
        
        <?php if ($costo_coins > 0) : ?>
        <div class="coins-info-box">
            <p><strong>✅ Producto canjeable</strong></p>
            <p>Los usuarios podrán canjear este producto con <strong><?php echo $coins_manager->format_coins($costo_coins); ?> coins</strong>.</p>
        </div>
        <?php else : ?>
        <div class="coins-info-box">
            <p><strong>⛔ No canjeable</strong></p>
            <p>Este producto no puede ser canjeado con coins. Solo compra con dinero.</p>
        </div>
        <?php endif; ?>
        
        <div class="coins-info-box" style="margin-top: 15px; background: rgba(59, 130, 246, 0.08); border-left-color: #3b82f6;">
            <p style="color: #1e40af;"><strong>📊 Cómo ganar coins:</strong></p>
            <p style="color: #374151; margin-left: 10px;">• 1 coin por curso premium comprado</p>
            <p style="color: #374151; margin-left: 10px;">• 1 coin por reseña verificada</p>
            <p style="color: #374151; margin-left: 10px;">• 0.5 coins por compartir en redes</p>
        </div>
    </div>
    <?php
}

/**
 * Guardar metabox de coins
 */
function coins_save_product_metabox($post_id) {
    // Verificar nonce
    if (!isset($_POST['coins_product_costo_nonce']) || 
        !wp_verify_nonce($_POST['coins_product_costo_nonce'], 'coins_save_product_costo')) {
        return;
    }
    
    // Verificar autoguardado
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar costo en coins
    if (isset($_POST['costo_coins'])) {
        $costo_coins = floatval($_POST['costo_coins']);
        
        if ($costo_coins < 0) {
            $costo_coins = 0;
        }
        
        $coins_manager = Coins_Manager::get_instance();
        $coins_manager->set_costo_coins_producto($post_id, $costo_coins);
    }
}
add_action('save_post_product', 'coins_save_product_metabox');

/**
 * Mostrar aviso en producto si es canjeable
 */
function coins_add_product_admin_notice() {
    global $post;
    
    if (!$post || $post->post_type !== 'product') {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($post->ID);
    
    if ($costo_coins > 0) {
        ?>
        <div class="notice notice-info" style="border-left-color: #da0480;">
            <p>
                <strong>🪙 Producto canjeable con coins:</strong>
                Este producto puede ser canjeado por <strong><?php echo $coins_manager->format_coins($costo_coins); ?> coins</strong>.
            </p>
        </div>
        <?php
    }
}
add_action('edit_form_after_title', 'coins_add_product_admin_notice');
?>