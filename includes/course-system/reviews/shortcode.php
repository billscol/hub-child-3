<?php
/**
 * Reviews Shortcode
 * Shortcode [resenas_producto] para mostrar reseñas y formulario
 * 
 * @package CourseSystem
 * @subpackage Reviews
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para mostrar reseñas
 * Uso: [resenas_producto]
 */
function course_reviews_shortcode($atts) {
    global $product;
    
    // Si no hay producto, obtenerlo
    if (!$product) {
        global $post;
        if ($post) {
            $product = wc_get_product($post->ID);
        }
    }
    
    if (!$product) {
        return '';
    }
    
    $product_id = $product->get_id();
    $product_title = $product->get_name();
    
    // Obtener reseña destacada
    $args = array(
        'post_id' => $product_id,
        'status' => 'approve',
        'number' => 1,
        'orderby' => 'comment_date_gmt',
        'order' => 'DESC',
        'type' => 'review'
    );
    
    $comments = get_comments($args);
    
    // Calcular total de reseñas
    $all_comments = get_comments(array(
        'post_id' => $product_id,
        'status' => 'approve',
        'type' => 'review'
    ));
    
    $total_reviews = count($all_comments);
    
    ob_start();
    ?>
    <style>
    .resenas-container {
        background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e;
        padding: 35px;
        border-radius: 16px;
        color: #fff;
        border: 1px solid rgba(218, 4, 128, 0.3);
        box-shadow: 0 4px 20px rgba(218, 4, 128, 0.1);
    }
    
    .resena-destacada {
        margin-bottom: 40px;
        padding-bottom: 40px;
        border-bottom: 2px solid rgba(218, 4, 128, 0.2);
    }
    
    .resena-destacada h2 {
        margin: 0 0 25px 0;
        font-size: 24px;
        font-weight: 800;
        color: #da0480;
        line-height: 1.4;
    }
    
    .resena-item {
        display: flex;
        gap: 20px;
        align-items: flex-start;
        background: rgba(218, 4, 128, 0.05);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid rgba(218, 4, 128, 0.2);
    }
    
    .resena-item .avatar {
        flex-shrink: 0;
    }
    
    .resena-item .avatar img {
        border-radius: 50%;
        border: 2px solid rgba(218, 4, 128, 0.4);
    }
    
    .resena-content {
        flex: 1;
    }
    
    .resena-author-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    
    .resena-author-name {
        font-weight: 700;
        font-size: 16px;
        color: #fff;
    }
    
    .resena-date {
        color: #9ca3af;
        font-size: 13px;
    }
    
    .rating-stars {
        display: flex;
        gap: 3px;
        margin-bottom: 10px;
        font-size: 18px;
    }
    
    .resena-text {
        margin: 0;
        color: #e2e8f0;
        line-height: 1.7;
        font-size: 15px;
    }
    
    .resena-formulario {
        background: rgba(218, 4, 128, 0.05);
        padding: 30px;
        border-radius: 12px;
        border: 1px solid rgba(218, 4, 128, 0.2);
    }
    
    .resena-formulario h3 {
        margin: 0 0 25px 0;
        font-size: 22px;
        font-weight: 800;
        color: #da0480;
        line-height: 1.4;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #e2e8f0;
        font-size: 15px;
    }
    
    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid rgba(218, 4, 128, 0.3);
        border-radius: 8px;
        background: rgba(26, 38, 64, 0.8);
        color: #fff;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.3s;
    }
    
    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #da0480;
        background: rgba(26, 38, 64, 1);
        box-shadow: 0 0 0 4px rgba(218, 4, 128, 0.1);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }
    
    .star-rating-input {
        display: flex;
        gap: 8px;
        font-size: 32px;
    }
    
    .star-click {
        cursor: pointer;
        color: #4b5563;
        transition: all 0.2s;
    }
    
    .star-click:hover {
        transform: scale(1.1);
    }
    
    .star-active {
        color: #da0480;
    }
    
    .submit-review-btn {
        background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
        color: #fff;
        border: none;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(218, 4, 128, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .submit-review-btn:hover {
        background: linear-gradient(135deg, #b00368 0%, #8a0252 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(218, 4, 128, 0.5);
    }
    
    .login-message {
        background: rgba(218, 4, 128, 0.1);
        padding: 15px 20px;
        border-radius: 8px;
        border-left: 4px solid #da0480;
        color: #e2e8f0;
        margin: 0 0 20px 0;
    }
    
    .login-message label {
        color: #da0480;
        cursor: pointer;
        font-weight: 700;
        transition: color 0.2s;
        text-decoration: underline;
    }
    
    .login-message label:hover {
        color: #ff1fa6;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .resenas-container {
            padding: 25px 20px;
        }
        
        .resena-destacada h2 {
            font-size: 18px;
        }
        
        .resena-item {
            flex-direction: column;
            gap: 15px;
            padding: 18px;
        }
        
        .resena-item .avatar img {
            width: 50px;
            height: 50px;
        }
        
        .resena-author-header {
            margin-bottom: 8px;
        }
        
        .resena-author-name {
            font-size: 15px;
        }
        
        .rating-stars {
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .resena-text {
            font-size: 14px;
        }
        
        .resena-formulario {
            padding: 20px;
        }
        
        .resena-formulario h3 {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .star-rating-input {
            font-size: 28px;
            gap: 6px;
        }
        
        .form-input,
        .form-textarea {
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .submit-review-btn {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
        }
    }
    </style>
    
    <div class="resenas-container">
        <!-- SECCIÓN 1: RESEÑA DESTACADA (Si existe) -->
        <?php if (!empty($comments)) : 
            $comment = $comments[0];
            $rating = get_comment_meta($comment->comment_ID, 'rating', true);
            $author_name = $comment->comment_author;
            $author_email = $comment->comment_author_email;
            $author_avatar = get_avatar($author_email, 60);
            $comment_date = get_comment_date('d/m/Y', $comment->comment_ID);
            $comment_text = $comment->comment_content;
        ?>
            <div class="resena-destacada">
                <h2>🌟 <?php echo esc_html($total_reviews); ?> valoración<?php echo $total_reviews != 1 ? 'es' : ''; ?> en <?php echo esc_html($product_title); ?></h2>
                
                <!-- Reseña Principal -->
                <div class="resena-item">
                    <!-- Avatar -->
                    <div class="avatar">
                        <?php echo wp_kses_post($author_avatar); ?>
                    </div>
                    
                    <!-- Contenido Reseña -->
                    <div class="resena-content">
                        <!-- Encabezado -->
                        <div class="resena-author-header">
                            <span class="resena-author-name"><?php echo esc_html($author_name); ?></span>
                            <span class="resena-date"><?php echo esc_html($comment_date); ?></span>
                        </div>
                        
                        <!-- Estrellas -->
                        <?php 
                        echo '<div class="rating-stars">';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<span style="color:#da0480;">★</span>';
                            } else {
                                echo '<span style="color:#4b5563;">☆</span>';
                            }
                        }
                        echo '</div>';
                        ?>
                        
                        <!-- Texto Comentario -->
                        <p class="resena-text"><?php echo wp_kses_post(nl2br($comment_text)); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- SECCIÓN 2: FORMULARIO PARA NUEVA RESEÑA -->
        <div class="resena-formulario">
            <h3>📝 Aade tu valoración al curso <?php echo esc_html($product_title); ?></h3>
            
            <?php echo course_render_review_form($product_id, $product_title); ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('resenas_producto', 'course_reviews_shortcode');
?>