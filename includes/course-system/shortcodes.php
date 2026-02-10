<?php
/**
 * Course System - Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// SHORTCODE: [autor_curso]
// ============================================

add_shortcode('autor_curso', 'show_course_author_shortcode');
function show_course_author_shortcode() {
    ob_start();
    display_course_author_inline();
    return ob_get_clean();
}

function display_course_author_inline() {
    global $product;
    
    if (!is_object($product) || !method_exists($product, 'get_id')) {
        $product = wc_get_product(get_the_ID());
    }
    
    if (!$product) {
        return;
    }
    
    $terms = get_the_terms($product->get_id(), 'course_author');
    
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $image_id = get_term_meta($term->term_id, 'author_image', true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : 'https://via.placeholder.com/24';
            $author_link = get_term_link($term);
            $bio = get_term_meta($term->term_id, 'author_bio', true);
            
            $author_courses_count = get_author_courses_count_cached($term->term_id);
            
            $bio_tooltip = $bio ? wp_trim_words($bio, 25, '...') : '';
            ?>
            <a href="<?php echo esc_url($author_link); ?>" 
               class="author-badge-responsive"
               role="link"
               aria-label="Ver perfil del autor <?php echo esc_attr($term->name); ?><?php echo $author_courses_count > 1 ? ' - ' . $author_courses_count . ' cursos disponibles' : ''; ?>"
               <?php if ($bio_tooltip) { ?>data-bio="<?php echo esc_attr($bio_tooltip); ?>"<?php } ?>>
                
                <img src="<?php echo esc_url($image_url); ?>" 
                     alt="Foto de <?php echo esc_attr($term->name); ?>" 
                     class="author-avatar"
                     loading="lazy"
                     decoding="async">
                
                <span class="author-text-full">Creado por <?php echo esc_html($term->name); ?></span>
                <span class="author-text-medium">Por <?php echo esc_html($term->name); ?></span>
                <span class="author-text-short"><?php echo esc_html($term->name); ?></span>
            </a>
            <?php
            
            // Schema Markup
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $term->name,
                'url' => get_term_link($term),
                'image' => $image_url,
                'jobTitle' => 'Instructor',
            );
            
            if ($bio) {
                $schema['description'] = wp_trim_words($bio, 50);
            }
            ?>
            <script type="application/ld+json">
            <?php echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
            </script>
            <?php
        }
    }
}

// ============================================
// SHORTCODE: [calificacion_curso]
// ============================================

add_shortcode('calificacion_curso', 'show_course_rating_badge');
function show_course_rating_badge($atts) {
    global $product;
    
    if (!is_object($product) || !method_exists($product, 'get_id')) {
        $product = wc_get_product(get_the_ID());
    }
    
    if (!$product) {
        return '';
    }
    
    $product_id = $product->get_id();
    
    // Verificar caché de calificación
    $cache_key = 'product_rating_' . $product_id;
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        $total_reviews = $cached_data['total'];
        $avg_rating = $cached_data['average'];
    } else {
        $args = array(
            'post_id' => $product_id,
            'status' => 'approve',
            'type' => 'review'
        );
        
        $comments = get_comments($args);
        $total_reviews = count($comments);
        
        if ($total_reviews > 0) {
            $total_rating = 0;
            foreach ($comments as $comment) {
                $rating = get_comment_meta($comment->comment_ID, 'rating', true);
                $total_rating += (int)$rating;
            }
            $avg_rating = $total_rating / $total_reviews;
        } else {
            $avg_rating = 0;
        }
        
        // Guardar en caché por 6 horas
        set_transient($cache_key, array(
            'total' => $total_reviews,
            'average' => $avg_rating
        ), 6 * HOUR_IN_SECONDS);
    }
    
    $atts = shortcode_atts(array(
        'show_average' => 'no',
        'show_count' => 'no',
    ), $atts);
    
    // Calcular estrellas
    if ($total_reviews > 0) {
        $full_stars = floor($avg_rating);
        $decimal = $avg_rating - $full_stars;
        $has_half = ($decimal >= 0.3 && $decimal < 0.8);
        $empty_stars = 5 - $full_stars - ($has_half ? 1 : 0);
        
        if ($decimal >= 0.8) {
            $full_stars++;
            $has_half = false;
            $empty_stars = 5 - $full_stars;
        }
    } else {
        $full_stars = 0;
        $has_half = false;
        $empty_stars = 5;
    }
    
    ob_start();
    ?>
    <div class="calificacion-curso-badge" role="img" aria-label="<?php echo $total_reviews > 0 ? 'Calificación ' . number_format($avg_rating, 1) . ' de 5 estrellas basada en ' . $total_reviews . ' reseñas' : 'Sin reseñas aún'; ?>">
        
        <div class="rating-stars">
            <?php 
            for ($i = 1; $i <= $full_stars; $i++) {
                echo '<span class="star-full">★</span>';
            }
            
            if ($has_half) {
                echo '<span class="star-half">
                    <span class="star-half-fill">★</span>
                    ★
                </span>';
            }
            
            for ($i = 1; $i <= $empty_stars; $i++) {
                echo '<span class="star-empty">★</span>';
            }
            ?>
        </div>
        
        <?php if ($total_reviews > 0) { ?>
            
            <?php if ($atts['show_average'] === 'yes') { ?>
                <span class="rating-average">
                    <?php echo number_format($avg_rating, 1); ?>
                </span>
            <?php } ?>
            
            <?php if ($atts['show_count'] === 'yes') { ?>
                <span class="rating-count">
                    (<?php echo $total_reviews; ?>)
                </span>
                <span class="rating-count-short">
                    (<?php echo $total_reviews; ?>)
                </span>
            <?php } ?>
            
        <?php } else { ?>
            <span class="no-reviews-text">Sin reseñas</span>
        <?php } ?>
    </div>
    
    <?php
    if ($total_reviews > 0) {
        $rating_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'AggregateRating',
            'ratingValue' => number_format($avg_rating, 1),
            'reviewCount' => $total_reviews,
            'bestRating' => '5',
            'worstRating' => '1'
        );
        ?>
        <script type="application/ld+json">
        <?php echo json_encode($rating_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>
        <?php
    }
    
    return ob_get_clean();
}

// ============================================
// SHORTCODE: [fecha_actualizacion]
// ============================================

add_shortcode('fecha_actualizacion', 'show_update_date');
function show_update_date($atts) {
    global $product;
    
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
    
    $custom_date = get_post_meta($product_id, '_product_update_date', true);
    $custom_text = get_post_meta($product_id, '_product_update_text', true);
    
    if (empty($custom_text)) {
        $custom_text = 'Actualizado';
    }
    
    if ($custom_date) {
        $date_formatted = date('d/m/Y', strtotime($custom_date));
        $date_iso = date('c', strtotime($custom_date));
    } else {
        $date_formatted = get_the_modified_date('d/m/Y', $product_id);
        $date_iso = get_the_modified_date('c', $product_id);
    }
    
    ob_start();
    ?>
    <div class="fecha-actualizacion-responsive" role="status" aria-label="<?php echo esc_attr($custom_text . ' ' . $date_formatted); ?>">
        
        <svg xmlns="http://www.w3.org/2000/svg" class="update-icon" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5" role="img" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        
        <time datetime="<?php echo esc_attr($date_iso); ?>">
            <span class="update-text-full">
                <?php echo esc_html($custom_text); ?> <?php echo esc_html($date_formatted); ?>
            </span>
            
            <span class="update-text-medium">
                <?php echo esc_html($date_formatted); ?>
            </span>
            
            <span class="update-text-short">
                <?php echo esc_html($date_formatted); ?>
            </span>
        </time>
    </div>
    <?php
    
    return ob_get_clean();
}


// ============================================
// SHORTCODE: [cursos_autor] - CARRUSEL HORIZONTAL LIMPIO
// ============================================

add_shortcode('cursos_autor', 'author_courses_shortcode');
function author_courses_shortcode($atts) {
    return display_author_courses($atts);
}

function display_author_courses($atts = array()) {
    global $product;

    if (!is_object($product) || !method_exists($product, 'get_id')) {
        $product = wc_get_product(get_the_ID());
    }

    if (!$product) {
        return '';
    }

    $atts = shortcode_atts(array(
        'limit'   => 12,
        'orderby' => 'rand',
        'order'   => 'DESC',
        'title'   => '',
    ), $atts);

    $terms = get_the_terms($product->get_id(), 'course_author');

    if (!$terms || is_wp_error($terms)) {
        return '';
    }

    $author_term = $terms[0];

    if (empty($atts['title'])) {
        $atts['title'] = 'Más cursos de ' . $author_term->name;
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => intval($atts['limit']),
        'post__not_in'   => array($product->get_id()),
        'post_status'    => 'publish',
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
        'tax_query'      => array(
            array(
                'taxonomy' => 'course_author',
                'field'    => 'term_id',
                'terms'    => $author_term->term_id,
            ),
        ),
    );

    $author_products = new WP_Query($args);

    if (!$author_products->have_posts()) {
        return '';
    }

    ob_start();

    $unique_id = 'author-carousel-' . uniqid();
    ?>

    <section class="author-courses-carousel <?php echo esc_attr($unique_id); ?>" style="margin: 60px 0; clear: both;">

        <!-- Título simple -->
        <h3 style="margin: 0 0 24px 0; font-size: 26px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 12px;">
            <svg style="width: 24px; height: 24px; fill: #da0480;" viewBox="0 0 24 24">
                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
            </svg>
            <?php echo esc_html($atts['title']); ?>
        </h3>

        <div class="carousel-wrapper" style="position: relative;">

            <!-- Contenedor scroll horizontal -->
            <div class="courses-scroll">
                <?php
                while ($author_products->have_posts()) {
                    $author_products->the_post();
                    $course_id   = get_the_ID();
                    $course_obj  = wc_get_product($course_id);
                    $thumbnail   = get_the_post_thumbnail_url($course_id, 'medium');
                    $title       = get_the_title();
                    $price_html  = $course_obj ? $course_obj->get_price_html() : '';
                    $permalink   = get_permalink($course_id);
                    ?>

                    <article class="course-card">
                        <a href="<?php echo esc_url($permalink); ?>" class="course-link">
                            <div class="course-thumb">
                                <?php if ($thumbnail) : ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                <?php else : ?>
                                    <div class="course-thumb-placeholder">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="course-content">
                                <h4 class="course-title"><?php echo esc_html($title); ?></h4>

                                <?php if ($price_html) : ?>
                                    <div class="course-footer">
                                        <span class="course-price"><?php echo wp_kses_post($price_html); ?></span>
                                        <span class="course-cta">
                                            Ver curso
                                            <svg viewBox="0 0 24 24">
                                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                                            </svg>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <span class="course-top-line"></span>
                    </article>

                <?php } ?>
            </div>

            <!-- Botones navegación desktop -->
            <button class="carousel-prev" type="button">
                <svg viewBox="0 0 24 24">
                    <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
                </svg>
            </button>

            <button class="carousel-next" type="button">
                <svg viewBox="0 0 24 24">
                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                </svg>
            </button>

        </div>
    </section>

    <style>
        .<?php echo $unique_id; ?> .courses-scroll {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            overflow-y: hidden; /* 👈 evita scroll vertical interno */
            scroll-behavior: smooth;
            padding: 10px 5px 25px 5px;
            margin: 0 -5px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        .<?php echo $unique_id; ?> .courses-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .<?php echo $unique_id; ?> .courses-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .<?php echo $unique_id; ?> .courses-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #da0480, #b00368);
            border-radius: 10px;
        }

        .<?php echo $unique_id; ?> .course-card {
            flex: 0 0 300px; /* ~4 cursos en desktop */
            scroll-snap-align: start;
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            overflow: hidden; /* 👈 nada se desborda */
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .<?php echo $unique_id; ?> .course-link {
            display: flex;
            flex-direction: column;
            height: 100%; /* 👈 el contenido llena la card sin generar scroll interno */
            text-decoration: none;
        }

        .<?php echo $unique_id; ?> .course-thumb {
            width: 100%;
            aspect-ratio: 16 / 9; /* 👈 altura controlada sin fijar px */
            overflow: hidden;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.15), rgba(218, 4, 128, 0.08));
        }

        .<?php echo $unique_id; ?> .course-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            display: block;
        }

        .<?php echo $unique_id; ?> .course-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .<?php echo $unique_id; ?> .course-thumb-placeholder svg {
            width: 60px;
            height: 60px;
            fill: rgba(218, 4, 128, 0.35);
        }

        .<?php echo $unique_id; ?> .course-content {
            padding: 16px 16px 18px 16px;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto; /* 👈 se adapta a la altura del contenido */
        }

        .<?php echo $unique_id; ?> .course-title {
            margin: 0 0 10px 0;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
            max-height: 3.2em; /* ~2 líneas */
            overflow: hidden;
        }

        .<?php echo $unique_id; ?> .course-footer {
            margin-top: auto; /* 👈 baja el footer sin forzar altura fija */
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .<?php echo $unique_id; ?> .course-price {
            color: #da0480;
            font-weight: 800;
            font-size: 16px;
        }

        .<?php echo $unique_id; ?> .course-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #da0480;
            border: 1px solid rgba(218, 4, 128, 0.3);
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.15), rgba(218, 4, 128, 0.08));
        }

        .<?php echo $unique_id; ?> .course-cta svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        .<?php echo $unique_id; ?> .course-top-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #da0480, #b00368);
            opacity: 0;
            transition: opacity 0.3s;
        }

        /* Hover desktop */
        @media (hover: hover) and (pointer: fine) {
            .<?php echo $unique_id; ?> .course-card:hover {
                transform: translateY(-6px);
                border-color: rgba(218, 4, 128, 0.4);
                box-shadow: 0 14px 36px rgba(218, 4, 128, 0.22);
            }

            .<?php echo $unique_id; ?> .course-card:hover .course-thumb img {
                transform: scale(1.06);
            }

            .<?php echo $unique_id; ?> .course-card:hover .course-top-line {
                opacity: 1;
            }
        }

        .<?php echo $unique_id; ?> .carousel-prev,
        .<?php echo $unique_id; ?> .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #da0480, #b00368);
            box-shadow: 0 8px 24px rgba(218, 4, 128, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
            z-index: 5;
            color: #fff;
        }

        .<?php echo $unique_id; ?> .carousel-prev {
            left: -18px;
            opacity: 0;
        }

        .<?php echo $unique_id; ?> .carousel-next {
            right: -18px;
        }

        .<?php echo $unique_id; ?> .carousel-prev svg,
        .<?php echo $unique_id; ?> .carousel-next svg {
            width: 22px;
            height: 22px;
            fill: currentColor;
        }

        .<?php echo $unique_id; ?> .carousel-wrapper:hover .carousel-prev,
        .<?php echo $unique_id; ?> .carousel-wrapper:hover .carousel-next {
            opacity: 1;
        }

        .<?php echo $unique_id; ?> .carousel-prev:hover,
        .<?php echo $unique_id; ?> .carousel-next:hover {
            transform: translateY(-50%) scale(1.08);
            box-shadow: 0 12px 32px rgba(218, 4, 128, 0.4);
        }

        /* Responsive: tablet */
        @media (max-width: 1024px) {
            .<?php echo $unique_id; ?> .course-card {
                flex: 0 0 260px;
            }
        }

        /* Responsive: mobile */
        @media (max-width: 768px) {
            .<?php echo $unique_id; ?> .course-card {
                flex: 0 0 75vw; /* ancho relativo, sin forzar altura */
            }

            .<?php echo $unique_id; ?> .carousel-prev,
            .<?php echo $unique_id; ?> .carousel-next {
                display: none;
            }

            .<?php echo $unique_id; ?> .course-title {
                max-height: none; /* deja al título crecer si es necesario */
            }
        }

        @media (max-width: 480px) {
            .<?php echo $unique_id; ?> .course-card {
                flex: 0 0 82vw;
            }
        }
    </style>

    <script>
        (function() {
            const wrapper  = document.querySelector('.<?php echo $unique_id; ?> .carousel-wrapper');
            if (!wrapper) return;

            const carousel = wrapper.querySelector('.courses-scroll');
            const prevBtn  = wrapper.querySelector('.carousel-prev');
            const nextBtn  = wrapper.querySelector('.carousel-next');

            if (!carousel || !prevBtn || !nextBtn) return;

            const step = 320; // px a desplazar por click

            prevBtn.addEventListener('click', function() {
                carousel.scrollBy({ left: -step, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', function() {
                carousel.scrollBy({ left: step, behavior: 'smooth' });
            });

            function updateButtons() {
                const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth;

                if (carousel.scrollLeft <= 10) {
                    prevBtn.style.opacity = '0';
                } else {
                    prevBtn.style.opacity = '1';
                }

                if (carousel.scrollLeft >= maxScrollLeft - 10) {
                    nextBtn.style.opacity = '0';
                } else {
                    nextBtn.style.opacity = '1';
                }
            }

            carousel.addEventListener('scroll', updateButtons);
            window.addEventListener('resize', updateButtons);

            updateButtons();
        })();
    </script>

    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
