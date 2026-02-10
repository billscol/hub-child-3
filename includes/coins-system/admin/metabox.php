<?php
/**
 * Metabox de Coins en Productos
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox de coins
 */
function add_coins_metabox() {
    add_meta_box(
        'product_coins',
        '🪙 Configuración de Coins',
        'render_coins_metabox',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_coins_metabox');

/**
 * Renderizar metabox
 */
function render_coins_metabox($post) {
    wp_nonce_field('coins_metabox', 'coins_metabox_nonce');
    
    $coins_requeridos = get_post_meta($post->ID, '_coins_requeridos', true);
    $es_canjeable = !empty($coins_requeridos);
    
    ?>
    <div style="padding: 10px;">
        <p>
            <label>
                <input type="checkbox" 
                       name="_producto_canjeable_coins" 
                       value="1" 
                       <?php checked($es_canjeable); ?>
                       onchange="document.getElementById('coins-config').style.display = this.checked ? 'block' : 'none';">
                <strong>Producto canjeable con coins</strong>
            </label>
        </p>
        
        <div id="coins-config" style="display: <?php echo $es_canjeable ? 'block' : 'none'; ?>; margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
            <p>
                <label><strong>Coins requeridos:</strong></label><br>
                <input type="number" 
                       name="_coins_requeridos" 
                       value="<?php echo esc_attr($coins_requeridos); ?>" 
                       min="1" 
                       step="1"
                       placeholder="Ej: 10"
                       style="width: 100%; padding: 8px; margin-top: 5px;">
            </p>
            
            <p style="color: #666; font-size: 12px; margin: 10px 0 0 0;">
                💡 <strong>Nota:</strong> Los usuarios podrán canjear este curso usando sus coins en lugar de pagar.
            </p>
        </div>
    </div>
    <?php
}

/**
 * Guardar metabox
 */
function save_coins_metabox($post_id) {
    if (!isset($_POST['coins_metabox_nonce']) || 
        !wp_verify_nonce($_POST['coins_metabox_nonce'], 'coins_metabox')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar configuración
    if (isset($_POST['_producto_canjeable_coins']) && $_POST['_producto_canjeable_coins'] == '1') {
        $coins = isset($_POST['_coins_requeridos']) ? intval($_POST['_coins_requeridos']) : 0;
        if ($coins > 0) {
            update_post_meta($post_id, '_coins_requeridos', $coins);
        }
    } else {
        delete_post_meta($post_id, '_coins_requeridos');
    }
}
add_action('save_post_product', 'save_coins_metabox');
?>