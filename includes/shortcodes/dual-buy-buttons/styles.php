<?php
/**
 * Estilos CSS para el shortcode [dual_buy_buttons]
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function dual_buy_buttons_add_styles() {
    ?>
    <style>
        .dual-buy-buttons-wrapper {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
        }
        
        .buy-now-button,
        .view-cart-button {
            flex: 1;
            min-width: 200px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Botón Comprar Ahora - Rosa principal */
        .buy-now-button {
            background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
            color: #fff;
        }
        
        .buy-now-button:hover {
            background: linear-gradient(135deg, #ff1fa6 0%, #da0480 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(218, 4, 128, 0.4);
        }
        
        .buy-now-button:active {
            transform: translateY(-1px);
        }
        
        /* Botón Ver Carrito - Outline */
        .view-cart-button {
            background: rgba(218, 4, 128, 0.1);
            color: #da0480;
            border: 2px solid rgba(218, 4, 128, 0.4);
        }
        
        .view-cart-button:hover {
            background: rgba(218, 4, 128, 0.15);
            border-color: #da0480;
            color: #da0480;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(218, 4, 128, 0.25);
        }
        
        /* Iconos */
        .button-icon {
            flex-shrink: 0;
        }
        
        /* Estado cargando */
        .buy-now-button.loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .buy-now-button.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-left: 10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: button-spin 0.6s linear infinite;
        }
        
        @keyframes button-spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dual-buy-buttons-wrapper {
                flex-direction: column;
                gap: 12px;
            }
            
            .buy-now-button,
            .view-cart-button {
                width: 100%;
                min-width: auto;
                padding: 14px 24px;
                font-size: 15px;
            }
        }
        
        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dual-buy-buttons-wrapper {
            animation: fadeInUp 0.5s ease-out;
        }
    </style>
    <?php
}
add_action('wp_head', 'dual_buy_buttons_add_styles', 999);
?>