<?php
/**
 * Course System - Taxonomía de Autores
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'register_course_author_taxonomy', 0);
function register_course_author_taxonomy() {
    $args = array(
        'labels' => array(
            'name' => 'Autores',
            'singular_name' => 'Autor',
            'menu_name' => 'Autores',
            'all_items' => 'Todos los Autores',
            'edit_item' => 'Editar Autor',
            'update_item' => 'Actualizar Autor',
            'add_new_item' => 'Agregar Nuevo Autor',
            'new_item_name' => 'Nombre del Nuevo Autor',
        ),
        'hierarchical' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'rewrite' => array('slug' => 'autor'),
        'show_in_quick_edit' => true,
        'meta_box_cb' => 'author_radio_meta_box',
    );
    register_taxonomy('course_author', array('product'), $args);
}

// Campos personalizados del autor
add_action('course_author_add_form_fields', 'add_author_fields');
function add_author_fields() {
    ?>
    <div class="form-field">
        <label>Foto del Autor</label>
        <input type="hidden" id="author_image" name="author_image" value="">
        <button type="button" class="button upload-author-image">Subir Imagen</button>
        <div class="author-image-preview" style="margin-top: 10px;"></div>
    </div>
    <div class="form-field">
        <label>Biografía</label>
        <textarea name="author_bio" rows="5" cols="40" placeholder="Escribe una breve biografía del autor..."></textarea>
        <p class="description">Aparecerá en el tooltip del badge.</p>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        $('.upload-author-image').on('click', function(e) {
            e.preventDefault();
            if (mediaUploader) { mediaUploader.open(); return; }
            mediaUploader = wp.media({ title: 'Elegir Foto del Autor', button: { text: 'Usar esta imagen' }, multiple: false });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#author_image').val(attachment.id);
                $('.author-image-preview').html('<img src="' + attachment.url + '" style="max-width: 150px; border-radius: 50%;">');
            });
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

add_action('course_author_edit_form_fields', 'edit_author_fields');
function edit_author_fields($term) {
    $image_id = get_term_meta($term->term_id, 'author_image', true);
    $bio = get_term_meta($term->term_id, 'author_bio', true);
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    ?>
    <tr class="form-field">
        <th><label>Foto del Autor</label></th>
        <td>
            <input type="hidden" id="author_image" name="author_image" value="<?php echo esc_attr($image_id); ?>">
            <button type="button" class="button upload-author-image">Subir Imagen</button>
            <div class="author-image-preview" style="margin-top: 10px;">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; border-radius: 50%;">
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <tr class="form-field">
        <th><label>Biografía</label></th>
        <td>
            <textarea name="author_bio" rows="5" cols="40"><?php echo esc_textarea($bio); ?></textarea>
            <p class="description">Aparecerá en el tooltip del badge.</p>
        </td>
    </tr>
    <script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        $('.upload-author-image').on('click', function(e) {
            e.preventDefault();
            if (mediaUploader) { mediaUploader.open(); return; }
            mediaUploader = wp.media({ title: 'Elegir Foto del Autor', button: { text: 'Usar esta imagen' }, multiple: false });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#author_image').val(attachment.id);
                $('.author-image-preview').html('<img src="' + attachment.url + '" style="max-width: 150px; border-radius: 50%;">');
            });
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

add_action('created_course_author', 'save_author_fields');
add_action('edited_course_author', 'save_author_fields');
function save_author_fields($term_id) {
    if (isset($_POST['author_image'])) {
        update_term_meta($term_id, 'author_image', sanitize_text_field($_POST['author_image']));
    }
    if (isset($_POST['author_bio'])) {
        update_term_meta($term_id, 'author_bio', sanitize_textarea_field($_POST['author_bio']));
    }
    delete_transient('author_courses_count_' . $term_id);
}

// Ocultar columna de cantidad en la administración de course_author
add_filter('manage_edit-course_author_columns', 'remove_course_author_posts_column', 10, 1);
function remove_course_author_posts_column($columns) {
    unset($columns['posts']);
    return $columns;
}

// También ocultar en el sortable columns
add_filter('manage_edit-course_author_sortable_columns', 'remove_course_author_sortable', 10, 1);
function remove_course_author_sortable($columns) {
    unset($columns['posts']);
    return $columns;
}


