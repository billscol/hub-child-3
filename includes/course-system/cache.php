<?php
/**
 * Course System - Gestión de Caché
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener contador de cursos con caché
function get_author_courses_count_cached($term_id) {
    $cache_key = 'author_courses_count_' . $term_id;
    $count = get_transient($cache_key);
    
    if (false === $count) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'course_author',
                    'field' => 'term_id',
                    'terms' => $term_id,
                ),
            ),
        );
        
        $query = new WP_Query($args);
        $count = $query->found_posts;
        
        set_transient($cache_key, $count, 12 * HOUR_IN_SECONDS);
    }
    
    return $count;
}

// Limpiar caché de calificación cuando se aprueba/actualiza una reseña
add_action('comment_post', 'clear_product_rating_cache', 10, 2);
add_action('edit_comment', 'clear_product_rating_cache_by_id');
add_action('wp_set_comment_status', 'clear_product_rating_cache_by_id');

function clear_product_rating_cache($comment_id, $comment_approved = null) {
    $comment = get_comment($comment_id);
    if ($comment && $comment->comment_type === 'review') {
        delete_transient('product_rating_' . $comment->comment_post_ID);
    }
}

function clear_product_rating_cache_by_id($comment_id) {
    $comment = get_comment($comment_id);
    if ($comment && $comment->comment_type === 'review') {
        delete_transient('product_rating_' . $comment->comment_post_ID);
    }
}

// Limpiar cachés cuando cambia el estado del producto
add_action('transition_post_status', 'clear_all_caches_on_product_change', 10, 3);
function clear_all_caches_on_product_change($new_status, $old_status, $post) {
    if ($post->post_type === 'product') {
        $terms = get_the_terms($post->ID, 'course_author');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                delete_transient('author_courses_count_' . $term->term_id);
            }
        }
        delete_transient('product_rating_' . $post->ID);
    }
}

// Limpieza al desactivar
register_deactivation_hook(__FILE__, 'clear_all_course_system_caches');
function clear_all_course_system_caches() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_author_courses_count_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_author_courses_count_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_product_rating_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_product_rating_%'");
}
