<?php
/**
 * Template Name: Publicar Curso
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( ! is_user_logged_in() ) {
    echo '<div class="cb-course-publish-wrap"><p>Debes iniciar sesión para publicar un curso.</p></div>';
    get_footer();
    return;
}

$current_user_id = get_current_user_id();

// Comprobar si el usuario es vendedor Dokan
if ( function_exists( 'dokan_is_user_seller' ) && ! dokan_is_user_seller( $current_user_id ) ) {
    echo '<div class="cb-course-publish-wrap"><p>Tu cuenta aún no está habilitada como vendedor.</p></div>';
    get_footer();
    return;
}

/**
 * Helper: construir currículum desde el formulario
 */
if ( ! function_exists( 'cb_build_curriculum_from_post' ) ) {
    function cb_build_curriculum_from_post() {
        if ( empty( $_POST['curriculum'] ) || ! is_array( $_POST['curriculum'] ) ) {
            return array();
        }

        $curriculum = array();

        foreach ( $_POST['curriculum'] as $module ) {
            $name         = isset( $module['name'] ) ? trim( wp_unslash( $module['name'] ) ) : '';
            $lessons_text = isset( $module['lessons_text'] ) ? wp_unslash( $module['lessons_text'] ) : '';

            if ( '' === $name ) {
                continue;
            }

            $lessons = array();
            if ( $lessons_text ) {
                $lines = preg_split( '/\r\n|\r|\n/', $lessons_text );
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( '' !== $line ) {
                        $lessons[] = sanitize_text_field( $line );
                    }
                }
            }

            $curriculum[] = array(
                'name'    => sanitize_text_field( $name ),
                'lessons' => $lessons,
                'locked'  => false,
            );
        }

        return $curriculum;
    }
}

// Procesar envío del formulario
$errors = array();

if (
    'POST' === $_SERVER['REQUEST_METHOD']
    && isset( $_POST['cb_publish_course_nonce'] )
    && wp_verify_nonce( $_POST['cb_publish_course_nonce'], 'cb_publish_course' )
) {

    $title          = sanitize_text_field( $_POST['course_title'] ?? '' );
    $price          = floatval( $_POST['course_price'] ?? 0 );
    $sale_price     = isset( $_POST['course_sale_price'] ) ? floatval( $_POST['course_sale_price'] ) : 0;
    $excerpt        = sanitize_textarea_field( $_POST['course_excerpt'] ?? '' );
    $content        = wp_kses_post( $_POST['course_description'] ?? '' );
    $author_term    = intval( $_POST['course_author'] ?? 0 );
    $update_date    = sanitize_text_field( $_POST['product_update_date'] ?? '' );
    $video_url      = sanitize_text_field( $_POST['course_video_url'] ?? '' );
    $cats_selected  = isset( $_POST['product_cats'] ) ? array_map( 'intval', (array) $_POST['product_cats'] ) : array();

    $curriculum = cb_build_curriculum_from_post();

    if ( ! $title ) {
        $errors[] = 'El título del curso es obligatorio.';
    }
    if ( $price <= 0 ) {
        $errors[] = 'El precio debe ser mayor a 0.';
    }
    if ( ! $excerpt ) {
        $errors[] = 'La descripción corta es obligatoria.';
    }
    if ( ! $content ) {
        $errors[] = 'La descripción completa es obligatoria.';
    }
    if ( empty( $curriculum ) ) {
        $errors[] = 'El currículum del curso es obligatorio.';
    }
    if ( ! $author_term && empty( $_POST['new_course_author_name'] ) ) {
        $errors[] = 'Debes seleccionar un autor del curso o crear uno nuevo.';
    }
    if ( empty( $_FILES['course_cover']['name'] ) ) {
        $errors[] = 'Debes subir la foto de portada del curso (imagen del producto).';
    }

    // Crear nuevo autor si se envió
    if ( empty( $errors ) && ! empty( $_POST['new_course_author_name'] ) ) {
        $new_author_name  = trim( wp_unslash( $_POST['new_course_author_name'] ) );
        $new_author_image = sanitize_text_field( $_POST['new_author_image_url'] ?? '' );

        if ( $new_author_name !== '' ) {
            $term = wp_insert_term( $new_author_name, 'course_author' ); // [web:171][web:174]
            if ( ! is_wp_error( $term ) && ! empty( $term['term_id'] ) ) {
                $author_term = (int) $term['term_id'];
                if ( ! empty( $new_author_image ) ) {
                    update_term_meta( $author_term, '_course_author_avatar_url', esc_url_raw( $new_author_image ) );
                }
            } else {
                $errors[] = 'No se pudo crear el nuevo autor. Intenta con otro nombre.';
            }
        }
    }

    // Crear nueva categoría de producto si se envió
    if ( empty( $errors ) && ! empty( $_POST['new_product_cat_name'] ) ) {
        $new_cat_name = trim( wp_unslash( $_POST['new_product_cat_name'] ) );
        if ( $new_cat_name !== '' ) {
            $cat_term = wp_insert_term( $new_cat_name, 'product_cat' ); // [web:171][web:174]
            if ( ! is_wp_error( $cat_term ) && ! empty( $cat_term['term_id'] ) ) {
                $new_cat_id      = (int) $cat_term['term_id'];
                $cats_selected[] = $new_cat_id;
            } else {
                $errors[] = 'No se pudo crear la nueva categoría. Intenta con otro nombre.';
            }
        }
    }

    if ( empty( $errors ) ) {

        // Crear producto pendiente con el vendedor actual como autor (Dokan) [web:65][web:145]
        $product_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status'  => 'pending',
            'post_type'    => 'product',
            'post_author'  => $current_user_id,
        ) );

        if ( $product_id && ! is_wp_error( $product_id ) ) {

            // Tipo de producto WooCommerce
            wp_set_object_terms( $product_id, 'simple', 'product_type' );

            // Categorías WooCommerce
            if ( ! empty( $cats_selected ) ) {
                wp_set_object_terms( $product_id, $cats_selected, 'product_cat' );
            }

            // Precio normal y oferta
            update_post_meta( $product_id, '_regular_price', $price );

            if ( $sale_price > 0 && $sale_price < $price ) {
                update_post_meta( $product_id, '_sale_price', $sale_price );
                update_post_meta( $product_id, '_price', $sale_price );
            } else {
                delete_post_meta( $product_id, '_sale_price' );
                update_post_meta( $product_id, '_price', $price );
            }

            // Curso digital
            update_post_meta( $product_id, '_virtual', 'yes' );

            // Autor de curso
            if ( $author_term ) {
                wp_set_object_terms( $product_id, array( $author_term ), 'course_author' );
            }

            // Currículum
            update_post_meta( $product_id, '_course_curriculum', $curriculum );

            // Fecha de actualización
            update_post_meta( $product_id, '_product_update_date', $update_date );

            // Video del producto
            if ( ! empty( $video_url ) ) {
                update_post_meta( $product_id, '_video_url_producto', esc_url_raw( $video_url ) );
            }

            // Imagen destacada (portada)
            if ( ! empty( $_FILES['course_cover']['name'] ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attachment_id = media_handle_upload( 'course_cover', $product_id ); // [web:177]

                if ( ! is_wp_error( $attachment_id ) ) {
                    set_post_thumbnail( $product_id, $attachment_id ); // [web:152][web:177]
                }
            }

            // Redirigir a listado de productos del vendor (desde ahí Dokan muestra el aviso) [web:65][web:145]
            if ( function_exists( 'dokan_get_navigation_url' ) ) {
                wp_safe_redirect( dokan_get_navigation_url( 'products' ) );
            } else {
                wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
            }
            exit;
        } else {
            $errors[] = 'No se pudo crear el curso. Inténtalo de nuevo.';
        }
    }
}

// Media uploader
wp_enqueue_media();

?>

<div class="cb-page-bg">
    <div class="cb-course-publish-wrap">

        <div class="cb-course-card">
            <div class="cb-course-header">
                <div class="cb-header-content">
                    <h1>Publicar nuevo curso</h1>
                    <p>Completa la información de tu curso para enviarlo a revisión.</p>
                    <p class="cb-required-note">Los campos marcados con * son obligatorios.</p>
                </div>
            </div>

            <?php if ( ! empty( $errors ) ) : ?>
                <div class="cb-course-alert">
                    <strong>Revisa los siguientes errores:</strong>
                    <ul>
                        <?php foreach ( $errors as $err ) : ?>
                            <li><?php echo esc_html( $err ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="cb-course-publish-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'cb_publish_course', 'cb_publish_course_nonce' ); ?>

                <!-- Información Básica -->
                <div class="cb-form-section">
                    <h2 class="cb-section-heading">Información básica</h2>

                    <div class="cb-form-row">
                        <div class="cb-form-group">
                            <label class="cb-label">Título del curso *</label>
                            <input type="text" name="course_title" required class="cb-input"
                                   placeholder="Ej: Instagram Marketing Mastery">
                        </div>
                    </div>

                    <div class="cb-form-row cb-two-cols">
                        <div class="cb-form-group">
                            <label class="cb-label">Precio normal *</label>
                            <input type="number" name="course_price" min="0" step="0.01" required class="cb-input"
                                   placeholder="99.00">
                            <small class="cb-hint">Precio original sin descuento.</small>
                        </div>

                        <div class="cb-form-group">
                            <label class="cb-label">Precio oferta</label>
                            <input type="number" name="course_sale_price" min="0" step="0.01" class="cb-input"
                                   placeholder="19.00">
                            <small class="cb-hint">Precio especial en CursosBarato.</small>
                        </div>
                    </div>

                    <div class="cb-form-row">
                        <div class="cb-form-group">
                            <label class="cb-label">Foto de portada del curso (imagen del producto) *</label>
                            <input type="file" name="course_cover" accept="image/*" class="cb-input-file">
                            <small class="cb-hint">
                                Sube la portada del curso. Se usará como imagen del producto. Recomendado JPG/PNG mínimo 1000×1000 px.
                            </small>
                        </div>
                    </div>

                    <div class="cb-form-row">
                        <div class="cb-form-group">
                            <label class="cb-label">Descripción corta *</label>
                            <textarea name="course_excerpt" rows="3" required class="cb-textarea"
                                      placeholder="Resumen breve que verá el alumno antes de comprar."></textarea>
                            <small class="cb-hint">Máximo 2–3 líneas.</small>
                        </div>
                    </div>

                    <div class="cb-form-row">
                        <div class="cb-form-group">
                            <label class="cb-label">Descripción completa *</label>
                            <textarea name="course_description" rows="8" required class="cb-textarea"
                                      placeholder="Describe en detalle lo que aprenderá el alumno, requisitos, público objetivo, etc."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Autor y Categorías -->
                <div class="cb-form-section">
                    <h2 class="cb-section-heading">Autor y categorías</h2>

                    <div class="cb-form-row cb-two-cols">
                        <div class="cb-form-group">
                            <label class="cb-label">Autor del curso *</label>
                            <div class="cb-radio-grid">
                                <?php
                                $authors = get_terms( array(
                                    'taxonomy'   => 'course_author',
                                    'hide_empty' => false,
                                    'orderby'    => 'name',
                                ) );
                                if ( ! empty( $authors ) && ! is_wp_error( $authors ) ) :
                                    foreach ( $authors as $author ) : ?>
                                        <label class="cb-radio-card">
                                            <input type="radio" name="course_author" value="<?php echo esc_attr( $author->term_id ); ?>">
                                            <span class="cb-radio-label"><?php echo esc_html( $author->name ); ?></span>
                                        </label>
                                    <?php endforeach;
                                else : ?>
                                    <p class="cb-empty-state">No hay autores. Crea uno nuevo abajo.</p>
                                <?php endif; ?>
                            </div>

                            <div class="cb-divider">o crea uno nuevo</div>

                            <div class="cb-new-author-box">
                                <label class="cb-label">Nombre del nuevo autor</label>
                                <input type="text" name="new_course_author_name" class="cb-input"
                                       placeholder="Nombre del nuevo autor">

                                <div class="cb-author-image-upload">
                                    <label class="cb-label">Foto de perfil del autor (Obligatorio)</label>
                                    <input type="hidden" id="new_author_image_url" name="new_author_image_url" value="">
                                    <button type="button" class="cb-btn-upload" id="upload-author-image">
                                        Subir foto del autor
                                    </button>
                                    <div id="author-image-preview" class="cb-image-preview"></div>
                                    <small class="cb-hint">Recomendado formato cuadrado, se mostrará como avatar.</small>
                                </div>
                            </div>
                        </div>

                        <div class="cb-form-group">
                            <label class="cb-label">Categorías del curso</label>
                            <div class="cb-checkbox-grid">
                                <?php
                                $cats = get_terms( array(
                                    'taxonomy'   => 'product_cat',
                                    'hide_empty' => false,
                                    'orderby'    => 'name',
                                ) );
                                if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) :
                                    foreach ( $cats as $cat ) : ?>
                                        <label class="cb-checkbox-card">
                                            <input type="checkbox" name="product_cats[]" value="<?php echo esc_attr( $cat->term_id ); ?>">
                                            <span class="cb-checkbox-label"><?php echo esc_html( $cat->name ); ?></span>
                                        </label>
                                    <?php endforeach;
                                else : ?>
                                    <p class="cb-empty-state">No hay categorías. Crea una nueva abajo.</p>
                                <?php endif; ?>
                            </div>

                            <p class="cb-help-strong">
                                Importante: marca siempre la categoría <strong>Premium</strong> para cursos de pago,
                                para que se cobre el precio configurado arriba. Además, selecciona al menos una
                                categoría temática que corresponda al contenido (por ejemplo: Marketing, Programación, Diseño, etc.).
                            </p>

                            <div class="cb-divider">o crea una nueva</div>

                            <label class="cb-label">Crear nueva categoría</label>
                            <input type="text" name="new_product_cat_name" class="cb-input"
                                   placeholder="Nombre de la nueva categoría (ej: Marketing, Programación)">
                        </div>
                    </div>
                </div>

                <!-- Currículum -->
                <div class="cb-form-section">
                    <h2 class="cb-section-heading">Currículum del curso</h2>
                    <p class="cb-section-desc">Organiza el contenido en módulos y lecciones.</p>

                    <div id="cb-curriculum-wrapper">
                        <div class="cb-module-card" data-index="0">
                            <div class="cb-module-header">
                                <h3 class="cb-module-title">Módulo 1</h3>
                                <button type="button" class="cb-btn-remove cb-remove-module" style="display:none;">Eliminar</button>
                            </div>

                            <div class="cb-form-group">
                                <label class="cb-label">Nombre del módulo *</label>
                                <input type="text" name="curriculum[0][name]" class="cb-input" required
                                       placeholder="Ej: Fundamentos de Instagram Marketing">
                            </div>

                            <div class="cb-form-group">
                                <label class="cb-label">Lecciones del módulo *</label>
                                <textarea name="curriculum[0][lessons_text]" class="cb-textarea" rows="4" required
                                          placeholder="Escribe una lección por línea."></textarea>
                                <small class="cb-hint">Una lección por línea.</small>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="cb-btn-add" id="cb-add-module">Agregar módulo</button>
                </div>

                <!-- Detalles Adicionales -->
                <div class="cb-form-section">
                    <h2 class="cb-section-heading">Detalles adicionales</h2>

                    <div class="cb-form-row cb-two-cols">
                        <div class="cb-form-group">
                            <label class="cb-label">Fecha de actualización</label>
                            <input type="date" name="product_update_date" class="cb-input">
                        </div>

                        <div class="cb-form-group">
                            <label class="cb-label">Video de presentación (URL)</label>
                            <input type="url" name="course_video_url" class="cb-input"
                                   placeholder="https://ejemplo.com/video.mp4">
                            <small class="cb-hint">URL directa del video MP4 (opcional).</small>
                        </div>
                    </div>
                </div>

                <div class="cb-form-actions">
                    <button type="submit" class="cb-btn-submit">Enviar curso para revisión</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Media Uploader para foto de autor
    const uploadBtn = document.getElementById('upload-author-image');
    const imageUrlInput = document.getElementById('new_author_image_url');
    const imagePreview = document.getElementById('author-image-preview');

    if (uploadBtn && typeof wp !== 'undefined' && wp.media) {
        let mediaUploader;

        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Selecciona foto del autor',
                button: { text: 'Usar esta imagen' },
                multiple: false,
                library: { type: 'image' }
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                imageUrlInput.value = attachment.url;
                imagePreview.innerHTML = '<img src="' + attachment.url + '" alt="Foto del autor"><button type="button" class="cb-remove-image">×</button>';

                const removeBtn = imagePreview.querySelector('.cb-remove-image');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        imageUrlInput.value = '';
                        imagePreview.innerHTML = '';
                    });
                }
            });

            mediaUploader.open();
        });
    }

    // Curriculum dinámico
    const wrapper = document.getElementById('cb-curriculum-wrapper');
    const addBtn  = document.getElementById('cb-add-module');

    if (wrapper && addBtn) {
        function renumberModules() {
            const modules = wrapper.querySelectorAll('.cb-module-card');
            modules.forEach(function(mod, index) {
                mod.dataset.index = index;
                const titleEl = mod.querySelector('.cb-module-title');
                if (titleEl) {
                    titleEl.textContent = 'Módulo ' + (index + 1);
                }

                const nameInput       = mod.querySelector('input[name^="curriculum"]');
                const lessonsTextarea = mod.querySelector('textarea[name^="curriculum"]');

                if (nameInput) {
                    nameInput.name = 'curriculum[' + index + '][name]';
                }
                if (lessonsTextarea) {
                    lessonsTextarea.name = 'curriculum[' + index + '][lessons_text]';
                }

                const removeBtn = mod.querySelector('.cb-remove-module');
                if (removeBtn) {
                    removeBtn.style.display = (modules.length > 1) ? 'inline-block' : 'none';
                }
            });
        }

        addBtn.addEventListener('click', function() {
            const modules  = wrapper.querySelectorAll('.cb-module-card');
            const newIndex = modules.length;

            const template = document.createElement('div');
            template.className = 'cb-module-card';
            template.dataset.index = newIndex;
            template.innerHTML = `
                <div class="cb-module-header">
                    <h3 class="cb-module-title">Módulo ${newIndex + 1}</h3>
                    <button type="button" class="cb-btn-remove cb-remove-module">Eliminar</button>
                </div>
                <div class="cb-form-group">
                    <label class="cb-label">Nombre del módulo *</label>
                    <input type="text" name="curriculum[${newIndex}][name]" class="cb-input" required
                           placeholder="Ej: Estrategias avanzadas">
                </div>
                <div class="cb-form-group">
                    <label class="cb-label">Lecciones del módulo *</label>
                    <textarea name="curriculum[${newIndex}][lessons_text]" class="cb-textarea" rows="4" required
                              placeholder="Escribe una lección por línea."></textarea>
                    <small class="cb-hint">Una lección por línea.</small>
                </div>
            `;

            wrapper.appendChild(template);
            renumberModules();
        });

        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('cb-remove-module')) {
                const modules = wrapper.querySelectorAll('.cb-module-card');
                if (modules.length <= 1) return;

                const item = e.target.closest('.cb-module-card');
                if (!item) return;

                item.remove();
                renumberModules();
            }
        });

        renumberModules();
    }
});
</script>

<style>
* {
    box-sizing: border-box;
}

.cb-page-bg {
    padding: 200px 20px 80px;
    background: #171718;
    min-height: 100vh;
}

.cb-course-publish-wrap {
    max-width: 1200px;
    margin: 0 auto;
}

.cb-course-card {
    background: #1f1f23;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

.cb-course-header {
    margin-bottom: 32px;
    text-align: center;
}

.cb-header-content h1 {
    font-size: 28px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 6px;
}

.cb-header-content p {
    font-size: 15px;
    color: #9ca3af;
    margin: 0;
}

.cb-required-note {
    margin-top: 8px;
    font-size: 13px;
    color: #a1a1aa;
}

.cb-course-alert {
    background: rgba(248,113,113,0.1);
    border: 2px solid #f87171;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    color: #fca5a5;
}

.cb-course-alert strong {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
}

.cb-course-alert ul {
    margin: 0;
    padding-left: 20px;
    font-size: 14px;
}

.cb-form-section {
    background: #27272b;
    border-radius: 16px;
    padding: 24px 24px 26px;
    margin-bottom: 20px;
}

.cb-section-heading {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 12px;
}

.cb-section-desc {
    color: #9ca3af;
    margin: 0 0 18px;
    font-size: 14px;
}

.cb-form-row {
    margin-bottom: 18px;
}

.cb-two-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.cb-form-group {
    margin-bottom: 16px;
}

.cb-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #e5e7eb;
    margin-bottom: 6px;
}

.cb-input,
.cb-textarea,
.cb-input-file {
    width: 100%;
    background: #1f1f23;
    border: 2px solid #3f3f46;
    border-radius: 10px;
    padding: 10px 14px;
    color: #ffffff;
    font-size: 14px;
    transition: all 0.2s;
}

.cb-input:focus,
.cb-textarea:focus {
    outline: none;
    border-color: #da0480;
    box-shadow: 0 0 0 3px rgba(218,4,128,0.1);
}

.cb-textarea {
    resize: vertical;
    min-height: 90px;
    font-family: inherit;
}

.cb-hint {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #71717a;
}

.cb-radio-grid,
.cb-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.cb-radio-card,
.cb-checkbox-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #1f1f23;
    border: 2px solid #3f3f46;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.cb-radio-card:hover,
.cb-checkbox-card:hover {
    border-color: #da0480;
}

.cb-radio-card input,
.cb-checkbox-card input {
    margin: 0;
    accent-color: #da0480;
}

.cb-radio-label,
.cb-checkbox-label {
    font-size: 13px;
    color: #e5e7eb;
    font-weight: 500;
}

.cb-divider {
    text-align: center;
    color: #71717a;
    font-size: 12px;
    margin: 16px 0;
    position: relative;
}

.cb-divider::before,
.cb-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: calc(50% - 60px);
    height: 1px;
    background: #3f3f46;
}

.cb-divider::before {
    left: 0;
}

.cb-divider::after {
    right: 0;
}

.cb-new-author-box {
    background: #1f1f23;
    border: 2px dashed #3f3f46;
    border-radius: 12px;
    padding: 16px;
    margin-top: 4px;
}

.cb-author-image-upload {
    margin-top: 12px;
}

.cb-btn-upload {
    padding: 8px 16px;
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: transform 0.2s;
}

.cb-btn-upload:hover {
    transform: translateY(-1px);
}

.cb-image-preview {
    margin-top: 10px;
    position: relative;
}

.cb-image-preview img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #3f3f46;
}

.cb-remove-image {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 22px;
    height: 22px;
    background: #f87171;
    color: #ffffff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
}

.cb-help-strong {
    font-size: 12px;
    color: #e5e7eb;
    margin-top: 6px;
}

.cb-module-card {
    background: #1f1f23;
    border: 2px solid #3f3f46;
    border-radius: 12px;
    padding: 18px 18px 20px;
    margin-bottom: 14px;
}

.cb-module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.cb-module-title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
}

.cb-btn-remove {
    padding: 7px 12px;
    background: transparent;
    border: 1px solid #71717a;
    color: #f87171;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}

.cb-btn-remove:hover {
    background: rgba(248,113,113,0.08);
    border-color: #f87171;
}

.cb-btn-add {
    width: 100%;
    padding: 12px;
    background: #27272b;
    border: 2px dashed #3f3f46;
    color: #9ca3af;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.cb-btn-add:hover {
    border-color: #da0480;
    color: #da0480;
}

.cb-form-actions {
    margin-top: 28px;
    text-align: center;
}

.cb-btn-submit {
    padding: 14px 40px;
    background: linear-gradient(135deg, #da0480, #b00368);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(218,4,128,0.3);
    transition: all 0.3s;
}

.cb-btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 36px rgba(218,4,128,0.4);
}

.cb-empty-state {
    color: #71717a;
    font-size: 13px;
    font-style: italic;
}

@media (max-width: 768px) {
    .cb-page-bg {
        padding: 160px 16px 60px;
    }

    .cb-course-card {
        padding: 24px;
    }

    .cb-header-content h1 {
        font-size: 22px;
    }

    .cb-two-cols {
        grid-template-columns: 1fr;
    }

    .cb-form-section {
        padding: 18px;
    }
}
</style>

<?php
get_footer();
