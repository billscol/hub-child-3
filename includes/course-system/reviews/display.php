<?php
/**
 * Reviews Display
 * Display de reseñas personalizadas
 * 
 * @package CourseSystem
 * @subpackage Reviews
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtener mejor reseña destacada
 */
function course_get_featured_review($product_id) {
    $args = array(
        'post_id' => $product_id,
        'status' => 'approve',
        'number' => 1,
        'orderby' => 'comment_date_gmt',
        'order' => 'DESC',
        'type' => 'review'
    );
    
    $comments = get_comments($args);
    
    return !empty($comments) ? $comments[0] : null;
}

/**
 * Renderizar estrellas de rating
 */
function course_render_stars($rating) {
    $output = '<div class="rating-stars" style="display: flex; gap: 3px; font-size: 18px;">';
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '<span style="color:#da0480;">★</span>';
        } else {
            $output .= '<span style="color:#4b5563;">☆</span>';
        }
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Renderizar formulario de reseña
 */
function course_render_review_form($product_id, $product_title) {
    if (!is_user_logged_in()) {
        return '<div class="login-message" style="background: rgba(218, 4, 128, 0.1); padding: 15px 20px; border-radius: 8px; border-left: 4px solid #da0480; color: #e2e8f0; margin: 0 0 20px 0;">
            <p style="margin:0;">Debes <label for="sp-modal-main" style="color: #da0480; cursor: pointer; font-weight: 700; transition: color 0.2s; text-decoration: underline;">iniciar sesión</label> para publicar una valoración.</p>
        </div>';
    }
    
    $current_user = wp_get_current_user();
    
    ob_start();
    ?>
    <form method="post">
        <!-- Calificación con Estrellas -->
        <div class="form-group">
            <label class="form-label">⭐ ¿Cuántas estrellas le das?</label>
            <div class="star-rating-input">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <span class="star-click" data-rating="<?php echo $i; ?>" data-product="<?php echo $product_id; ?>">☆</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating-<?php echo $product_id; ?>" value="5">
        </div>
        
        <!-- Nombre -->
        <div class="form-group">
            <label class="form-label">Tu nombre</label>
            <input 
                type="text" 
                name="author" 
                value="<?php echo esc_attr($current_user->display_name); ?>" 
                required 
                class="form-input"
            >
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label class="form-label">Tu email</label>
            <input 
                type="email" 
                name="email" 
                value="<?php echo esc_attr($current_user->user_email); ?>" 
                required 
                class="form-input"
            >
        </div>
        
        <!-- Comentario -->
        <div class="form-group">
            <label class="form-label">Tu reseña</label>
            <textarea 
                name="comment" 
                placeholder="Comparte tu experiencia con este curso..." 
                required 
                class="form-textarea"
            ></textarea>
        </div>
        
        <!-- Campos ocultos -->
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <?php wp_nonce_field('submit_review_' . $product_id, 'review_nonce'); ?>
        
        <!-- Botón -->
        <button type="submit" class="submit-review-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Enviar reseña
        </button>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Inicializar estrellas con rating 5
        $('.star-click').each(function() {
            if ($(this).data('rating') <= 5) {
                $(this).html('★');
            }
        });
        
        // Manejar clic en estrellas
        $('.star-click').on('click', function() {
            var rating = $(this).data('rating');
            var productId = $(this).data('product');
            
            // Actualizar valor hidden
            $('#rating-' + productId).val(rating);
            
            // Actualizar visual
            $(this).parent().find('.star-click').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).html('★').addClass('star-active');
                } else {
                    $(this).html('☆').removeClass('star-active');
                }
            });
        });
        
        // Hover en estrellas
        $('.star-click').on('mouseenter', function() {
            var rating = $(this).data('rating');
            
            $(this).parent().find('.star-click').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).html('★');
                } else {
                    $(this).html('☆');
                }
            });
        });
        
        // Restaurar al salir
        $('.star-rating-input').on('mouseleave', function() {
            var productId = $(this).find('.star-click:first').data('product');
            var currentRating = $('#rating-' + productId).val();
            
            $(this).find('.star-click').each(function() {
                if ($(this).data('rating') <= currentRating) {
                    $(this).html('★');
                } else {
                    $(this).html('☆');
                }
            });
        });
    });
    </script>
    <?php
    
    return ob_get_clean();
}
?>