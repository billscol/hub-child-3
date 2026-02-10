<?php
/**
 * Loader Central de Shortcodes
 * Carga todos los shortcodes organizados desde sus carpetas
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cargar shortcode: [sp_auth]
 * Sistema de autenticación (login/registro)
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/sp-auth/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/sp-auth/index.php';
}

/**
 * Cargar shortcode: [course_curriculum]
 * Sistema de currículum de cursos con módulos y lecciones
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/course-curriculum/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/course-curriculum/index.php';
}

/**
 * Cargar shortcode: [resenas_producto]
 * Sistema de reseñas y valoraciones
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/resenas-producto/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/resenas-producto/index.php';
}

/**
 * Cargar shortcode: [video_producto]
 * Sistema de videos con autoplay y modal
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/video-producto/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/video-producto/index.php';
}

/**
 * Cargar shortcode: [boton_reporte]
 * Sistema de reportes de cursos
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/boton-reporte/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/boton-reporte/index.php';
}

/**
 * Cargar shortcode: [dual_buy_buttons]
 * Botones duales de compra (Comprar Ahora + Ver Carrito)
 */
if (file_exists(get_stylesheet_directory() . '/includes/shortcodes/dual-buy-buttons/index.php')) {
    require_once get_stylesheet_directory() . '/includes/shortcodes/dual-buy-buttons/index.php';
}

/**
 * Cargar shortcodes de cursos (si existen)
 * Estos son los shortcodes que ya estaban organizados:
 * - filtros-cursos.php
 * - grid-cursos.php
 * - filtros-cursos-js.php
 */
function cargar_shortcodes_cursos() {
    $shortcodes_dir = get_stylesheet_directory() . '/includes/shortcodes/';
    
    // Cargar shortcode de filtros
    if (file_exists($shortcodes_dir . 'filtros-cursos.php')) {
        require_once $shortcodes_dir . 'filtros-cursos.php';
    }
    
    // Cargar shortcode de grid
    if (file_exists($shortcodes_dir . 'grid-cursos.php')) {
        require_once $shortcodes_dir . 'grid-cursos.php';
    }
    
    // Cargar JavaScript del slider de precio
    if (file_exists($shortcodes_dir . 'filtros-cursos-js.php')) {
        require_once $shortcodes_dir . 'filtros-cursos-js.php';
    }
}
add_action('after_setup_theme', 'cargar_shortcodes_cursos');
?>