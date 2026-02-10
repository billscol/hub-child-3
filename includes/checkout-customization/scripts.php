<?php
/**
 * Scripts personalizados para Checkout
 * JavaScript para funcionalidades interactivas del checkout
 * 
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inyectar JavaScript personalizado en el footer
 */
add_action('wp_footer', 'custom_checkout_scripts', 999);
function custom_checkout_scripts() {
    // Solo cargar en página de checkout
    if (!is_checkout()) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        var initialized = false;
        var buttonObserver = null;
        var isProcessing = false;

        // Inicializar checkout personalizado
        function initCheckout() {
            if (initialized) return;
            initialized = true;
            
            console.log('🚀 Iniciando checkout personalizado...');

            // CERRAR/OCULTAR PANEL DE LOGIN
            $('.sp-auth-panel, .sp-panel-login, .sp-auth-overlay').remove();

            // CAMBIAR LABELS
            $('label[for="billing-phone"]').text('WhatsApp');
            $('label[for="email"]').text('Correo electrónico');

            // MANTENER RESUMEN ABIERTO
            $('.wc-block-components-checkout-order-summary__content').addClass('is-open').css({
                'display': 'block',
                'max-height': 'none'
            });

            // AJUSTAR POSICIÓN DEL RESUMEN
            adjustSummaryPosition();
            $(window).on('scroll resize', adjustSummaryPosition);
            setTimeout(adjustSummaryPosition, 100);
            setTimeout(adjustSummaryPosition, 500);
            setTimeout(adjustSummaryPosition, 1500);

            // CREAR BOTONES PROXY
            createProxyButtons();
        }

        // Ajustar posición del resumen según header sticky
        function adjustSummaryPosition() {
            if ($(window).width() <= 768) return;
            
            var $summary = $('.wp-block-woocommerce-checkout-order-summary-block');
            var $header = $('.lqd-site-header, .main-header, header.is-stuck, .lqd-stickybar-wrap');
            
            if ($summary.length === 0) return;
            
            var headerHeight = 0;
            $header.each(function() {
                var $this = $(this);
                if ($this.hasClass('is-stuck') || $this.css('position') === 'fixed' || $this.css('position') === 'sticky') {
                    var thisHeight = $this.outerHeight();
                    if (thisHeight > headerHeight) {
                        headerHeight = thisHeight;
                    }
                }
            });
            
            if (headerHeight > 0) {
                $summary.css('top', (headerHeight + 60) + 'px');
                $summary.css('max-height', 'calc(100vh - ' + (headerHeight + 80) + 'px)');
            } else {
                $summary.css('top', '80px');
                $summary.css('max-height', 'calc(100vh - 100px)');
            }
        }

        // Crear botones proxy en el resumen
        function createProxyButtons() {
            if ($(window).width() <= 768) return;
            
            var $summary = $('.wp-block-woocommerce-checkout-order-summary-block');
            var $terms = $('.wc-block-checkout__terms');
            
            if ($('#moved-checkout-actions').length > 0) return;
            if ($summary.length === 0) return;
            
            console.log('✅ Creando botones proxy en el resumen...');
            
            var $container = $('<div id="moved-checkout-actions"></div>');
            
            // CLONAR TÉRMINOS
            if ($terms.length > 0) {
                var $termsClone = $terms.first().clone(true, true);
                $termsClone.addClass('moved-to-summary');
                $container.append($termsClone);
            }
            
            // BOTÓN REALIZAR PEDIDO
            var $proxyPlaceOrderBtn = $('<button id="proxy-place-order-button" type="button">Realizar el pedido</button>');
            
            $(document).off('click.proxyCheckout', '#proxy-place-order-button');
            $(document).on('click.proxyCheckout', '#proxy-place-order-button', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                if (isProcessing) return false;
                
                isProcessing = true;
                console.log('🔘 Click en botón proxy detectado');
                
                var $proxyBtn = $(this);
                $proxyBtn.prop('disabled', true).text('Procesando...');
                
                setTimeout(function() {
                    var $realBtn = $('.wc-block-components-checkout-place-order-button');
                    if ($realBtn.length > 0) {
                        console.log('✅ Botón real encontrado, activando click...');
                        $realBtn[0].click();
                        
                        setTimeout(function() {
                            $proxyBtn.prop('disabled', false).text('Realizar el pedido');
                            isProcessing = false;
                        }, 2000);
                    } else {
                        console.error('❌ Botón real no encontrado');
                        $proxyBtn.prop('disabled', false).text('Realizar el pedido');
                        isProcessing = false;
                    }
                }, 100);
                
                return false;
            });
            
            $container.append($proxyPlaceOrderBtn);
            
            // BOTÓN VOLVER AL CARRITO
            var cartUrl = $('.wc-block-components-checkout-return-to-cart-button').attr('href') || (typeof wc_checkout_params !== 'undefined' ? wc_checkout_params.cart_url : '/cart');
            var $proxyReturnBtn = $('<a id="proxy-return-to-cart-button" href="' + cartUrl + '">Volver al carrito</a>');
            $container.append($proxyReturnBtn);
            
            $summary.append($container);
            console.log('✅ Botones proxy creados exitosamente');
        }

        // Observador para detectar cambios en el DOM
        function observeCheckoutChanges() {
            var targetNode = document.querySelector('.wc-block-checkout');
            if (!targetNode) {
                setTimeout(observeCheckoutChanges, 500);
                return;
            }

            var config = { childList: true, subtree: true };
            
            var callback = function(mutationsList, observer) {
                for (var mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        initCheckout();
                    }
                }
            };

            var observer = new MutationObserver(callback);
            observer.observe(targetNode, config);
        }

        // INICIAR OBSERVADOR
        observeCheckoutChanges();

        // INICIALIZAR INMEDIATAMENTE
        setTimeout(initCheckout, 500);
        setTimeout(initCheckout, 1500);
        setTimeout(initCheckout, 3000);

        // REINICIAR EN ACTUALIZACIÓN DE CHECKOUT
        $(document.body).on('updated_checkout', function() {
            initialized = false;
            setTimeout(initCheckout, 300);
        });

        // PREVENIR CIERRE DE RESUMEN EN MOBILE
        $(document).on('click', '.wc-block-components-checkout-order-summary__title', function(e) {
            e.preventDefault();
            $('.wc-block-components-checkout-order-summary__content').addClass('is-open').css({
                'display': 'block',
                'max-height': 'none'
            });
        });
    });
    </script>
    <?php
}
