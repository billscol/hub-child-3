<?php
/**
 * Index del shortcode [course_curriculum]
 * Carga todos los archivos necesarios
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos del shortcode
require_once __DIR__ . '/backend-metabox.php';
require_once __DIR__ . '/frontend-display.php';
require_once __DIR__ . '/shortcode.php';
?>