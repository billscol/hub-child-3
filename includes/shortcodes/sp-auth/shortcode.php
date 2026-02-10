<?php
/**
 * Shortcode: [sp_auth]
 * Sistema de autenticación con modal de login/registro
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

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

?>