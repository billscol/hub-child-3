<?php
/**
 * Index del shortcode [resenas_producto]
 * Carga todos los archivos necesarios
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos del shortcode
require_once __DIR__ . '/process-review.php';
require_once __DIR__ . '/shortcode.php';
?>