<?php
/**
 * Course System - Administración
 */

if (!defined('ABSPATH')) {
    exit;
}

// Columna de autores en admin
add_filter('manage_edit-product_columns', 'add_author_column_to_products', 15);
function add_author_column_to_products($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key == 'product_cat') {
            $new_columns['course_author'] = 'Autor';
        }
    }
    return $new_columns;
}

add_action('manage_product_posts_custom_column', 'fill_author_column_in_products', 10, 2);
function fill_author_column_in_products($column, $post_id) {
    if ($column == 'course_author') {
        $terms = get_the_terms($post_id, 'course_author');
        if ($terms && !is_wp_error($terms)) {
            $author_links = array();
            foreach ($terms as $term) {
                $author_links[] = '<a href="' . admin_url('edit.php?post_type=product&course_author=' . $term->slug) . '">' . esc_html($term->name) . '</a>';
            }
            echo implode(', ', $author_links);
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}
