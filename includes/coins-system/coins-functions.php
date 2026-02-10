<?php
/**
 * Funciones auxiliares del sistema de coins
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enviar email de notificación cuando se otorgan coins
 * 
 * @param int $user_id ID del usuario
 * @param float $cantidad Cantidad de coins otorgados
 * @param int $order_id ID de la orden
 */
function coins_enviar_notificacion_email($user_id, $cantidad, $order_id) {
    $user = get_userdata($user_id);
    
    if (!$user) {
        return false;
    }
    
    $coins_totales = coins_manager()->get_coins($user_id);
    $order = wc_get_order($order_id);
    
    $to      = $user->user_email;
    $subject = '🪙 Has recibido ' . coins_manager()->format_coins($cantidad) . ' coins';
    
    $message  = "Hola {$user->display_name},\n\n";
    $message .= "¡Gracias por tu compra!\n\n";
    $message .= "Has recibido " . coins_manager()->format_coins($cantidad) . " coins por tu compra de curso premium.\n\n";
    $message .= "Balance actual: " . coins_manager()->format_coins($coins_totales) . " coins\n\n";
    $message .= "Puedes usar tus coins para canjear cursos gratis en nuestra tienda.\n\n";
    $message .= "Ver cursos canjeables: " . get_permalink(wc_get_page_id('shop')) . "\n\n";
    if ($order) {
        $message .= "Orden #" . $order->get_order_number() . "\n\n";
    }
    $message .= "Gracias por confiar en " . get_bloginfo('name') . ".\n\n";
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Obtener el balance de coins de un usuario (función helper)
 * 
 * @param int $user_id ID del usuario (opcional, por defecto usuario actual)
 * @return float
 */
function get_user_coins($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    return coins_manager()->get_coins($user_id);
}

/**
 * Verificar si un producto requiere coins (productos gratis)
 * 
 * @param int $product_id ID del producto
 * @return bool
 */
function producto_requiere_coins($product_id) {
    return has_term('gratis', 'product_cat', $product_id);
}

/**
 * Verificar si un producto otorga coins (productos premium)
 * 
 * @param int $product_id ID del producto
 * @return bool
 */
function producto_otorga_coins($product_id) {
    return has_term('premium', 'product_cat', $product_id);
}

/**
 * Obtener el costo en coins de un producto (gratis)
 * 
 * @param int $product_id ID del producto
 * @return float
 */
function get_costo_en_coins($product_id) {
    return coins_manager()->get_costo_coins_producto($product_id);
}

/**
 * Helper: coins que gana el usuario por curso premium (editable, default 2)
 * 
 * @param int $product_id
 * @return int
 */
function coins_get_coins_ganados_producto($product_id) {
    if (!has_term('premium', 'product_cat', $product_id)) {
        return 0;
    }

    $valor_meta = get_post_meta($product_id, '_coins_ganados', true);
    $valor_meta = intval($valor_meta);

    if ($valor_meta > 0) {
        return $valor_meta;
    }

    // Default si no hay meta o es 0
    return 2;
}

/**
 * Shortcode para mostrar balance de coins
 * Uso: [coins_balance]
 */
add_shortcode('coins_balance', 'coins_balance_shortcode');

function coins_balance_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesión para ver tus coins.</p>';
    }
    
    $user_id = get_current_user_id();
    $coins   = coins_manager()->get_coins($user_id);
    
    $atts = shortcode_atts(array(
        'mostrar_link' => 'si',
        'texto_link'   => 'Ver historial de coins'
    ), $atts);
    
    $output  = '<div class="coins-balance-shortcode" style="background:#f7f7f7;padding:15px;border-radius:8px;display:inline-block;">';
    $output .= '<span class="coins-numero" style="font-size:24px;font-weight:bold;margin-right:5px;">' . coins_manager()->format_coins($coins) . '</span>';
    $output .= '<span class="coins-texto">coins disponibles</span>';
    
    if ($atts['mostrar_link'] === 'si') {
        $output .= ' <br><a href="' . esc_url(wc_get_account_endpoint_url('historial-coins')) . '">';
        $output .= esc_html($atts['texto_link']) . '</a>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Shortcode para mostrar costo en coins de un producto (solo cursos gratis)
 * Uso: [coins_precio] o [coins_precio product_id="123"]
 */
add_shortcode('coins_precio', 'coins_precio_shortcode');

function coins_precio_shortcode($atts) {
    global $product;
    
    $atts = shortcode_atts(array(
        'product_id' => 0,
        'show_label' => 'si'
    ), $atts);
    
    $product_id = (int) $atts['product_id'];
    
    if (!$product_id && $product instanceof WC_Product) {
        $product_id = $product->get_id();
    }
    
    if (!$product_id || !function_exists('coins_manager')) {
        return '';
    }
    
    $costo = coins_manager()->get_costo_coins_producto($product_id);
    
    if ($costo <= 0) {
        return '';
    }
    
    $label = ($costo == 1) ? 'Coin' : 'Coins';
    $texto = '🪙 ' . coins_manager()->format_coins($costo) . ' ' . $label;
    
    if ($atts['show_label'] === 'no') {
        $texto = coins_manager()->format_coins($costo);
    }
    
    return '<span class="coins-precio">' . $texto . '</span>';
}

/**
 * Shortcode para mostrar cursos canjeables
 * Uso: [cursos_canjeables limite="6" columnas="3"]
 */
add_shortcode('cursos_canjeables', 'cursos_canjeables_shortcode');

function cursos_canjeables_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limite'   => 6,
        'columnas' => 3
    ), $atts);
    
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => (int) $atts['limite'],
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'gratis'
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return '<p>No hay cursos canjeables disponibles en este momento.</p>';
    }
    
    $output  = '<div class="cursos-canjeables-grid" style="display:grid;grid-template-columns:repeat(' . (int) $atts['columnas'] . ',1fr);gap:20px;">';
    
    while ($query->have_posts()) {
        $query->the_post();
        $product = wc_get_product(get_the_ID());
        
        $costo = coins_manager()->get_costo_coins_producto($product->get_id());
        $label = ($costo == 1) ? 'Coin' : 'Coins';
        
        $output .= '<div class="curso-canjeable" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.05);padding-bottom:15px;">';
        $output .= '<a href="' . get_permalink() . '">';
        $output .= get_the_post_thumbnail(get_the_ID(), 'medium', array('style' => 'width:100%;height:auto;'));
        $output .= '<h3 style="padding:10px 15px;margin:0;font-size:16px;">' . get_the_title() . '</h3>';
        $output .= '</a>';
        $output .= '<p class="precio-coins" style="padding:0 15px;margin:5px 0 10px;font-weight:bold;color:#667eea;">🪙 ' . coins_manager()->format_coins($costo) . ' ' . $label . '</p>';
        $output .= '<div style="padding:0 15px;">';
        $output .= '<a href="' . esc_url($product->add_to_cart_url()) . '" class="button add_to_cart_button ajax_add_to_cart" data-product_id="' . $product->get_id() . '">Canjear</a>';
        $output .= '</div>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    wp_reset_postdata();
    
    return $output;
}

/**
 * Shortcode para mostrar precio del curso:
 * - Premium: precio original tachado + precio actual grande
 * - Gratis: costo en coins
 * Uso: [curso_precio] o [curso_precio product_id="123"]
 */
add_shortcode('curso_precio', 'curso_precio_shortcode');

function curso_precio_shortcode($atts) {
    global $product;

    $atts = shortcode_atts(array(
        'product_id' => 0,
    ), $atts);

    $product_id = (int) $atts['product_id'];

    if (!$product_id && $product instanceof WC_Product) {
        $product_id = $product->get_id();
    }

    if (!$product_id) {
        return '';
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return '';
    }

    // Si es curso gratis → mostrar coins con imagen
    if (has_term('gratis', 'product_cat', $product_id)) {
        if (!function_exists('coins_manager')) {
            return '';
        }

        $costo = coins_manager()->get_costo_coins_producto($product_id);
        if ($costo <= 0) {
            return '';
        }

        $label = ($costo == 1) ? 'Coin' : 'Coins';
        
        $coin_image_url = 'https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png';

        ob_start();
        ?>
        <div class="curso-precio-wrapper curso-precio-gratis">
            <span class="curso-precio-coins">
                <img src="<?php echo esc_url($coin_image_url); ?>" alt="Coin" class="coin-icon" />
                <?php echo esc_html(coins_manager()->format_coins($costo)); ?> <?php echo esc_html($label); ?>
            </span>
        </div>
        <?php
        return ob_get_clean();
    }

    // Si es curso premium → mostrar precio con posible descuento
    if (has_term('premium', 'product_cat', $product_id)) {
        $precio_regular = $product->get_regular_price();
        $precio_oferta  = $product->get_sale_price();
        $tiene_oferta   = $product->is_on_sale() && $precio_oferta !== '';

        $precio_regular_html = $precio_regular !== '' ? wc_price($precio_regular) : '';
        $precio_oferta_html  = $tiene_oferta ? wc_price($precio_oferta) : $precio_regular_html;

        ob_start();
        ?>
        <div class="curso-precio-wrapper curso-precio-premium">
            <?php if ($tiene_oferta && $precio_regular_html): ?>
                <div class="curso-precio-original">
                    <span class="curso-precio-original-texto"><?php echo wp_kses_post($precio_regular_html); ?></span>
                </div>
            <?php endif; ?>
            <div class="curso-precio-actual">
                <span class="curso-precio-actual-texto"><?php echo wp_kses_post($precio_oferta_html); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Si no es ni premium ni gratis, mostrar precio HTML estándar
    $precio_html = $product->get_price_html();
    if (!$precio_html) {
        return '';
    }

    return '<div class="curso-precio-wrapper curso-precio-simple">' . $precio_html . '</div>';
}

/**
 * Shortcode: [coins_ganados] -> "Ganarás X coins" + modal info
 */
add_shortcode('coins_ganados', 'coins_ganados_shortcode');

function coins_ganados_shortcode($atts) {
    if (!is_product()) {
        return '';
    }

    global $product;
    if (!$product) {
        return '';
    }

    $product_id = $product->get_id();

    // Solo mostrar para cursos premium
    if (!has_term('premium', 'product_cat', $product_id)) {
        return '';
    }

    if (!function_exists('coins_get_coins_ganados_producto')) {
        return '';
    }

    $coins_ganados = coins_get_coins_ganados_producto($product_id);
    if ($coins_ganados <= 0) {
        return '';
    }

    // ID único para modal
    $uid       = uniqid('coins-info-');
    $toggle_id = 'coins-modal-toggle-' . $uid;

    ob_start();
    ?>
    <div class="coins-ganados-wrapper">
        <input type="checkbox" id="<?php echo esc_attr($toggle_id); ?>" class="coins-modal-toggle" style="display:none" />

        <label for="<?php echo esc_attr($toggle_id); ?>" class="coins-ganados-trigger">
            Ganarás <strong><?php echo esc_html(coins_manager()->format_coins($coins_ganados)); ?> coins</strong>
        </label>

        <div class="coins-modal-overlay">
            <label for="<?php echo esc_attr($toggle_id); ?>" class="coins-modal-close-area"></label>
            <div class="coins-modal-dialog">
                <div class="coins-modal-header">
                    <div class="coins-modal-icon">
                        <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coins">
                    </div>
                    <div>
                        <h3><?php echo esc_html('¿Qué son los coins?'); ?></h3>
                        <p><?php
                            printf(
                                'Ganarás %s coins al comprar este curso.',
                                esc_html(coins_manager()->format_coins($coins_ganados))
                            );
                        ?></p>
                    </div>
                    <label for="<?php echo esc_attr($toggle_id); ?>" class="coins-modal-close-btn">&times;</label>
                </div>

                <div class="coins-modal-body">
                    <p><strong><?php echo esc_html('¿Para qué sirven?'); ?></strong></p>
                    <p><?php echo esc_html('Los coins son puntos que acumulas en tu cuenta cada vez que compras cursos premium o dejas reseñas verificadas.'); ?></p>

                    <p><strong><?php echo esc_html('¿Cómo los puedes usar?'); ?></strong></p>
                    <ul>
                        <li><?php echo esc_html('Canjéalos por cursos catalogados como "gratis con coins".'); ?></li>
                        <li><?php echo esc_html('Mira tu balance y movimientos desde tu área de cliente en "Mis Coins".'); ?></li>
                    </ul>

                    <p><strong><?php echo esc_html('Ejemplo:'); ?></strong>
                        <?php
                        printf(
                            ' Si compras este curso, ganarás %s coins que se sumarán automáticamente a tu balance una vez se complete la compra.',
                            esc_html(coins_manager()->format_coins($coins_ganados))
                        );
                        ?>
                    </p>
                </div>

                <div class="coins-modal-footer">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('historial-coins')); ?>" class="coins-btn primary">
                        Ver mis coins
                    </a>
                    <label for="<?php echo esc_attr($toggle_id); ?>" class="coins-btn secondary">
                        Entendido
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Estilos para shortcode [coins_ganados] y su modal
 */
add_action('wp_head', function () {
    ?>
    <style>
      /* Prevenir scroll horizontal en todo el sitio */
      html, body {
        overflow-x: hidden;
        max-width: 100vw;
      }

      /* Bloquear scroll del body cuando modal está abierto */
      body.coins-modal-open {
        overflow: hidden !important;
        position: fixed;
        width: 100%;
        height: 100%;
      }

      .coins-ganados-wrapper{
        margin:12px 0 0;
      }
      .coins-ganados-wrapper *{
        box-sizing: border-box;
      }
      .coins-ganados-trigger{
        display:inline-flex;
        align-items:center;
        gap:4px;
        padding:5px 10px;
        border-radius:999px;
        background:linear-gradient(135deg,rgba(218,4,128,.15),rgba(218,4,128,.08));
        border:1.5px solid rgba(218,4,128,.4);
        color:#da0480;
        font-size:11px;
        font-weight:700;
        cursor:pointer;
        user-select:none;
        box-shadow:0 4px 14px rgba(218,4,128,.2);
        font-family:"Space Grotesk",sans-serif;
        transition:all .3s;
      }
      .coins-ganados-trigger strong{
        color:#fff;
        font-weight:800;
      }
      .coins-ganados-trigger:hover{
        transform:translateY(-1px);
        box-shadow:0 8px 24px rgba(218,4,128,.35);
        border-color:#da0480;
      }

      .coins-modal-overlay{
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0);
        backdrop-filter:blur(0);
        opacity:0;
        visibility:hidden;
        transition:all .3s cubic-bezier(.4,0,.2,1);
        z-index:2147483646;
        pointer-events:none;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:20px;
        overflow-y:auto;
        overflow-x:hidden;
        max-width:100vw;
      }
      .coins-modal-close-area{
        position:absolute;
        inset:0;
        cursor:default;
      }

      .coins-modal-dialog{
        position:relative;
        width:420px;
        max-width:calc(100vw - 40px);
        background:linear-gradient(135deg,#0f0f0f,#1a1a1a);
        color:#fff;
        border-radius:20px;
        border:1.5px solid rgba(218,4,128,.25);
        padding:20px 20px 18px;
        box-shadow:0 30px 90px rgba(0,0,0,.7),0 0 60px rgba(218,4,128,.12);
        transform:translateY(20px) scale(.96);
        transition:all .3s cubic-bezier(.4,0,.2,1);
        pointer-events:auto;
        z-index:2147483647;
        font-family:"Space Grotesk",sans-serif;
        box-sizing:border-box;
        overflow-x:hidden;
      }

      .coins-modal-header{
        display:flex;
        align-items:flex-start;
        gap:12px;
        margin-bottom:14px;
      }
      .coins-modal-icon{
        width:40px;
        height:40px;
        border-radius:999px;
        background:radial-gradient(circle at 30% 30%,#ffffff,#f5c6e1 40%,#7a0345 100%);
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
      }
      .coins-modal-icon img{
        width:26px;
        height:26px;
        object-fit:contain;
      }
      .coins-modal-header h3{
        margin:0 0 4px;
        font-size:18px;
        font-weight:800;
        color:#fff;
        word-break:break-word;
      }
      .coins-modal-header p{
        margin:0;
        font-size:13px;
        color:#9ca3af;
      }
      .coins-modal-close-btn{
        margin-left:auto;
        font-size:28px;
        color:#e5e7eb;
        cursor:pointer;
        line-height:1;
        padding:0;
        width:24px;
        height:24px;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
      }
      .coins-modal-close-btn:hover{
        color:#da0480;
      }

      .coins-modal-body{
        font-size:13px;
        color:#e5e7eb;
        line-height:1.6;
        margin-bottom:14px;
        word-break:break-word;
      }
      .coins-modal-body p{
        margin:0 0 8px;
      }
      .coins-modal-body ul{
        margin:0 0 10px 18px;
        padding:0;
      }
      .coins-modal-body li{
        margin-bottom:4px;
      }

      .coins-modal-footer{
        display:flex;
        justify-content:flex-end;
        gap:8px;
        flex-wrap:wrap;
      }

      .coins-modal-footer .coins-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:8px 16px;
        border-radius:999px;
        font-size:13px;
        font-weight:700;
        text-decoration:none;
        cursor:pointer;
        border:none;
        transition:all .2s;
        font-family:"Space Grotesk",sans-serif;
      }
      .coins-modal-footer .coins-btn.primary{
        background:linear-gradient(135deg,#da0480,#b00368);
        color:#fff;
        box-shadow:0 4px 16px rgba(218,4,128,.35);
      }
      .coins-modal-footer .coins-btn.primary:hover{
        transform:translateY(-1px);
        box-shadow:0 8px 24px rgba(218,4,128,.45);
      }
      .coins-modal-footer .coins-btn.secondary{
        background:rgba(0,0,0,.3);
        color:#cbd5e1;
        border:1px solid rgba(255,255,255,.08);
      }
      .coins-modal-footer .coins-btn.secondary:hover{
        background:rgba(0,0,0,.45);
        border-color:#da0480;
        color:#da0480;
      }

      .coins-modal-toggle:checked ~ .coins-modal-overlay{
        opacity:1;
        visibility:visible;
        background:rgba(0,0,0,.8);
        backdrop-filter:blur(8px);
        pointer-events:auto;
      }
      .coins-modal-toggle:checked ~ .coins-modal-overlay .coins-modal-dialog{
        transform:translateY(0) scale(1);
      }

      @media(max-width:768px){
        .coins-ganados-trigger{
          font-size:10px;
          padding:4px 8px;
          gap:3px;
        }
        .coins-modal-overlay{
          padding:15px;
        }
        .coins-modal-dialog{
          max-width:calc(100vw - 30px);
        }
      }

      @media(max-width:480px){
        .coins-ganados-trigger{
          font-size:9px;
          padding:4px 7px;
          gap:3px;
        }
        .coins-modal-overlay{
          padding:10px;
        }
        .coins-modal-dialog{
          width:100%;
          max-width:calc(100vw - 20px);
          padding:18px 16px 16px;
        }
        .coins-modal-header h3{
          font-size:16px;
        }
        .coins-modal-body{
          font-size:12px;
        }
        .coins-modal-footer .coins-btn{
          font-size:12px;
          padding:7px 12px;
        }
      }
    </style>
    <?php
}, 999);
