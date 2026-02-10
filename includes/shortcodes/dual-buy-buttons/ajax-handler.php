<?php
/**
 * Manejador AJAX para "Comprar Ahora"
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function dual_buttons_add_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Manejar clic en "Comprar Ahora"
        $(document).on('click', '.buy-now-button', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var productId = button.data('product-id');
            
            // Verificar si el usuario está logueado
            <?php if (!is_user_logged_in()) { ?>
                // Abrir modal de login
                $('#sp-modal-main').prop('checked', true);
                
                // Guardar destino de redirección después del login
                window.sp_after_login_redirect = '<?php echo esc_js(wc_get_checkout_url()); ?>';
                return;
            <?php } ?>
            
            // Deshabilitar botón y mostrar cargando
            button.addClass('loading').prop('disabled', true);
            
            // Agregar al carrito via AJAX
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_ajax_add_to_cart',
                    product_id: productId,
                    quantity: 1
                },
                success: function(response) {
                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    
                    // Redirigir al checkout
                    window.location.href = '<?php echo esc_js(wc_get_checkout_url()); ?>';
                },
                error: function() {
                    alert('❌ Error al agregar el producto al carrito. Inténtalo de nuevo.');
                    button.removeClass('loading').prop('disabled', false);
                }
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'dual_buttons_add_scripts', 999);

// Registrar acción AJAX para agregar al carrito
function woocommerce_ajax_add_to_cart_handler() {
    if (!isset($_POST['product_id'])) {
        wp_send_json_error();
    }
    
    $product_id = absint($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    
    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity)) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);
        
        wp_send_json_success(array(
            'cart_hash' => WC()->cart->get_cart_hash(),
        ));
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart_handler');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart_handler');
?>