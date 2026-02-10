<?php
/**
 * Shortcode: [filtros_cursos]
 * Panel de filtros para cursos (columna izquierda)
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('filtros_cursos', 'filtros_cursos_shortcode');

function filtros_cursos_shortcode($atts) {
    $atts = shortcode_atts(array(
        'target' => 'cursos-grid-ajax'
    ), $atts);

    // Obtener todas las categorías de producto (excluyendo categoría por defecto/uncategorized)
    $categorias = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'exclude'    => get_option('default_product_cat') // excluye "Uncategorized" por defecto [web:32][web:34]
    ));

    // Obtener rango de precios de productos
    global $wpdb;
    $precio_min = $wpdb->get_var("
        SELECT MIN(CAST(meta_value AS UNSIGNED)) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_price' 
        AND meta_value != ''
    ");
    $precio_max = $wpdb->get_var("
        SELECT MAX(CAST(meta_value AS UNSIGNED)) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_price' 
        AND meta_value != ''
    ");

    $precio_min = $precio_min ? intval($precio_min) : 0;
    $precio_max = $precio_max ? intval($precio_max) : 500000;

    ob_start();
    ?>
    <div class="filtros-cursos-sidebar" data-target="<?php echo esc_attr($atts['target']); ?>">
        
        <!-- Toggle para móvil -->
        <button type="button" class="filtros-toggle-mobile" id="filtros-toggle-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="21" x2="4" y2="14"></line>
                <line x1="4" y1="10" x2="4" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12" y2="3"></line>
                <line x1="20" y1="21" x2="20" y2="16"></line>
                <line x1="20" y1="12" x2="20" y2="3"></line>
                <line x1="1" y1="14" x2="7" y2="14"></line>
                <line x1="9" y1="8" x2="15" y2="8"></line>
                <line x1="17" y1="16" x2="23" y2="16"></line>
            </svg>
            <span>Filtros</span>
            <svg class="filtros-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div class="filtros-header">
            <h3 class="filtros-titulo">Filtrar cursos</h3>
        </div>

        <div class="filtros-panel" id="filtros-panel">
            
            <!-- Buscador -->
            <div class="filtro-grupo">
                <label class="filtro-label">Buscar curso</label>
                <div class="filtro-input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="filtro-search" class="filtro-search" placeholder="Nombre del curso..." />
                </div>
            </div>

            <!-- Tipo de curso -->
            <div class="filtro-grupo">
                <label class="filtro-label" for="filtro-tipo">Tipo de curso</label>
                <select id="filtro-tipo" class="filtro-select filtro-tipo">
                    <option value="">Todos los tipos</option>
                    <option value="premium">Premium</option>
                    <option value="gratis">Gratis con coins</option>
                </select>
            </div>

            <!-- Rango de precio con SLIDER -->
            <div class="filtro-grupo">
                <label class="filtro-label">Rango de precio</label>
                <div class="precio-slider-container">
                    <div class="precio-valores">
                        <span class="precio-valor-min">$<span class="precio-min-display"><?php echo number_format($precio_min, 0, ',', '.'); ?></span></span>
                        <span class="precio-separador">—</span>
                        <span class="precio-valor-max">$<span class="precio-max-display"><?php echo number_format($precio_max, 0, ',', '.'); ?></span></span>
                    </div>
                    <div class="precio-slider-wrapper">
                        <div class="precio-slider-track"></div>
                        <input 
                            type="range" 
                            class="precio-slider precio-slider-min"
                            min="<?php echo $precio_min; ?>" 
                            max="<?php echo $precio_max; ?>" 
                            value="<?php echo $precio_min; ?>"
                            step="10000"
                            data-min="<?php echo $precio_min; ?>"
                            data-max="<?php echo $precio_max; ?>"
                        />
                        <input 
                            type="range" 
                            class="precio-slider precio-slider-max"
                            min="<?php echo $precio_min; ?>" 
                            max="<?php echo $precio_max; ?>" 
                            value="<?php echo $precio_max; ?>"
                            step="10000"
                            data-min="<?php echo $precio_min; ?>"
                            data-max="<?php echo $precio_max; ?>"
                        />
                    </div>
                </div>
            </div>

            <!-- Categorías como checkboxes -->
            <div class="filtro-grupo">
                <label class="filtro-label">Categorías</label>
                <div class="filtro-categorias-list">
                    <?php if (!empty($categorias) && !is_wp_error($categorias)) : ?>
                        <?php foreach ($categorias as $cat): ?>
                            <label class="categoria-checkbox">
                                <input type="checkbox" name="categoria[]" value="<?php echo esc_attr($cat->slug); ?>" class="filtro-categoria-check" />
                                <span class="categoria-nombre"><?php echo esc_html($cat->name); ?></span>
                                <span class="categoria-count"><?php echo intval($cat->count); ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:13px;color:#9ca3af;">No hay categorías disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Limpiar filtros -->
            <button type="button" class="btn-limpiar-filtros filtro-limpiar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                </svg>
                Limpiar filtros
            </button>
        </div>
    </div>

    <style>
    .filtros-cursos-sidebar {
        font-family: "Space Grotesk", -apple-system, BlinkMacSystemFont, sans-serif;
        position: sticky;
        top: 20px;
    }

    /* Toggle móvil (oculto en desktop) */
    .filtros-toggle-mobile {
        display: none;
        width: 100%;
        padding: 14px 20px;
        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
        border: 1.5px solid rgba(218,4,128,.25);
        border-radius: 12px;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .filtros-toggle-mobile svg:first-child {
        flex-shrink: 0;
    }

    .filtros-toggle-mobile span {
        flex: 1;
        text-align: left;
    }

    .filtros-chevron {
        flex-shrink: 0;
        transition: transform 0.3s;
    }

    .filtros-toggle-mobile.active .filtros-chevron {
        transform: rotate(180deg);
    }

    .filtros-toggle-mobile:hover {
        border-color: #da0480;
        box-shadow: 0 4px 16px rgba(218,4,128,0.2);
    }

    .filtros-header {
        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
        border: 1.5px solid rgba(218,4,128,.25);
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
    }

    .filtros-titulo {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filtros-titulo::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(180deg, #da0480, #b00368);
        border-radius: 2px;
    }

    .filtros-panel {
        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
        border: 1.5px solid rgba(218,4,128,.25);
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .filtro-grupo {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filtro-label {
        font-size: 13px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filtro-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filtro-input-wrapper svg {
        position: absolute;
        left: 14px;
        color: #9ca3af;
        pointer-events: none;
        z-index: 1;
    }

    .filtro-input-wrapper input {
        width: 100%;
        padding: 12px 14px 12px 44px;
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #fff;
        font-size: 15px;
        transition: all 0.3s;
    }

    .filtro-input-wrapper input:focus {
        outline: none;
        border-color: #da0480;
        background: rgba(0,0,0,0.6);
        box-shadow: 0 0 0 3px rgba(218,4,128,0.15);
    }

    .filtro-input-wrapper input::placeholder {
        color: #6b7280;
    }

    .filtro-select {
        width: 100%;
        padding: 12px 14px;
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%239ca3af' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }

    .filtro-select:focus {
        outline: none;
        border-color: #da0480;
        background-color: rgba(0,0,0,0.6);
        box-shadow: 0 0 0 3px rgba(218,4,128,0.15);
    }

    .filtro-select option {
        background: #1a1a1a;
        color: #fff;
        padding: 10px;
    }

    /* Categorías como checkboxes */
    .filtro-categorias-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 280px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .filtro-categorias-list::-webkit-scrollbar {
        width: 6px;
    }

    .filtro-categorias-list::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.2);
        border-radius: 3px;
    }

    .filtro-categorias-list::-webkit-scrollbar-thumb {
        background: rgba(218,4,128,0.4);
        border-radius: 3px;
    }

    .filtro-categorias-list::-webkit-scrollbar-thumb:hover {
        background: rgba(218,4,128,0.6);
    }

    .categoria-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        user-select: none;
    }

    .categoria-checkbox:hover {
        background: rgba(0,0,0,0.5);
        border-color: rgba(218,4,128,0.3);
    }

    .categoria-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #da0480;
    }

    .categoria-nombre {
        flex: 1;
        color: #e5e7eb;
        font-size: 14px;
        font-weight: 500;
    }

    .categoria-count {
        font-size: 12px;
        color: #9ca3af;
        background: rgba(255,255,255,0.05);
        padding: 2px 8px;
        border-radius: 999px;
        font-weight: 600;
    }

    .categoria-checkbox input[type="checkbox"]:checked ~ .categoria-nombre {
        color: #da0480;
        font-weight: 700;
    }

    .categoria-checkbox input[type="checkbox"]:checked ~ .categoria-count {
        background: rgba(218,4,128,0.2);
        color: #da0480;
    }

    /* Slider de precio */
    .precio-slider-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .precio-valores {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: rgba(218,4,128,0.1);
        border: 1px solid rgba(218,4,128,0.3);
        border-radius: 8px;
    }

    .precio-valor-min,
    .precio-valor-max {
        font-size: 16px;
        font-weight: 700;
        color: #da0480;
    }

    .precio-separador {
        color: #9ca3af;
        font-weight: 400;
        margin: 0 8px;
    }

    .precio-slider-wrapper {
        position: relative;
        height: 40px;
        display: flex;
        align-items: center;
    }

    .precio-slider-track {
        position: absolute;
        width: 100%;
        height: 6px;
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
        pointer-events: none;
    }

    .precio-slider-track::before {
        content: '';
        position: absolute;
        height: 100%;
        background: linear-gradient(90deg, #da0480, #b00368);
        border-radius: 3px;
        left: 0%;
        right: 0%;
        transition: all 0.3s;
    }

    .precio-slider {
        position: absolute;
        width: 100%;
        height: 40px;
        -webkit-appearance: none;
        appearance: none;
        background: transparent;
        pointer-events: none;
        cursor: pointer;
    }

    .precio-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #da0480, #b00368);
        border: 3px solid #0f0f0f;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: auto;
        box-shadow: 0 2px 8px rgba(218,4,128,0.4);
        transition: all 0.2s;
    }

    .precio-slider::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 16px rgba(218,4,128,0.6);
    }

    .precio-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #da0480, #b00368);
        border: 3px solid #0f0f0f;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: auto;
        box-shadow: 0 2px 8px rgba(218,4,128,0.4);
        transition: all 0.2s;
    }

    .precio-slider::-moz-range-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 16px rgba(218,4,128,0.6);
    }

    .precio-slider-max {
        pointer-events: none;
    }

    .precio-slider-max::-webkit-slider-thumb {
        pointer-events: auto;
    }

    .precio-slider-max::-moz-range-thumb {
        pointer-events: auto;
    }

    /* Botón limpiar */
    .btn-limpiar-filtros {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(218,4,128,0.15);
        border: 1px solid rgba(218,4,128,0.4);
        border-radius: 10px;
        color: #da0480;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-limpiar-filtros:hover {
        background: rgba(218,4,128,0.25);
        border-color: #da0480;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(218,4,128,0.3);
    }

    .btn-limpiar-filtros svg {
        width: 18px;
        height: 18px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .filtros-cursos-sidebar {
            position: static;
            margin-bottom: 24px;
        }
    }

    @media (max-width: 768px) {
        /* Mostrar toggle y ocultar header en móvil */
        .filtros-toggle-mobile {
            display: flex;
        }

        .filtros-header {
            display: none;
        }

        /* Panel colapsado por defecto */
        .filtros-panel {
            display: none;
            margin-top: 0;
            border-radius: 12px;
            border-top: 1.5px solid rgba(218,4,128,.25);
            animation: slideDown 0.3s ease-out;
        }

        .filtros-panel.active {
            display: flex;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }

    @media (max-width: 640px) {
        .filtros-toggle-mobile {
            padding: 12px 16px;
            font-size: 15px;
        }

        .filtros-panel {
            padding: 20px;
            gap: 20px;
        }

        .filtro-label {
            font-size: 12px;
        }

        .filtro-input-wrapper input,
        .filtro-select {
            padding: 11px 12px 11px 42px;
            font-size: 14px;
        }

        .filtro-select {
            padding-right: 36px;
        }

        .precio-valores {
            padding: 9px 12px;
        }

        .precio-valor-min,
        .precio-valor-max {
            font-size: 14px;
        }

        .btn-limpiar-filtros {
            padding: 11px 18px;
            font-size: 14px;
        }

        .categoria-checkbox {
            padding: 9px 10px;
        }

        .categoria-nombre {
            font-size: 13px;
        }
    }
    </style>

<script>
    (function() {
        // Toggle filtros en móvil
        const toggleBtn = document.getElementById('filtros-toggle-btn');
        const panel = document.getElementById('filtros-panel');

        if (toggleBtn && panel) {
            toggleBtn.addEventListener('click', function() {
                panel.classList.toggle('active');
                toggleBtn.classList.toggle('active');
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}