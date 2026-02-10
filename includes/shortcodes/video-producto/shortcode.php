<?php
/**
 * Shortcode: [video_producto]
 * Muestra video del producto con autoplay y modal
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function shortcode_video_producto($atts) {
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts, 'video_producto');
    
    $product_id = $atts['id'];
    
    // Obtener ID del producto automáticamente
    if (!$product_id || $product_id == 0) {
        global $post, $product;
        
        if (isset($post->ID)) {
            $product_id = $post->ID;
        }
        
        if ((!$product_id || $product_id == 0) && $product && is_a($product, 'WC_Product')) {
            $product_id = $product->get_id();
        }
        
        if ((!$product_id || $product_id == 0) && get_the_ID()) {
            $product_id = get_the_ID();
        }
        
        if ((!$product_id || $product_id == 0) && is_product()) {
            $queried_object = get_queried_object();
            if ($queried_object) {
                $product_id = $queried_object->ID;
            }
        }
    }
    
    if (!$product_id || $product_id == 0) {
        return '';
    }
    
    // Obtener URL del video
    $video_url = get_post_meta($product_id, '_video_url_producto', true);
    
    $unique_id = 'video_modal_' . $product_id . '_' . uniqid();
    
    // SI NO HAY VIDEO: Mostrar solo la imagen
    if (empty($video_url)) {
        $imagen_producto = get_the_post_thumbnail($product_id, 'full', array(
            'style' => 'width: 100%; height: auto; display: block; border-radius: 10px 10px 0 0;'
        ));
        
        if (empty($imagen_producto)) {
            $imagen_producto = '<img src="' . wc_placeholder_img_src('full') . '" alt="Producto sin imagen" style="width: 100%; height: auto; display: block; border-radius: 10px 10px 0 0;">';
        }
        
        return '
        <div class="producto-imagen-container" style="position: relative; width: 100%; overflow: hidden; background: #f5f5f5; border-radius: 10px 10px 0 0;">
            ' . $imagen_producto . '
        </div>';
    }
    
    // SI HAY VIDEO: Mostrar video con autoplay y modal
    return '
    <div class="producto-video-wrapper" style="position: relative; width: 100%;">
        <!-- Video con autoplay en silencio -->
        <div class="producto-video-preview" id="trigger-' . $unique_id . '" style="position: relative; width: 100%; overflow: hidden; background: #000; border-radius: 10px 10px 0 0; cursor: pointer;">
            <video id="preview-' . $unique_id . '" class="video-preview" 
                   style="width: 100%; height: auto; display: block; border-radius: 10px 10px 0 0;" 
                   muted loop playsinline 
                   preload="auto">
                <source src="' . esc_url($video_url) . '" type="video/mp4">
            </video>
            
            <!-- Botón Play -->
            <div id="play-btn-' . $unique_id . '" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: rgba(218, 4, 128, 0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 8px 24px rgba(218, 4, 128, 0.5);">
                <svg style="width: 36px; height: 36px; fill: #fff; margin-left: 5px;" viewBox="0 0 24 24">
                    <polygon points="8 5 19 12 8 19 8 5"></polygon>
                </svg>
            </div>
        </div>
        
        <!-- Modal con video completo -->
        <div id="modal-' . $unique_id . '" class="video-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.95); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
            <div style="position: relative; max-width: 90vw; max-height: 90vh; width: 1200px;">
                <button id="close-' . $unique_id . '" style="position: absolute; top: -50px; right: 0; background: #da0480; color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 24px; line-height: 1; transition: all 0.3s;">×</button>
                <video id="full-' . $unique_id . '" controls style="width: 100%; height: auto; border-radius: 10px; box-shadow: 0 10px 40px rgba(218, 4, 128, 0.4);">
                    <source src="' . esc_url($video_url) . '" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
    
    <script>
    (function() {
        var preview = document.getElementById("preview-' . $unique_id . '");
        var modal = document.getElementById("modal-' . $unique_id . '");
        var fullVideo = document.getElementById("full-' . $unique_id . '");
        var trigger = document.getElementById("trigger-' . $unique_id . '");
        var closeBtn = document.getElementById("close-' . $unique_id . '");
        var playBtn = document.getElementById("play-btn-' . $unique_id . '");
        
        // Autoplay silencioso por 10 segundos
        if (preview) {
            preview.currentTime = 0;
            preview.play();
            setTimeout(function() {
                preview.pause();
            }, 10000);
        }
        
        // Hover en botón play
        if (playBtn) {
            playBtn.addEventListener("mouseenter", function() {
                this.style.transform = "translate(-50%, -50%) scale(1.1)";
            });
            playBtn.addEventListener("mouseleave", function() {
                this.style.transform = "translate(-50%, -50%) scale(1)";
            });
        }
        
        // Abrir modal
        if (trigger && modal) {
            trigger.addEventListener("click", function() {
                modal.style.display = "flex";
                if (fullVideo) {
                    fullVideo.currentTime = 0;
                    fullVideo.play();
                }
                if (preview) {
                    preview.pause();
                }
                document.body.style.overflow = "hidden";
            });
        }
        
        // Cerrar modal
        function cerrarModal() {
            if (modal) {
                modal.style.display = "none";
            }
            if (fullVideo) {
                fullVideo.pause();
            }
            document.body.style.overflow = "auto";
        }
        
        if (closeBtn) {
            closeBtn.addEventListener("click", cerrarModal);
        }
        
        if (modal) {
            modal.addEventListener("click", function(e) {
                if (e.target === this) {
                    cerrarModal();
                }
            });
        }
        
        // Cerrar con ESC
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                cerrarModal();
            }
        });
    })();
    </script>
    ';
}

add_shortcode('video_producto', 'shortcode_video_producto');
?>