<?php
/**
 * Recompensas por compartir en redes sociales (estructura básica)
 * TODO: implementar lógica real cuando definas flujos de compartir
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función placeholder para otorgar coins por compartir
 * 
 * @param int $user_id
 * @param int $product_id
 * @param string $platform (facebook, twitter, whatsapp, etc.)
 * @param float $coins (por defecto 0.5)
 */
function coins_otorgar_por_compartir($user_id, $product_id, $platform = 'otros', $coins = 0.5) {
    if (!$user_id || !$product_id || $coins <= 0) {
        return false;
    }
    
    global $wpdb;
    $tabla = $wpdb->prefix . 'coins_social_shares';
    
    // Registrar en historial de coins
    $descripcion = sprintf('Coins por compartir curso ID %d en %s', $product_id, $platform);
    $ok = coins_manager()->agregar_coins($user_id, $coins, $descripcion, null);
    
    if ($ok) {
        // Registrar detalle en tabla de shares
        $wpdb->insert(
            $tabla,
            array(
                'user_id'        => $user_id,
                'product_id'     => $product_id,
                'platform'       => $platform,
                'coins_otorgados'=> $coins,
                'fecha'          => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%f', '%s')
        );
    }
    
    return $ok;
}
