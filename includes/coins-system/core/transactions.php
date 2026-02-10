<?php
/**
 * Gestión de Historial de Transacciones
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtener historial de transacciones de un usuario
 */
function get_user_coins_transactions($user_id, $limit = 20) {
    return coins_manager()->get_transaction_history($user_id, $limit);
}

/**
 * Shortcode para mostrar historial de transacciones
 * Uso: [historial_coins]
 */
function shortcode_historial_coins($atts) {
    if (!is_user_logged_in()) {
        return '<p>Inicia sesión para ver tu historial de coins.</p>';
    }
    
    $user_id = get_current_user_id();
    $transactions = get_user_coins_transactions($user_id, 50);
    
    if (empty($transactions)) {
        return '<p>Aún no tienes transacciones de coins.</p>';
    }
    
    ob_start();
    ?>
    <div class="coins-transaction-history">
        <h3>📊 Historial de Coins</h3>
        
        <div class="transactions-table">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="transaction-<?php echo esc_attr($transaction->tipo); ?>">
                            <td><?php echo date('d/m/Y H:i', strtotime($transaction->fecha)); ?></td>
                            <td>
                                <?php if ($transaction->tipo === 'ganado'): ?>
                                    <span class="badge badge-success">➕ Ganado</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">➖ Gastado</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($transaction->descripcion); ?></td>
                            <td class="amount">
                                <?php if ($transaction->tipo === 'ganado'): ?>
                                    <span class="positive">+<?php echo number_format($transaction->cantidad, 0); ?></span>
                                <?php else: ?>
                                    <span class="negative">-<?php echo number_format($transaction->cantidad, 0); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($transaction->saldo_nuevo, 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .coins-transaction-history {
            margin: 30px 0;
        }
        
        .coins-transaction-history h3 {
            color: #da0480;
            margin-bottom: 20px;
        }
        
        .transactions-table {
            overflow-x: auto;
        }
        
        .transactions-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .transactions-table th {
            background: linear-gradient(135deg, #da0480, #b00368);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .transactions-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .transactions-table tr:hover {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .amount .positive {
            color: #28a745;
            font-weight: 700;
        }
        
        .amount .negative {
            color: #dc3545;
            font-weight: 700;
        }
        
        @media (max-width: 768px) {
            .transactions-table table {
                font-size: 14px;
            }
            
            .transactions-table th,
            .transactions-table td {
                padding: 8px;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('historial_coins', 'shortcode_historial_coins');
?>