<?php
/**
 * Modal de Coins
 * Modal que aparece cuando el usuario no tiene suficientes coins
 * 
 * @package CoinsSystem
 * @subpackage Frontend
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar modal de coins al footer
 */
function coins_add_modal_to_footer() {
    if (!is_singular('product')) {
        return;
    }
    
    $coins_manager = Coins_Manager::get_instance();
    $user_coins = is_user_logged_in() ? coins_get_balance(get_current_user_id()) : 0;
    
    ?>
    <!-- Modal Coins -->
    <div id="coins-modal-overlay" style="display: none;">
        <div id="coins-modal">
            <button id="coins-modal-close" class="coins-modal-close">&times;</button>
            
            <div class="coins-modal-header">
                <div class="coins-modal-icon">
                    <img src="https://cursobarato.co/wp-content/uploads/2026/02/coin-1.png" alt="Coin">
                </div>
                <h2>No tienes suficientes coins</h2>
            </div>
            
            <p id="coins-modal-mensaje" class="coins-modal-text">
                No tienes suficientes coins para canjear este curso.
            </p>
            
            <div class="coins-modal-summary">
                <p><span>Coins necesarios:</span> <strong id="coins-modal-necesarios">0</strong></p>
                <p><span>Tus coins:</span> <strong id="coins-modal-usuario"><?php echo esc_html($coins_manager->format_coins($user_coins)); ?></strong></p>
            </div>
            
            <div id="coins-modal-login" class="coins-modal-actions" style="display: none;">
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="coins-modal-btn primary">
                    Iniciar sesión
                </a>
            </div>
            
            <div id="coins-modal-gana-coins" class="coins-modal-actions" style="display: none;">
                <a href="<?php echo esc_url(site_url('/gana-coins')); ?>" class="coins-modal-btn primary">
                    Cómo ganar coins
                </a>
            </div>
            
            <p class="coins-modal-footnote">
                Ganas 1 coin por cada curso premium que compras y 1 coin por cada reseña verificada.
            </p>
        </div>
    </div>
    
    <style>
    body.coins-modal-open {
        overflow: hidden;
    }
    
    /* Overlay de fondo */
    #coins-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.75);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    /* Caja del modal */
    #coins-modal {
        background: #0b1020;
        border-radius: 16px;
        max-width: 420px;
        width: 100%;
        padding: 24px 22px 20px;
        position: relative;
        box-shadow: 0 18px 45px rgba(0,0,0,0.6);
        color: #f5f5f7;
        border: 1px solid rgba(218, 4, 128, 0.35);
    }
    
    .coins-modal-close {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: transparent;
        font-size: 22px;
        cursor: pointer;
        color: #f5f5f7;
        line-height: 1;
        transition: all 0.2s;
    }
    
    .coins-modal-close:hover {
        color: #da0480;
        transform: scale(1.1);
    }
    
    .coins-modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }
    
    .coins-modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: radial-gradient(circle at 30% 30%, #ffffff, #f5c6e1 40%, #7a0345 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .coins-modal-icon img {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }
    
    .coins-modal-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.3;
    }
    
    .coins-modal-text {
        margin: 6px 0 16px;
        font-size: 14px;
        color: #ced2e0;
    }
    
    .coins-modal-summary {
        background: rgba(9, 14, 35, 0.9);
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid rgba(196, 200, 206, 0.25);
        margin-bottom: 16px;
        font-size: 14px;
    }
    
    .coins-modal-summary p {
        margin: 4px 0;
        display: flex;
        justify-content: space-between;
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
        margin-bottom: 12px;
    }
    
    .coins-modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 22px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Space Grotesk', Sans-serif;
    }
    
    .coins-modal-btn.primary {
        background: linear-gradient(135deg, #da0480 0%, #b00368 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(218, 4, 128, 0.4);
    }
    
    .coins-modal-btn.primary:hover {
        background: linear-gradient(135deg, #b00368 0%, #8a0252 100%);
        box-shadow: 0 6px 20px rgba(218, 4, 128, 0.6);
        transform: translateY(-1px);
    }
    
    .coins-modal-footnote {
        font-size: 12px;
        color: #9ca3af;
        text-align: center;
        margin: 12px 0 0 0;
        line-height: 1.5;
    }
    
    @media (max-width: 480px) {
        #coins-modal {
            padding: 20px 18px;
            max-width: 100%;
        }
        
        .coins-modal-header {
            flex-direction: column;
            text-align: center;
        }
        
        .coins-modal-header h2 {
            font-size: 18px;
        }
    }
    </style>
    
    <script>
    // Funciones del modal de coins
    function coinsShowModal(coinsNecesarios, userCoins, isLoggedIn) {
        const overlay = document.getElementById('coins-modal-overlay');
        const necesariosEl = document.getElementById('coins-modal-necesarios');
        const usuarioEl = document.getElementById('coins-modal-usuario');
        const loginDiv = document.getElementById('coins-modal-login');
        const ganaDiv = document.getElementById('coins-modal-gana-coins');
        
        if (necesariosEl) necesariosEl.textContent = coinsNecesarios;
        if (usuarioEl) usuarioEl.textContent = userCoins;
        
        if (isLoggedIn) {
            if (loginDiv) loginDiv.style.display = 'none';
            if (ganaDiv) ganaDiv.style.display = 'block';
        } else {
            if (loginDiv) loginDiv.style.display = 'block';
            if (ganaDiv) ganaDiv.style.display = 'none';
        }
        
        if (overlay) {
            overlay.style.display = 'flex';
            document.body.classList.add('coins-modal-open');
        }
    }
    
    function coinsCloseModal() {
        const overlay = document.getElementById('coins-modal-overlay');
        if (overlay) {
            overlay.style.display = 'none';
            document.body.classList.remove('coins-modal-open');
        }
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.getElementById('coins-modal-close');
        const overlay = document.getElementById('coins-modal-overlay');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', coinsCloseModal);
        }
        
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    coinsCloseModal();
                }
            });
        }
        
        // ESC para cerrar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                coinsCloseModal();
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'coins_add_modal_to_footer');
?>