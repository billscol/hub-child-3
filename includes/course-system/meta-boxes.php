<?php
/**
 * Course System - Meta Boxes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Metabox de autor (radio button)
function author_radio_meta_box($post, $box) {
    $terms = get_terms(array(
        'taxonomy' => 'course_author',
        'hide_empty' => false,
    ));
    
    $current_terms = wp_get_object_terms($post->ID, 'course_author', array('fields' => 'ids'));
    $current_id = !empty($current_terms) ? $current_terms[0] : 0;
    
    if (empty($terms) || is_wp_error($terms)) {
        echo '<p>No hay autores. <a href="' . admin_url('edit-tags.php?taxonomy=course_author&post_type=product') . '" target="_blank">Crear autor</a></p>';
        return;
    }
    
    echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">';
    
    echo '<label style="display: block; margin-bottom: 8px;">';
    echo '<input type="radio" name="course_author_single" value="0" ' . checked($current_id, 0, false) . '> ';
    echo '<em>-- Sin autor --</em>';
    echo '</label>';
    
    foreach ($terms as $term) {
        $checked = ($term->term_id == $current_id) ? 'checked="checked"' : '';
        
        echo '<label style="display: block; margin-bottom: 8px; cursor: pointer;">';
        echo '<input type="radio" name="course_author_single" value="' . $term->term_id . '" ' . $checked . '> ';
        echo '<strong>' . esc_html($term->name) . '</strong>';
        echo '</label>';
    }
    
    echo '</div>';
    echo '<p style="margin-top: 10px;"><a href="' . admin_url('edit-tags.php?taxonomy=course_author&post_type=product') . '" target="_blank">+ Agregar nuevo autor</a></p>';
    
    wp_nonce_field('save_course_author_meta', 'course_author_nonce');
}

add_action('save_post_product', 'save_course_author_selection', 10, 2);
function save_course_author_selection($post_id, $post) {
    if (!isset($_POST['course_author_nonce']) || !wp_verify_nonce($_POST['course_author_nonce'], 'save_course_author_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['course_author_single'])) {
        $term_id = intval($_POST['course_author_single']);
        
        if ($term_id > 0) {
            wp_set_object_terms($post_id, array($term_id), 'course_author', false);
            delete_transient('author_courses_count_' . $term_id);
        } else {
            wp_set_object_terms($post_id, array(), 'course_author', false);
        }
    }
}

// Metabox fecha de actualización
add_action('add_meta_boxes', 'add_update_date_metabox');
function add_update_date_metabox() {
    add_meta_box(
        'product_update_date',
        '🔄 Fecha de Actualización',
        'render_update_date_metabox',
        'product',
        'side',
        'default'
    );
}

function render_update_date_metabox($post) {
    wp_nonce_field('save_update_date', 'update_date_nonce');
    
    $custom_date = get_post_meta($post->ID, '_product_update_date', true);
    $custom_text = get_post_meta($post->ID, '_product_update_text', true);
    
    if (empty($custom_text)) {
        $custom_text = 'Actualizado';
    }
    ?>
    
    <div style="margin-bottom:15px;">
        <label><strong>Fecha:</strong></label><br>
        <input type="date" name="product_update_date" value="<?php echo esc_attr($custom_date); ?>" style="width:100%;padding:8px;margin-top:5px;">
        <small style="color:#666;display:block;margin-top:5px;">Vacío = última modificación</small>
    </div>
    
    <div style="margin-bottom:15px;">
        <label><strong>Texto:</strong></label><br>
        <input type="text" name="product_update_text" value="<?php echo esc_attr($custom_text); ?>" style="width:100%;padding:8px;margin-top:5px;" placeholder="Actualizado">
    </div>
    
    <?php
}

add_action('save_post_product', 'save_update_date_data');
function save_update_date_data($post_id) {
    if (!isset($_POST['update_date_nonce']) || !wp_verify_nonce($_POST['update_date_nonce'], 'save_update_date')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['product_update_date'])) {
        update_post_meta($post_id, '_product_update_date', sanitize_text_field($_POST['product_update_date']));
    }
    
    if (isset($_POST['product_update_text'])) {
        update_post_meta($post_id, '_product_update_text', sanitize_text_field($_POST['product_update_text']));
    }
}
