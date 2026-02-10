<?php
/**
 * Index del shortcode [boton_reporte]
 * Carga todos los archivos necesarios
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos del shortcode
require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/admin-columns.php';
require_once __DIR__ . '/metabox.php';
require_once __DIR__ . '/email-notification.php';
require_once __DIR__ . '/shortcode.php';
?>