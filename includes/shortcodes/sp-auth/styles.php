<?php
/**
 * Estilos CSS para el shortcode [sp_auth]
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('sp_auth_add_styles')) {
  add_action('wp_head', 'sp_auth_add_styles', 999);
  function sp_auth_add_styles() {
    ?>
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
      
      /* FIX DEFINITIVO: Deshabilitar TODO el modal cuando está cerrado */
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
    <?php
  }
}
?>