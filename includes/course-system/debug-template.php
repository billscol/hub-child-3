<?php
/**
 * Debug: verificar carga de template
 */

add_action('init', function() {
    if (is_admin()) {
        return;
    }
    
    // Verificar constantes
    if (defined('COURSE_SYSTEM_PATH')) {
        error_log('✓ COURSE_SYSTEM_PATH definido: ' . COURSE_SYSTEM_PATH);
    } else {
        error_log('✗ COURSE_SYSTEM_PATH NO está definido');
    }
    
    // Verificar archivo template
    $template_file = COURSE_SYSTEM_PATH . 'templates/archive-author.php';
    if (file_exists($template_file)) {
        error_log('✓ Template existe: ' . $template_file);
    } else {
        error_log('✗ Template NO existe: ' . $template_file);
    }
});

add_filter('template_include', function($template) {
    if (is_tax('course_author')) {
        error_log('🎯 Estamos en página de autor');
        error_log('Template original: ' . $template);
        
        $custom_template = COURSE_SYSTEM_PATH . 'templates/archive-author.php';
        
        if (file_exists($custom_template)) {
            error_log('✓ Usando template personalizado: ' . $custom_template);
            return $custom_template;
        } else {
            error_log('✗ Template personalizado NO encontrado');
        }
    }
    return $template;
}, 999);
