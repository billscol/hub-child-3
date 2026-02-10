<?php
/**
 * ARCHIVO styles.php
 * 
 * Estilos personalizados para Carrito y Checkout
 * 
 * Diseño oscuro elegante con colores personalizados
 * 
 * RUTA ACTUAL /home/bills/dominios/cursobarato/wp-content/themes/hub-child/includes/checkout-customization/styles.php
 * @package YourChildTheme
 * @subpackage CheckoutCustomization
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inyectar estilos CSS personalizados en el head
 */
add_action('wp_head', 'custom_cart_checkout_dark_styles', 999);
function custom_cart_checkout_dark_styles() {
    // Solo cargar en páginas de carrito y checkout
    if (!is_cart() && !is_checkout()) {
        return;
    }
    ?>
    <style id="custom-cart-checkout-dark-v11">
        /* ========================================
           OCULTAR PANEL DE LOGIN EN CHECKOUT
        ======================================== */
        
        .sp-auth-panel,
        .sp-panel-login,
        .sp-auth-overlay {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            position: fixed !important;
            left: -9999px !important;
            z-index: -1 !important;
        }
        
        /* ========================================
           AJUSTE ESPECÍFICO PARA TEMA HUB
        ======================================== */
        
        .woocommerce-checkout #lqd-site-content {
            padding-top: 173px !important;
            padding-bottom: 70px !important;
        }
        
        /* ========================================
           ELIMINAR RESUMEN DUPLICADO
        ======================================== */
        
        .checkout-order-summary-block-fill-wrapper {
            display: none !important;
        }
        
        /* ========================================
           RESUMEN FLOTANTE SIEMPRE ABIERTO
        ======================================== */
        
        .wc-block-components-checkout-order-summary__title {
            cursor: pointer;
        }
        
        .wc-block-components-checkout-order-summary__title-text {
            color: #da0480 !important;
            font-weight: 800 !important;
            font-size: 22px !important;
        }
        
        .wc-block-components-checkout-order-summary__title-price {
            color: #da0480 !important;
            font-weight: 800 !important;
        }
        
        .wc-block-components-checkout-order-summary__content {
            display: block !important;
            max-height: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        @media (max-width: 768px) {
            .wc-block-components-checkout-order-summary__content.is-open,
            .wc-block-components-checkout-order-summary__content {
                display: block !important;
                max-height: none !important;
            }
        }
        
        /* ========================================
           REORDENAR SECCIONES DEL CHECKOUT
        ======================================== */
        
        .wc-block-components-form {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .wc-block-checkout__contact-fields {
            order: 2 !important;
        }
        
        .wc-block-checkout__billing-fields {
            order: 1 !important;
        }
        
        .wc-block-checkout__payment-method {
            order: 3 !important;
        }
        
        .wc-block-checkout__order-notes {
            order: 4 !important;
        }
        
        .wc-block-checkout__terms {
            order: 5 !important;
        }
        
        .wc-block-checkout__actions {
            order: 6 !important;
        }
        
        /* ========================================
           FONDO OSCURO COMPLETO
        ======================================== */
        
        body.woocommerce-cart,
        body.woocommerce-checkout {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e !important;
        }
        
        .woocommerce {
            padding: 40px 0;
        }
        
        /* ========================================
           OCULTAR CAMPOS DE DIRECCIÓN EN EL FORMULARIO
           (NO ocultar la tarjeta de dirección guardada)
        ======================================== */
        
        /* Ocultar campos SOLO dentro del formulario */
        .wc-block-components-address-form .wc-block-components-address-form__country,
        .wc-block-components-address-form .wc-block-components-address-form__address_1,
        .wc-block-components-address-form .wc-block-components-address-form__address_2,
        .wc-block-components-address-form .wc-block-components-address-form__address_2-toggle,
        .wc-block-components-address-form .wc-block-components-address-form__address_2-hidden-input,
        .wc-block-components-address-form .wc-block-components-address-form__city,
        .wc-block-components-address-form .wc-block-components-address-form__state,
        .wc-block-components-address-form .wc-block-components-address-form__postcode,
        .wc-block-components-address-form .wc-block-components-country-input,
        .wc-block-components-address-form .wc-block-components-state-input {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* Mostrar SOLO nombre, apellido y teléfono EN EL FORMULARIO */
        .wc-block-components-address-form .wc-block-components-address-form__first_name,
        .wc-block-components-address-form .wc-block-components-address-form__last_name,
        .wc-block-components-address-form .wc-block-components-address-form__phone {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            width: auto !important;
            position: relative !important;
            left: 0 !important;
        }
        
        /* ========================================
           MOSTRAR TARJETA DE DIRECCIÓN GUARDADA
        ======================================== */
        
        /* Asegurar que la tarjeta de dirección guardada sea visible */
        .wc-block-components-address-card-wrapper,
        .wc-block-components-address-card {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            width: auto !important;
            position: relative !important;
            left: 0 !important;
            margin-bottom: 20px !important;
        }
        
        /* Estilos bonitos para la tarjeta de dirección guardada */
        .wc-block-components-address-card {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.08) 0%, rgba(26, 31, 46, 0.9) 100%) !important;
            border: 2px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 12px !important;
            padding: 20px !important;
            box-shadow: 0 4px 12px rgba(218, 4, 128, 0.15) !important;
            transition: all 0.3s ease !important;
        }
        
        .wc-block-components-address-card:hover {
            border-color: rgba(218, 4, 128, 0.5) !important;
            box-shadow: 0 6px 16px rgba(218, 4, 128, 0.25) !important;
        }
        
        /* Contenido de la dirección */
        .wc-block-components-address-card address {
            color: #e2e8f0 !important;
            font-style: normal !important;
            line-height: 1.7 !important;
            margin-bottom: 15px !important;
        }
        
        /* Nombre del cliente (línea principal) */
        .wc-block-components-address-card__address-section--primary {
            display: block !important;
            font-weight: 700 !important;
            font-size: 17px !important;
            color: #fff !important;
            margin-bottom: 10px !important;
            padding-bottom: 10px !important;
            border-bottom: 1px solid rgba(218, 4, 128, 0.2) !important;
        }
        
        /* Datos secundarios (dirección, teléfono) */
        .wc-block-components-address-card__address-section--secondary {
            display: block !important;
            font-size: 14px !important;
            color: #cbd5e0 !important;
            line-height: 1.6 !important;
        }
        
        /* Botón "Editar" de la tarjeta */
        .wc-block-components-address-card__edit {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            align-items: center !important;
            gap: 6px !important;
            margin-top: 12px !important;
            padding: 10px 18px !important;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.15) 0%, rgba(218, 4, 128, 0.08) 100%) !important;
            border: 2px solid rgba(218, 4, 128, 0.4) !important;
            border-radius: 8px !important;
            color: #da0480 !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.3s !important;
            text-decoration: none !important;
        }
        
        /* Icono antes del texto "Editar" */
        .wc-block-components-address-card__edit::before {
            content: "✏️";
            font-size: 14px;
        }
        
        .wc-block-components-address-card__edit:hover {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.25) 0%, rgba(218, 4, 128, 0.15) 100%) !important;
            border-color: rgba(218, 4, 128, 0.6) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(218, 4, 128, 0.2) !important;
        }
        
        .wc-block-components-address-card__edit:focus {
            outline: 2px solid #da0480 !important;
            outline-offset: 2px !important;
        }
        
        /* ========================================
           OCULTAR DESCRIPCIÓN DEL PRODUCTO EN RESUMEN
        ======================================== */
        
        .wc-block-components-product-metadata,
        .wc-block-components-product-metadata__description {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* ========================================
           TAMAÑO DE IMAGEN DEL PRODUCTO EN RESUMEN
        ======================================== */
        
        .wc-block-components-order-summary .wc-block-components-order-summary-item__image > img {
            max-width: 70px !important;
            width: 65px !important;
        }
        
        /* ========================================
           MOVER BOTONES AL RESUMEN (SOLO ESCRITORIO)
        ======================================== */
        
        @media (min-width: 769px) {
            .wc-block-checkout__terms:not(.moved-to-summary),
            .wc-block-checkout__actions:not(.moved-to-summary),
            .wc-block-checkout__actions_row:not(.moved-to-summary) {
                display: none !important;
            }
            
            #moved-checkout-actions {
                margin-top: 25px;
                padding-top: 25px;
                border-top: 2px solid rgba(218, 4, 128, 0.2);
            }
            
            .wc-block-checkout__terms.moved-to-summary {
                display: block !important;
                margin-bottom: 20px !important;
                padding: 15px !important;
                background: rgba(218, 4, 128, 0.05) !important;
                border-radius: 10px !important;
                border: 1px solid rgba(218, 4, 128, 0.2) !important;
            }
            
            .wc-block-checkout__terms.moved-to-summary .wc-block-components-checkbox__label {
                font-size: 13px !important;
                line-height: 1.5 !important;
                color: #e2e8f0 !important;
            }
            
            #proxy-place-order-button {
                width: 100% !important;
                margin-bottom: 15px !important;
                justify-content: center !important;
                background: linear-gradient(135deg, #da0480 0%, #b00368 100%) !important;
                color: #fff !important;
                border: none !important;
                padding: 14px 28px !important;
                border-radius: 10px !important;
                font-weight: 700 !important;
                font-size: 15px !important;
                cursor: pointer !important;
                transition: all 0.3s !important;
                box-shadow: 0 4px 12px rgba(218, 4, 128, 0.3) !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
            }
            
            #proxy-place-order-button:hover {
                background: linear-gradient(135deg, #b00368 0%, #8a0252 100%) !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 16px rgba(218, 4, 128, 0.5) !important;
            }
            
            #proxy-place-order-button:disabled {
                opacity: 0.6 !important;
                cursor: not-allowed !important;
                transform: none !important;
            }
            
            #proxy-return-to-cart-button {
                width: 100% !important;
                text-align: center !important;
                padding: 12px 20px !important;
                background: rgba(218, 4, 128, 0.1) !important;
                border: 2px solid rgba(218, 4, 128, 0.3) !important;
                border-radius: 10px !important;
                color: #da0480 !important;
                font-weight: 600 !important;
                transition: all 0.3s !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                cursor: pointer !important;
                text-decoration: none !important;
            }
            
            #proxy-return-to-cart-button:hover {
                background: rgba(218, 4, 128, 0.2) !important;
                border-color: rgba(218, 4, 128, 0.5) !important;
            }
        }
        
        /* ========================================
           TÍTULOS
        ======================================== */
        
        .woocommerce h1,
        .woocommerce h2,
        .woocommerce h3 {
            color: #da0480 !important;
            font-weight: 800 !important;
        }
        
        .woocommerce .entry-title {
            font-size: 32px;
            color: #da0480;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 800;
        }
        
        /* ========================================
           CAMBIAR TEXTOS CON CSS
        ======================================== */
        
        .wc-block-checkout__contact-fields .wc-block-components-checkout-step__title::after {
            content: 'Información de envío de curso';
            font-size: 22px;
            font-weight: 800;
            color: #da0480;
        }
        
        .wc-block-checkout__contact-fields .wc-block-components-checkout-step__title {
            font-size: 0 !important;
            line-height: 22px !important;
        }
        
        /* ========================================
           TABLAS (CARRITO)
        ======================================== */
        
        .woocommerce table.cart {
            border: 1px solid rgba(218, 4, 128, 0.3);
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e;
            box-shadow: 0 4px 20px rgba(218, 4, 128, 0.1);
        }
        
        .woocommerce table.cart thead {
            background: rgba(218, 4, 128, 0.05);
            border-bottom: 2px solid rgba(218, 4, 128, 0.2);
        }
        
        .woocommerce table.cart thead th {
            color: #da0480 !important;
            font-weight: 800;
            padding: 18px 15px;
            border-bottom: 1px solid rgba(218, 4, 128, 0.3);
        }
        
        .woocommerce table.cart td {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(218, 4, 128, 0.15);
            color: #e2e8f0;
        }
        
        .woocommerce table.cart tbody tr:hover {
            background: rgba(218, 4, 128, 0.05);
        }
        
        .woocommerce table.cart .product-thumbnail img {
            border-radius: 12px;
            border: 2px solid rgba(218, 4, 128, 0.4);
        }
        
        .woocommerce table.cart .product-name a {
            color: #fff;
            font-weight: 700;
            transition: color 0.3s;
        }
        
        .woocommerce table.cart .product-name a:hover {
            color: #da0480;
        }
        
        .woocommerce table.cart .product-price,
        .woocommerce table.cart .product-subtotal {
            color: #da0480 !important;
            font-weight: 700;
        }
        
        /* ========================================
           BOTONES
        ======================================== */
        
        .woocommerce button.button,
        .woocommerce input.button,
        .woocommerce a.button,
        .woocommerce #respond input#submit,
        .woocommerce .checkout-button,
        .wc-block-components-button,
        .wp-element-button {
            background: linear-gradient(135deg, #da0480 0%, #b00368 100%) !important;
            color: #fff !important;
            border: none !important;
            padding: 14px 28px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            cursor: pointer !important;
            transition: all 0.3s !important;
            box-shadow: 0 4px 12px rgba(218, 4, 128, 0.3) !important;
            text-shadow: none !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .woocommerce button.button:hover,
        .woocommerce input.button:hover,
        .woocommerce a.button:hover,
        .woocommerce .checkout-button:hover,
        .wc-block-components-button:hover,
        .wp-element-button:hover {
            background: linear-gradient(135deg, #b00368 0%, #8a0252 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(218, 4, 128, 0.5) !important;
        }
        
        .woocommerce button.button[name="update_cart"] {
            background: rgba(218, 4, 128, 0.1) !important;
            color: #da0480 !important;
            border: 2px solid rgba(218, 4, 128, 0.3) !important;
        }
        
        .woocommerce button.button[name="update_cart"]:hover {
            background: rgba(218, 4, 128, 0.2) !important;
            border-color: rgba(218, 4, 128, 0.5) !important;
        }
        
        /* ========================================
           TOTALES DEL CARRITO
        ======================================== */
        
        .woocommerce .cart-collaterals .cart_totals {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e;
            border: 1px solid rgba(218, 4, 128, 0.3);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(218, 4, 128, 0.1);
        }
        
        .woocommerce .cart-collaterals .cart_totals h2 {
            color: #da0480 !important;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(218, 4, 128, 0.2);
        }
        
        .woocommerce .cart-collaterals .cart_totals th,
        .woocommerce .cart-collaterals .cart_totals td {
            padding: 12px 0;
            border-bottom: 1px solid rgba(218, 4, 128, 0.15);
            color: #e2e8f0;
        }
        
        .woocommerce .cart-collaterals .cart_totals .order-total th,
        .woocommerce .cart-collaterals .cart_totals .order-total td {
            color: #da0480 !important;
            font-weight: 800;
            font-size: 22px;
            border-bottom: none;
            padding-top: 20px;
        }
        
        /* ========================================
           CAMPOS DE FORMULARIO
        ======================================== */
        
        .wc-block-components-text-input,
        .wc-block-components-combobox {
            position: relative !important;
            margin-bottom: 6px !important;
        }
        
        .wc-block-checkout__billing-fields .wc-block-components-text-input {
            margin-bottom: 6px !important;
        }
        
        .wc-block-components-address-form__first_name,
        .wc-block-components-address-form__last_name,
        .wc-block-components-address-form__phone {
            margin-bottom: 6px !important;
        }
        
        .wc-block-components-text-input label,
        .wc-block-components-combobox label {
            position: relative !important;
            display: block !important;
            color: #e2e8f0 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            margin-bottom: 10px !important;
            pointer-events: none !important;
            background: transparent !important;
            padding: 0 0 0 2px !important;
            z-index: 1 !important;
            order: -1 !important;
        }
        
        .wc-block-components-text-input,
        .wc-block-components-address-form__email {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .wc-block-components-text-input input,
        .wc-block-components-combobox input,
        .wc-block-components-textarea textarea {
            border: 2px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 10px !important;
            padding: 14px 15px !important;
            transition: all 0.3s !important;
            background: rgba(26, 38, 64, 0.8) !important;
            color: #fff !important;
            font-weight: 500 !important;
            font-size: 15px !important;
            width: 100% !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
            position: relative !important;
            z-index: 2 !important;
        }
        
        .wc-block-components-text-input input::placeholder,
        .wc-block-components-textarea textarea::placeholder {
            color: #6b7280 !important;
        }
        
        .wc-block-components-text-input input:focus,
        .wc-block-components-combobox input:focus,
        .wc-block-components-textarea textarea:focus {
            border-color: #da0480 !important;
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(218, 4, 128, 0.1), 0 4px 12px rgba(218, 4, 128, 0.2) !important;
            background: rgba(26, 38, 64, 1) !important;
        }
        
        .wc-block-components-text-input.email-error input {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1) !important;
        }
        
        /* ========================================
           MENSAJES
        ======================================== */
        
        .woocommerce-message,
        .woocommerce-info,
        .woocommerce-error,
        .wc-block-components-notice-banner {
            background: rgba(218, 4, 128, 0.1) !important;
            color: #e2e8f0 !important;
            border-left: 4px solid #da0480 !important;
            border-radius: 8px;
            padding: 15px 20px;
        }
        
        #custom-gmail-notice {
            background: rgba(218, 4, 128, 0.1);
            border-left: 4px solid #da0480;
            padding: 15px 20px;
            border-radius: 10px;
            color: #e2e8f0;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
            box-shadow: 0 2px 8px rgba(218, 4, 128, 0.15);
        }
        
        #custom-gmail-notice strong {
            color: #da0480;
        }
        
        .email-error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        
        /* ========================================
           CANTIDADES
        ======================================== */
        
        .woocommerce .quantity input.qty,
        .wc-block-components-quantity-selector input {
            border: 2px solid rgba(218, 4, 128, 0.3);
            border-radius: 8px;
            padding: 8px 12px;
            text-align: center;
            background: rgba(26, 38, 64, 0.8);
            color: #fff;
        }
        
        /* ========================================
           BLOQUES WOOCOMMERCE CHECKOUT
        ======================================== */
        
        .wp-block-woocommerce-checkout {
            background: transparent !important;
        }
        
        .wp-block-woocommerce-checkout-fields-block,
        .wc-block-checkout__shipping-fields,
        .wc-block-checkout__billing-fields,
        .wc-block-checkout__contact-fields {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e !important;
            border: 1px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 16px !important;
            padding: 35px !important;
            box-shadow: 0 4px 20px rgba(218, 4, 128, 0.1) !important;
            margin-bottom: 20px !important;
        }
        
        .wp-block-woocommerce-checkout-order-summary-block {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e !important;
            border: 1px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 16px !important;
            padding: 35px !important;
            box-shadow: 0 6px 25px rgba(218, 4, 128, 0.15) !important;
            position: sticky !important;
            top: 80px !important;
            z-index: 10 !important;
            max-height: calc(100vh - 100px) !important;
            overflow-y: auto !important;
            scroll-behavior: smooth;
            transition: top 0.3s ease !important;
        }
        
        .wp-block-woocommerce-checkout-order-summary-block::-webkit-scrollbar {
            width: 6px;
        }
        
        .wp-block-woocommerce-checkout-order-summary-block::-webkit-scrollbar-track {
            background: rgba(218, 4, 128, 0.05);
            border-radius: 10px;
        }
        
        .wp-block-woocommerce-checkout-order-summary-block::-webkit-scrollbar-thumb {
            background: rgba(218, 4, 128, 0.3);
            border-radius: 10px;
        }
        
        .wp-block-woocommerce-checkout-order-summary-block::-webkit-scrollbar-thumb:hover {
            background: rgba(218, 4, 128, 0.5);
        }
        
        .wp-block-woocommerce-checkout-order-summary-totals-block {
            background: rgba(218, 4, 128, 0.05) !important;
            border: 1px solid rgba(218, 4, 128, 0.2) !important;
            border-radius: 12px !important;
            padding: 20px !important;
            margin-top: 20px !important;
        }
        
        .wc-block-components-order-summary-item {
            border-bottom: 1px solid rgba(218, 4, 128, 0.15) !important;
            padding: 15px 0 !important;
        }
        
        .wc-block-components-order-summary-item:last-child {
            border-bottom: none !important;
        }
        
        .wp-block-woocommerce-checkout-payment-block {
            background: linear-gradient(135deg, rgba(218, 4, 128, 0.1) 0%, rgba(26, 31, 46, 0.95) 100%), #1a1f2e !important;
            border: 1px solid rgba(218, 4, 128, 0.3) !important;
            border-radius: 16px !important;
            padding: 35px !important;
            box-shadow: 0 4px 20px rgba(218, 4, 128, 0.1) !important;
            margin-top: 20px !important;
        }
        
        .wp-block-woocommerce-checkout-order-summary-coupon-form-block {
            background: rgba(218, 4, 128, 0.05) !important;
            border: 1px solid rgba(218, 4, 128, 0.2) !important;
            border-radius: 12px !important;
            padding: 20px !important;
            margin-bottom: 20px !important;
        }
        
        .wp-block-woocommerce-checkout-fields-block h2,
        .wp-block-woocommerce-checkout-order-summary-block h2,
        .wp-block-woocommerce-checkout-payment-block h2,
        .wc-block-components-checkout-step__title {
            color: #da0480 !important;
            font-size: 22px !important;
            font-weight: 800 !important;
            margin-bottom: 25px !important;
            padding-bottom: 15px !important;
            border-bottom: 2px solid rgba(218, 4, 128, 0.2) !important;
        }
        
        .wc-block-components-order-summary-item__name,
        .wc-block-components-product-name {
            color: #fff !important;
            font-weight: 700 !important;
        }
        
        .wc-block-components-totals-item__value,
        .wc-block-components-order-summary-item__total-price {
            color: #da0480 !important;
            font-weight: 800 !important;
        }
        
        .wc-block-components-totals-item__label {
            color: #e2e8f0 !important;
        }
        
        .wc-block-components-totals-footer-item .wc-block-components-totals-item__value {
            color: #da0480 !important;
            font-size: 24px !important;
            font-weight: 800 !important;
        }
        
        /* ========================================
           RESPONSIVE
        ======================================== */
        
        @media (max-width: 768px) {
            .wc-block-checkout__terms:not(.moved-to-summary),
            .wc-block-checkout__actions:not(.moved-to-summary),
            .wc-block-checkout__actions_row:not(.moved-to-summary) {
                display: block !important;
            }
            
            #moved-checkout-actions {
                display: none !important;
            }
            
            .wc-block-checkout__terms:not(.moved-to-summary) {
                margin: 20px 0 !important;
                padding: 15px !important;
                background: rgba(218, 4, 128, 0.05) !important;
                border-radius: 10px !important;
                border: 1px solid rgba(218, 4, 128, 0.2) !important;
            }
            
            .wc-block-checkout__terms:not(.moved-to-summary) .wc-block-components-checkbox__label {
                font-size: 13px !important;
                line-height: 1.6 !important;
                color: #e2e8f0 !important;
                display: block !important;
            }
            
            .wc-block-checkout__actions_row:not(.moved-to-summary) {
                display: flex !important;
                flex-direction: column-reverse !important;
                gap: 12px !important;
                margin-top: 20px !important;
            }
            
            .wc-block-checkout__actions_row:not(.moved-to-summary) .wc-block-components-checkout-return-to-cart-button {
                width: 100% !important;
                text-align: center !important;
                padding: 14px 20px !important;
                background: rgba(218, 4, 128, 0.1) !important;
                border: 2px solid rgba(218, 4, 128, 0.3) !important;
                border-radius: 10px !important;
                color: #da0480 !important;
                font-weight: 700 !important;
                font-size: 15px !important;
                transition: all 0.3s !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                box-shadow: 0 2px 8px rgba(218, 4, 128, 0.15) !important;
            }
            
            .wc-block-checkout__actions_row:not(.moved-to-summary) .wc-block-components-checkout-return-to-cart-button:hover,
            .wc-block-checkout__actions_row:not(.moved-to-summary) .wc-block-components-checkout-return-to-cart-button:active {
                background: rgba(218, 4, 128, 0.2) !important;
                border-color: rgba(218, 4, 128, 0.5) !important;
                transform: translateY(-1px) !important;
            }
            
            .wc-block-checkout__actions_row:not(.moved-to-summary) .wc-block-components-checkout-place-order-button {
                width: 100% !important;
                padding: 16px 28px !important;
                font-size: 16px !important;
                font-weight: 700 !important;
                justify-content: center !important;
            }
            
            .woocommerce-checkout #lqd-site-content {
                padding-top: 120px !important;
            }
            
            .woocommerce {
                padding: 20px 15px;
            }
            
            .woocommerce .entry-title {
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .woocommerce table.cart td,
            .woocommerce table.cart th {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .woocommerce button.button,
            .woocommerce input.button,
            .woocommerce a.button,
            .wc-block-components-button {
                padding: 12px 20px !important;
                font-size: 14px !important;
                width: 100%;
                margin-bottom: 10px;
                justify-content: center;
            }
            
            .wp-block-woocommerce-checkout-fields-block,
            .wp-block-woocommerce-checkout-order-summary-block,
            .wp-block-woocommerce-checkout-payment-block {
                padding: 25px 20px !important;
            }
            
            .wp-block-woocommerce-checkout-order-summary-block {
                position: relative !important;
                top: auto !important;
                max-height: none !important;
                margin-bottom: 20px !important;
            }
        }
    </style>
    <?php
}
