<?php
/**
 * Scripts JavaScript para el shortcode [sp_auth]
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('sp_auth_add_scripts')) {
  add_action('wp_footer', 'sp_auth_add_scripts', 99998);
  function sp_auth_add_scripts() {
    ?>
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
?>