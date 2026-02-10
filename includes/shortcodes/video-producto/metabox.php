<?php
/**
 * Metabox para agregar video al producto
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// 1. Crear Meta Box visible para el video del producto
function video_producto_meta_box() {
    add_meta_box(
        'video_producto_meta_box',
        '<span style="color: #da0480;">⏯</span> Video del Producto',
        'video_producto_meta_box_html',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'video_producto_meta_box');

// 2. HTML del Meta Box con previsualización
function video_producto_meta_box_html($post) {
    $video_url = get_post_meta($post->ID, '_video_url_producto', true);
    wp_nonce_field('video_producto_nonce_action', 'video_producto_nonce');
    ?>
    <div style="padding: 10px;">
        <p style="margin-bottom: 10px;">
            <strong>URL del Video:</strong><br>
            <input type="url" 
                   id="video_url_producto" 
                   name="_video_url_producto" 
                   value="<?php echo esc_attr($video_url); ?>" 
                   placeholder="https://ejemplo.com/video.mp4"
                   style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
        </p>
        
        <p style="font-size: 11px; color: #666; margin: 10px 0;">
            <strong>ID del Producto:</strong> <?php echo $post->ID; ?><br>
            <strong>Estado:</strong> 
            <?php 
            if (!empty($video_url)) {
                echo '<span style="color: green;">✓ Video guardado</span>';
            } else {
                echo '<span style="color: orange;">⚠ Sin video</span>';
            }
            ?>
        </p>
        
        <?php if (!empty($video_url)): ?>
            <div id="video-preview" style="margin-top: 10px; border: 2px solid #da0480; border-radius: 8px; overflow: hidden;">
                <video style="width: 100%; height: auto; display: block;" controls>
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                </video>
            </div>
            
            <p style="margin-top: 10px;">
                <button type="button" 
                        id="eliminar-video-btn" 
                        class="button button-secondary" 
                        style="width: 100%; background: #dc3232; color: white; border-color: #dc3232;">
                    🗑️ Eliminar Video
                </button>
            </p>
        <?php else: ?>
            <p style="background: #f0f0f0; padding: 10px; border-radius: 4px; text-align: center; color: #666;">
                <em>No hay video agregado</em><br>
                <small>Se mostrará la imagen del producto</small>
            </p>
        <?php endif; ?>
        
        <p style="margin-top: 15px; padding: 10px; background: #fff8dc; border-left: 3px solid #da0480; font-size: 11px;">
            <strong>💡 Nota:</strong> El video se reproduce en silencio 10 segundos. Al hacer clic se abre en popup.
        </p>
    </div>
    
    <script>
        jQuery(document).ready(function($) {
            $('#eliminar-video-btn').on('click', function() {
                if (confirm('¿Estás seguro de eliminar este video?')) {
                    $('#video_url_producto').val('');
                    $('#video-preview').fadeOut(300);
                    $(this).parent().fadeOut(300);
                    alert('Video eliminado. Haz clic en "Actualizar" para guardar los cambios.');
                }
            });
            
            $('#video_url_producto').on('change paste', function() {
                var url = $(this).val();
                if (url) {
                    $('#video-preview').remove();
                    $(this).parent().after(
                        '<div id="video-preview" style="margin-top: 10px; border: 2px solid #da0480; border-radius: 8px; overflow: hidden;">' +
                        '<video style="width: 100%; height: auto; display: block;" controls>' +
                        '<source src="' + url + '" type="video/mp4">' +
                        '</video></div>'
                    );
                }
            });
        });
    </script>
    <?php
}

// 3. Guardar el video del producto
function guardar_video_producto($post_id) {
    if (!isset($_POST['video_producto_nonce']) || !wp_verify_nonce($_POST['video_producto_nonce'], 'video_producto_nonce_action')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['_video_url_producto'])) {
        $video_url = sanitize_text_field($_POST['_video_url_producto']);
        
        if (!empty($video_url)) {
            update_post_meta($post_id, '_video_url_producto', esc_url_raw($video_url));
        } else {
            delete_post_meta($post_id, '_video_url_producto');
        }
    }
}
add_action('save_post_product', 'guardar_video_producto');
?>