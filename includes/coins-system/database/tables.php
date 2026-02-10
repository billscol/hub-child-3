<?php
/**
 * Creación y gestión de tablas de base de datos
 * Sistema de Coins
 * 
 * @package Hub_Child_Theme
 * @subpackage Coins_System
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crear tablas necesarias para el sistema de coins
 */
function crear_tablas_coins() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tabla de historial de coins
    $tabla_historial = $wpdb->prefix . 'coins_historial';
    $sql1 = "CREATE TABLE IF NOT EXISTS $tabla_historial (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        tipo varchar(20) NOT NULL,
        cantidad decimal(10,2) NOT NULL,
        saldo_anterior decimal(10,2) NOT NULL,
        saldo_nuevo decimal(10,2) NOT NULL,
        descripcion text,
        order_id bigint(20),
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY fecha (fecha)
    ) $charset_collate;";
    
    // Tabla de recompensas por reseñas
    $tabla_reviews = $wpdb->prefix . 'coins_reviews_rewarded';
    $sql2 = "CREATE TABLE IF NOT EXISTS $tabla_reviews (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        comment_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        coins_otorgados decimal(10,2) NOT NULL,
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_comment (comment_id),
        KEY user_id (user_id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    // Tabla de recompensas por compartir
    $tabla_shares = $wpdb->prefix . 'coins_social_shares';
    $sql3 = "CREATE TABLE IF NOT EXISTS $tabla_shares (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        platform varchar(20) NOT NULL,
        coins_otorgados decimal(10,2) NOT NULL,
        fecha datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY product_id (product_id),
        KEY fecha (fecha)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}

/**
 * Verificar si las tablas existen
 */
function coins_tables_exist() {
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'coins_historial',
        $wpdb->prefix . 'coins_reviews_rewarded',
        $wpdb->prefix . 'coins_social_shares'
    ];
    
    foreach ($tables as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return false;
        }
    }
    
    return true;
}

/**
 * Eliminar tablas (usar con precaución)
 */
function coins_drop_tables() {
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'coins_historial',
        $wpdb->prefix . 'coins_reviews_rewarded',
        $wpdb->prefix . 'coins_social_shares'
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}
?>