<?php
/**
 * Index del shortcode [sp_auth]
 * Carga todos los archivos necesarios
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Cargar archivos del shortcode
require_once __DIR__ . '/shortcode.php';
require_once __DIR__ . '/modal.php';
require_once __DIR__ . '/styles.php';
require_once __DIR__ . '/scripts.php';
?>