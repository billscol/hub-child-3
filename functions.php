<?php
/**
 * Hub Child Theme Functions
 * 
 * @package HubChildTheme
 * @version 1.0.0
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prevenir errores en el editor de páginas
 */
if (is_admin() && isset($_GET['action']) && $_GET['action'] === 'edit') {
    remove_action('init', 'cargar_sistema_creditos', 0);
}


// Template personalizado solo para la página de carrito
add_filter('template_include', function($template) {
    if (is_cart()) {
        $custom = get_stylesheet_directory() . '/woocommerce/cart/custom-cart-page.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 99);


/**
 * ============================================
 * ESTILOS DEL THEME
 * ============================================
 */
add_action('wp_enqueue_scripts', 'liquid_child_theme_style', 99);

function liquid_parent_theme_scripts() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

function liquid_child_theme_style() {
    wp_enqueue_style('child-hub-style', get_stylesheet_directory_uri() . '/style.css');	
}

/**
 * ============================================
 * MÓDULOS PERSONALIZADOS
 * ============================================
 */

// Definir constantes del child theme
define('CHILD_THEME_DIR', get_stylesheet_directory());
define('CHILD_THEME_URI', get_stylesheet_directory_uri());
define('CHILD_THEME_VERSION', '1.0.0');

/**
 * Cargar módulo de personalización de checkout
 * Solo si WooCommerce está activo
 */
add_action('after_setup_theme', 'load_child_theme_modules');
function load_child_theme_modules() {
    if (class_exists('WooCommerce')) {
        $checkout_module = CHILD_THEME_DIR . '/includes/checkout-customization/index.php';
        
        if (file_exists($checkout_module)) {
            require_once $checkout_module;
        }
    }
}

/**
 * ============================================
 * Sistema de Coins para Cursos
 * Carga el sistema de coins modular
 * ============================================
 */

// Definir constantes del sistema de coins
define('COINS_VERSION', '2.0.0');
define('COINS_PATH', get_stylesheet_directory() . '/includes/coins-system/');
define('COINS_URL', get_stylesheet_directory_uri() . '/includes/coins-system/');

/**
 * Cargar archivos del sistema de coins ANTES de todo
 */
function cargar_sistema_coins() {
    // Verificar que WooCommerce esté activo
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Cargar archivos del sistema
    if (file_exists(COINS_PATH . 'coins-functions.php')) {
        require_once COINS_PATH . 'coins-functions.php';
    }
    
    if (file_exists(COINS_PATH . 'class-coins-manager.php')) {
        require_once COINS_PATH . 'class-coins-manager.php';
    }
    
    if (file_exists(COINS_PATH . 'class-coins-gateway.php')) {
        require_once COINS_PATH . 'class-coins-gateway.php';
    }
    
    if (file_exists(COINS_PATH . 'coins-hooks.php')) {
        require_once COINS_PATH . 'coins-hooks.php';
    }
    
    if (file_exists(COINS_PATH . 'coins-metabox.php')) {
        require_once COINS_PATH . 'coins-metabox.php';
    }
    
    if (file_exists(COINS_PATH . 'coins-reviews.php')) {
        require_once COINS_PATH . 'coins-reviews.php';
    }
    
    if (file_exists(COINS_PATH . 'coins-social.php')) {
        require_once COINS_PATH . 'coins-social.php';
    }
    
    // Cargar admin solo en backend
    if (is_admin() && file_exists(COINS_PATH . 'coins-admin.php')) {
        require_once COINS_PATH . 'coins-admin.php';
    }
}
// Ejecutar en init con prioridad alta para asegurar que WooCommerce esté listo
add_action('init', 'cargar_sistema_coins', 0);

/**
 * Registrar el gateway de coins en WooCommerce
 */
function registrar_gateway_coins($gateways) {
    // Cargar la clase si aún no está cargada
    if (!class_exists('WC_Gateway_Coins') && file_exists(COINS_PATH . 'class-coins-gateway.php')) {
        require_once COINS_PATH . 'class-coins-gateway.php';
    }
    
    // Registrar si la clase existe
    if (class_exists('WC_Gateway_Coins')) {
        $gateways[] = 'WC_Gateway_Coins';
    }
    
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'registrar_gateway_coins');

/**
 * Crear tablas al activar el tema
 */
function crear_tablas_coins() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de historial de coins
    $tabla_historial = $wpdb->prefix . 'coins_historial';
    $sql1 = "CREATE TABLE IF NOT EXISTS $tabla_historial (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        tipo varchar(20) NOT NULL,
        cantidad decimal(10,2) NOT NULL,
        saldo_anterior decimal(10,2) NOT NULL,
        saldo_nuevo decimal(10,2) NOT NULL,
        descripcion text,
        order_id bigint(20),
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY fecha (fecha)
    ) $charset_collate;";
    
    // Tabla de recompensas por reseñas
    $tabla_reviews = $wpdb->prefix . 'coins_reviews_rewarded';
    $sql2 = "CREATE TABLE IF NOT EXISTS $tabla_reviews (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        comment_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        coins_otorgados decimal(10,2) NOT NULL,
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_comment (comment_id),
        KEY user_id (user_id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    // Tabla de recompensas por compartir
    $tabla_shares = $wpdb->prefix . 'coins_social_shares';
    $sql3 = "CREATE TABLE IF NOT EXISTS $tabla_shares (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        platform varchar(20) NOT NULL,
        coins_otorgados decimal(10,2) NOT NULL,
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY product_id (product_id),
        KEY fecha (fecha)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}
add_action('after_switch_theme', 'crear_tablas_coins');


/**
 * ============================================
 * FIN SISTEMA DE COINS PARA CURSOS
 * ============================================
 */

/**
 * Integración de campos de cursos en Dokan
 */
if ( file_exists( get_stylesheet_directory() . '/includes/dokan-integration.php' ) ) {
    require_once get_stylesheet_directory() . '/includes/dokan-integration.php';
}


add_action( 'wp_footer', function () {
    if ( ! is_page( 'dashboard' ) ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar el botón "Add new product" de Dokan
        var link = document.querySelector('.dokan-add-product-link a.dokan-btn.dokan-btn-theme');
        if (link) {
            link.setAttribute('href', '<?php echo esc_url( home_url( '/publicar-curso/' ) ); ?>');
        }
    });
    </script>
    <?php
} );


/**
 * ============================================
 * SISTEMA DE CURSOS
 * ============================================
 */
require_once get_stylesheet_directory() . '/includes/course-system/init.php';


/**
 * Cargar shortcodes personalizados de cursos
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

/**
 * ============================================
 * FIN - SISTEMA DE CURSOS
 * ============================================
 */




/**
 * Forzar Single Product de Elementor - Plantilla 43501
 */
add_action('template_redirect', function() {
    if (is_singular('product')) {
        // Forzar la plantilla de Elementor
        add_filter('template_include', function($template) {
            // Renderizar plantilla de Elementor
            if (did_action('elementor/loaded')) {
                // Obtener el plugin de Elementor
                $elementor = \Elementor\Plugin::instance();
                
                // Renderizar la plantilla
                $elementor->frontend->add_body_class('elementor-template-full-width');
                
                // Crear archivo temporal para renderizar
                $file = plugin_dir_path(__FILE__) . 'elementor-product-template.php';
                
                if (!file_exists($file)) {
                    file_put_contents($file, '<?php
                    get_header();
                    echo do_shortcode("[elementor-template id=\"724\"]");
                    get_footer();
                    ?>');
                }
                
                return $file;
            }
            return $template;
        }, 99);
    }
}, 1);


// ============================================
// SISTEMA DE MÓDULOS Y LECCIONES - BACKEND MEJORADO
// ============================================

// Agregar metabox de Módulos y Lecciones en productos
add_action('add_meta_boxes', 'add_course_curriculum_metabox');
function add_course_curriculum_metabox() {
    add_meta_box(
        'course_curriculum',
        '📚 Currículum del Curso (Módulos y Lecciones)',
        'render_course_curriculum_metabox',
        'product',
        'normal',
        'high'
    );
}

function render_course_curriculum_metabox($post) {
    wp_nonce_field('save_course_curriculum', 'course_curriculum_nonce');
    
    $curriculum = get_post_meta($post->ID, '_course_curriculum', true);
    if (empty($curriculum)) {
        $curriculum = array();
    }
    
    // Calcular totales
    $total_modules = count($curriculum);
    $total_lessons = 0;
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    ?>
    
    <style>
        #course-curriculum-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .curriculum-header {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            padding: 20px;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(218, 4, 128, 0.05) 100%);
            border: 1px solid rgba(218, 4, 128, 0.3);
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .curriculum-stat {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        
        .curriculum-stat label {
            font-weight: 600;
            color: #da0480;
            margin: 0;
            font-size: 14px;
        }
        
        .curriculum-stat input {
            width: 55px !important;
            text-align: center;
            font-weight: 700;
            color: #333;
            border: 2px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 6px;
            padding: 6px !important;
        }
        
        .module-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #da0480;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        
        .module-item:hover {
            box-shadow: 0 4px 16px rgba(218, 4, 128, 0.2);
            transform: translateY(-2px);
        }
        
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .module-header h4 {
            margin: 0;
            color: #da0480;
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .module-item input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .module-item input[type="text"]:focus {
            border-color: #da0480;
            outline: none;
            box-shadow: 0 0 0 4px rgba(218, 4, 128, 0.1);
        }
        
        .locked-section {
            background: rgba(218, 4, 128, 0.06);
            padding: 14px;
            border-left: 4px solid #da0480;
            border-radius: 8px;
            margin: 18px 0;
        }
        
        .locked-section label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin: 0;
        }
        
        .locked-section input[type="checkbox"] {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: #da0480;
        }
        
        .locked-section small {
            display: block;
            margin-top: 10px;
            margin-left: 32px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .lessons-container {
            margin-top: 18px;
            padding: 18px;
            background: rgba(218, 4, 128, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(218, 4, 128, 0.1);
        }
        
        .lessons-container > label {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 15px;
        }
        
        .lesson-item {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .lesson-item::before {
            content: "▶";
            color: #da0480;
            font-size: 11px;
            flex-shrink: 0;
        }
        
        .lesson-item input {
            flex: 1;
            padding: 10px 12px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 7px;
        }
        
        .lesson-item input:focus {
            border-color: #da0480 !important;
            box-shadow: 0 0 0 3px rgba(218, 4, 128, 0.1);
        }
        
        .button {
            border-radius: 8px !important;
            padding: 10px 18px !important;
            font-weight: 600 !important;
            transition: all 0.3s !important;
            border: none !important;
            cursor: pointer !important;
        }
        
        .button-primary {
            background: linear-gradient(135deg, #da0480 0%, #b00368 100%) !important;
            border-color: #da0480 !important;
            text-shadow: none !important;
            box-shadow: 0 3px 8px rgba(218, 4, 128, 0.35) !important;
        }
        
        .button-primary:hover {
            background: linear-gradient(135deg, #b00368 0%, #8a0252 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(218, 4, 128, 0.5) !important;
        }
        
        .remove-module {
            background: #ef4444 !important;
            color: #fff !important;
        }
        
        .remove-module:hover {
            background: #dc2626 !important;
            transform: translateY(-1px);
        }
        
        .remove-lesson {
            background: #f87171 !important;
            color: #fff !important;
            padding: 8px 14px !important;
            font-size: 13px !important;
        }
        
        .remove-lesson:hover {
            background: #ef4444 !important;
        }
        
        .add-lesson {
            background: #fff !important;
            color: #da0480 !important;
            border: 2px solid #da0480 !important;
        }
        
        .add-lesson:hover {
            background: #da0480 !important;
            color: #fff !important;
        }
        
        /* Responsive */
        @media (max-width: 782px) {
            .curriculum-header {
                flex-direction: column;
            }
            
            .curriculum-stat {
                width: 100%;
                justify-content: space-between;
            }
            
            .module-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .module-header .remove-module {
                width: 100%;
            }
            
            .lesson-item {
                flex-wrap: wrap;
            }
            
            .lesson-item input {
                width: 100%;
            }
            
            .remove-lesson {
                width: 100%;
            }
        }
    </style>
    
    <div id="course-curriculum-container">
        <div class="curriculum-header">
            <div class="curriculum-stat">
                <label>📦 Total Módulos:</label>
                <input type="number" id="total-modules" value="<?php echo $total_modules; ?>" readonly>
            </div>
            <div class="curriculum-stat">
                <label>▶ Total Lecciones:</label>
                <input type="number" id="total-lessons" value="<?php echo $total_lessons; ?>" readonly>
            </div>
        </div>
        
        <div id="modules-list">
            <?php if (!empty($curriculum)) {
                foreach ($curriculum as $index => $module) { 
                    $is_locked = isset($module['locked']) ? $module['locked'] : false;
                    ?>
                <div class="module-item" data-index="<?php echo $index; ?>">
                    <div class="module-header">
                        <h4>📁 Módulo #<?php echo $index + 1; ?></h4>
                        <button type="button" class="button remove-module">🗑️ Eliminar</button>
                    </div>
                    
                    <p style="margin-bottom: 16px;">
                        <label style="font-weight:600; display:block; margin-bottom:10px; color:#374151;">Nombre del Módulo:</label>
                        <input type="text" name="curriculum[<?php echo $index; ?>][name]" value="<?php echo esc_attr($module['name']); ?>" placeholder="Ej: Mentalidad del Trafficker">
                    </p>
                    
                    <div class="locked-section">
                        <label>
                            <input type="checkbox" name="curriculum[<?php echo $index; ?>][locked]" value="1" <?php checked($is_locked, true); ?>>
                            <span style="font-weight:600; font-size:14px;">🔒 Bloquear este módulo (solo visible para compradores)</span>
                        </label>
                        <small>Si está marcado, los visitantes verán "Contenido Privado" en lugar de las lecciones.</small>
                    </div>
                    
                    <div class="lessons-container">
                        <label>▶ Lecciones del Módulo:</label>
                        <div class="lessons-list">
                            <?php if (!empty($module['lessons'])) {
                                foreach ($module['lessons'] as $l_index => $lesson) { ?>
                                <div class="lesson-item">
                                    <input type="text" name="curriculum[<?php echo $index; ?>][lessons][<?php echo $l_index; ?>]" value="<?php echo esc_attr($lesson); ?>" placeholder="Nombre de la lección">
                                    <button type="button" class="button remove-lesson">✕</button>
                                </div>
                            <?php }
                            } ?>
                        </div>
                        <button type="button" class="button add-lesson">+ Agregar Lección</button>
                    </div>
                </div>
            <?php }
            } ?>
        </div>
        
        <button type="button" class="button button-primary" id="add-module">+ Agregar Nuevo Módulo</button>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var moduleIndex = <?php echo count($curriculum); ?>;
        
        $('#add-module').on('click', function() {
            var html = '<div class="module-item" data-index="' + moduleIndex + '">' +
                '<div class="module-header">' +
                '<h4>📁 Módulo #' + (moduleIndex + 1) + '</h4>' +
                '<button type="button" class="button remove-module">🗑️ Eliminar</button>' +
                '</div>' +
                '<p style="margin-bottom: 16px;">' +
                '<label style="font-weight:600; display:block; margin-bottom:10px; color:#374151;">Nombre del Módulo:</label>' +
                '<input type="text" name="curriculum[' + moduleIndex + '][name]" placeholder="Ej: Configuraciones Iniciales">' +
                '</p>' +
                '<div class="locked-section">' +
                '<label>' +
                '<input type="checkbox" name="curriculum[' + moduleIndex + '][locked]" value="1">' +
                '<span style="font-weight:600; font-size:14px;">🔒 Bloquear este módulo (solo visible para compradores)</span>' +
                '</label>' +
                '<small>Si está marcado, los visitantes verán "Contenido Privado" en lugar de las lecciones.</small>' +
                '</div>' +
                '<div class="lessons-container">' +
                '<label>▶ Lecciones del Módulo:</label>' +
                '<div class="lessons-list"></div>' +
                '<button type="button" class="button add-lesson">+ Agregar Lección</button>' +
                '</div>' +
                '</div>';
            
            $('#modules-list').append(html);
            moduleIndex++;
            updateTotals();
        });
        
        $(document).on('click', '.add-lesson', function() {
            var moduleItem = $(this).closest('.module-item');
            var moduleIdx = moduleItem.data('index');
            var lessonCount = moduleItem.find('.lesson-item').length;
            
            var html = '<div class="lesson-item">' +
                '<input type="text" name="curriculum[' + moduleIdx + '][lessons][' + lessonCount + ']" placeholder="Nombre de la lección">' +
                '<button type="button" class="button remove-lesson">✕</button>' +
                '</div>';
            
            moduleItem.find('.lessons-list').append(html);
            updateTotals();
        });
        
        $(document).on('click', '.remove-module', function() {
            if (confirm('¿Estás seguro de eliminar este módulo completo?')) {
                $(this).closest('.module-item').fadeOut(300, function() {
                    $(this).remove();
                    updateTotals();
                    renumberModules();
                });
            }
        });
        
        $(document).on('click', '.remove-lesson', function() {
            $(this).closest('.lesson-item').fadeOut(200, function() {
                $(this).remove();
                updateTotals();
            });
        });
        
        function updateTotals() {
            var totalModules = $('.module-item').length;
            var totalLessons = $('.lesson-item').length;
            $('#total-modules').val(totalModules);
            $('#total-lessons').val(totalLessons);
        }
        
        function renumberModules() {
            $('.module-item').each(function(index) {
                $(this).find('.module-header h4').text('📁 Módulo #' + (index + 1));
            });
        }
    });
    </script>
    <?php
}

// Guardar datos del currículum
add_action('save_post_product', 'save_course_curriculum');
function save_course_curriculum($post_id) {
    if (!isset($_POST['course_curriculum_nonce']) || !wp_verify_nonce($_POST['course_curriculum_nonce'], 'save_course_curriculum')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['curriculum'])) {
        $curriculum = array();
        foreach ($_POST['curriculum'] as $module) {
            if (!empty($module['name'])) {
                $lessons = array();
                if (!empty($module['lessons'])) {
                    foreach ($module['lessons'] as $lesson) {
                        if (!empty($lesson)) {
                            $lessons[] = sanitize_text_field($lesson);
                        }
                    }
                }
                $curriculum[] = array(
                    'name' => sanitize_text_field($module['name']),
                    'lessons' => $lessons,
                    'locked' => isset($module['locked']) ? true : false
                );
            }
        }
        update_post_meta($post_id, '_course_curriculum', $curriculum);
    }
}

// ============================================
// SISTEMA DE MÓDULOS Y LECCIONES - FRONTEND MEJORADO
// ============================================

add_action('woocommerce_after_single_product_summary', 'display_course_curriculum', 15);
function display_course_curriculum() {
    global $product;
    
    $curriculum = get_post_meta($product->get_id(), '_course_curriculum', true);
    
    if (empty($curriculum)) {
        return;
    }
    
    // Verificar si el usuario actual ha comprado el producto
    $current_user = wp_get_current_user();
    $has_bought = false;
    
    if ($current_user->ID > 0) {
        $has_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
    }
    
    $total_modules = count($curriculum);
    $total_lessons = 0;
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    ?>
    
    <style>
        .course-curriculum-section {
            margin: 50px 0;
            padding: 35px;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(218, 4, 128, 0.03) 100%);
            border: 2px solid rgba(218, 4, 128, 0.25);
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(218, 4, 128, 0.1);
        }
        
        .curriculum-main-title {
            font-size: 28px;
            font-weight: 700;
            color: #da0480;
            margin: 0 0 25px 0;
            text-align: center;
        }
        
        .curriculum-stats {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .curriculum-stat-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(218, 4, 128, 0.1);
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid rgba(218, 4, 128, 0.3);
            box-shadow: 0 4px 12px rgba(218, 4, 128, 0.08);
        }
        
        .curriculum-stat-box svg {
            flex-shrink: 0;
            color: #da0480;
        }
        
        .curriculum-stat-box span {
            font-size: 16px;
            font-weight: 700;
            color: #da0480;
            letter-spacing: 0.5px;
        }
        
        .curriculum-accordion {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        
        .curriculum-module {
            border: 2px solid rgba(218, 4, 128, 0.25);
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(218, 4, 128, 0.08);
        }
        
        .curriculum-module:hover {
            border-color: rgba(218, 4, 128, 0.5);
            box-shadow: 0 8px 20px rgba(218, 4, 128, 0.18);
            transform: translateY(-3px);
        }
        
        .module-header {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.12) 0%, rgba(218, 4, 128, 0.08) 100%);
            transition: all 0.3s;
            border-bottom: 1px solid rgba(218, 4, 128, 0.15);
        }
        
        .module-header:hover {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.18) 0%, rgba(218, 4, 128, 0.12) 100%);
        }
        
        .module-title {
            font-size: 17px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .module-number {
            background: #da0480;
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
        }
        
        .chevron-icon {
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        
        .module-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out;
        }
        
        .module-lessons {
            padding: 20px 25px;
            background: rgba(218, 4, 128, 0.03);
        }
        
        .lesson-item-display {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            color: #374151;
            font-size: 15px;
            font-weight: 500;
            border-bottom: 1px solid rgba(218, 4, 128, 0.12);
            transition: all 0.2s;
        }
        
        .lesson-item-display:last-child {
            border-bottom: none;
        }
        
        .lesson-item-display:hover {
            padding-left: 8px;
            color: #da0480;
        }
        
        .lesson-item-display svg {
            flex-shrink: 0;
            color: #da0480;
        }
        
        .lesson-icon-wrapper {
            width: 36px;
            height: 36px;
            background: rgba(218, 4, 128, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .locked-content {
            padding: 50px 25px;
            text-align: center;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.05) 0%, rgba(218, 4, 128, 0.02) 100%);
        }
        
        .locked-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: rgba(218, 4, 128, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(218, 4, 128, 0.3);
        }
        
        .locked-content h4 {
            color: #da0480;
            margin: 0 0 12px 0;
            font-size: 24px;
            font-weight: 800;
        }
        
        .locked-content p {
            color: #6b7280;
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .locked-lesson-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            padding: 10px 20px;
            background: rgba(218, 4, 128, 0.1);
            border-radius: 20px;
            color: #da0480;
            font-weight: 600;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .course-curriculum-section {
                padding: 25px 20px;
                margin: 35px 0;
                border-radius: 16px;
            }
            
            .curriculum-main-title {
                font-size: 22px;
                margin-bottom: 20px;
            }
            
            .curriculum-stats {
                flex-direction: column;
                gap: 12px;
            }
            
            .curriculum-stat-box {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
            }
            
            .module-header {
                padding: 16px 18px;
            }
            
            .module-title {
                font-size: 15px;
            }
            
            .module-number {
                width: 28px;
                height: 28px;
                font-size: 13px;
            }
            
            .lesson-item-display {
                font-size: 14px;
                padding: 12px 0;
            }
            
            .lesson-icon-wrapper {
                width: 32px;
                height: 32px;
            }
            
            .locked-content {
                padding: 40px 20px;
            }
            
            .locked-icon-wrapper {
                width: 70px;
                height: 70px;
            }
            
            .locked-content h4 {
                font-size: 20px;
            }
            
            .locked-content p {
                font-size: 15px;
            }
        }
    </style>
    
    <div class="course-curriculum-section">
        <h3 class="curriculum-main-title">📚 Contenido del Curso</h3>
        
        <div class="curriculum-stats">
            <div class="curriculum-stat-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                </svg>
                <span><?php echo $total_modules; ?> MÓDULOS</span>
            </div>
            <div class="curriculum-stat-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span><?php echo $total_lessons; ?> LECCIONES</span>
            </div>
        </div>
        
        <div class="curriculum-accordion">
            <?php foreach ($curriculum as $index => $module) { 
                $is_locked = isset($module['locked']) ? $module['locked'] : false;
                $show_content = !$is_locked || $has_bought;
                ?>
            <div class="curriculum-module">
                <div class="module-header" onclick="toggleModule(this)">
                    <div class="module-title">
                        <span class="module-number"><?php echo $index + 1; ?></span>
                        <?php if ($is_locked && !$has_bought) { ?>
                            <span>🔒</span>
                        <?php } ?>
                        <span><?php echo esc_html($module['name']); ?></span>
                    </div>
                    <svg class="chevron-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="module-content">
                    <div class="module-lessons">
                        <?php if ($show_content) { 
                            // MOSTRAR CONTENIDO - Módulo desbloqueado o usuario compró
                            if (!empty($module['lessons'])) {
                                foreach ($module['lessons'] as $lesson) { ?>
                                <div class="lesson-item-display">
                                    <div class="lesson-icon-wrapper">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
                                    </div>
                                    <span><?php echo esc_html($lesson); ?></span>
                                </div>
                            <?php }
                            }
                        } else { 
                            // MÓDULO BLOQUEADO - Mostrar mensaje
                            ?>
                            <div class="locked-content">
                                <div class="locked-icon-wrapper">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </div>
                                <h4>Contenido Privado</h4>
                                <p>Este módulo está disponible solo para nuestros alumnos.<br>Adquiere el curso para desbloquear todo el contenido.</p>
                                <?php if (!empty($module['lessons'])) { ?>
                                <div class="locked-lesson-count">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span><?php echo count($module['lessons']); ?> lecciones en este módulo</span>
                                </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <script>
    function toggleModule(header) {
        var module = header.closest('.curriculum-module');
        var content = module.querySelector('.module-content');
        var chevron = module.querySelector('.chevron-icon');
        var isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
        
        if (isOpen) {
            content.style.maxHeight = '0';
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            chevron.style.transform = 'rotate(180deg)';
        }
    }
    </script>
    <?php
}


// ============================================
// SHORTCODE PARA ELEMENTOR CON BLOQUEO - MEJORADO
// ============================================

add_shortcode('course_curriculum', 'course_curriculum_shortcode');
function course_curriculum_shortcode($atts) {
    global $product;
    
    // Si no hay producto, intentar obtenerlo del post actual
    if (!$product) {
        global $post;
        if ($post) {
            $product = wc_get_product($post->ID);
        }
    }
    
    if (!$product) {
        return '';
    }
    
    $curriculum = get_post_meta($product->get_id(), '_course_curriculum', true);
    
    if (empty($curriculum)) {
        return '';
    }
    
    // Verificar si el usuario actual ha comprado el producto
    $current_user = wp_get_current_user();
    $has_bought = false;
    
    if ($current_user->ID > 0) {
        $has_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
    }
    
    $total_modules = count($curriculum);
    $total_lessons = 0;
    foreach ($curriculum as $module) {
        if (!empty($module['lessons'])) {
            $total_lessons += count($module['lessons']);
        }
    }
    
    ob_start();
    ?>
    <div class="course-curriculum-section" style="margin:40px 0;padding:30px;background:linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e;border:1px solid rgba(218, 4, 128, 0.3);border-radius:16px;box-shadow:0 4px 20px rgba(218, 4, 128, 0.1);">
        <div class="curriculum-stats" style="display:flex;gap:30px;margin-bottom:25px;color:#9ca3af;font-size:15px;flex-wrap:wrap;justify-content:center;">
            <div style="display:flex;align-items:center;gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span style="color:#da0480;"><strong><?php echo $total_modules; ?> MÓDULOS</strong></span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#da0480" stroke="#da0480" stroke-width="1">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span style="color:#da0480;"><strong><?php echo $total_lessons; ?> LECCIONES</strong></span>
            </div>
        </div>
        
        <div class="curriculum-accordion">
            <?php foreach ($curriculum as $index => $module) { 
                $is_locked = isset($module['locked']) ? $module['locked'] : false;
                $show_content = !$is_locked || $has_bought;
                ?>
            <div class="curriculum-module" style="border:1px solid rgba(218, 4, 128, 0.3);border-radius:12px;margin-bottom:15px;overflow:hidden;transition:all 0.3s;">
                <div class="module-header" style="padding:18px 20px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg, rgba(218, 4, 128, 0.15) 0%, rgba(30, 35, 45, 0.8) 100%);transition:all 0.3s;border-bottom:1px solid rgba(218, 4, 128, 0.2);" onclick="toggleModuleShortcode(this)" onmouseover="this.style.background='linear-gradient(135deg, rgba(218, 4, 128, 0.25) 0%, rgba(30, 35, 45, 0.9) 100%)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(218, 4, 128, 0.15) 0%, rgba(30, 35, 45, 0.8) 100%)'">
                    <span style="color:#fff;font-size:16px;font-weight:600;">
                        <?php if ($is_locked && !$has_bought) { ?>
                            🔒 
                        <?php } ?>
                        <?php echo $index + 1; ?>. <?php echo esc_html($module['name']); ?>
                    </span>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2.5" style="transition:transform 0.3s;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="module-content" style="max-height:0;overflow:hidden;transition:max-height 0.4s ease-in-out;">
                    <div class="module-lessons" style="padding:15px 20px;background:rgba(218, 4, 128, 0.05);">
                        <?php if ($show_content) { 
                            // MOSTRAR CONTENIDO - Módulo desbloqueado o usuario compró
                            if (!empty($module['lessons'])) {
                                foreach ($module['lessons'] as $lesson) { ?>
                                <div class="lesson-item-display" style="display:flex;align-items:center;gap:12px;padding:10px 0;color:#9ca3af;font-size:15px;border-bottom:1px solid rgba(218, 4, 128, 0.1);transition:all 0.2s;" onmouseover="this.style.paddingLeft='8px';this.style.color='#da0480'" onmouseout="this.style.paddingLeft='0';this.style.color='#9ca3af'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#da0480" stroke="#da0480" stroke-width="0">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span><?php echo esc_html($lesson); ?></span>
                                </div>
                            <?php }
                            }
                        } else { 
                            // MÓDULO BLOQUEADO - Mostrar mensaje
                            ?>
                            <div class="locked-content" style="padding:40px 30px;text-align:center;background:linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(20, 25, 35, 0.6) 100%);">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2" style="margin:0 auto 15px;display:block;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <h4 style="color:#da0480;margin:0 0 10px 0;font-size:18px;font-weight:700;">Contenido Privado</h4>
                                <p style="color:#9ca3af;margin:0;font-size:15px;">Disponible solo para nuestros alumnos.</p>
                                <p style="color:#6b7280;margin:10px 0 0 0;font-size:14px;">
                                    <?php 
                                    if (!empty($module['lessons'])) {
                                        echo '▶ ' . count($module['lessons']) . ' lecciones en este módulo';
                                    }
                                    ?>
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <style>
        @media (max-width: 768px) {
            .course-curriculum-section {
                padding: 20px !important;
                margin: 30px 0 !important;
            }
            .curriculum-stats {
                gap: 20px !important;
                font-size: 13px !important;
            }
            .curriculum-stats > div {
                gap: 6px !important;
            }
            .curriculum-stats svg {
                width: 18px !important;
                height: 18px !important;
            }
            .curriculum-stats span {
                font-size: 13px !important;
            }
            .module-header {
                padding: 15px !important;
            }
            .module-header span {
                font-size: 14px !important;
            }
            .lesson-item-display {
                font-size: 14px !important;
            }
            .locked-content {
                padding: 30px 20px !important;
            }
        }
    </style>
    
    <script>
    function toggleModuleShortcode(header) {
        var module = header.closest('.curriculum-module');
        var content = module.querySelector('.module-content');
        var chevron = module.querySelector('.chevron-icon');
        var isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
        
        if (isOpen) {
            content.style.maxHeight = '0';
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            chevron.style.transform = 'rotate(180deg)';
        }
    }
    </script>
    <?php
    
    return ob_get_clean();
}

// ============================================
// RESEÑAS Y COMENTARIOS PERSONALIZADOS CON GUARDADO - MEJORADO
// ============================================

// Paso 1: Procesar formulario enviado
add_action('init', 'process_product_review_submission');
function process_product_review_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_nonce'])) {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['review_nonce'], 'submit_review_' . $_POST['product_id'])) {
            wp_die('Error de seguridad');
        }
        
        // Verificar que el usuario esté autenticado
        if (!is_user_logged_in()) {
            wp_die('Debes estar logueado para dejar una reseña');
        }
        
        $product_id = intval($_POST['product_id']);
        $rating = intval($_POST['rating']);
        $author = sanitize_text_field($_POST['author']);
        $email = sanitize_email($_POST['email']);
        $comment = sanitize_textarea_field($_POST['comment']);
        
        // Validar datos
        if (empty($comment) || empty($author) || empty($email) || $rating < 1 || $rating > 5) {
            wp_die('Por favor completa todos los campos correctamente');
        }
        
        // Preparar datos del comentario
        $comment_data = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_author_url' => '',
            'comment_content' => $comment,
            'comment_type' => 'review',
            'comment_approved' => 0, // Pendiente de aprobación
            'user_id' => get_current_user_id(),
        );
        
        // Insertar comentario
        $comment_id = wp_insert_comment($comment_data);
        
        if ($comment_id) {
            // Guardar la calificación como meta
            update_comment_meta($comment_id, 'rating', $rating);
            
            // Redirigir con mensaje
            wp_redirect(add_query_arg('review_added', 'success', get_permalink($product_id)));
            exit;
        } else {
            wp_die('Error al guardar la reseña');
        }
    }
}

// Paso 2: Mostrar mensaje de éxito
add_action('wp_head', 'show_review_success_message');
function show_review_success_message() {
    if (isset($_GET['review_added']) && $_GET['review_added'] === 'success') {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                alert("¡Gracias por tu reseña! Aparecerá después de ser revisada por el administrador.");
            });
        </script>';
    }
}

// Paso 3: Shortcode para mostrar reseñas
add_shortcode('resenas_producto', 'show_product_reviews_custom');
function show_product_reviews_custom() {
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
    $product_title = $product->get_name(); // Obtener nombre del producto
    
    $args = array(
        'post_id' => $product_id,
        'status' => 'approve',
        'number' => 1,
        'orderby' => 'comment_date_gmt',
        'order' => 'DESC',
        'type' => 'review'
    );
    
    $comments = get_comments($args);
    
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
            margin-bottom: 0px;
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
            margin-bottom: 0px;
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
                margin-bottom: 0px;
            }
            
            .resena-author-name {
                font-size: 15px;
            }
            
            .rating-stars {
                font-size: 16px;
                margin-bottom: 0px;
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
        <?php if (!empty($comments)) {
            $comment = $comments[0];
            $rating = get_comment_meta($comment->comment_ID, 'rating', true);
            $author_name = $comment->comment_author;
            $author_email = $comment->comment_author_email;
            $author_avatar = get_avatar($author_email, 60);
            $comment_date = get_comment_date('d/m/Y', $comment->comment_ID);
            $comment_text = $comment->comment_content;
            
            // Calcular cantidad de reseñas
            $all_comments = get_comments(array(
                'post_id' => $product_id,
                'status' => 'approve',
                'type' => 'review'
            ));
            $total_reviews = count($all_comments);
            ?>
            
            <div class="resena-destacada">
                <h2>
                    ⭐ <?php echo esc_html($total_reviews); ?> valoración<?php echo $total_reviews != 1 ? 'es' : ''; ?> en <?php echo esc_html($product_title); ?>
                </h2>
                
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
                            <span class="resena-author-name">
                                <?php echo esc_html($author_name); ?>
                            </span>
                            <span class="resena-date">
                                <?php echo esc_html($comment_date); ?>
                            </span>
                        </div>
                        
                        <!-- Estrellas -->
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<span style="color:#da0480;">★</span>' : '<span style="color:#4b5563;">★</span>';
                            } ?>
                        </div>
                        
                        <!-- Texto Comentario -->
                        <p class="resena-text">
                            <?php echo wp_kses_post(nl2br($comment_text)); ?>
                        </p>
                    </div>
                </div>
            </div>
            
        <?php } ?>
        
        <!-- SECCIÓN 2: FORMULARIO PARA NUEVA RESEÑA -->
        <div class="resena-formulario">
            <h3>✍️ Añade tu valoración al curso <?php echo esc_html($product_title); ?></h3>
            
            <?php if (!is_user_logged_in()) { ?>
                <!-- Mensaje si no está logueado -->
                <div class="login-message">
                    <p style="margin:0;">
                        Debes <label for="sp-modal-main">iniciar sesión</label> para publicar una valoración.
                    </p>
                </div>
            <?php } else { ?>
                <!-- Formulario -->
                <form method="post">
                    
                    <!-- Calificación con Estrellas -->
                    <div class="form-group">
                        <label class="form-label">
                            ¿Cuántas estrellas le das? *
                        </label>
                        <div class="star-rating-input">
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <span class="star-click" data-rating="<?php echo $i; ?>" data-product="<?php echo $product_id; ?>">★</span>
                            <?php } ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-<?php echo $product_id; ?>" value="5">
                    </div>
                    
                    <!-- Nombre -->
                    <div class="form-group">
                        <label class="form-label">Tu nombre *</label>
                        <input type="text" name="author" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>" required class="form-input">
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label">Tu email *</label>
                        <input type="email" name="email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required class="form-input">
                    </div>
                    
                    <!-- Comentario -->
                    <div class="form-group">
                        <label class="form-label">Tu reseña *</label>
                        <textarea name="comment" placeholder="Comparte tu experiencia con este curso..." required class="form-textarea"></textarea>
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
            <?php } ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Inicializar estrellas con rating 5
        $('.star-click').each(function() {
            if ($(this).data('rating') <= 5) {
                $(this).addClass('star-active');
            }
        });
        
        // Click en estrellas
        $('.star-click').on('click', function() {
            var rating = $(this).data('rating');
            var product_id = $(this).data('product');
            $('#rating-' + product_id).val(rating);
            
            $('.star-click[data-product="' + product_id + '"]').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('star-active');
                } else {
                    $(this).removeClass('star-active');
                }
            });
        });
        
        // Hover estrellas
        $('.star-click').on('mouseenter', function() {
            var rating = $(this).data('rating');
            var product_id = $(this).data('product');
            
            $('.star-click[data-product="' + product_id + '"]').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('star-active');
                } else {
                    $(this).removeClass('star-active');
                }
            });
        }).on('mouseleave', function() {
            var product_id = $(this).data('product');
            var current_rating = $('#rating-' + product_id).val();
            
            $('.star-click[data-product="' + product_id + '"]').each(function() {
                if ($(this).data('rating') <= current_rating) {
                    $(this).addClass('star-active');
                } else {
                    $(this).removeClass('star-active');
                }
            });
        });
    });
    </script>
    <?php
    
    return ob_get_clean();
}



// ============================================
// DESCARGAS EXTERNAS - BYPASS TOTAL
// ============================================

// Forzar redirección INMEDIATA sin validación
add_action('template_redirect', 'force_immediate_redirect_for_downloads');
function force_immediate_redirect_for_downloads() {
    // Solo en URLs de descarga de WooCommerce
    if (isset($_GET['download_file']) && isset($_GET['order']) && isset($_GET['email'])) {
        
        $product_id = isset($_GET['download_file']) ? intval($_GET['download_file']) : 0;
        $order_key = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : '';
        $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        
        if ($product_id && $order_key && $email) {
            // Obtener producto
            $product = wc_get_product($product_id);
            
            if ($product && $product->is_downloadable()) {
                $downloads = $product->get_downloads();
                
                if (!empty($downloads)) {
                    // Tomar el primer archivo descargable
                    $download = reset($downloads);
                    $file_url = $download->get_file();
                    
                    // Si es URL externa, redirigir inmediatamente
                    if (filter_var($file_url, FILTER_VALIDATE_URL)) {
                        // Limpiar buffer
                        if (ob_get_level()) {
                            ob_end_clean();
                        }
                        
                        // Redirigir
                        wp_redirect($file_url, 302);
                        exit;
                    }
                }
            }
        }
    }
}

// Deshabilitar validación de archivo para URLs externas
add_filter('woocommerce_downloadable_file_exists', '__return_true', 999);

// Forzar método redirect
add_filter('woocommerce_file_download_method', function() { return 'redirect'; }, 999);

// Bypass de verificación de ruta
add_filter('woocommerce_file_download_path', 'return_external_url_as_is', 999, 3);
function return_external_url_as_is($file_path, $product_id, $download_id) {
    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        return $file_path;
    }
    return $file_path;
}

// Evitar headers que causen error 500
add_action('woocommerce_before_download_product', function() {
    @ini_set('display_errors', 0);
    @error_reporting(0);
    if (ob_get_level()) {
        ob_end_clean();
    }
}, 1);



// ============================================
// FIX PARA DESCARGAS EN MI CUENTA
// ============================================

// Interceptar descargas desde "Mi cuenta"
add_action('init', 'fix_my_account_downloads', 1);
function fix_my_account_downloads() {
    // Detectar URL de descarga de WooCommerce
    global $wp;
    
    if (isset($wp->query_vars['downloads'])) {
        $download_ids = explode('/', $wp->query_vars['downloads']);
        
        if (count($download_ids) === 2) {
            $download_id = $download_ids[0];
            $email = base64_decode($download_ids[1]);
            
            // Obtener datos de descarga
            global $wpdb;
            $download = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions 
                WHERE download_id = %s AND user_email = %s
            ", $download_id, $email));
            
            if ($download) {
                $product_id = $download->product_id;
                $product = wc_get_product($product_id);
                
                if ($product && $product->is_downloadable()) {
                    $downloads = $product->get_downloads();
                    
                    if (!empty($downloads)) {
                        foreach ($downloads as $download_item) {
                            $file_url = $download_item->get_file();
                            
                            // Si es URL externa
                            if (filter_var($file_url, FILTER_VALIDATE_URL)) {
                                // Limpiar buffer
                                while (ob_get_level()) {
                                    ob_end_clean();
                                }
                                
                                // Redirigir
                                wp_redirect($file_url, 302);
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
}

// Modificar el link de descarga en "Mi cuenta"
add_filter('woocommerce_customer_get_downloadable_products', 'modify_download_links_in_account');
function modify_download_links_in_account($downloads) {
    foreach ($downloads as &$download) {
        $product_id = $download['product_id'];
        $product = wc_get_product($product_id);
        
        if ($product && $product->is_downloadable()) {
            $product_downloads = $product->get_downloads();
            
            if (!empty($product_downloads)) {
                foreach ($product_downloads as $product_download) {
                    $file_url = $product_download->get_file();
                    
                    // Si es URL externa, modificar el link
                    if (filter_var($file_url, FILTER_VALIDATE_URL)) {
                        $download['download_url'] = $file_url;
                    }
                }
            }
        }
    }
    
    return $downloads;
}

// Agregar target="_blank" al link de descarga
add_filter('woocommerce_account_downloads_column_download-file', 'add_target_blank_to_downloads', 10, 1);
function add_target_blank_to_downloads($download) {
    $product = wc_get_product($download['product_id']);
    
    if ($product && $product->is_downloadable()) {
        $downloads = $product->get_downloads();
        
        if (!empty($downloads)) {
            foreach ($downloads as $item) {
                $url = $item->get_file();
                
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" class="woocommerce-Button button download">' . esc_html($item->get_name()) . '</a>';
                    return;
                }
            }
        }
    }
    
    // Default
    echo '<a href="' . esc_url($download['download_url']) . '" class="woocommerce-Button button download">' . esc_html($download['download_name']) . '</a>';
}














/* ============================================
   ACCEDER → MODAL LOGIN/REGISTER (DISEÑO MODERNO - FIX DROPDOWN)
   Shortcode: [sp_auth]
   Color: #da0480
   ============================================ */

// Variable global para rastrear si ya se cargó el modal
global $sp_auth_modal_loaded;
$sp_auth_modal_loaded = false;

if (!function_exists('sp_auth_button_shortcode')) {
  add_shortcode('sp_auth', 'sp_auth_button_shortcode');
  function sp_auth_button_shortcode() {
    global $sp_auth_modal_loaded;
    $sp_auth_modal_loaded = true;
    
    if (is_user_logged_in()) {
      $user_id = get_current_user_id();
      $user = wp_get_current_user();
      $avatar = get_avatar_url($user_id, array('size' => 80));
      $display_name = $user->display_name;
      
      // Generar ID único para este dropdown
      $dropdown_id = 'sp-dropdown-' . uniqid();
      
      ob_start(); ?>
      <div class="sp-auth-logged-wrapper" style="position:relative;z-index:99999999!important;display:inline-block">
        <input type="checkbox" id="<?php echo $dropdown_id; ?>" class="sp-avatar-toggle" style="display:none!important" />
        <label for="<?php echo $dropdown_id; ?>" class="sp-avatar-label" style="position:relative;z-index:99999999!important;cursor:pointer!important;pointer-events:auto!important;display:inline-flex;align-items:center;gap:10px;padding:6px 12px 6px 6px;border-radius:50px;background:linear-gradient(135deg,rgba(218,4,128,.15),rgba(218,4,128,.08));border:1.5px solid rgba(218,4,128,.3);transition:all .3s;backdrop-filter:blur(10px)">
          <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($display_name); ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(218,4,128,.5);pointer-events:none" />
          <span style="color:#da0480;font-weight:600;font-size:14px;pointer-events:none;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?php echo esc_html($display_name); ?>
          </span>
          <svg style="width:16px;height:16px;fill:#da0480;transition:.3s;pointer-events:none" class="sp-caret-icon" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
        </label>
        
        <div class="sp-user-dropdown-modern sp-dropdown-<?php echo $dropdown_id; ?>" style="position:absolute;top:calc(100% + 8px);right:0;min-width:260px;background:linear-gradient(135deg,#0d0d0d,#1a1a1a);border:1.5px solid rgba(218,4,128,.25);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.6),0 0 40px rgba(218,4,128,.1);opacity:0;visibility:hidden;transform:translateY(-15px) scale(.95);transition:all .3s cubic-bezier(.4,0,.2,1);z-index:999999999!important;pointer-events:auto!important;overflow:hidden">
          
          <?php
          $account = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/mi-cuenta/');
          $logout = wp_logout_url(home_url('/tienda/'));
          ?>
          
          <div style="padding:16px;border-bottom:1px solid rgba(218,4,128,.15);background:linear-gradient(135deg,rgba(218,4,128,.08),rgba(218,4,128,.03))">
            <div style="display:flex;align-items:center;gap:12px">
              <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($display_name); ?>" style="width:48px;height:48px;border-radius:50%;border:2px solid rgba(218,4,128,.4)" />
              <div>
                <div style="color:#fff;font-weight:700;font-size:15px;margin-bottom:2px"><?php echo esc_html($display_name); ?></div>
                <div style="color:#9ca3af;font-size:12px"><?php echo esc_html($user->user_email); ?></div>
                <?php if (function_exists('coins_manager')):
                  $user_coins = coins_manager()->get_coins($user_id);
                ?>
                  <div style="margin-top:6px;display:flex;align-items:center;gap:8px;font-size:12px;color:#e5e7eb">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:rgba(218,4,128,.15);border:1px solid rgba(218,4,128,.4)">
                      <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins" style="width:14px;height:14px;object-fit:contain">
                    </span>
                    <span><strong><?php echo esc_html(coins_manager()->format_coins($user_coins)); ?></strong> coins disponibles</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div style="padding:8px">
            <a href="<?php echo esc_url($account); ?>" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
              <span>Escritorio</span>
            </a>
            <a href="<?php echo esc_url(function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url('orders', '', $account) : home_url('/mi-cuenta/orders/')); ?>" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 16H5V5h2v3h10V5h2v14z"/></svg>
              <span>Pedidos</span>
            </a>
            <a href="<?php echo esc_url(function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url('downloads', '', $account) : home_url('/mi-cuenta/downloads/')); ?>" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/></svg>
              <span>Mis cursos</span>
            </a>
            <a href="/mi-cuenta/soporte/" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
              <span>Soporte</span>
            </a>
            <a href="<?php echo esc_url(function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url('edit-account', '', $account) : home_url('/mi-cuenta/edit-account/')); ?>" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              <span>Mi perfil</span>
            </a>
            <a href="/mi-cuenta/lista-deseos/" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#e5e7eb;text-decoration:none;transition:.2s;font-size:14px">
              <svg style="width:20px;height:20px;fill:currentColor;opacity:.7" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
              <span>Lista de deseos</span>
            </a>
          </div>
          
          <div style="padding:8px;border-top:1px solid rgba(218,4,128,.15)">
            <a href="<?php echo esc_url($logout); ?>" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#ff8a8a;text-decoration:none;transition:.2s;font-size:14px;font-weight:600">
              <svg style="width:20px;height:20px;fill:currentColor" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
              <span>Cerrar sesión</span>
            </a>
          </div>
        </div>
      </div>
      
      <style>
        /* Dropdown específico visible */
        #<?php echo $dropdown_id; ?>:checked ~ .sp-dropdown-<?php echo $dropdown_id; ?>{
          opacity:1!important;
          visibility:visible!important;
          transform:translateY(0) scale(1)!important;
        }
        /* Rotar caret */
        #<?php echo $dropdown_id; ?>:checked ~ .sp-avatar-label .sp-caret-icon{
          transform:rotate(180deg);
        }
        /* Hover avatar */
        .sp-avatar-label:hover{
          transform:translateY(-2px);
          box-shadow:0 8px 24px rgba(218,4,128,.2);
          border-color:#da0480!important;
        }
        /* Hover links */
        .sp-dropdown-<?php echo $dropdown_id; ?> a:hover{
          background:rgba(218,4,128,.12)!important;
          color:#da0480!important;
          transform:translateX(4px);
        }
        @media (max-width:480px){
          .sp-avatar-label span{display:none}
          .sp-avatar-label{padding:6px!important}
        }
      </style>
      
      <script>
      // Cerrar dropdown al hacer clic fuera
      (function() {
        const dropdownId = '<?php echo $dropdown_id; ?>';
        const checkbox = document.getElementById(dropdownId);
        
        document.addEventListener('click', function(e) {
          // Si el dropdown está abierto
          if (checkbox && checkbox.checked) {
            const wrapper = checkbox.closest('.sp-auth-logged-wrapper');
            // Si el clic fue fuera del wrapper, cerrar
            if (wrapper && !wrapper.contains(e.target)) {
              checkbox.checked = false;
            }
          }
        });
      })();
      </script>
      <?php return ob_get_clean();
    }
    
    // Usuario NO logueado
    ob_start(); ?>
    <label for="sp-modal-main" class="sp-auth-btn-modern" style="position:relative!important;z-index:99999999!important;cursor:pointer!important;pointer-events:auto!important;display:inline-flex!important;align-items:center;gap:8px;padding:11px 20px;border-radius:50px;background:linear-gradient(135deg,rgba(218,4,128,.15),rgba(218,4,128,.08))!important;border:1.5px solid rgba(218,4,128,.4)!important;color:#da0480!important;font-weight:700;font-size:14px;user-select:none;transition:all .3s;backdrop-filter:blur(10px);box-shadow:0 4px 20px rgba(218,4,128,.15)">
      <svg style="width:18px;height:18px;fill:currentColor;pointer-events:none" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      <span style="pointer-events:none">Acceder</span>
    </label>
    <?php return ob_get_clean();
  }
}

// Renderizar modal en footer
if (!function_exists('sp_auth_render_modal')) {
  add_action('wp_footer', 'sp_auth_render_modal', 99999);
  function sp_auth_render_modal() {
    global $sp_auth_modal_loaded;
    if (!$sp_auth_modal_loaded || is_user_logged_in()) return;
    
    $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
    ?>
    <div class="sp-auth-modal-container" style="position:fixed;inset:0;pointer-events:none;z-index:2147483647!important">
      <input type="checkbox" id="sp-modal-main" class="sp-modal-toggle" style="display:none" />
      
      <div class="sp-auth-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0);backdrop-filter:blur(0);opacity:0;visibility:hidden;transition:all .3s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:2147483646!important">
        <label for="sp-modal-main" class="sp-modal-close" style="position:absolute;inset:0;cursor:default;pointer-events:none"></label>
        
        <div class="sp-auth-dialog" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(.92);width:440px;max-width:94vw;background:linear-gradient(135deg,#0f0f0f,#1a1a1a);color:#fff;border-radius:24px;border:1.5px solid rgba(218,4,128,.25);padding:30px  30px 150px 30px;box-shadow:0 30px 90px rgba(0,0,0,.7),0 0 60px rgba(218,4,128,.12);transition:all .3s cubic-bezier(.4,0,.2,1);pointer-events:auto;z-index:2147483647!important;backdrop-filter:blur(20px)">
          
          <input type="radio" name="sp-tab-main" id="sp-tab-login" class="sp-tab-input" checked style="display:none" />
          <input type="radio" name="sp-tab-main" id="sp-tab-register" class="sp-tab-input" style="display:none" />
          
          <div class="sp-auth-tabs" style="display:flex;gap:8px;margin-bottom:28px;padding:6px;background:rgba(0,0,0,.3);border-radius:16px;border:1px solid rgba(255,255,255,.05)">
            <label for="sp-tab-login" class="sp-auth-tab sp-tab-login" style="flex:1;padding:12px 16px;border-radius:12px;color:#9ca3af;font-weight:700;cursor:pointer;text-align:center;transition:all .3s;user-select:none;font-size:15px;position:relative">
              Iniciar sesión
            </label>
            <label for="sp-tab-register" class="sp-auth-tab sp-tab-register" style="flex:1;padding:12px 16px;border-radius:12px;color:#9ca3af;font-weight:700;cursor:pointer;text-align:center;transition:all .3s;user-select:none;font-size:15px;position:relative">
              Registrarme
            </label>
          </div>

          <div class="sp-auth-panels" style="position:relative;min-height:360px">
            <!-- Login -->
            <div class="sp-auth-panel sp-panel-login" style="position:absolute;inset:0;opacity:1;visibility:visible;transition:all .3s">
              <form method="post" action="<?php echo esc_url($account_url); ?>">
                <div style="text-align:center;margin-bottom:24px">
                  <div style="width:64px;height:64px;margin:0 auto 16px;background:linear-gradient(135deg,rgba(218,4,128,.2),rgba(218,4,128,.1));border-radius:20px;display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(218,4,128,.3)">
                    <svg style="width:32px;height:32px;fill:#da0480" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </div>
                  <h3 style="margin:0 0 8px;font-size:24px;color:#fff;font-weight:800">¡Bienvenido!</h3>
                  <p style="margin:0;color:#9ca3af;font-size:14px">Inicia sesión para continuar</p>
                </div>
                
                <label style="display:block;margin:0 0 18px">
                  <span style="display:block;font-size:13px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Correo o usuario</span>
                  <input type="text" name="username" required autocomplete="username" style="width:100%;height:48px;padding:12px 16px;border-radius:14px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);color:#fff;transition:.3s;font-size:15px" />
                </label>
                
                <label style="display:block;margin:0 0 16px">
                  <span style="display:block;font-size:13px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Contraseña</span>
                  <input type="password" name="password" required autocomplete="current-password" style="width:100%;height:48px;padding:12px 16px;border-radius:14px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);color:#fff;transition:.3s;font-size:15px" />
                </label>
                
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
                  <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
                    <input type="checkbox" name="rememberme" value="forever" style="width:18px;height:18px;cursor:pointer" />
                    <span>Recordarme</span>
                  </label>
                  <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" style="color:#da0480;text-decoration:none;font-size:13px;font-weight:600">¿Olvidaste tu contraseña?</a>
                </div>
                
                <?php if (function_exists('wp_nonce_field')) wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                <input type="hidden" name="login" value="Acceder" />
                
                <button type="submit" style="width:100%;height:52px;padding:14px;border-radius:14px;background:linear-gradient(135deg,#da0480,#b00368);color:#fff;font-weight:800;border:none;cursor:pointer;letter-spacing:.3px;transition:.3s;box-shadow:0 8px 24px rgba(218,4,128,.3);font-size:16px">
                  Iniciar sesión
                </button>
              </form>
            </div>

            <!-- Register -->
            <div class="sp-auth-panel sp-panel-register" style="position:absolute;inset:0;opacity:0;visibility:hidden;transition:all .3s">
              <form method="post" action="<?php echo esc_url($account_url); ?>">
                <div style="text-align:center;margin-bottom:24px">
                  <div style="width:64px;height:64px;margin:0 auto 16px;background:linear-gradient(135deg,rgba(218,4,128,.2),rgba(218,4,128,.1));border-radius:20px;display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(218,4,128,.3)">
                    <svg style="width:32px;height:32px;fill:#da0480" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </div>
                  <h3 style="margin:0 0 8px;font-size:24px;color:#fff;font-weight:800">Crear cuenta</h3>
                  <p style="margin:0;color:#9ca3af;font-size:14px">Únete a nuestra comunidad</p>
                </div>
                
                <label style="display:block;margin:0 0 18px">
                  <span style="display:block;font-size:13px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Correo electrónico</span>
                  <input type="email" name="email" required autocomplete="email" style="width:100%;height:48px;padding:12px 16px;border-radius:14px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);color:#fff;transition:.3s;font-size:15px" />
                </label>
                
                <?php
                  if (apply_filters('woocommerce_registration_generate_username', 'no') === 'no') {
                    echo '<label style="display:block;margin:0 0 18px"><span style="display:block;font-size:13px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Usuario</span><input type="text" name="username" required autocomplete="username" style="width:100%;height:48px;padding:12px 16px;border-radius:14px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);color:#fff;transition:.3s;font-size:15px" /></label>';
                  }
                  if (apply_filters('woocommerce_registration_generate_password', 'no') === 'no') {
                    echo '<label style="display:block;margin:0 0 24px"><span style="display:block;font-size:13px;margin-bottom:8px;color:#cbd5e1;font-weight:600">Contraseña</span><input type="password" name="password" required autocomplete="new-password" style="width:100%;height:48px;padding:12px 16px;border-radius:14px;background:rgba(0,0,0,.3);border:1.5px solid rgba(255,255,255,.08);color:#fff;transition:.3s;font-size:15px" /></label>';
                  }
                  if (function_exists('wp_nonce_field')) wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce');
                ?>
                <input type="hidden" name="register" value="Registrarme" />
                
                <button type="submit" style="width:100%;height:52px;padding:14px;border-radius:14px;background:linear-gradient(135deg,#da0480,#b00368);color:#fff;font-weight:800;border:none;cursor:pointer;letter-spacing:.3px;transition:.3s;box-shadow:0 8px 24px rgba(218,4,128,.3);font-size:16px">
                  Crear cuenta
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
      /* Hover botón principal */
      .sp-auth-btn-modern:hover{
        transform:translateY(-2px)!important;
        box-shadow:0 8px 28px rgba(218,4,128,.25)!important;
        border-color:#da0480!important;
      }
      
      /* Tabs activos */
      #sp-tab-login:checked ~ .sp-auth-tabs .sp-tab-login{
        color:#fff!important;
        background:linear-gradient(135deg,#da0480,#b00368)!important;
        box-shadow:0 4px 16px rgba(218,4,128,.35)!important;
      }
      #sp-tab-register:checked ~ .sp-auth-tabs .sp-tab-register{
        color:#fff!important;
        background:linear-gradient(135deg,#da0480,#b00368)!important;
        box-shadow:0 4px 16px rgba(218,4,128,.35)!important;
      }
      
      /* Panels visibilidad */
      #sp-tab-login:checked ~ .sp-auth-panels .sp-panel-login{opacity:1!important;visibility:visible!important;transform:translateX(0)!important}
      #sp-tab-login:checked ~ .sp-auth-panels .sp-panel-register{opacity:0!important;visibility:hidden!important;transform:translateX(20px)!important}
      #sp-tab-register:checked ~ .sp-auth-panels .sp-panel-login{opacity:0!important;visibility:hidden!important;transform:translateX(-20px)!important}
      #sp-tab-register:checked ~ .sp-auth-panels .sp-panel-register{opacity:1!important;visibility:visible!important;transform:translateX(0)!important}
      
      /* 🔒 FIX DEFINITIVO: Deshabilitar TODO el modal cuando está cerrado */
      .sp-auth-modal-overlay{
        pointer-events:none!important;
      }
      .sp-auth-modal-overlay .sp-modal-close{
        pointer-events:none!important;
      }
      .sp-auth-modal-overlay .sp-auth-dialog{
        pointer-events:none!important;
      }
      .sp-auth-modal-overlay .sp-auth-dialog *{
        pointer-events:none!important;
      }
      
      /* Modal abierto - Habilitar TODO */
      #sp-modal-main:checked ~ .sp-auth-modal-overlay{
        opacity:1!important;
        visibility:visible!important;
        background:rgba(0,0,0,.8)!important;
        backdrop-filter:blur(8px)!important;
        pointer-events:auto!important;
      }
      #sp-modal-main:checked ~ .sp-auth-modal-overlay .sp-modal-close{
        pointer-events:auto!important;
      }
      #sp-modal-main:checked ~ .sp-auth-modal-overlay .sp-auth-dialog{
        transform:translate(-50%,-50%) scale(1)!important;
        pointer-events:auto!important;
      }
      #sp-modal-main:checked ~ .sp-auth-modal-overlay .sp-auth-dialog *{
        pointer-events:auto!important;
      }
      
      /* Inputs focus */
      .sp-auth-panel input[type="text"]:focus,
      .sp-auth-panel input[type="email"]:focus,
      .sp-auth-panel input[type="password"]:focus{
        outline:none!important;
        border-color:#da0480!important;
        box-shadow:0 0 0 4px rgba(218,4,128,.15)!important;
        background:rgba(0,0,0,.4)!important;
      }
      
      /* Hover buttons submit */
      button[type="submit"]:hover{
        transform:translateY(-2px)!important;
        box-shadow:0 12px 32px rgba(218,4,128,.4)!important;
      }
      button[type="submit"]:active{
        transform:translateY(0)!important;
      }
    </style>
    <script>
      (function() {
        document.addEventListener('DOMContentLoaded', function() {
          var checkoutUrl = '<?php echo esc_js(wc_get_checkout_url()); ?>';
          
          var loginForm    = document.querySelector('.sp-panel-login form');
          var registerForm = document.querySelector('.sp-panel-register form');

          function attachRedirect(form) {
            if (!form) return;
            form.addEventListener('submit', function() {
              // Solo redirigir si venimos de "Comprar ahora"
              if (window.sp_after_login_redirect === checkoutUrl) {
                if (!form.querySelector('input[name="redirect_to"]')) {
                  var input = document.createElement('input');
                  input.type  = 'hidden';
                  input.name  = 'redirect_to';
                  input.value = checkoutUrl;
                  form.appendChild(input);
                }
              }
            });
          }

          attachRedirect(loginForm);
          attachRedirect(registerForm);
        });
      })();
    </script>
    <?php
  }
}






/* -----------------------------------------------------------
 * 1. AGREGAR CAMPO DE VIDEO A LA PÁGINA DE PRODUCTO (BACKEND)
 * ----------------------------------------------------------- */
add_action('woocommerce_product_options_general_product_data', function() {
    woocommerce_wp_text_input([
        'id'          => '_video_producto',
        'label'       => 'URL del video del producto (MP4)',
        'placeholder' => 'https://tusitio.com/video.mp4',
        'desc_tip'    => true,
        'description' => 'Coloca aquí el enlace directo al video MP4.',
    ]);
});

/* Guardar */
add_action('woocommerce_admin_process_product_object', function($product){
    if (isset($_POST['_video_producto'])) {
        $product->update_meta_data('_video_producto', esc_url_raw($_POST['_video_producto']));
    }
});

/* -----------------------------------------------------------
 * 2. MOSTRAR EL VIDEO EN LA IMAGEN PRINCIPAL DEL PRODUCTO
 * ----------------------------------------------------------- */
add_filter('woocommerce_single_product_image_thumbnail_html', function($html, $post_thumbnail_id){
    global $post;

    $video = get_post_meta($post->ID, '_video_producto', true);
    if (!$video) return $html; // si no tiene video, deja la imagen

    ob_start(); ?>

    <div class="product-video-wrapper" style="position:relative; width:100%;">

        <video id="productPreviewVideo"
               src="<?php echo esc_url($video); ?>"
               muted
               playsinline
               preload="auto"
               style="width:100%; border-radius:10px; cursor:pointer;">
        </video>

        <!-- Ícono Play -->
        <div id="videoPlayButton" style="
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%, -50%);
            background:#000;
            border:3px solid #da0480;
            width:70px;
            height:70px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:0.3s;
        ">
            <div style="
                width:0;
                height:0;
                border-left:20px solid #da0480;
                border-top:12px solid transparent;
                border-bottom:12px solid transparent;
                margin-left:5px;
            "></div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const video = document.getElementById("productPreviewVideo");
            const btn   = document.getElementById("videoPlayButton");

            if (video) {
                // Reproducción automática de 10 segundos en silencio
                video.currentTime = 0;
                video.play();

                setTimeout(() => {
                    video.pause();
                }, 10000); // 10 segundos

                // Cuando se hace clic → reproducir completo con sonido
                btn.addEventListener("click", function() {
                    btn.style.display = "none";
                    video.muted = false;
                    video.play();
                });

                video.addEventListener("click", function() {
                    btn.style.display = "none";
                    video.muted = false;
                    video.play();
                });
            }
        });
    </script>

    <?php return ob_get_clean();

}, 10, 2);





// ========================================
// VIDEO CON AUTOPLAY Y MODAL - WOOCOMMERCE
// ========================================

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

// 4. SHORTCODE - Video con autoplay o imagen
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
                   muted loop playsinline preload="metadata">
                <source src="' . esc_url($video_url) . '" type="video/mp4">
                Tu navegador no soporta videos HTML5.
            </video>
            
            <!-- Overlay con botón de play MÁS PEQUEÑO -->
            <div class="video-play-overlay" id="overlay-' . $unique_id . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                 background: rgba(0, 0, 0, 0.3); display: flex; align-items: center; justify-content: center; 
                 transition: all 0.3s ease;">
                <div class="play-button-modal" style="width: 60px; height: 60px; background: #da0480; 
                     border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                     box-shadow: 0 4px 15px rgba(255, 215, 0, 0.5); transition: all 0.3s ease;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5v14l11-7z" fill="#000000"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Modal para el video completo -->
        <div id="' . $unique_id . '" class="video-modal" style="display: none; position: fixed; z-index: 9999; 
             left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.9);">
            
            <!-- Botón cerrar -->
            <span class="close-modal" id="close-' . $unique_id . '" 
                  style="position: absolute; top: 20px; right: 35px; color: #da0480; font-size: 40px; 
                         font-weight: bold; cursor: pointer; z-index: 10000; transition: 0.3s;">
                &times;
            </span>
            
            <!-- Contenido del modal -->
            <div class="modal-content-video" style="position: relative; margin: auto; padding: 0; width: 90%; 
                 max-width: 900px; top: 50%; transform: translateY(-50%);">
                <video id="video-' . $unique_id . '" 
                       style="width: 100%; height: auto; display: block; border-radius: 10px;" 
                       controls>
                    <source src="' . esc_url($video_url) . '" type="video/mp4">
                    Tu navegador no soporta videos HTML5.
                </video>
            </div>
        </div>
        
        <style>
            .producto-video-preview:hover .video-play-overlay {
                background: rgba(0, 0, 0, 0.5);
            }
            
            .producto-video-preview:hover .play-button-modal {
                transform: scale(1.15);
                box-shadow: 0 6px 20px rgba(255, 215, 0, 0.7);
            }
            
            .video-play-overlay.hidden {
                opacity: 0;
                pointer-events: none;
            }
            
            .close-modal:hover,
            .close-modal:focus {
                color: #fff;
                text-decoration: none;
                cursor: pointer;
            }
            
            @keyframes fadeIn {
                from {opacity: 0;}
                to {opacity: 1;}
            }
            
            @keyframes slideIn {
                from {
                    transform: translateY(-50%) scale(0.7);
                    opacity: 0;
                }
                to {
                    transform: translateY(-50%) scale(1);
                    opacity: 1;
                }
            }
            
            .video-modal {
                animation: fadeIn 0.3s ease-in-out;
            }
            
            .modal-content-video {
                animation: slideIn 0.3s ease-in-out;
            }
        </style>
        
        <script>
            (function() {
                var modal = document.getElementById("' . $unique_id . '");
                var trigger = document.getElementById("trigger-' . $unique_id . '");
                var closeBtn = document.getElementById("close-' . $unique_id . '");
                var videoPreview = document.getElementById("preview-' . $unique_id . '");
                var videoModal = document.getElementById("video-' . $unique_id . '");
                var overlay = document.getElementById("overlay-' . $unique_id . '");
                var hasAutoPlayed = false;
                
                // Reproducir video preview automáticamente en silencio
                if (videoPreview) {
                    videoPreview.play().catch(function(error) {
                        console.log("Autoplay bloqueado:", error);
                    });
                    
                    // Detener después de 10 segundos
                    setTimeout(function() {
                        if (!hasAutoPlayed) {
                            videoPreview.pause();
                            videoPreview.currentTime = 0;
                        }
                    }, 10000);
                }
                
                // Abrir modal al hacer clic
                if (trigger) {
                    trigger.onclick = function() {
                        hasAutoPlayed = true;
                        modal.style.display = "block";
                        if (videoPreview) {
                            videoPreview.pause();
                        }
                        if (videoModal) {
                            videoModal.play();
                        }
                    }
                }
                
                // Cerrar modal con el botón X
                if (closeBtn) {
                    closeBtn.onclick = function() {
                        modal.style.display = "none";
                        if (videoModal) {
                            videoModal.pause();
                            videoModal.currentTime = 0;
                        }
                        if (videoPreview) {
                            videoPreview.currentTime = 0;
                            videoPreview.muted = true;
                            videoPreview.play();
                            hasAutoPlayed = false;
                            
                            setTimeout(function() {
                                if (!hasAutoPlayed) {
                                    videoPreview.pause();
                                    videoPreview.currentTime = 0;
                                }
                            }, 10000);
                        }
                    }
                }
                
                // Cerrar modal al hacer clic fuera del video
                modal.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                        if (videoModal) {
                            videoModal.pause();
                            videoModal.currentTime = 0;
                        }
                        if (videoPreview) {
                            videoPreview.currentTime = 0;
                            videoPreview.muted = true;
                            videoPreview.play();
                            hasAutoPlayed = false;
                            
                            setTimeout(function() {
                                if (!hasAutoPlayed) {
                                    videoPreview.pause();
                                    videoPreview.currentTime = 0;
                                }
                            }, 10000);
                        }
                    }
                }
                
                // Cerrar modal con tecla ESC
                document.addEventListener("keydown", function(event) {
                    if (event.key === "Escape" && modal.style.display === "block") {
                        modal.style.display = "none";
                        if (videoModal) {
                            videoModal.pause();
                            videoModal.currentTime = 0;
                        }
                        if (videoPreview) {
                            videoPreview.currentTime = 0;
                            videoPreview.muted = true;
                            videoPreview.play();
                            hasAutoPlayed = false;
                            
                            setTimeout(function() {
                                if (!hasAutoPlayed) {
                                    videoPreview.pause();
                                    videoPreview.currentTime = 0;
                                }
                            }, 10000);
                        }
                    }
                });
            })();
        </script>
    </div>';
}
add_shortcode('video_producto', 'shortcode_video_producto');


// ============================================
// SISTEMA DE REPORTES DE CURSOS
// ============================================

// PASO 1: Registrar Custom Post Type para reportes
if (!function_exists('register_course_reports_cpt')) {
    add_action('init', 'register_course_reports_cpt');
    function register_course_reports_cpt() {
        $args = array(
            'labels' => array(
                'name' => 'Reportes de Cursos',
                'singular_name' => 'Reporte',
                'menu_name' => 'Reportes',
                'add_new' => 'Agregar Reporte',
                'add_new_item' => 'Agregar Nuevo Reporte',
                'edit_item' => 'Ver Reporte',
                'view_item' => 'Ver Reporte',
                'all_items' => 'Todos los Reportes',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-warning',
            'menu_position' => 26,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
            ),
            'map_meta_cap' => true,
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
        );
        register_post_type('course_report', $args);
    }
}

// PASO 2: Agregar metaboxes para información del reporte
if (!function_exists('add_report_meta_boxes')) {
    add_action('add_meta_boxes', 'add_report_meta_boxes');
    function add_report_meta_boxes() {
        add_meta_box(
            'report_details',
            'Detalles del Reporte',
            'render_report_details_meta_box',
            'course_report',
            'side',
            'high'
        );
        
        add_meta_box(
            'report_status',
            'Estado del Reporte',
            'render_report_status_meta_box',
            'course_report',
            'side',
            'high'
        );
    }
}

if (!function_exists('render_report_details_meta_box')) {
    function render_report_details_meta_box($post) {
        $report_type = get_post_meta($post->ID, '_report_type', true);
        $product_id = get_post_meta($post->ID, '_product_id', true);
        $user_id = get_post_meta($post->ID, '_user_id', true);
        $report_date = get_post_meta($post->ID, '_report_date', true);
        
        $user = get_userdata($user_id);
        $product = wc_get_product($product_id);
        
        $report_types = array(
            'outdated' => '📅 Versión desactualizada',
            'error' => '🐛 Error en el curso',
            'broken_link' => '🔗 Enlace roto',
            'wrong_info' => '❌ Información incorrecta',
            'other' => '💬 Otro'
        );
        
        echo '<div style="padding: 10px;">';
        
        echo '<p><strong>Tipo de reporte:</strong><br>';
        echo isset($report_types[$report_type]) ? $report_types[$report_type] : $report_type;
        echo '</p>';
        
        echo '<p><strong>Curso reportado:</strong><br>';
        if ($product) {
            echo '<a href="' . get_edit_post_link($product_id) . '" target="_blank">' . $product->get_name() . '</a>';
        }
        echo '</p>';
        
        echo '<p><strong>Reportado por:</strong><br>';
        if ($user) {
            echo $user->display_name . '<br>';
            echo '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
        }
        echo '</p>';
        
        echo '<p><strong>Fecha:</strong><br>';
        echo date('d/m/Y H:i', strtotime($report_date));
        echo '</p>';
        
        echo '</div>';
    }
}

// PASO 2.5: Metabox para cambiar estado
if (!function_exists('render_report_status_meta_box')) {
    function render_report_status_meta_box($post) {
        $current_status = get_post_meta($post->ID, '_report_status', true);
        if (empty($current_status)) {
            $current_status = 'pending';
        }
        
        wp_nonce_field('save_report_status', 'report_status_nonce');
        
        echo '<div style="padding: 10px;">';
        echo '<select name="report_status" id="report_status" style="width: 100%; padding: 8px; font-size: 14px;">';
        echo '<option value="pending" ' . selected($current_status, 'pending', false) . '>⏳ Pendiente</option>';
        echo '<option value="resolved" ' . selected($current_status, 'resolved', false) . '>✅ Resuelto</option>';
        echo '</select>';
        echo '<p style="color: #666; font-size: 12px; margin-top: 10px;">Al cambiar a "Resuelto", se enviará un email al usuario.</p>';
        echo '</div>';
    }
}

// PASO 2.6: Guardar estado y enviar email
if (!function_exists('save_report_status')) {
    add_action('save_post_course_report', 'save_report_status');
    function save_report_status($post_id) {
        if (!isset($_POST['report_status_nonce']) || !wp_verify_nonce($_POST['report_status_nonce'], 'save_report_status')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['report_status'])) {
            $old_status = get_post_meta($post_id, '_report_status', true);
            $new_status = sanitize_text_field($_POST['report_status']);
            
            update_post_meta($post_id, '_report_status', $new_status);
            
            // Si cambió a resuelto, enviar email
            if ($old_status !== 'resolved' && $new_status === 'resolved') {
                send_report_resolved_email($post_id);
            }
        }
    }
}

// PASO 2.7: Función para enviar email
if (!function_exists('send_report_resolved_email')) {
    function send_report_resolved_email($report_id) {
        $user_id = get_post_meta($report_id, '_user_id', true);
        $product_id = get_post_meta($report_id, '_product_id', true);
        $report_type = get_post_meta($report_id, '_report_type', true);
        
        $user = get_userdata($user_id);
        $product = wc_get_product($product_id);
        
        if (!$user || !$product) {
            return;
        }
        
        $report_types = array(
            'outdated' => 'Versión desactualizada',
            'error' => 'Error en el curso',
            'broken_link' => 'Enlace roto',
            'wrong_info' => 'Información incorrecta',
            'other' => 'Otro problema'
        );
        
        $type_label = isset($report_types[$report_type]) ? $report_types[$report_type] : $report_type;
        
        $to = $user->user_email;
        $subject = '✅ Tu reporte ha sido resuelto';
        
        $message = sprintf(
            "Hola %s,\n\n" .
            "Te informamos que el reporte que realizaste sobre el curso \"%s\" ha sido revisado y resuelto.\n\n" .
            "Tipo de reporte: %s\n\n" .
            "Puedes volver a acceder al curso aquí:\n%s\n\n" .
            "Gracias por tu colaboración para mejorar nuestros cursos.\n\n" .
            "Saludos,\n" .
            "El equipo de %s",
            $user->display_name,
            $product->get_name(),
            $type_label,
            get_permalink($product_id),
            get_bloginfo('name')
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
}

// PASO 3: Personalizar columnas en listado de reportes
if (!function_exists('set_report_columns')) {
    add_filter('manage_course_report_posts_columns', 'set_report_columns');
    function set_report_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => 'Mensaje del Reporte',
            'report_type' => 'Tipo',
            'product' => 'Curso',
            'user' => 'Usuario',
            'status' => 'Estado',
            'date' => 'Fecha'
        );
    }
}

if (!function_exists('fill_report_columns')) {
    add_action('manage_course_report_posts_custom_column', 'fill_report_columns', 10, 2);
    function fill_report_columns($column, $post_id) {
        switch ($column) {
            case 'report_type':
                $type = get_post_meta($post_id, '_report_type', true);
                $icons = array(
                    'outdated' => '📅',
                    'error' => '🐛',
                    'broken_link' => '🔗',
                    'wrong_info' => '❌',
                    'other' => '💬'
                );
                echo isset($icons[$type]) ? $icons[$type] : '📝';
                break;
            
            case 'product':
                $product_id = get_post_meta($post_id, '_product_id', true);
                $product = wc_get_product($product_id);
                if ($product) {
                    echo '<a href="' . get_edit_post_link($product_id) . '">' . $product->get_name() . '</a>';
                }
                break;
            
            case 'user':
                $user_id = get_post_meta($post_id, '_user_id', true);
                $user = get_userdata($user_id);
                if ($user) {
                    echo $user->display_name;
                }
                break;
            
            case 'status':
                $status = get_post_meta($post_id, '_report_status', true);
                if ($status === 'resolved') {
                    echo '<span style="color: #46b450; font-weight: 600;">✅ Resuelto</span>';
                } else {
                    echo '<span style="color: #ffb900; font-weight: 600;">⏳ Pendiente</span>';
                }
                break;
        }
    }
}

// PASO 4: Shortcode del botón de reporte (RESPONSIVE)
if (!function_exists('course_report_button_shortcode')) {
    add_shortcode('boton_reporte', 'course_report_button_shortcode');
    function course_report_button_shortcode($atts) {
        // Verificar si el usuario está logueado
        if (!is_user_logged_in()) {
            return '';
        }
        
        global $product;
        
        // Validar que $product sea un objeto válido
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            $product = wc_get_product(get_the_ID());
        }
        
        if (!$product) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="course-report-wrapper">
            <button type="button" class="course-report-btn" id="openReportModal" style="
                background: transparent;
                border: 1px solid rgba(255, 255, 255, 0.15);
                color: rgba(255, 255, 255, 0.5);
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                margin: 5px 0;
            ">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span class="report-btn-text">Reportar problema</span>
            </button>
            
            <!-- Modal de Reporte -->
            <div id="reportModal" class="report-modal" style="display: none;">
                <div class="report-modal-overlay"></div>
                <div class="report-modal-content">
                    <div class="report-modal-header">
                        <h3 style="margin: 0; font-size: 18px; color: #fff;">Reportar un problema con este curso</h3>
                        <button type="button" class="report-modal-close" id="closeReportModal">&times;</button>
                    </div>
                    
                    <form id="courseReportForm" style="padding: 20px;">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                        <input type="hidden" name="action" value="submit_course_report">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('course_report_nonce'); ?>">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #e0e0e0;">¿Qué tipo de problema encontraste?</label>
                            <select name="report_type" required style="width: 100%; padding: 10px; border: 1px solid #3a3a3a; border-radius: 4px; background: #2a2a2a; color: #e0e0e0; font-size: 14px;">
                                <option value="">Selecciona una opción</option>
                                <option value="outdated">📅 El curso está desactualizado</option>
                                <option value="error">🐛 Hay un error en el curso</option>
                                <option value="broken_link">🔗 Enlace roto o no funciona</option>
                                <option value="wrong_info">❌ Información incorrecta</option>
                                <option value="other">💬 Otro problema</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #e0e0e0;">Describe el problema</label>
                            <textarea name="report_message" rows="4" placeholder="Cuéntanos más detalles sobre el problema..." style="width: 100%; padding: 10px; border: 1px solid #3a3a3a; border-radius: 4px; resize: vertical; background: #2a2a2a; color: #e0e0e0; font-size: 14px;"></textarea>
                        </div>
                        
                        <div id="reportResponse" style="margin-bottom: 15px; display: none; padding: 10px; border-radius: 4px;"></div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" id="cancelReport" style="padding: 10px 20px; background: #3a3a3a; color: #e0e0e0; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Cancelar</button>
                            <button type="submit" style="padding: 10px 20px; background: #da0480; color: #000; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">Enviar Reporte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .course-report-btn:hover {
                border-color: rgba(255, 255, 255, 0.3);
                color: rgba(255, 255, 255, 0.8);
            }
            
            /* RESPONSIVE: Ocultar texto en tablets y móviles */
            @media (max-width: 1024px) {
                .course-report-btn .report-btn-text {
                    display: none;
                }
                .course-report-btn {
                    padding: 6px;
                }
            }
            
            .report-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 999999;
            }
            
            .report-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.85);
            }
            
            .report-modal-content {
                position: relative;
                max-width: 500px;
                margin: 50px auto;
                background: #1a1a1a;
                border-radius: 8px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.7);
                max-height: 90vh;
                overflow-y: auto;
                border: 1px solid #2a2a2a;
            }
            
            /* Modal responsive */
            @media (max-width: 768px) {
                .report-modal-content {
                    margin: 20px;
                    max-width: calc(100% - 40px);
                }
            }
            
            .report-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #2a2a2a;
            }
            
            .report-modal-close {
                background: none;
                border: none;
                font-size: 28px;
                cursor: pointer;
                color: #666;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color 0.3s;
            }
            
            .report-modal-close:hover {
                color: #e0e0e0;
            }
            
            .report-success {
                background: #1a4d2e;
                color: #7cfc7c;
                border: 1px solid #2d6a4f;
            }
            
            .report-error {
                background: #4d1a1a;
                color: #fc7c7c;
                border: 1px solid #6a2d2d;
            }
            
            #cancelReport:hover {
                background: #4a4a4a;
            }
            
            select:focus,
            textarea:focus {
                outline: none;
                border-color: #da0480;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            const modal = $('#reportModal');
            const openBtn = $('#openReportModal');
            const closeBtn = $('#closeReportModal');
            const cancelBtn = $('#cancelReport');
            const form = $('#courseReportForm');
            const response = $('#reportResponse');
            
            // Abrir modal
            openBtn.on('click', function() {
                modal.fadeIn(200);
                $('body').css('overflow', 'hidden');
            });
            
            // Cerrar modal
            function closeModal() {
                modal.fadeOut(200);
                $('body').css('overflow', '');
                form[0].reset();
                response.hide();
            }
            
            closeBtn.on('click', closeModal);
            cancelBtn.on('click', closeModal);
            
            // Cerrar al hacer clic en el overlay
            $('.report-modal-overlay').on('click', closeModal);
            
            // Enviar formulario
            form.on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Enviando...');
                response.hide();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            response.removeClass('report-error').addClass('report-success')
                                .html('✓ Reporte enviado correctamente. ¡Gracias por tu ayuda!')
                                .fadeIn();
                            
                            setTimeout(function() {
                                closeModal();
                            }, 2000);
                        } else {
                            response.removeClass('report-success').addClass('report-error')
                                .html('✗ ' + (res.data || 'Error al enviar el reporte'))
                                .fadeIn();
                        }
                    },
                    error: function() {
                        response.removeClass('report-success').addClass('report-error')
                            .html('✗ Error de conexión. Intenta nuevamente.')
                            .fadeIn();
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('Enviar Reporte');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

// PASO 5: Procesar el reporte vía AJAX
if (!function_exists('handle_course_report_submission')) {
    add_action('wp_ajax_submit_course_report', 'handle_course_report_submission');
    function handle_course_report_submission() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'course_report_nonce')) {
            wp_send_json_error('Seguridad inválida');
        }
        
        // Verificar usuario logueado
        if (!is_user_logged_in()) {
            wp_send_json_error('Debes iniciar sesión');
        }
        
        // Validar datos
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : '';
        $report_message = isset($_POST['report_message']) ? sanitize_textarea_field($_POST['report_message']) : '';
        
        if (!$product_id || !$report_type) {
            wp_send_json_error('Datos incompletos');
        }
        
        // Obtener información del producto
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Producto no encontrado');
        }
        
        $user = wp_get_current_user();
        
        // Crear el reporte
        $report_title = sprintf('Reporte: %s', $product->get_name());
        
        $report_id = wp_insert_post(array(
            'post_type' => 'course_report',
            'post_title' => $report_title,
            'post_content' => $report_message,
            'post_status' => 'publish',
        ));
        
        if ($report_id) {
            // Guardar metadatos
            update_post_meta($report_id, '_report_type', $report_type);
            update_post_meta($report_id, '_product_id', $product_id);
            update_post_meta($report_id, '_user_id', $user->ID);
            update_post_meta($report_id, '_report_date', current_time('mysql'));
            update_post_meta($report_id, '_report_status', 'pending'); // Estado por defecto
            
            wp_send_json_success('Reporte enviado correctamente');
        } else {
            wp_send_json_error('Error al crear el reporte');
        }
    }
}


/* ============================================
   PLATAFORMA DE CURSOS - MI CUENTA COMPLETO
   ============================================ */

// Registrar Custom Post Type para Tickets de Soporte
if (!function_exists('sp_register_support_tickets')) {
  add_action('init', 'sp_register_support_tickets');
  function sp_register_support_tickets() {
    register_post_type('support_ticket', array(
      'labels' => array(
        'name' => 'Tickets de Soporte',
        'singular_name' => 'Ticket',
        'add_new' => 'Nuevo Ticket',
        'add_new_item' => 'Agregar Ticket',
        'edit_item' => 'Editar Ticket',
        'view_item' => 'Ver Ticket',
      ),
      'public' => false,
      'show_ui' => true,
      'show_in_menu' => true,
      'capability_type' => 'post',
      'supports' => array('title', 'editor', 'comments'),
      'menu_icon' => 'dashicons-tickets-alt',
    ));
  }
}

// Agregar endpoint personalizado para soporte
if (!function_exists('sp_add_support_endpoint')) {
  add_action('init', 'sp_add_support_endpoint');
  function sp_add_support_endpoint() {
    add_rewrite_endpoint('soporte', EP_ROOT | EP_PAGES);
  }
  
  // Agregar Soporte al menú y eliminar items innecesarios
  add_filter('woocommerce_account_menu_items', 'sp_custom_account_menu_items');
  function sp_custom_account_menu_items($items) {
    // Eliminar items innecesarios para una plataforma de cursos
    unset($items['edit-address']); // Direcciones
    unset($items['payment-methods']); // Métodos de pago
    
    // Reorganizar menú
    $new_items = array();
    foreach ($items as $key => $label) {
      $new_items[$key] = $label;
      
      // Renombrar items para contexto de cursos
      if ($key === 'dashboard') {
        $new_items[$key] = 'Inicio';
      }
      if ($key === 'orders') {
        $new_items[$key] = 'Mis Cursos';
      }
      if ($key === 'downloads') {
        $new_items[$key] = 'Recursos';
      }
      
      // Insertar Soporte después de Downloads
      if ($key === 'downloads') {
        $new_items['soporte'] = 'Soporte';
      }
    }
    
    // Renombrar Account details
    if (isset($new_items['edit-account'])) {
      $new_items['edit-account'] = 'Mi Perfil';
    }
    
    return $new_items;
  }
  
  // Contenido del endpoint Soporte
  add_action('woocommerce_account_soporte_endpoint', 'sp_support_endpoint_content');
  function sp_support_endpoint_content() {
    include get_stylesheet_directory() . '/woocommerce/myaccount/soporte.php';
  }
}

// Personalizar Dashboard
if (!function_exists('sp_custom_dashboard_content')) {
  add_action('woocommerce_account_dashboard', 'sp_custom_dashboard_content', 5);
  function sp_custom_dashboard_content() {
    $user = wp_get_current_user();
    
    // Contar cursos (productos comprados)
    $customer_orders = wc_get_orders(array(
      'customer_id' => get_current_user_id(),
      'status' => array('wc-completed', 'wc-processing'),
      'limit' => -1,
    ));
    
    $total_courses = 0;
    foreach ($customer_orders as $order) {
      $total_courses += count($order->get_items());
    }
    
    // Contar tickets
    $tickets = get_posts(array(
      'post_type' => 'support_ticket',
      'author' => get_current_user_id(),
      'posts_per_page' => -1,
    ));
    
    $open_tickets = array_filter($tickets, function($t) {
      return get_post_meta($t->ID, '_ticket_status', true) !== 'resolved';
    });
    
    ?>
    <div class="sp-dashboard-stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:32px">
      
      <!-- Total Cursos -->
      <div style="background:linear-gradient(135deg,rgba(218,4,128,.15),rgba(218,4,128,.08));padding:24px;border-radius:16px;border:1.5px solid rgba(218,4,128,.3)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;background:rgba(218,4,128,.2);border-radius:14px;display:flex;align-items:center;justify-content:center">
            <svg style="width:28px;height:28px;fill:#da0480" viewBox="0 0 24 24">
              <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:32px;font-weight:800;color:#fff"><?php echo $total_courses; ?></div>
            <div style="font-size:13px;color:#9ca3af;font-weight:600">Cursos Activos</div>
          </div>
        </div>
      </div>
      
      <!-- Pedidos -->
      <div style="background:rgba(0,0,0,.3);padding:24px;border-radius:16px;border:1.5px solid rgba(255,255,255,.08)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;background:rgba(59,130,246,.15);border-radius:14px;display:flex;align-items:center;justify-content:center">
            <svg style="width:28px;height:28px;fill:#3b82f6" viewBox="0 0 24 24">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:32px;font-weight:800;color:#fff"><?php echo count($customer_orders); ?></div>
            <div style="font-size:13px;color:#9ca3af;font-weight:600">Compras Realizadas</div>
          </div>
        </div>
      </div>
      
      <!-- Tickets Abiertos -->
      <div style="background:rgba(0,0,0,.3);padding:24px;border-radius:16px;border:1.5px solid rgba(255,255,255,.08)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;background:rgba(245,158,11,.15);border-radius:14px;display:flex;align-items:center;justify-content:center">
            <svg style="width:28px;height:28px;fill:#f59e0b" viewBox="0 0 24 24">
              <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:32px;font-weight:800;color:#fff"><?php echo count($open_tickets); ?></div>
            <div style="font-size:13px;color:#9ca3af;font-weight:600">Tickets Abiertos</div>
          </div>
        </div>
      </div>
      
    </div>
    
    <div style="background:rgba(0,0,0,.2);padding:20px 24px;border-radius:16px;border:1.5px solid rgba(218,4,128,.15);margin-bottom:24px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <svg style="width:24px;height:24px;fill:#da0480" viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
        </svg>
        <h3 style="margin:0;color:#fff;font-size:18px;font-weight:700">Bienvenido, <?php echo esc_html($user->display_name); ?>! 👋</h3>
      </div>
      <p style="margin:0;color:#9ca3af;line-height:1.7;font-size:15px">
        Desde tu panel puedes acceder a tus cursos, descargar recursos, gestionar tu perfil y obtener soporte cuando lo necesites.
      </p>
    </div>
    <?php
    
    // Remover el contenido por defecto de WooCommerce
    remove_action('woocommerce_account_dashboard', 'woocommerce_account_dashboard', 10);
  }
}

// Estilos personalizados para Mi Cuenta - CON FONDO ANIMADO Y FIX RESPONSIVE
if (!function_exists('sp_myaccount_custom_styles')) {
  add_action('wp_head', 'sp_myaccount_custom_styles', 999);
  function sp_myaccount_custom_styles() {
    if (!is_account_page()) return;
    ?>
    <style>
      /* ===== FONDO ANIMADO PATRÓN BLANCO/NEGRO ===== */
      body.woocommerce-account{
        position:relative;
        background:#000!important;
        overflow-x:hidden;
      }
      
      /* Capa 1: Grid animado blanco/negro */
      body.woocommerce-account:before{
        content:''!important;
        position:fixed!important;
        top:0!important;
        left:0!important;
        width:100%!important;
        height:100%!important;
        background:
          repeating-linear-gradient(
            0deg,
            rgba(255,255,255,.02) 0px,
            rgba(255,255,255,.02) 2px,
            transparent 2px,
            transparent 40px
          ),
          repeating-linear-gradient(
            90deg,
            rgba(255,255,255,.02) 0px,
            rgba(255,255,255,.02) 2px,
            transparent 2px,
            transparent 40px
          ),
          linear-gradient(135deg, #000 0%, #0a0a0a 50%, #050505 100%)!important;
        background-size:40px 40px, 40px 40px, 100% 100%!important;
        animation:gridMove 20s linear infinite!important;
        opacity:.5!important;
        z-index:0!important;
        pointer-events:none!important;
      }
      
      @keyframes gridMove{
        0%{background-position:0 0, 0 0, 0% 0%}
        100%{background-position:40px 40px, -40px -40px, 0% 0%}
      }
      
      /* Capa 2: Partículas flotantes rosa */
      body.woocommerce-account:after{
        content:''!important;
        position:fixed!important;
        top:0!important;
        left:0!important;
        width:100%!important;
        height:100%!important;
        background-image:
          radial-gradient(circle at 20% 30%, rgba(218,4,128,.05) 0%, transparent 50%),
          radial-gradient(circle at 80% 70%, rgba(218,4,128,.05) 0%, transparent 50%),
          radial-gradient(circle at 50% 50%, rgba(218,4,128,.03) 0%, transparent 60%),
          radial-gradient(2px 2px at 25% 45%, rgba(255,255,255,.1), transparent),
          radial-gradient(2px 2px at 75% 65%, rgba(255,255,255,.1), transparent),
          radial-gradient(1px 1px at 60% 80%, rgba(218,4,128,.15), transparent)!important;
        background-size:100% 100%, 100% 100%, 100% 100%, 200% 200%, 200% 200%, 200% 200%!important;
        animation:particlesFloat 15s ease-in-out infinite!important;
        z-index:0!important;
        pointer-events:none!important;
      }
      
      @keyframes particlesFloat{
        0%, 100%{background-position:0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%}
        50%{background-position:0% 0%, 0% 0%, 0% 0%, 100% 100%, 100% 100%, 100% 100%}
      }
      
      /* ===== ESTRUCTURA PRINCIPAL ===== */
      body.woocommerce-account .woocommerce,
      body.woocommerce-account div.woocommerce{
        max-width:1400px!important;
        margin:0 auto!important;
        padding:40px 20px!important;
        margin-top:120px!important;
        position:relative!important;
        z-index:1!important;
      }
      
      /* Contenedor de Mi Cuenta */
      body.woocommerce-account .woocommerce-MyAccount-navigation,
      body.woocommerce-account .woocommerce-MyAccount-content{
        display:inline-block!important;
        vertical-align:top!important;
        margin:0!important;
        position:relative!important;
        z-index:2!important;
      }
      
      /* Navegación Lateral IZQUIERDA */
      body.woocommerce-account .woocommerce-MyAccount-navigation{
        width:280px!important;
        margin-right:32px!important;
        float:left!important;
        padding:24px 0!important;
        background:linear-gradient(135deg,rgba(15,15,15,.95),rgba(26,26,26,.95))!important;
        border:1.5px solid rgba(218,4,128,.2)!important;
        border-radius:20px!important;
        box-shadow:0 20px 60px rgba(0,0,0,.4),0 0 40px rgba(218,4,128,.08)!important;
        position:sticky!important;
        top:20px!important;
        backdrop-filter:blur(10px)!important;
      }
      
      /* Contenido DERECHA */
      body.woocommerce-account .woocommerce-MyAccount-content{
        width:calc(100% - 312px)!important;
        float:left!important;
        padding:32px!important;
        background:linear-gradient(135deg,rgba(15,15,15,.95),rgba(26,26,26,.95))!important;
        border:1.5px solid rgba(218,4,128,.2)!important;
        border-radius:20px!important;
        min-height:600px!important;
        box-shadow:0 20px 60px rgba(0,0,0,.4)!important;
        backdrop-filter:blur(20px)!important;
      }
      
      /* Clearfix */
      body.woocommerce-account .woocommerce:after,
      body.woocommerce-account div.woocommerce:after{
        content:""!important;
        display:table!important;
        clear:both!important;
      }
      
      /* ===== NAVEGACIÓN ===== */
      body.woocommerce-account .woocommerce-MyAccount-navigation ul{
        margin:0!important;
        padding:0!important;
        list-style:none!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li{
        margin:0!important;
        padding:0!important;
        display:block!important;
        width:100%!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li a{
        display:flex!important;
        align-items:center!important;
        gap:12px!important;
        padding:14px 24px!important;
        color:#9ca3af!important;
        text-decoration:none!important;
        transition:.2s!important;
        font-size:15px!important;
        font-weight:600!important;
        border-left:3px solid transparent!important;
        background:transparent!important;
        width:100%!important;
        box-sizing:border-box!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li a:before{
        content:''!important;
        width:20px!important;
        height:20px!important;
        display:block!important;
        background-size:contain!important;
        background-repeat:no-repeat!important;
        opacity:.6!important;
        filter:brightness(0) invert(1)!important;
        flex-shrink:0!important;
      }
      
      /* Iconos SVG */
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--dashboard a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>')!important;
      }
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--orders a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>')!important;
      }
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--downloads a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/></svg>')!important;
      }
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--soporte a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>')!important;
      }
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--edit-account a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>')!important;
      }
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a:before{
        background-image:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>')!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li a:hover,
      body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a{
        color:#da0480!important;
        background:rgba(218,4,128,.08)!important;
        border-left-color:#da0480!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li a:hover:before,
      body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a:before{
        opacity:1!important;
        filter:brightness(0) saturate(100%) invert(32%) sepia(89%) saturate(2537%) hue-rotate(315deg) brightness(89%) contrast(101%)!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout{
        margin-top:12px!important;
        padding-top:12px!important;
        border-top:1px solid rgba(218,4,128,.15)!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a{
        color:#ff8a8a!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a:hover{
        background:rgba(255,80,80,.08)!important;
        color:#ff5050!important;
        border-left-color:#ff5050!important;
      }
      
      /* ===== CONTENIDO ===== */
      body.woocommerce-account .woocommerce-MyAccount-content h2,
      body.woocommerce-account .woocommerce-MyAccount-content h3{
        color:#fff!important;
        font-weight:800!important;
        margin-bottom:24px!important;
        font-size:28px!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-content p{
        color:#9ca3af!important;
        line-height:1.8!important;
        font-size:15px!important;
      }
      
      body.woocommerce-account .woocommerce-MyAccount-content a{
        color:#da0480!important;
        font-weight:600!important;
      }
      
      /* ===== TABLAS ===== */
      body.woocommerce-account table.my_account_orders,
      body.woocommerce-account table.woocommerce-orders-table,
      body.woocommerce-account table.shop_table_responsive{
        width:100%!important;
        border-collapse:separate!important;
        border-spacing:0 12px!important;
        margin:0!important;
        background:transparent!important;
      }
      
      body.woocommerce-account table thead{
        background:rgba(218,4,128,.1)!important;
        border-radius:12px!important;
      }
      
      body.woocommerce-account table th{
        background:transparent!important;
        color:#da0480!important;
        font-weight:700!important;
        padding:16px!important;
        text-align:left!important;
        font-size:13px!important;
        text-transform:uppercase!important;
        letter-spacing:.5px!important;
        border:none!important;
      }
      
      body.woocommerce-account table tbody tr{
        background:rgba(0,0,0,.3)!important;
        border-radius:12px!important;
        transition:.3s!important;
      }
      
      body.woocommerce-account table tbody tr:hover{
        background:rgba(218,4,128,.08)!important;
        transform:translateX(4px)!important;
      }
      
      body.woocommerce-account table td{
        padding:18px 16px!important;
        color:#e5e7eb!important;
        border:none!important;
        border-top:1px solid rgba(255,255,255,.05)!important;
        border-bottom:1px solid rgba(255,255,255,.05)!important;
        background:transparent!important;
      }
      
      body.woocommerce-account table td:first-child{
        border-left:1px solid rgba(255,255,255,.05)!important;
        border-top-left-radius:12px!important;
        border-bottom-left-radius:12px!important;
      }
      
      body.woocommerce-account table td:last-child{
        border-right:1px solid rgba(255,255,255,.05)!important;
        border-top-right-radius:12px!important;
        border-bottom-right-radius:12px!important;
      }
      
      body.woocommerce-account table a{
        color:#da0480!important;
        text-decoration:none!important;
        font-weight:600!important;
        transition:.2s!important;
      }
      
      body.woocommerce-account table a:hover{
        color:#ff57a8!important;
      }
      
      /* ===== MENSAJES ===== */
      body.woocommerce-account .woocommerce-message,
      body.woocommerce-account .woocommerce-info,
      body.woocommerce-account .woocommerce-error{
        background:rgba(218,4,128,.1)!important;
        border:1.5px solid rgba(218,4,128,.3)!important;
        border-radius:16px!important;
        padding:24px!important;
        color:#fff!important;
        margin-bottom:24px!important;
      }
      
      body.woocommerce-account .woocommerce-message:before,
      body.woocommerce-account .woocommerce-info:before,
      body.woocommerce-account .woocommerce-error:before{
        display:none!important;
      }
      
      /* ===== BOTONES ===== */
      body.woocommerce-account .woocommerce-button,
      body.woocommerce-account button.button,
      body.woocommerce-account a.button,
      body.woocommerce-account .button{
        background:linear-gradient(135deg,#da0480,#b00368)!important;
        color:#fff!important;
        border:none!important;
        padding:12px 24px!important;
        border-radius:12px!important;
        font-weight:700!important;
        cursor:pointer!important;
        transition:.3s!important;
        box-shadow:0 4px 16px rgba(218,4,128,.3)!important;
        text-decoration:none!important;
        display:inline-block!important;
      }
      
      body.woocommerce-account .woocommerce-button:hover,
      body.woocommerce-account button.button:hover,
      body.woocommerce-account a.button:hover,
      body.woocommerce-account .button:hover{
        background:linear-gradient(135deg,#c00370,#900258)!important;
        transform:translateY(-2px)!important;
        box-shadow:0 8px 24px rgba(218,4,128,.4)!important;
      }
      
      /* ===== FORMS ===== */
      body.woocommerce-account input[type="text"],
      body.woocommerce-account input[type="email"],
      body.woocommerce-account input[type="password"],
      body.woocommerce-account input[type="tel"],
      body.woocommerce-account textarea,
      body.woocommerce-account select{
        width:100%!important;
        padding:14px 16px!important;
        background:rgba(0,0,0,.3)!important;
        border:1.5px solid rgba(255,255,255,.08)!important;
        border-radius:12px!important;
        color:#fff!important;
        transition:.3s!important;
        font-size:15px!important;
      }
      
      body.woocommerce-account input:focus,
      body.woocommerce-account textarea:focus,
      body.woocommerce-account select:focus{
        outline:none!important;
        border-color:#da0480!important;
        box-shadow:0 0 0 4px rgba(218,4,128,.15)!important;
        background:rgba(0,0,0,.4)!important;
      }
      
      body.woocommerce-account label{
        color:#cbd5e1!important;
        font-weight:600!important;
        font-size:14px!important;
        margin-bottom:8px!important;
        display:block!important;
      }
      
      /* ===== RESPONSIVE ===== */
      @media (max-width:968px){
        body.woocommerce-account .woocommerce,
        body.woocommerce-account div.woocommerce{
          margin-top:20px!important;
          padding-top:20px!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-navigation{
          width:100%!important;
          float:none!important;
          margin-right:0!important;
          margin-bottom:24px!important;
          position:static!important;
          top:0!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-content{
          width:100%!important;
          float:none!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-navigation ul{
          display:grid!important;
          grid-template-columns:repeat(auto-fit,minmax(140px,1fr))!important;
          gap:8px!important;
          padding:0 16px!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-navigation li a{
          justify-content:center!important;
          border-left:none!important;
          border-bottom:3px solid transparent!important;
          font-size:14px!important;
          padding:12px 8px!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-navigation li a:hover,
        body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a{
          border-left:none!important;
          border-bottom-color:#da0480!important;
        }
      }
      
      @media (max-width:640px){
        body.woocommerce-account .woocommerce,
        body.woocommerce-account div.woocommerce{
          padding:20px 16px!important;
          margin-top:10px!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-content{
          padding:24px 16px!important;
        }
        
        body.woocommerce-account .woocommerce-MyAccount-navigation ul{
          grid-template-columns:1fr!important;
        }
        
        body.woocommerce-account table{
          display:block!important;
          overflow-x:auto!important;
        }
      }
    </style>
    <?php
  }
}

// ============================================
// SHORTCODE BOTONES DE COMPRA PERSONALIZADOS
// ============================================

// Shortcode para botones de compra personalizados
function custom_wc_dual_buttons_shortcode($atts) {
    // Obtener el producto actual
    global $product;
    
    if (!$product) {
        return '';
    }
    
    $product_id = $product->get_id();
    
    // Verificar si el producto ya está en el carrito
    $in_cart = false;
    if (function_exists('WC') && WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $in_cart = true;
                break;
            }
        }
    }
    
    // URL del checkout con el producto
    $checkout_url = add_query_arg('add-to-cart', $product_id, wc_get_checkout_url());
    
    // Texto del botón de añadir al carrito
    $add_to_cart_text  = $in_cart ? 'En tu carrito' : 'Añadir al carrito';
    $add_to_cart_class = $in_cart ? 'in-cart' : '';
    
    // Lógica de coins para productos gratis
    $es_gratis   = has_term('gratis', 'product_cat', $product_id);
    $coins_necesarios = 0;
    $coins_usuario    = 0;
    $tiene_coins_suficientes = true;
    
    if ($es_gratis && function_exists('coins_manager')) {
        $coins_necesarios = coins_manager()->get_costo_coins_producto($product_id);
        
        if (is_user_logged_in()) {
            $coins_usuario = coins_manager()->get_coins(get_current_user_id());
        } else {
            $coins_usuario = 0;
        }
        
        $tiene_coins_suficientes = ($coins_usuario >= $coins_necesarios);
    }

    $is_logged_in = is_user_logged_in();
    
    // Data attributes para JS
    $data_attrs  = ' data-es-gratis="' . ($es_gratis ? '1' : '0') . '"';
    if ($es_gratis) {
        $data_attrs .= ' data-coins-necesarios="' . esc_attr($coins_necesarios) . '"';
        $data_attrs .= ' data-coins-usuario="' . esc_attr($coins_usuario) . '"';
        $data_attrs .= ' data-tiene-coins="' . ($tiene_coins_suficientes ? '1' : '0') . '"';
    }
    $data_attrs .= ' data-logged-in="' . ($is_logged_in ? '1' : '0') . '"';
    
    ob_start();
    ?>
    <div class="custom-wc-buttons-wrapper"<?php echo $data_attrs; ?>>
        <button type="button" 
                class="custom-buy-now-btn <?php echo esc_attr($add_to_cart_class); ?>"
                <?php if (!$is_logged_in): ?>
                    data-open-auth-modal="1"
                <?php else: ?>
                    <?php if (!$es_gratis || $tiene_coins_suficientes): ?>
                        onclick="window.location.href='<?php echo esc_url($checkout_url); ?>'"
                    <?php else: ?>
                        data-open-coins-modal="1"
                    <?php endif; ?>
                <?php endif; ?>>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            Comprar ahora
        </button>
        
        <button 
            <?php if ($es_gratis && !$tiene_coins_suficientes): ?>
                type="button"
                data-open-coins-modal="1"
            <?php else: ?>
                type="submit"
                name="add-to-cart"
            <?php endif; ?>
            value="<?php echo esc_attr($product_id); ?>" 
            class="custom-add-to-cart-btn <?php echo esc_attr($add_to_cart_class); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <?php echo esc_html($add_to_cart_text); ?>
        </button>
        
        <!-- Link Ver Carrito (oculto por defecto) -->
        <div class="custom-view-cart-link" style="display: none;">
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Ver carrito
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dual_buy_buttons', 'custom_wc_dual_buttons_shortcode');

// Agregar CSS personalizado para los botones
function custom_wc_dual_buttons_styles() {
    ?>
    <style type="text/css">
    
  /* ================================
   PRECIOS DE CURSO (SHORTCODE [curso_precio])
   ================================ */

/* Wrapper general de precios */
.curso-precio-wrapper {
    margin: 10px 0 20px;
}

/* --------- Cursos PREMIUM --------- */
.curso-precio-premium {
    display: flex;
    flex-direction: row;
    align-items: baseline;
    gap: 12px;
}

/* Precio actual (con descuento, grande) - IZQUIERDA */
.curso-precio-premium .curso-precio-actual {
    order: 1;
}

.curso-precio-premium .curso-precio-actual-texto {
    font-size: 30px;
    font-weight: 700;
    color: #C4C8CE;
    line-height: 1.2;
    display: inline-block;
}

/* Precio original (tachado, pequeño) - DERECHA */
.curso-precio-premium .curso-precio-original {
    order: 2;
}

.curso-precio-premium .curso-precio-original-texto {
    font-size: 15px;
    color: #C4C8CE;
    text-decoration: line-through;
    opacity: 0.9;
    display: inline-block;
}

/* --------- Cursos GRATIS (coins con imagen) --------- */
.curso-precio-gratis .curso-precio-coins {
    font-size: 24px;
    font-weight: 700;
    color: #da0480;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.curso-precio-gratis .coin-icon {
    width: 28px;
    height: 28px;
    object-fit: contain;
    flex-shrink: 0;
}

/* --------- Fallback simple --------- */
.curso-precio-simple {
    font-size: 20px;
    color: #C4C8CE;
    font-weight: 600;
}

/* ================================
   RESPONSIVE PRECIOS
   ================================ */
@media (max-width: 767px) {
    .curso-precio-premium {
        gap: 10px;
    }
    .curso-precio-premium .curso-precio-original-texto {
        font-size: 13px;
    }
    .curso-precio-premium .curso-precio-actual-texto {
        font-size: 24px;
    }
    .curso-precio-gratis .curso-precio-coins {
        font-size: 20px;
    }
    .curso-precio-gratis .coin-icon {
        width: 24px;
        height: 24px;
    }
}

@media (max-width: 480px) {
    .curso-precio-premium {
        gap: 8px;
    }
    .curso-precio-premium .curso-precio-original-texto {
        font-size: 12px;
    }
    .curso-precio-premium .curso-precio-actual-texto {
        font-size: 22px;
    }
    .curso-precio-gratis .curso-precio-coins {
        font-size: 18px;
    }
    .curso-precio-gratis .coin-icon {
        width: 22px;
        height: 22px;
    }
}

    
        /* Contenedor de botones */
        .custom-wc-buttons-wrapper {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            margin: 20px 0;
        }

        /* Estilos base para ambos botones */
        .custom-buy-now-btn,
        .custom-add-to-cart-btn {
            width: 100%;
            border-radius: 10px;
            padding: 18px 28px;
            color: #fff;
            border: 2px solid rgba(218, 4, 128, 0.3);
            font-family: "Space Grotesk", Sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(218, 4, 128, 0.2);
        }

        /* Botón de comprar ahora - estilo principal con gradiente */
        .custom-buy-now-btn {
            background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
            border-color: rgba(218, 4, 128, 0.5);
            position: relative;
            overflow: hidden;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        /* Efecto de brillo que pasa por el botón */
        .custom-buy-now-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.3), 
                transparent
            );
            transition: left 0.7s ease;
        }

        .custom-buy-now-btn:hover::before {
            left: 100%;
        }

        /* Animación de pulso suave en el glow */
        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 4px 12px rgba(218, 4, 128, 0.3);
            }
            50% {
                box-shadow: 0 4px 20px rgba(218, 4, 128, 0.5), 
                            0 0 30px rgba(218, 4, 128, 0.2);
            }
        }

        /* Botón de añadir al carrito - estilo secundario */
        .custom-add-to-cart-btn {
            background: rgba(26, 38, 64, 0.8);
            border-color: rgba(218, 4, 128, 0.3);
        }

        /* Hover effects */
        .custom-buy-now-btn:hover {
            background: linear-gradient(135deg, #b00368 0%, #8a0252 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 4, 128, 0.6);
            border-color: rgba(218, 4, 128, 0.7);
            animation: none; /* Detener animación en hover */
        }

        .custom-add-to-cart-btn:hover {
            background: rgba(218, 4, 128, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(218, 4, 128, 0.3);
            border-color: rgba(218, 4, 128, 0.5);
        }

        /* Estado cuando el producto está en el carrito */
        .custom-add-to-cart-btn.in-cart {
            background: rgba(218, 4, 128, 0.1);
            border-color: rgba(218, 4, 128, 0.4);
            color: #da0480;
            cursor: default;
            pointer-events: none;
        }

        .custom-add-to-cart-btn.in-cart::before {
            content: "✓";
            font-size: 18px;
            margin-right: 5px;
        }

        /* Active/pressed state */
        .custom-buy-now-btn:active,
        .custom-add-to-cart-btn:active {
            transform: translateY(0);
        }

        /* SVG icons */
        .custom-buy-now-btn svg,
        .custom-add-to-cart-btn svg {
            flex-shrink: 0;
        }

        /* Link Ver Carrito */
        .custom-view-cart-link {
            text-align: center;
            margin-top: 5px;
            animation: fadeInUp 0.4s ease;
        }

        .custom-view-cart-link a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            text-decoration: none;
            font-family: "Space Grotesk", Sans-serif;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .custom-view-cart-link a:hover {
            color: #da0480;
            transform: translateY(-2px);
        }

        .custom-view-cart-link a svg {
            flex-shrink: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive - Tablets */
        @media (max-width: 1199px) {
            .custom-wc-buttons-wrapper {
                gap: 12px;
            }
            
            .custom-buy-now-btn,
            .custom-add-to-cart-btn {
                padding: 16px 24px;
                font-size: 14px;
            }

            .custom-view-cart-link a {
                font-size: 13px;
            }
        }

        /* Responsive - Mobile */
        @media (max-width: 767px) {
            .custom-wc-buttons-wrapper {
                gap: 12px;
                margin: 15px 0;
            }
            
            .custom-buy-now-btn,
            .custom-add-to-cart-btn {
                padding: 14px 20px;
                font-size: 14px;
            }

            .custom-buy-now-btn svg,
            .custom-add-to-cart-btn svg {
                width: 18px;
                height: 18px;
            }

            .custom-view-cart-link a {
                font-size: 13px;
            }

            .custom-view-cart-link a svg {
                width: 16px;
                height: 16px;
            }

            /* Reducir intensidad de animación en móviles */
            @keyframes pulseGlow {
                0%, 100% {
                    box-shadow: 0 4px 12px rgba(218, 4, 128, 0.25);
                }
                50% {
                    box-shadow: 0 4px 16px rgba(218, 4, 128, 0.4);
                }
            }
        }

        /* Para dispositivos muy pequeños */
        @media (max-width: 480px) {
            .custom-buy-now-btn,
            .custom-add-to-cart-btn {
                padding: 12px 18px;
                font-size: 13px;
            }

            .custom-buy-now-btn svg,
            .custom-add-to-cart-btn svg {
                width: 16px;
                height: 16px;
            }

            .custom-view-cart-link a {
                font-size: 12px;
                padding: 8px 16px;
            }
        }

        /* Reducir animaciones para usuarios que prefieren menos movimiento */
        @media (prefers-reduced-motion: reduce) {
            .custom-buy-now-btn {
                animation: none;
            }
            .custom-buy-now-btn::before {
                display: none;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'custom_wc_dual_buttons_styles');

// Script para manejar la adición al carrito con AJAX y modal de coins
function custom_wc_dual_buttons_scripts() {
    if (is_product()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Manejar click en añadir al carrito (solo cuando no debe abrir modal)
            $('.custom-add-to-cart-btn:not(.in-cart)[data-open-coins-modal!="1"]').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var product_id = button.val();
                var wrapper = button.closest('.custom-wc-buttons-wrapper');
                
                button.prop('disabled', true).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg> Agregando...');
                
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        action: 'woocommerce_ajax_add_to_cart',
                        product_id: product_id,
                    },
                    success: function(response) {
                        if (response.error) {
                            button.prop('disabled', false).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg> Añadir al carrito');
                        } else {
                            button.addClass('in-cart')
                                  .html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> En tu carrito')
                                  .css('pointer-events', 'none');
                            
                            // Mostrar link de ver carrito
                            wrapper.find('.custom-view-cart-link').fadeIn(300);
                            
                            // Actualizar fragmentos del carrito
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
                        }
                    },
                    error: function() {
                        button.prop('disabled', false).html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg> Añadir al carrito');
                    }
                });
            });

            // Abrir modal de login/registro desde Comprar ahora cuando no está logueado
            $(document).on('click', '[data-open-auth-modal="1"]', function(e) {
                e.preventDefault();
                var checkoutUrl = '<?php echo esc_js(wc_get_checkout_url()); ?>';

                // Marcar que venimos de "Comprar ahora"
                window.sp_after_login_redirect = checkoutUrl;

                var modalToggle = document.getElementById('sp-modal-main');
                if (modalToggle) {
                    modalToggle.checked = true;
                }
            });

            // Manejar botones que deben abrir modal de coins
            $(document).on('click', '[data-open-coins-modal="1"]', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.custom-wc-buttons-wrapper');
                
                var coinsNecesarios = wrapper.data('coins-necesarios') || 0;
                var coinsUsuario    = wrapper.data('coins-usuario') || 0;
                var loggedIn        = wrapper.data('logged-in') == 1;
                
                // Actualizar texto del modal
                $('#coins-modal-necesarios').text(coinsNecesarios);
                $('#coins-modal-usuario').text(coinsUsuario);
                
                if (!loggedIn) {
                    $('#coins-modal-mensaje').html('Debes iniciar sesión para usar tus coins y canjear este curso.');
                    $('#coins-modal-login').show();
                    $('#coins-modal-gana-coins').hide();
                } else {
                    $('#coins-modal-mensaje').html('No tienes suficientes coins para canjear este curso. Gana más coins comprando cursos premium y dejando reseñas.');
                    $('#coins-modal-login').hide();
                    $('#coins-modal-gana-coins').show();
                }
                
                $('body').addClass('coins-modal-open');
                $('#coins-modal-overlay')
                    .css('display', 'flex')
                    .hide()
                    .fadeIn(200);
            });

            // Cerrar modal
            $(document).on('click', '#coins-modal-close, #coins-modal-overlay', function(e) {
                if (e.target.id === 'coins-modal-overlay' || e.target.id === 'coins-modal-close') {
                    $('#coins-modal-overlay').fadeOut(200, function() {
                        $('body').removeClass('coins-modal-open');
                    });
                }
            });
        });
        </script>

        <!-- Modal Coins -->
        <div id="coins-modal-overlay">
            <div id="coins-modal">
                <button id="coins-modal-close" class="coins-modal-close">×</button>
                <div class="coins-modal-header">
                    <div class="coins-modal-icon">
                        <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coin">
                    </div>
                    <h2>No tienes suficientes coins</h2>
                </div>
                <p id="coins-modal-mensaje" class="coins-modal-text">
                    No tienes suficientes coins para canjear este curso.
                </p>
                
                <div class="coins-modal-summary">
                    <p><span>Coins necesarios:</span> <strong id="coins-modal-necesarios">0</strong></p>
                    <p><span>Tus coins:</span> <strong id="coins-modal-usuario">0</strong></p>
                </div>
                
                <div id="coins-modal-login" class="coins-modal-actions" style="display:none;">
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="coins-modal-btn primary">
                        Iniciar sesión
                    </a>
                </div>
                
                <div id="coins-modal-gana-coins" class="coins-modal-actions" style="display:none;">
                    <a href="<?php echo esc_url(site_url('/gana-coins/')); ?>" class="coins-modal-btn primary">
                        Cómo ganar coins
                    </a>
                </div>
                
                <p class="coins-modal-footnote">
                    Ganas 1 coin por cada curso premium que compras y 1 coin por cada reseña verificada.
                </p>
            </div>
        </div>
        <style>
            body.coins-modal-open {
                overflow: hidden;
            }

            /* Overlay de fondo */
            #coins-modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.75);
                z-index: 9999;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            /* Caja del modal */
            #coins-modal {
                background: #0b1020;
                border-radius: 16px;
                max-width: 420px;
                width: 100%;
                padding: 24px 22px 20px;
                position: relative;
                box-shadow: 0 18px 45px rgba(0,0,0,0.6);
                color: #f5f5f7;
                border: 1px solid rgba(218, 4, 128, 0.35);
            }

            .coins-modal-close {
                position: absolute;
                top: 10px;
                right: 10px;
                border: none;
                background: transparent;
                font-size: 22px;
                cursor: pointer;
                color: #f5f5f7;
                line-height: 1;
            }

            .coins-modal-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 10px;
            }

            .coins-modal-icon {
                width: 40px;
                height: 40px;
                border-radius: 999px;
                background: radial-gradient(circle at 30% 30%, #ffffff, #f5c6e1 40%, #7a0345 100%);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .coins-modal-icon img {
                width: 26px;
                height: 26px;
                object-fit: contain;
            }

            .coins-modal-header h2 {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
            }

            .coins-modal-text {
                margin: 6px 0 16px;
                font-size: 14px;
                color: #ced2e0;
            }

            .coins-modal-summary {
                background: rgba(9, 14, 35, 0.9);
                border-radius: 10px;
                padding: 10px 14px;
                border: 1px solid rgba(196, 200, 206, 0.25);
                margin-bottom: 16px;
                font-size: 14px;
            }

            .coins-modal-summary p {
                margin: 4px 0;
                display: flex;
                justify-content: space-between;
            }

            .coins-modal-summary span {
                color: #C4C8CE;
            }

            .coins-modal-summary strong {
                color: #ffffff;
                font-weight: 700;
            }

            .coins-modal-actions {
                text-align: center;
                margin-bottom: 12px;
            }

            .coins-modal-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 22px;
                border-radius: 999px;
                font-size: 14px;
                font-weight: 700;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: "Space Grotesk", Sans-serif;
            }

            .coins-modal-btn.primary {
                background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4);
            }

            .coins-modal-btn.primary:hover {
                background: linear-gradient(135deg, #b00368 0%, #8a0252 100%);
                box-shadow: 0 6px 20px rgba(218, 4, 128, 0.6);
                transform: translateY(-1px);
            }

            .coins-modal-footnote {
                margin: 6px 0 0;
                font-size: 12px;
                color: #8f94a6;
                text-align: center;
            }

            @media (max-width: 480px) {
                #coins-modal {
                    padding: 20px 18px 18px;
                }
                .coins-modal-header h2 {
                    font-size: 18px;
                }
                .coins-modal-summary {
                    font-size: 13px;
                }
                .coins-modal-btn {
                    width: 100%;
                }
            }
        </style>
        <?php
    }
}
add_action('wp_footer', 'custom_wc_dual_buttons_scripts');

// Handler AJAX para añadir al carrito
function custom_ajax_add_to_cart() {
    $product_id = absint($_POST['product_id']);
    $quantity = 1;
    
    if ($product_id) {
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($cart_item_key) {
            WC_AJAX::get_refreshed_fragments();
        } else {
            wp_send_json_error();
        }
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'custom_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'custom_ajax_add_to_cart');









































?>