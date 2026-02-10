<?php
/**
 * Creación de Tablas de Base de Datos
 * Tablas necesarias para el sistema de coins
 * 
 * @package CoinsSystem
 * @subpackage Database
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crear tablas del sistema de coins
 * Se ejecuta al activar el tema
 */
function coins_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // ============================================
    // TABLA 1: HISTORIAL DE COINS
    // ============================================
    $table_historial = $wpdb->prefix . 'coins_historial';
    
    $sql_historial = "CREATE TABLE IF NOT EXISTS $table_historial (
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
    
    // ============================================
    // TABLA 2: RECOMPENSAS POR RESEÑAS
    // ============================================
    $table_reviews = $wpdb->prefix . 'coins_reviews_rewarded';
    
    $sql_reviews = "CREATE TABLE IF NOT EXISTS $table_reviews (
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
    
    // ============================================
    // TABLA 3: RECOMPENSAS POR COMPARTIR
    // ============================================
    $table_shares = $wpdb->prefix . 'coins_social_shares';
    
    $sql_shares = "CREATE TABLE IF NOT EXISTS $table_shares (
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
    
    // Ejecutar las queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    dbDelta($sql_historial);
    dbDelta($sql_reviews);
    dbDelta($sql_shares);
    
    // Guardar versión de la base de datos
    update_option('coins_db_version', COINS_VERSION);
}

// Ejecutar al activar el tema
add_action('after_switch_theme', 'coins_create_database_tables');

/**
 * Verificar y actualizar tablas si es necesario
 */
function coins_check_database_version() {
    $installed_version = get_option('coins_db_version', '0');
    
    if (version_compare($installed_version, COINS_VERSION, '<')) {
        coins_create_database_tables();
    }
}
add_action('admin_init', 'coins_check_database_version');

/**
 * Función auxiliar: Obtener nombre de tabla
 */
function coins_get_table_name($table_type) {
    global $wpdb;
    
    $tables = array(
        'historial' => $wpdb->prefix . 'coins_historial',
        'reviews' => $wpdb->prefix . 'coins_reviews_rewarded',
        'shares' => $wpdb->prefix . 'coins_social_shares'
    );
    
    return isset($tables[$table_type]) ? $tables[$table_type] : null;
}
?>