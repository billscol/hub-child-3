<?php
/**
 * Modal de Login/Registro para el shortcode [sp_auth]
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
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
        
        <div class="sp-auth-dialog" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(.92);width:440px;max-width:94vw;background:linear-gradient(135deg,#0f0f0f,#1a1a1a);color:#fff;border-radius:24px;border:1.5px solid rgba(218,4,128,.25);padding:30px 30px 150px 30px;box-shadow:0 30px 90px rgba(0,0,0,.7),0 0 60px rgba(218,4,128,.12);transition:all .3s cubic-bezier(.4,0,.2,1);pointer-events:auto;z-index:2147483647!important;backdrop-filter:blur(20px)">
          
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
    <?php
  }
}
?>