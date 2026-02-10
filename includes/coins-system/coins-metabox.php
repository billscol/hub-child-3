<?php
/**
 * Metabox para definir costo en coins por producto
 * y coins que gana el usuario en cursos premium
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar metabox
 */
add_action('add_meta_boxes', 'coins_agregar_metabox_producto');

function coins_agregar_metabox_producto() {
    add_meta_box(
        'coins_producto_metabox',
        'Coins del producto',
        'coins_producto_metabox_html',
        'product',
        'side',
        'default'
    );
}

/**
 * Contenido del metabox
 */
function coins_producto_metabox_html($post) {
    // Nonce para seguridad
    wp_nonce_field('coins_producto_metabox', 'coins_producto_metabox_nonce');
    
    $product_id = $post->ID;

    // Costo en coins (para cursos gratis)
    $costo = get_post_meta($product_id, '_coins_cost', true);
    $costo = $costo !== '' ? $costo : 1;

    // Coins que gana el usuario (para cursos premium)
    $coins_ganados = get_post_meta($product_id, '_coins_ganados', true);
    // No ponemos default aquí, el default real será 2 en el helper.
    $coins_ganados = $coins_ganados !== '' ? intval($coins_ganados) : '';

    // Verificar si es gratis o premium
    $es_gratis  = has_term('gratis', 'product_cat', $product_id);
    $es_premium = has_term('premium', 'product_cat', $product_id);
    ?>
    <p>
        <label for="coins_cost"><strong>Costo en coins (para cursos gratis)</strong></label><br>
        <input type="number" name="coins_cost" id="coins_cost" value="<?php echo esc_attr($costo); ?>" min="1" step="1" style="width:100%;" />
    </p>
    <p class="description">
        Define cuántos coins se necesitan para canjear este curso cuando está en la categoría <code>gratis</code>.<br>
        Por defecto: 1 coin.
    </p>

    <hr>

    <p>
        <label for="coins_ganados"><strong>Coins que gana el usuario (cursos premium)</strong></label><br>
        <input type="number" name="coins_ganados" id="coins_ganados" value="<?php echo esc_attr($coins_ganados); ?>" min="0" step="1" style="width:100%;" />
    </p>
    <p class="description">
        Cuántos coins ganará el usuario al comprar este curso cuando está en la categoría <code>premium</code>.<br>
        Si se deja vacío o en 0, el sistema usará <strong>2 coins por defecto</strong>.
    </p>

    <p>
        <strong>Estado:</strong><br>
        <?php if ($es_gratis): ?>
            Este producto está en la categoría <code>gratis</code> y se puede canjear con coins.
        <?php elseif ($es_premium): ?>
            Este producto está en la categoría <code>premium</code> y otorga coins al comprarse.
        <?php else: ?>
            Este producto no es ni <code>premium</code> ni <code>gratis</code>. 
            El costo en coins aplica para <code>gratis</code> y los coins ganados para <code>premium</code>.
        <?php endif; ?>
    </p>
    <?php
}

/**
 * Guardar metabox
 */
add_action('save_post_product', 'coins_guardar_metabox_producto');

function coins_guardar_metabox_producto($post_id) {
    // Verificar nonce
    if (!isset($_POST['coins_producto_metabox_nonce']) || !wp_verify_nonce($_POST['coins_producto_metabox_nonce'], 'coins_producto_metabox')) {
        return;
    }
    
    // Verificar autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Guardar costo en coins (cursos gratis)
    if (isset($_POST['coins_cost'])) {
        $costo = intval($_POST['coins_cost']);
        if ($costo < 1) {
            $costo = 1;
        }
        update_post_meta($post_id, '_coins_cost', $costo);
    }

    // Guardar coins que gana el usuario (cursos premium)
    if (isset($_POST['coins_ganados'])) {
        $coins_ganados = intval($_POST['coins_ganados']);
        if ($coins_ganados < 0) {
            $coins_ganados = 0;
        }
        update_post_meta($post_id, '_coins_ganados', $coins_ganados);
    }
}
