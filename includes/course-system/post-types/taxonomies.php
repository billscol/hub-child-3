<?php
/**
 * Taxonomías para Cursos
 * 
 * @package Hub_Child_Theme
 * @subpackage Course_System
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar taxonomías para cursos
 */
function register_course_taxonomies() {
    // Categorías de cursos
    register_taxonomy(
        'course_category',
        'course',
        array(
            'label'             => 'Categorías de Cursos',
            'rewrite'           => array('slug' => 'categoria-curso'),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        )
    );
    
    // Etiquetas de cursos
    register_taxonomy(
        'course_tag',
        'course',
        array(
            'label'             => 'Etiquetas',
            'rewrite'           => array('slug' => 'etiqueta-curso'),
            'hierarchical'      => false,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        )
    );
    
    // Nivel del curso
    register_taxonomy(
        'course_level',
        'course',
        array(
            'label'             => 'Nivel',
            'rewrite'           => array('slug' => 'nivel'),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        )
    );
    
    // Crear niveles por defecto
    create_default_course_levels();
}

/**
 * Crear niveles de curso por defecto
 */
function create_default_course_levels() {
    $levels = array(
        'principiante' => 'Principiante',
        'intermedio'   => 'Intermedio',
        'avanzado'     => 'Avanzado',
        'experto'      => 'Experto'
    );
    
    foreach ($levels as $slug => $name) {
        if (!term_exists($slug, 'course_level')) {
            wp_insert_term($name, 'course_level', array('slug' => $slug));
        }
    }
}
?>