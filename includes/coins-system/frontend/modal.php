<?php
/**
 * Modal de Coins Insuficientes
 * Muestra un modal cuando el usuario no tiene suficientes coins
 * 
 * @package CoinsSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar modal de coins insuficientes al footer
 */
function coins_add_insufficient_modal() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $user_coins = $coins_manager->get_coins(get_current_user_id());
    
    ?>
    <!-- Modal de Coins Insuficientes -->
    <div id="coins-modal-overlay" class="coins-modal-overlay" style="display: none;">
        <div id="coins-modal" class="coins-modal">
            <button id="coins-modal-close" class="coins-modal-close">&times;</button>
            
            <div class="coins-modal-header">
                <div class="coins-modal-icon">
                    🪙
                </div>
                <h2 id="coins-modal-title">No tienes suficientes coins</h2>
            </div>
            
            <p id="coins-modal-mensaje" class="coins-modal-text">
                No tienes suficientes coins para canjear este curso.
            </p>
            
            <div class="coins-modal-summary">
                <p>
                    <span>Coins necesarios:</span>
                    <strong id="coins-modal-necesarios">0</strong>
                </p>
                <p>
                    <span>Tus coins:</span>
                    <strong id="coins-modal-usuario"><?php echo $coins_manager->format_coins($user_coins); ?></strong>
                </p>
                <p style="color: #ef4444;">
                    <span>Te faltan:</span>
                    <strong id="coins-modal-faltantes">0</strong>
                </p>
            </div>
            
            <div class="coins-modal-actions">
                <a href="<?php echo esc_url(site_url('/gana-coins')); ?>" class="coins-modal-btn primary">
                    ✨ Cómo ganar coins
                </a>
            </div>
            
            <p class="coins-modal-footnote">
                💡 Ganas 1 coin por cada curso premium que compras y 1 coin por cada reseña verificada.
            </p>
        </div>
    </div>
    
    <style>
    /* Overlay de fondo */
    .coins-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(4px);
    }
    
    .coins-modal-overlay.active {
        display: flex !important;
    }
    
    /* Caja del modal */
    .coins-modal {
        background: #0b1020;
        border-radius: 16px;
        max-width: 500px;
        width: 100%;
        padding: 30px 25px;
        position: relative;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        color: #f5f5f7;
        border: 2px solid rgba(218, 4, 128, 0.4);
        animation: coinsModalSlideIn 0.3s ease-out;
    }
    
    @keyframes coinsModalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .coins-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        border: none;
        background: transparent;
        font-size: 28px;
        cursor: pointer;
        color: #f5f5f7;
        line-height: 1;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        transition: all 0.2s;
    }
    
    .coins-modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(90deg);
    }
    
    .coins-modal-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .coins-modal-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #ffffff, #f5c6e1 40%, #7a0345 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }
    
    .coins-modal-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.3;
    }
    
    .coins-modal-text {
        margin: 0 0 20px 0;
        font-size: 15px;
        color: #ced2e0;
        line-height: 1.6;
    }
    
    .coins-modal-summary {
        background: rgba(9, 14, 35, 0.9);
        border-radius: 12px;
        padding: 15px 18px;
        border: 1px solid rgba(196, 200, 206, 0.25);
        margin-bottom: 20px;
    }
    
    .coins-modal-summary p {
        margin: 8px 0;
        display: flex;
        justify-content: space-between;
        font-size: 15px;
    }
    
    .coins-modal-summary span {
        color: #C4C8CE;
    }
    
    .coins-modal-summary strong {
        color: #ffffff;
        font-weight: 700;
    }
    
    .coins-modal-actions {
        text-align: center;
        margin-bottom: 15px;
    }
    
    .coins-modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 28px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    
    .coins-modal-btn.primary {
        background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
        color: #ffffff;
        box-shadow: 0 6px 20px rgba(218, 4, 128, 0.5);
    }
    
    .coins-modal-btn.primary:hover {
        background: linear-gradient(135deg, #b00368 0%, #8a0252 100%);
        box-shadow: 0 8px 28px rgba(218, 4, 128, 0.7);
        transform: translateY(-2px);
    }
    
    .coins-modal-footnote {
        text-align: center;
        font-size: 13px;
        color: #9ca3af;
        margin: 15px 0 0 0;
        padding-top: 15px;
        border-top: 1px solid rgba(196, 200, 206, 0.15);
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        .coins-modal {
            padding: 25px 20px;
        }
        
        .coins-modal-header h2 {
            font-size: 18px;
        }
        
        .coins-modal-icon {
            width: 50px;
            height: 50px;
            font-size: 26px;
        }
    }
    
    /* Prevent body scroll when modal is open */
    body.coins-modal-open {
        overflow: hidden;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var modal = $('#coins-modal-overlay');
        var modalClose = $('#coins-modal-close');
        
        // Función para abrir modal
        window.coinsShowInsufficientModal = function(coinsNeeded, userCoins) {
            var faltantes = coinsNeeded - userCoins;
            
            $('#coins-modal-necesarios').text(coinsNeeded.toLocaleString('es-ES'));
            $('#coins-modal-usuario').text(userCoins.toLocaleString('es-ES'));
            $('#coins-modal-faltantes').text(faltantes.toLocaleString('es-ES'));
            
            modal.addClass('active');
            $('body').addClass('coins-modal-open');
        };
        
        // Cerrar modal al hacer click en X
        modalClose.on('click', function() {
            modal.removeClass('active');
            $('body').removeClass('coins-modal-open');
        });
        
        // Cerrar modal al hacer click fuera
        modal.on('click', function(e) {
            if (e.target === this) {
                $(this).removeClass('active');
                $('body').removeClass('coins-modal-open');
            }
        });
        
        // Cerrar con tecla ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && modal.hasClass('active')) {
                modal.removeClass('active');
                $('body').removeClass('coins-modal-open');
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'coins_add_insufficient_modal');
?>