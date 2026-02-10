<?php
/**
 * Shortcode: [grid_cursos]
 * Grid de cursos con carga AJAX (columna derecha)
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('grid_cursos', 'grid_cursos_shortcode');

function grid_cursos_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => 12,
        'columnas'       => 3,
        'id'             => 'cursos-grid-ajax'
    ), $atts);

    ob_start();
    ?>
    <div class="cursos-grid-wrapper" id="<?php echo esc_attr($atts['id']); ?>-wrapper">
        
        <!-- Header con ordenar -->
        <div class="cursos-grid-header">
            <div class="header-placeholder"></div>
            <div class="filtro-orden-wrapper">
                <label for="filtro-orden-grid" class="orden-label">Ordenar por:</label>
                <select id="filtro-orden-grid" class="filtro-select filtro-orden">
                    <option value="date">Más recientes</option>
                    <option value="popularity">Más populares</option>
                    <option value="price">Precio: menor a mayor</option>
                    <option value="price-desc">Precio: mayor a menor</option>
                </select>
            </div>
        </div>

        <!-- Grid de cursos -->
        <div id="<?php echo esc_attr($atts['id']); ?>" class="cursos-grid" data-columnas="<?php echo esc_attr($atts['columnas']); ?>" data-posts-per-page="<?php echo esc_attr($atts['posts_per_page']); ?>">
            <div class="cursos-loading">
                <div class="spinner"></div>
                <p>Cargando cursos...</p>
            </div>
        </div>

        <!-- Paginación -->
        <div class="cursos-paginacion"></div>
    </div>

    <style>
    .cursos-grid-wrapper {
        font-family: "Space Grotesk", -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .cursos-grid-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
        border: 1.5px solid rgba(218,4,128,.25);
        border-radius: 10px;
    }

    .header-placeholder {
        flex: 1;
    }

    .filtro-orden-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .orden-label {
        font-size: 14px;
        font-weight: 600;
        color: #9ca3af;
        white-space: nowrap;
    }

    .filtro-orden-wrapper .filtro-select {
        min-width: 200px;
        padding: 10px 14px;
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%239ca3af' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }

    .filtro-orden-wrapper .filtro-select:focus {
        outline: none;
        border-color: #da0480;
        background-color: rgba(0,0,0,0.6);
        box-shadow: 0 0 0 3px rgba(218,4,128,0.15);
    }

    .filtro-orden-wrapper .filtro-select option {
        background: #1a1a1a;
        color: #fff;
    }

    .cursos-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
        margin-bottom: 32px;
        min-height: 400px;
    }

    .cursos-grid[data-columnas="2"] {
        grid-template-columns: repeat(2, 1fr);
    }

    .cursos-grid[data-columnas="4"] {
        grid-template-columns: repeat(4, 1fr);
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

    .curso-card-imagen {
        position: relative;
        padding-top: 56.25%;
        overflow: hidden;
        background: #000;
    }

    .curso-card-imagen img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .curso-card:hover .curso-card-imagen img {
        transform: scale(1.08);
    }

    .curso-card-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        z-index: 2;
    }

    .curso-card-badge.premium {
        background: linear-gradient(135deg, #da0480, #b00368);
        color: #fff;
        box-shadow: 0 3px 12px rgba(218,4,128,0.5);
    }

    .curso-card-badge.gratis {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 3px 12px rgba(102,126,234,0.5);
    }

    /* Wishlist en esquina superior izquierda */
    .curso-wishlist {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 3;
    }

    .curso-wishlist .yith-wcwl-add-button a,
    .curso-wishlist .yith-wcwl-wishlistaddedbrowse a,
    .curso-wishlist .yith-wcwl-wishlistexistsbrowse a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(8px);
        border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,0.2);
        transition: all 0.3s;
        color: #fff !important;
        font-size: 0;
    }

    .curso-wishlist .yith-wcwl-add-button a::before,
    .curso-wishlist .yith-wcwl-wishlistaddedbrowse a::before,
    .curso-wishlist .yith-wcwl-wishlistexistsbrowse a::before {
        content: '♡';
        font-size: 20px;
        line-height: 1;
        font-weight: 400;
    }

    .curso-wishlist .yith-wcwl-wishlistaddedbrowse a::before,
    .curso-wishlist .yith-wcwl-wishlistexistsbrowse a::before {
        content: '♥';
        color: #da0480;
    }

    .curso-wishlist .yith-wcwl-add-button a:hover {
        background: rgba(218,4,128,0.9);
        border-color: #da0480;
        transform: scale(1.1);
    }

    .curso-card-contenido {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .curso-card-titulo {
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

    .curso-card-titulo a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s;
    }

    .curso-card-titulo a:hover {
        color: #da0480;
    }

    /* Calificaciones con estrellas */
    .curso-rating {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .curso-stars {
        display: flex;
        gap: 2px;
    }

    .curso-star {
        color: #fbbf24;
        font-size: 15px;
    }

    .curso-star.empty {
        color: rgba(255,255,255,0.15);
    }

    .curso-rating-count {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 500;
    }

    .curso-card-footer {
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,0.08);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* Precio estilo referencia (izquierda-derecha) */
    .curso-precio-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .curso-precio-wrapper.curso-precio-premium {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
    }

    .curso-precio-actual {
        order: 1;
    }

    .curso-precio-actual-texto {
        font-size: 22px;
        font-weight: 800;
        color: #da0480;
    }

    .curso-precio-original {
        order: 2;
    }

    .curso-precio-original-texto {
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: line-through;
    }

    /* Coins */
    .curso-precio-wrapper.curso-precio-gratis {
        display: flex;
        justify-content: flex-start;
    }

    .curso-precio-coins {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 20px;
        font-weight: 700;
        color: #667eea;
    }

    .curso-precio-coins .coin-icon {
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

    .cursos-loading {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(218,4,128,0.2);
        border-top-color: #da0480;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 16px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .cursos-paginacion {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cursos-paginacion button {
        min-width: 40px;
        height: 40px;
        padding: 0 16px;
        background: rgba(218,4,128,0.15);
        border: 1px solid rgba(218,4,128,0.3);
        border-radius: 8px;
        color: #da0480;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .cursos-paginacion button:hover:not(:disabled) {
        background: rgba(218,4,128,0.25);
        border-color: #da0480;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(218,4,128,0.3);
    }

    .cursos-paginacion button:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .cursos-paginacion button.active {
        background: linear-gradient(135deg, #da0480, #b00368);
        color: #fff;
        border-color: transparent;
    }

    @media (max-width: 1024px) {
        .cursos-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 22px;
        }

        .cursos-grid-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .header-placeholder {
            display: none;
        }

        .filtro-orden-wrapper {
            width: 100%;
        }

        .filtro-orden-wrapper .filtro-select {
            flex: 1;
        }
    }

    @media (max-width: 640px) {
        .cursos-grid-header {
            padding: 14px 16px;
        }

        .orden-label {
            font-size: 13px;
        }

        .filtro-orden-wrapper .filtro-select {
            font-size: 13px;
            min-width: auto;
        }

        .cursos-grid {
            grid-template-columns: 1fr !important;
            gap: 18px;
        }

        .curso-card-contenido {
            padding: 18px;
            gap: 8px;
        }

        .curso-card-titulo {
            font-size: 14px;
            min-height: 20px;
        }

        .curso-precio-actual-texto {
            font-size: 20px;
        }

        .curso-precio-original-texto {
            font-size: 14px;
        }

        .curso-btn {
            padding: 11px 16px;
            font-size: 13px;
        }

        .cursos-paginacion button {
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            font-size: 13px;
        }
    }
    </style>

    <script>
    (function() {
        const gridWrapper = document.getElementById('<?php echo esc_js($atts['id']); ?>-wrapper');
        const grid = document.getElementById('<?php echo esc_js($atts['id']); ?>');
        const paginacionElement = gridWrapper.querySelector('.cursos-paginacion');
        
        let paginaActual = 1;
        const postsPorPagina = parseInt(grid.getAttribute('data-posts-per-page'));

        function obtenerFiltros() {
            const categoriasChecked = Array.from(document.querySelectorAll('.filtro-categoria-check:checked'))
                .map(cb => cb.value);

            return {
                search: document.querySelector('.filtro-search')?.value || '',
                categorias: categoriasChecked.join(','),
                tipo: document.querySelector('.filtro-tipo')?.value || '',
                precio_min: document.querySelector('.precio-slider-min')?.value || '',
                precio_max: document.querySelector('.precio-slider-max')?.value || '',
                orden: document.querySelector('.filtro-orden')?.value || 'date'
            };
        }

        function cargarCursos() {
            const filtros = obtenerFiltros();
            
            grid.innerHTML = '<div class="cursos-loading"><div class="spinner"></div><p>Buscando cursos...</p></div>';

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'filtrar_cursos_grid',
                    search: filtros.search,
                    categorias: filtros.categorias,
                    tipo: filtros.tipo,
                    precio_min: filtros.precio_min,
                    precio_max: filtros.precio_max,
                    orden: filtros.orden,
                    pagina: paginaActual,
                    posts_per_page: postsPorPagina
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    grid.innerHTML = data.data.html;
                    
                    if (data.data.total_pages > 1) {
                        let pagHtml = '';
                        
                        pagHtml += `<button ${paginaActual === 1 ? 'disabled' : ''} onclick="cambiarPagina(${paginaActual - 1})">‹</button>`;
                        
                        for (let i = 1; i <= data.data.total_pages; i++) {
                            if (i === paginaActual) {
                                pagHtml += `<button class="active">${i}</button>`;
                            } else if (Math.abs(i - paginaActual) <= 2 || i === 1 || i === data.data.total_pages) {
                                pagHtml += `<button onclick="cambiarPagina(${i})">${i}</button>`;
                            } else if (Math.abs(i - paginaActual) === 3) {
                                pagHtml += `<span style="color:#9ca3af;padding:0 8px;">...</span>`;
                            }
                        }
                        
                        pagHtml += `<button ${paginaActual === data.data.total_pages ? 'disabled' : ''} onclick="cambiarPagina(${paginaActual + 1})">›</button>`;
                        
                        paginacionElement.innerHTML = pagHtml;
                    } else {
                        paginacionElement.innerHTML = '';
                    }
                } else {
                    grid.innerHTML = '<div class="cursos-loading"><p>No se encontraron cursos.</p></div>';
                }
            })
            .catch(err => {
                grid.innerHTML = '<div class="cursos-loading"><p>Error al cargar cursos.</p></div>';
            });
        }

        window.cambiarPagina = function(pagina) {
            paginaActual = pagina;
            cargarCursos();
            gridWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        document.addEventListener('DOMContentLoaded', function() {
            let searchTimeout;
            const searchInput = document.querySelector('.filtro-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        paginaActual = 1;
                        cargarCursos();
                    }, 500);
                });
            }

            const tipoSelect = document.querySelector('.filtro-tipo');
            if (tipoSelect) {
                tipoSelect.addEventListener('change', function() {
                    paginaActual = 1;
                    cargarCursos();
                });
            }

            const ordenSelect = document.querySelector('.filtro-orden');
            if (ordenSelect) {
                ordenSelect.addEventListener('change', function() {
                    paginaActual = 1;
                    cargarCursos();
                });
            }

            const checkboxes = document.querySelectorAll('.filtro-categoria-check');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    paginaActual = 1;
                    cargarCursos();
                });
            });

            const sliders = document.querySelectorAll('.precio-slider');
            sliders.forEach(slider => {
                let sliderTimeout;
                slider.addEventListener('change', function() {
                    clearTimeout(sliderTimeout);
                    sliderTimeout = setTimeout(() => {
                        paginaActual = 1;
                        cargarCursos();
                    }, 500);
                });
            });

            const btnLimpiar = document.querySelector('.filtro-limpiar');
            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function() {
                    document.querySelector('.filtro-search').value = '';
                    document.querySelector('.filtro-tipo').value = '';
                    document.querySelector('.filtro-orden').value = 'date';
                    
                    checkboxes.forEach(cb => cb.checked = false);
                    
                    const sliderMin = document.querySelector('.precio-slider-min');
                    const sliderMax = document.querySelector('.precio-slider-max');
                    sliderMin.value = sliderMin.getAttribute('data-min');
                    sliderMax.value = sliderMax.getAttribute('data-max');
                    
                    const event = new Event('input');
                    sliderMin.dispatchEvent(event);
                    
                    paginaActual = 1;
                    cargarCursos();
                });
            }

            cargarCursos();
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}



// AJAX Handler - tienda sin productos Dokan usando wc_get_products
add_action('wp_ajax_filtrar_cursos_grid', 'ajax_filtrar_cursos_grid');
add_action('wp_ajax_nopriv_filtrar_cursos_grid', 'ajax_filtrar_cursos_grid');

function ajax_filtrar_cursos_grid() {
    // IDs Dokan que no deben aparecer
    $productos_dokan_sistema = array(1231, 1232);

    $search        = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $categorias    = isset($_POST['categorias']) ? sanitize_text_field($_POST['categorias']) : '';
    $tipo          = isset($_POST['tipo']) ? sanitize_text_field($_POST['tipo']) : '';
    $precio_min    = isset($_POST['precio_min']) ? floatval($_POST['precio_min']) : 0;
    $precio_max    = isset($_POST['precio_max']) ? floatval($_POST['precio_max']) : 999999999;
    $orden         = isset($_POST['orden']) ? sanitize_text_field($_POST['orden']) : 'date';
    $pagina        = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
    $posts_per_page= isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 12;

    $paged_args = array(
        'status'     => 'publish',
        'type'       => 'simple',          // puedes quitar esto si tienes otros tipos
        'limit'      => $posts_per_page,
        'page'       => $pagina,
        'exclude'    => $productos_dokan_sistema,
        'paginate'   => true,              // para tener total y pages
        'visibility' => 'catalog',         // solo productos visibles en catálogo [web:58]
    );

    // Búsqueda por título
    if (!empty($search)) {
        $paged_args['search'] = $search;
    }

    // Categorías seleccionadas en filtros
    if (!empty($categorias)) {
        $categorias_array = explode(',', $categorias);
        $paged_args['category'] = $categorias_array;
    }

    // Tipo (premium / gratis) como categorías adicionales
    if (!empty($tipo)) {
        if (!isset($paged_args['category'])) {
            $paged_args['category'] = array();
        }
        $paged_args['category'][] = $tipo;
    }

    // Rango de precios
    if ($precio_min > 0 || $precio_max < 999999999) {
        $paged_args['min_price'] = $precio_min;
        $paged_args['max_price'] = $precio_max;
    }

    // Orden
    switch ($orden) {
        case 'popularity':
            $paged_args['orderby'] = 'popularity';
            $paged_args['order']   = 'DESC';
            break;
        case 'price':
            $paged_args['orderby'] = 'price';
            $paged_args['order']   = 'ASC';
            break;
        case 'price-desc':
            $paged_args['orderby'] = 'price';
            $paged_args['order']   = 'DESC';
            break;
        default:
            $paged_args['orderby'] = 'date';
            $paged_args['order']   = 'DESC';
    }

    // Obtener productos con wc_get_products
    $results = wc_get_products($paged_args); // devuelve objeto WC_Product_Query paginado [web:55][web:58]

    $products     = $results->products;
    $total        = $results->total;
    $total_pages  = $results->max_num_pages;

    ob_start();

    if (!empty($products)) {
        foreach ($products as $product) {
            $product_id = $product->get_id();

            $es_premium = has_term('premium', 'product_cat', $product_id);
            $es_gratis  = has_term('gratis', 'product_cat', $product_id);

            $rating       = $product->get_average_rating();
            $rating_count = $product->get_rating_count();

            $precio_regular  = $product->get_regular_price();
            $precio_venta    = $product->get_sale_price();
            $tiene_descuento = !empty($precio_venta) && $precio_venta < $precio_regular;

            $checkout_url = wc_get_checkout_url() . '?add-to-cart=' . $product_id;
            ?>
            <div class="curso-card">
                <div class="curso-card-imagen">
                    <div class="curso-wishlist">
                        <?php echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]'); ?>
                    </div>

                    <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <?php if ($product->get_image_id()): ?>
                            <?php echo wp_get_attachment_image($product->get_image_id(), 'medium_large'); ?>
                        <?php else: ?>
                            <img src="https://via.placeholder.com/600x400/171717/da0480?text=Curso" alt="<?php echo esc_attr($product->get_name()); ?>" />
                        <?php endif; ?>
                    </a>

                    <?php if ($es_premium): ?>
                        <span class="curso-card-badge premium">Premium</span>
                    <?php elseif ($es_gratis): ?>
                        <span class="curso-card-badge gratis">Gratis con coins</span>
                    <?php endif; ?>
                </div>
                <div class="curso-card-contenido">
                    <h3 class="curso-card-titulo">
                        <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                            <?php echo esc_html($product->get_name()); ?>
                        </a>
                    </h3>

                    <?php if ($rating > 0): ?>
                        <div class="curso-rating">
                            <div class="curso-stars">
                                <?php
                                $full_stars  = floor($rating);
                                $empty_stars = 5 - ceil($rating);

                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<span class="curso-star">★</span>';
                                }

                                if ($rating - $full_stars >= 0.5) {
                                    echo '<span class="curso-star">★</span>';
                                }

                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<span class="curso-star empty">★</span>';
                                }
                                ?>
                            </div>
                            <span class="curso-rating-count">(<?php echo esc_html($rating_count); ?>)</span>
                        </div>
                    <?php endif; ?>

                    <div class="curso-card-footer">
                        <?php if ($es_gratis && function_exists('coins_manager')): ?>
                            <?php $costo = coins_manager()->get_costo_coins_producto($product_id); ?>
                            <div class="curso-precio-wrapper curso-precio-gratis">
                                <span class="curso-precio-coins">
                                    <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins" class="coin-icon" />
                                    <?php echo esc_html(coins_manager()->format_coins($costo)); ?> Coins
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="curso-precio-wrapper curso-precio-premium">
                                <div class="curso-precio-actual">
                                    <span class="curso-precio-actual-texto">
                                        <?php echo wp_kses_post(wc_price($tiene_descuento ? $precio_venta : $precio_regular)); ?>
                                    </span>
                                </div>
                                <?php if ($tiene_descuento): ?>
                                    <div class="curso-precio-original">
                                        <span class="curso-precio-original-texto">
                                            <?php echo wp_kses_post(wc_price($precio_regular)); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="curso-acciones">
                            <a href="<?php echo esc_url($checkout_url); ?>" class="curso-btn curso-btn-comprar">
                                Comprar ahora
                            </a>
                            <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="curso-btn curso-btn-ver">
                                Ver curso
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="cursos-loading"><p>No se encontraron cursos con estos filtros.</p></div>';
    }

    $html = ob_get_clean();

    wp_send_json_success(array(
        'html'        => $html,
        'total_pages' => $total_pages,
    ));
}
