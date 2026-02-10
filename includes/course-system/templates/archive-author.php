<?php
/**
 * Template para página de autor
 * Diseño moderno consistente con el grid de la tienda
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Forzar eliminar solo la barra de título del tema, sin tocar el header/menu
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!document.body.classList.contains('tax-course_author')) {
        return;
    }

    // Eliminar todos los bloques .titlebar-inner (el H1 feo del tema)
    document.querySelectorAll('.titlebar-inner').forEach(function(el) {
        el.parentNode.removeChild(el);
    });
});
</script>
<?php

// Obtener el término del autor actual
$term = get_queried_object();
$author_id = $term->term_id;
$author_name = $term->name;
$author_bio = get_term_meta($author_id, 'author_bio', true);
$author_image_id = get_term_meta($author_id, 'author_image', true);
$author_image_url = $author_image_id ? wp_get_attachment_url($author_image_id) : 'https://via.placeholder.com/150';
$courses_count = get_author_courses_count_cached($author_id);

// Query de cursos del autor con paginación
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'tax_query'      => array(
        array(
            'taxonomy' => 'course_author',
            'field'    => 'term_id',
            'terms'    => $author_id,
        ),
    ),
);

$author_courses = new WP_Query($args);
?>

<style>
/* Fondo general solo para la página de autor */
body.tax-course_author {
    background: #0a0a0a !important;
}

/* Titlebar del theme en página de autor */
body.tax-course_author .titlebar {
    background-color: #171717 !important;
}

/* Contenedor principal de la página de autor */
.author-page-wrapper {
    font-family: "Space Grotesk", -apple-system, BlinkMacSystemFont, sans-serif;
    padding: 60px 20px;
    max-width: 1400px;
    margin: 0 auto;
    min-height: calc(100vh - 200px);
}

/* Header del autor */
.author-hero {
    background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
    border: 1.5px solid rgba(218,4,128,.3);
    border-radius: 20px;
    padding: 50px;
    margin-bottom: 50px;
    display: flex;
    align-items: center;
    gap: 40px;
    box-shadow: 0 8px 32px rgba(218,4,128,0.15);
    position: relative;
    overflow: hidden;
}

.author-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #da0480, #b00368, #da0480);
    background-size: 200% 100%;
    animation: gradientMove 3s ease infinite;
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.author-avatar-wrapper {
    flex-shrink: 0;
    position: relative;
}

.author-avatar-large {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(218, 4, 128, 0.5);
    box-shadow: 0 12px 40px rgba(218, 4, 128, 0.4);
    transition: transform 0.3s;
}

.author-avatar-large:hover {
    transform: scale(1.05);
}

.author-info {
    flex: 1;
}

.author-name {
    color: #fff;
    font-size: 42px;
    font-weight: 800;
    margin: 0 0 16px 0;
    line-height: 1.2;
    background: linear-gradient(135deg, #fff, #e5e7eb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.author-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.author-badge-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(218, 4, 128, 0.15);
    border: 1px solid rgba(218, 4, 128, 0.4);
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    color: #da0480;
}

.author-badge-item svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.author-bio {
    color: #9ca3af;
    font-size: 17px;
    line-height: 1.7;
    margin: 0;
    max-width: 800px;
}

/* Grid de cursos */
.courses-section-title {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 30px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.courses-section-title::before {
    content: '';
    width: 6px;
    height: 36px;
    background: linear-gradient(180deg, #da0480, #b00368);
    border-radius: 3px;
}

.author-courses-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-bottom: 40px;
}

.curso-card {
    background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
    border: 1.5px solid rgba(218,4,128,.15);
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    position: relative;
}

.curso-card:hover {
    transform: translateY(-6px);
    border-color: #da0480;
    box-shadow: 0 12px 32px rgba(218,4,128,0.25);
}

.curso-imagen {
    position: relative;
    padding-top: 56.25%;
    overflow: hidden;
    background: #000;
}

.curso-imagen img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.curso-card:hover .curso-imagen img {
    transform: scale(1.08);
}

.curso-wishlist {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 3;
}

.curso-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    z-index: 2;
}

.curso-badge.premium {
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #fff;
    box-shadow: 0 3px 12px rgba(218,4,128,0.5);
}

.curso-badge.gratis {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    box-shadow: 0 3px 12px rgba(102,126,234,0.5);
}

.curso-contenido {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.curso-titulo {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 42px;
}

.curso-titulo a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s;
}

.curso-titulo a:hover {
    color: #da0480;
}

.curso-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}

.curso-stars {
    display: flex;
    gap: 2px;
}

.star {
    font-size: 15px;
}

.star.full {
    color: #fbbf24;
}

.star.empty {
    color: rgba(255,255,255,0.15);
}

.rating-count {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

.curso-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.curso-precio {
    display: flex;
    align-items: center;
    gap: 10px;
}

.precio-actual {
    font-size: 22px;
    font-weight: 800;
    color: #da0480;
}

.precio-original {
    font-size: 16px;
    font-weight: 600;
    color: #6b7280;
    text-decoration: line-through;
}

.precio-coins {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 20px;
    font-weight: 700;
    color: #667eea;
}

.precio-coins img {
    width: 22px;
    height: 22px;
}

.curso-acciones {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.curso-btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    text-align: center;
}

.curso-btn-comprar {
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #fff;
    box-shadow: 0 4px 16px rgba(218,4,128,0.35);
}

.curso-btn-comprar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(218,4,128,0.5);
    color: #fff;
}

.curso-btn-ver {
    background: rgba(255,255,255,0.05);
    color: #e5e7eb;
    border: 1px solid rgba(255,255,255,0.1);
}

.curso-btn-ver:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(218,4,128,0.4);
    color: #da0480;
    transform: translateY(-2px);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
    border: 1.5px solid rgba(218,4,128,.15);
    border-radius: 16px;
}

.empty-state svg {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: #fff;
    font-size: 24px;
    margin-bottom: 10px;
}

.empty-state p {
    color: #9ca3af;
    font-size: 16px;
}

/* Paginación */
.author-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.author-pagination a,
.author-pagination span {
    min-width: 40px;
    height: 40px;
    padding: 0 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(218,4,128,0.15);
    border: 1px solid rgba(218,4,128,0.3);
    border-radius: 8px;
    color: #da0480;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s;
}

.author-pagination a:hover {
    background: rgba(218,4,128,0.25);
    border-color: #da0480;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(218,4,128,0.3);
}

.author-pagination .current {
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #fff;
    border-color: transparent;
}

/* Responsive */
@media (max-width: 1024px) {
    .author-courses-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }
    
    .author-hero {
        padding: 40px 30px;
        flex-direction: column;
        text-align: center;
    }
    
    .author-avatar-large {
        width: 140px;
        height: 140px;
    }
    
    .author-name {
        font-size: 32px;
    }
    
    .author-badges {
        justify-content: center;
    }
    
    .author-bio {
        text-align: center;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .author-page-wrapper {
        padding: 40px 15px;
    }
    
    .author-hero {
        padding: 30px 20px;
    }
    
    .author-avatar-large {
        width: 120px;
        height: 120px;
    }
    
    .author-name {
        font-size: 28px;
    }
    
    .courses-section-title {
        font-size: 24px;
    }
}

@media (max-width: 640px) {
    .author-courses-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
    
    .author-avatar-large {
        width: 100px;
        height: 100px;
    }
    
    .author-name {
        font-size: 24px;
    }
    
    .author-badge-item {
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .author-bio {
        font-size: 15px;
    }
    
    .courses-section-title {
        font-size: 20px;
    }
}
</style>

<div class="author-page-wrapper">
    
    <!-- Hero del autor -->
    <div class="author-hero">
        <div class="author-avatar-wrapper">
            <img src="<?php echo esc_url($author_image_url); ?>" 
                 alt="<?php echo esc_attr($author_name); ?>" 
                 class="author-avatar-large"
                 loading="eager">
        </div>
        
        <div class="author-info">
            <h1 class="author-name"><?php echo esc_html($author_name); ?></h1>
            
            <div class="author-badges">
                <span class="author-badge-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Instructor
                </span>
                
                <span class="author-badge-item" style="background: rgba(102, 126, 234, 0.15); border-color: rgba(102, 126, 234, 0.4); color: #667eea;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <?php echo $courses_count; ?> <?php echo $courses_count === 1 ? 'curso' : 'cursos'; ?>
                </span>
            </div>
            
            <?php if (!empty($author_bio)) : ?>
                <p class="author-bio"><?php echo esc_html($author_bio); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Grid de cursos -->
    <?php if ($author_courses->have_posts()) : ?>
        
        <h2 class="courses-section-title">Cursos de <?php echo esc_html($author_name); ?></h2>
        
        <div class="author-courses-grid">
            <?php
            while ($author_courses->have_posts()) : $author_courses->the_post();
                $product = wc_get_product(get_the_ID());
                $product_id = get_the_ID();
                $es_premium = has_term('premium', 'product_cat', $product_id);
                $es_gratis  = has_term('gratis', 'product_cat', $product_id);
                
                // Calificación
                $rating      = $product->get_average_rating();
                $rating_count = $product->get_rating_count();
                
                // Precios
                $precio_regular = $product->get_regular_price();
                $precio_venta   = $product->get_sale_price();
                $tiene_descuento = !empty($precio_venta) && $precio_venta < $precio_regular;
                
                // Checkout URL
                $checkout_url = wc_get_checkout_url() . '?add-to-cart=' . $product_id;
            ?>
                <div class="curso-card">
                    <div class="curso-imagen">
                        <div class="curso-wishlist">
                            <?php echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]'); ?>
                        </div>
                        
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else : ?>
                                <img src="https://via.placeholder.com/600x400/171717/da0480?text=Curso" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </a>
                        
                        <?php if ($es_premium) : ?>
                            <span class="curso-badge premium">Premium</span>
                        <?php elseif ($es_gratis) : ?>
                            <span class="curso-badge gratis">Gratis con coins</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="curso-contenido">
                        <h3 class="curso-titulo">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <?php if ($rating > 0) : 
                            $full_stars  = floor($rating);
                            $empty_stars = 5 - ceil($rating);
                        ?>
                            <div class="curso-rating">
                                <div class="curso-stars">
                                    <?php
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<span class="star full">★</span>';
                                    }
                                    
                                    if ($rating - $full_stars >= 0.5) {
                                        echo '<span class="star full">★</span>';
                                    }
                                    
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<span class="star empty">★</span>';
                                    }
                                    ?>
                                </div>
                                <span class="rating-count">(<?php echo $rating_count; ?>)</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="curso-footer">
                            <div class="curso-precio">
                                <?php if ($es_gratis && function_exists('coins_manager')) : 
                                    $costo = coins_manager()->get_costo_coins_producto($product_id);
                                ?>
                                    <span class="precio-coins">
                                        <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins">
                                        <?php echo esc_html(coins_manager()->format_coins($costo)); ?> Coins
                                    </span>
                                <?php else : ?>
                                    <span class="precio-actual">
                                        <?php echo wc_price($tiene_descuento ? $precio_venta : $precio_regular); ?>
                                    </span>
                                    <?php if ($tiene_descuento) : ?>
                                        <span class="precio-original">
                                            <?php echo wc_price($precio_regular); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="curso-acciones">
                                <a href="<?php echo esc_url($checkout_url); ?>" class="curso-btn curso-btn-comprar">
                                    Comprar ahora
                                </a>
                                <a href="<?php the_permalink(); ?>" class="curso-btn curso-btn-ver">
                                    Ver curso
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Paginación
        if ($author_courses->max_num_pages > 1) :
            $big = 999999999;
            echo '<div class="author-pagination">';
            echo paginate_links(array(
                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'    => '?paged=%#%',
                'current'   => max(1, $paged),
                'total'     => $author_courses->max_num_pages,
                'prev_text' => '‹',
                'next_text' => '›',
                'type'      => 'plain',
            ));
            echo '</div>';
        endif;
        ?>
        
    <?php else : ?>
        
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            <h3>Sin cursos disponibles</h3>
            <p>Este autor aún no tiene cursos publicados.</p>
        </div>
        
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>
