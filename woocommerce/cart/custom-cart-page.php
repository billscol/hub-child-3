<?php
/**
 * Plantilla de carrito personalizada - Diseño tipo tienda mejorado
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<style>
/* Fondo oscuro */
body.woocommerce-cart {
    background: #0a0a0a !important;
}

/* Ocultar todo el contenido nativo de WooCommerce */
.woocommerce-cart .woocommerce {
    display: none;
}

/* Wrapper principal del carrito */
.cb-custom-cart {
    font-family: "Space Grotesk", -apple-system, BlinkMacSystemFont, sans-serif;
    max-width: 1400px;
    margin: 0 auto;
    padding: 140px 20px 80px;
    color: #e5e7eb;
}

/* Header del carrito */
.cb-cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 16px;
}

.cb-cart-title {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cb-cart-title::before {
    content: '';
    width: 6px;
    height: 42px;
    background: linear-gradient(180deg, #da0480, #b00368);
    border-radius: 3px;
}

.cb-cart-badge {
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    background: rgba(218, 4, 128, 0.15);
    border: 1px solid rgba(218, 4, 128, 0.4);
    color: #da0480;
}

/* Layout: Cards izquierda + Resumen derecha */
.cb-cart-content {
    display: grid;
    grid-template-columns: minmax(0, 2.5fr) minmax(0, 1fr);
    gap: 30px;
    align-items: start;
}

/* Columna izquierda: Lista de productos */
.cb-cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Card de producto mejorada */
.cb-cart-card {
    background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
    border: 1.5px solid rgba(218,4,128,.15);
    border-radius: 14px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 180px minmax(0, 1fr);
    gap: 20px;
    padding: 16px;
    transition: all 0.3s;
    position: relative;
}

.cb-cart-card:hover {
    border-color: rgba(218,4,128,.4);
    box-shadow: 0 8px 24px rgba(218,4,128,0.15);
}

/* Imagen del producto */
.cb-cart-img {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
}

.cb-cart-img img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.4);
}

/* Badge premium/gratis en esquina superior derecha de la imagen */
.cb-cart-badge-corner {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    z-index: 2;
}

.cb-cart-badge-corner.premium {
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #fff;
    box-shadow: 0 3px 12px rgba(218,4,128,0.5);
}

.cb-cart-badge-corner.gratis {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    box-shadow: 0 3px 12px rgba(102,126,234,0.5);
}

/* Info del producto */
.cb-cart-info {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cb-cart-info h3 {
    font-size: 17px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px 0;
    line-height: 1.3;
    padding-right: 40px; /* espacio para botón eliminar */
}

.cb-cart-info h3 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s;
}

.cb-cart-info h3 a:hover {
    color: #da0480;
}

/* Precio con etiqueta Subtotal */
.cb-cart-price-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 8px;
}

.cb-cart-price-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cb-cart-price {
    font-size: 24px;
    font-weight: 800;
    color: #da0480;
    margin: 0;
}

/* Botón eliminar flotante arriba derecha */
.cb-cart-remove {
    position: absolute;
    top: 16px;
    right: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #ef4444;
    text-decoration: none;
    transition: all 0.3s;
    z-index: 3;
}

.cb-cart-remove:hover {
    background: rgba(239,68,68,0.2);
    border-color: #ef4444;
    color: #fff;
    transform: scale(1.1);
}

.cb-cart-remove svg {
    width: 14px;
    height: 14px;
}

/* Botón seguir comprando */
.cb-cart-continue {
    margin-top: 10px;
}

.cb-cart-continue a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 999px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #e5e7eb;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.cb-cart-continue a:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(218,4,128,0.4);
    color: #da0480;
    transform: translateY(-2px);
}

/* Columna derecha: Resumen */
.cb-cart-summary {
    background: radial-gradient(circle at top left, rgba(218,4,128,0.35), transparent 55%),
                linear-gradient(135deg, #020617, #0f0f0f);
    border-radius: 16px;
    border: 1.5px solid rgba(218,4,128,0.4);
    padding: 24px;
    box-shadow: 0 14px 40px rgba(15,23,42,0.9);
    position: sticky;
    top: 140px;
}

.cb-cart-summary h2 {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 20px 0;
}

.cb-summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    font-size: 15px;
}

.cb-summary-line span:first-child {
    color: #9ca3af;
    font-weight: 500;
}

.cb-summary-line span:last-child {
    color: #e5e7eb;
    font-weight: 600;
}

.cb-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0 0 0;
    margin-top: 8px;
    border-top: 2px solid rgba(218,4,128,0.3);
}

.cb-summary-total span:first-child {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
}

.cb-summary-total span:last-child {
    font-size: 26px;
    font-weight: 800;
    color: #da0480;
}

/* Botones de pago */
.cb-payment-buttons {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cb-checkout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 16px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #da0480, #b00368);
    box-shadow: 0 10px 30px rgba(218,4,128,0.5);
    border: none;
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
}

.cb-checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 40px rgba(218,4,128,0.7);
    color: #fff;
}

/* Contenedor PayPal y otros métodos */
.cb-alternative-payments {
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.cb-alternative-payments .woocommerce-mini-cart__buttons {
    margin: 0;
}

/* Estilizar botón PayPal nativo */
.cb-alternative-payments .paypal-button,
.cb-alternative-payments .wc-stripe-payment-request-button-separator,
.cb-alternative-payments .woocommerce-PaymentMethod {
    margin-bottom: 8px;
}

/* Estado vacío */
.cb-cart-empty {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
    border: 1.5px solid rgba(218,4,128,.15);
    border-radius: 16px;
}

.cb-cart-empty svg {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.cb-cart-empty h2 {
    color: #fff;
    font-size: 24px;
    margin-bottom: 10px;
}

.cb-cart-empty p {
    color: #9ca3af;
    font-size: 16px;
    margin-bottom: 30px;
}

.cb-cart-empty a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    border-radius: 12px;
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s;
}

.cb-cart-empty a:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(218,4,128,0.5);
}

/* Responsive */
@media (max-width: 1024px) {
    .cb-cart-content {
        grid-template-columns: 1fr;
    }
    
    .cb-cart-summary {
        position: static;
        top: auto;
    }
    
    .cb-custom-cart {
        padding-top: 100px;
    }
}

@media (max-width: 768px) {
    .cb-custom-cart {
        padding: 80px 15px 60px;
    }
    
    .cb-cart-title {
        font-size: 28px;
    }
    
    .cb-cart-title::before {
        height: 36px;
    }
    
    .cb-cart-card {
        grid-template-columns: 120px minmax(0, 1fr);
        gap: 14px;
    }
    
    .cb-cart-img {
        max-width: 120px;
    }
    
    .cb-cart-info h3 {
        font-size: 15px;
        padding-right: 35px;
    }
    
    .cb-cart-price {
        font-size: 20px;
    }
    
    .cb-cart-remove {
        width: 28px;
        height: 28px;
    }
}

@media (max-width: 480px) {
    .cb-cart-card {
        grid-template-columns: 1fr;
    }
    
    .cb-cart-img {
        max-width: 100%;
    }
    
    .cb-cart-info h3 {
        padding-right: 0;
    }
    
    .cb-cart-remove {
        top: 10px;
        right: 10px;
    }
}
</style>

<div class="cb-custom-cart">
    <?php
    // Ejecutar the_content() para Elementor (aunque no lo mostramos)
    while (have_posts()) : the_post();
        ob_start();
        the_content();
        ob_end_clean();
    endwhile;
    
    // Trabajar con la API de WooCommerce
    $cart = WC()->cart;
    
    if ($cart && !$cart->is_empty()) :
        $cart_items = $cart->get_cart();
        $items_count = $cart->get_cart_contents_count();
        ?>
        
        <!-- Header -->
        <div class="cb-cart-header">
            <h1 class="cb-cart-title">Tu carrito</h1>
            <span class="cb-cart-badge">
                <?php echo sprintf('%d curso%s', $items_count, $items_count === 1 ? '' : 's'); ?>
            </span>
        </div>
        
        <!-- Layout principal -->
        <div class="cb-cart-content">
            
            <!-- Columna izquierda: Cards de productos -->
            <div class="cb-cart-items">
                <?php foreach ($cart_items as $cart_item_key => $cart_item) :
                    $product = $cart_item['data'];
                    if (!$product || !$product->exists()) {
                        continue;
                    }
                    
                    $product_id = $product->get_id();
                    $product_name = $product->get_name();
                    $product_link = $product->is_visible() ? $product->get_permalink($cart_item) : '';
                    $thumbnail = $product->get_image('medium');
                    $price = $product->get_price();
                    $remove_url = wc_get_cart_remove_url($cart_item_key);
                    
                    // Detectar categorías
                    $es_premium = has_term('premium', 'product_cat', $product_id);
                    $es_gratis = has_term('gratis', 'product_cat', $product_id);
                ?>
                    <div class="cb-cart-card">
                        <!-- Botón eliminar flotante -->
                        <a href="<?php echo esc_url($remove_url); ?>" class="cb-cart-remove" aria-label="Eliminar producto">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                        
                        <div class="cb-cart-img">
                            <?php if ($product_link) : ?>
                                <a href="<?php echo esc_url($product_link); ?>">
                                    <?php echo $thumbnail; ?>
                                </a>
                            <?php else : ?>
                                <?php echo $thumbnail; ?>
                            <?php endif; ?>
                            
                            <!-- Badge en esquina de imagen -->
                            <?php if ($es_premium) : ?>
                                <span class="cb-cart-badge-corner premium">Premium</span>
                            <?php elseif ($es_gratis) : ?>
                                <span class="cb-cart-badge-corner gratis">Gratis</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cb-cart-info">
                            <div>
                                <h3>
                                    <?php if ($product_link) : ?>
                                        <a href="<?php echo esc_url($product_link); ?>">
                                            <?php echo esc_html($product_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html($product_name); ?>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            
                            <div class="cb-cart-price-wrap">
                                <span class="cb-cart-price-label">Subtotal</span>
                                <p class="cb-cart-price">
                                    <?php echo wc_price($price); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cb-cart-continue">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Seguir comprando
                    </a>
                </div>
            </div>
            
            <!-- Columna derecha: Resumen -->
            <div class="cb-cart-summary">
                <h2>Resumen del pedido</h2>
                
                <div class="cb-summary-line">
                    <span>Subtotal</span>
                    <span><?php echo wc_price($cart->get_subtotal()); ?></span>
                </div>
                
                <?php if ($cart->get_discount_total() > 0) : ?>
                    <div class="cb-summary-line">
                        <span>Descuento</span>
                        <span>-<?php echo wc_price($cart->get_discount_total()); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($cart->get_total_tax() > 0) : ?>
                    <div class="cb-summary-line">
                        <span>Impuestos</span>
                        <span><?php echo wc_price($cart->get_total_tax()); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="cb-summary-total">
                    <span>Total</span>
                    <span><?php echo wc_price($cart->get_total('edit')); ?></span>
                </div>
                
                <div class="cb-payment-buttons">
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cb-checkout-btn">
                        Proceder al pago
                    </a>
                    
                    <!-- Botones alternativos de pago (PayPal, etc.) -->
                    <div class="cb-alternative-payments">
                        <?php
                        // Hook para botones de pago express (PayPal, Apple Pay, Google Pay, etc.)
                        do_action('woocommerce_proceed_to_checkout');
                        ?>
                    </div>
                </div>
            </div>
            
        </div>
        
    <?php else : ?>
        
        <!-- Estado vacío -->
        <div class="cb-cart-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="#da0480" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h2>Tu carrito está vacío</h2>
            <p>Aún no has agregado ningún curso a tu carrito</p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                Explorar cursos
            </a>
        </div>
        
    <?php endif; ?>
</div>

<?php
get_footer();
