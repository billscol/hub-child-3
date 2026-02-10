<?php
/**
 * Metabox de Coins en Productos
 * Permite configurar el costo en coins de un producto
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
        'coins_product_metabox',
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
    wp_nonce_field('coins_save_product_meta', 'coins_product_nonce');
    
    $coins_manager = Coins_Manager::get_instance();
    $costo_coins = $coins_manager->get_costo_coins_producto($post->ID);
    
    ?>
    <style>
        .coins-metabox-container {
            padding: 10px 0;
        }
        .coins-field-group {
            margin-bottom: 20px;
        }
        .coins-field-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e1e1e;
            font-size: 14px;
        }
        .coins-field-group input[type="number"] {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #da0480;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            color: #da0480;
        }
        .coins-field-group input[type="number"]:focus {
            outline: none;
            border-color: #b00368;
            box-shadow: 0 0 0 3px rgba(218, 4, 128, 0.1);
        }
        .coins-help-text {
            font-size: 13px;
            color: #757575;
            margin-top: 8px;
            line-height: 1.5;
        }
        .coins-info-box {
            background: rgba(218, 4, 128, 0.08);
            border-left: 4px solid #da0480;
            padding: 12px;
            margin-top: 15px;
            border-radius: 4px;
        }
        .coins-info-box p {
            margin: 0;
            font-size: 13px;
            color: #1e1e1e;
            line-height: 1.6;
        }
        .coins-info-box strong {
            color: #da0480;
        }
        .coins-preview {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 6px;
            margin-top: 10px;
            text-align: center;
        }
        .coins-preview-amount {
            font-size: 24px;
            font-weight: 700;
            color: #da0480;
        }
    </style>
    
    <div class="coins-metabox-container">
        <div class="coins-field-group">
            <label for="costo_coins">
                🪙 Costo en Coins:
            </label>
            <input 
                type="number" 
                id="costo_coins" 
                name="costo_coins" 
                value="<?php echo esc_attr($costo_coins); ?>" 
                min="0" 
                step="1"
                placeholder="0"
            />
            <p class="coins-help-text">
                💡 <strong>Tip:</strong> Si estableces un valor mayor a 0, los usuarios podrán canjear este producto con sus coins.
            </p>
        </div>
        
        <?php if ($costo_coins > 0): ?>
        <div class="coins-preview">
            <div>Este producto cuesta:</div>
            <div class="coins-preview-amount"><?php echo $coins_manager->format_coins($costo_coins); ?> 🪙</div>
        </div>
        <?php endif; ?>
        
        <div class="coins-info-box">
            <p>
                <strong>ℹ️ Nota:</strong> Los productos con costo en coins deben tener precio <strong>$0</strong> para que funcione el sistema de canje.
            </p>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Preview dinámico
        $('#costo_coins').on('input', function() {
            var value = $(this).val();
            var preview = $('.coins-preview');
            var previewAmount = $('.coins-preview-amount');
            
            if (value > 0) {
                if (preview.length === 0) {
                    $('.coins-field-group').after('<div class="coins-preview"><div>Este producto cuesta:</div><div class="coins-preview-amount"></div></div>');
                    preview = $('.coins-preview');
                    previewAmount = $('.coins-preview-amount');
                }
                
                var formatted = parseInt(value).toLocaleString('es-ES');
                previewAmount.text(formatted + ' 🪙');
                preview.show();
            } else {
                preview.hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * Guardar metabox
 */
function coins_save_product_metabox($post_id) {
    // Verificar nonce
    if (!isset($_POST['coins_product_nonce']) || !wp_verify_nonce($_POST['coins_product_nonce'], 'coins_save_product_meta')) {
        return;
    }
    
    // Verificar autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar costo en coins
    if (isset($_POST['costo_coins'])) {
        $costo = floatval($_POST['costo_coins']);
        
        if ($costo < 0) {
            $costo = 0;
        }
        
        $coins_manager = Coins_Manager::get_instance();
        $coins_manager->set_costo_coins_producto($post_id, $costo);
    }
}
add_action('save_post_product', 'coins_save_product_metabox');
?>