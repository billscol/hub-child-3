<?php
/**
 * Integración de campos personalizados de cursos en Dokan
 * Este archivo añade los campos que ya funcionan en wp-admin al dashboard del vendedor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// CAMPO: Autor del curso (taxonomía course_author)
// ============================================

/**
 * Mostrar selector de autores en Dokan
 */
function cb_dokan_add_author_field( $post = null, $post_id = 0 ) {
    
    // Determinar product_id
    if ( ! $post_id && $post instanceof WP_Post ) {
        $product_id = $post->ID;
    } else {
        $product_id = $post_id;
    }
    
    $selected_authors = $product_id ? wp_get_post_terms( $product_id, 'course_author', array( 'fields' => 'ids' ) ) : array();
    
    $authors = get_terms( array(
        'taxonomy'   => 'course_author',
        'hide_empty' => false,
        'orderby'    => 'name',
    ) );
    
    if ( empty( $authors ) || is_wp_error( $authors ) ) {
        echo '<div class="dokan-form-group">';
        echo '<label class="form-label">' . esc_html__( 'Autor del curso', 'cursobarato' ) . '</label>';
        echo '<p class="help-block">' . esc_html__( 'No hay autores disponibles. Contacta al administrador.', 'cursobarato' ) . '</p>';
        echo '</div>';
        return;
    }
    ?>
    
    <div class="dokan-form-group">
        <label class="form-label"><?php esc_html_e( 'Autor del curso', 'cursobarato' ); ?></label>
        <select name="course_author[]" class="dokan-form-control" multiple style="height: auto; min-height: 80px;">
            <?php foreach ( $authors as $author ) : ?>
                <option value="<?php echo esc_attr( $author->term_id ); ?>" <?php echo in_array( $author->term_id, $selected_authors ) ? 'selected' : ''; ?>>
                    <?php echo esc_html( $author->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="help-block"><?php esc_html_e( 'Selecciona el autor o creador del curso. Mantén Ctrl/Cmd para seleccionar varios.', 'cursobarato' ); ?></p>
    </div>
    
    <?php
}
add_action( 'dokan_product_edit_after_product_tags', 'cb_dokan_add_author_field', 10, 2 );
add_action( 'dokan_new_product_after_product_tags', 'cb_dokan_add_author_field', 10, 2 );

/**
 * Guardar autores seleccionados
 */
function cb_dokan_save_author_field( $product_id ) {
    
    if ( ! isset( $_POST['course_author'] ) ) {
        return;
    }
    
    $author_ids = array_map( 'intval', (array) $_POST['course_author'] );
    $author_ids = array_filter( $author_ids );
    
    if ( ! empty( $author_ids ) ) {
        wp_set_object_terms( $product_id, $author_ids, 'course_author' );
    } else {
        wp_set_object_terms( $product_id, array(), 'course_author' );
    }
}
add_action( 'dokan_new_product_added', 'cb_dokan_save_author_field', 10, 1 );
add_action( 'dokan_product_updated', 'cb_dokan_save_author_field', 10, 1 );


// ============================================
// CAMPO: Currículum del curso (módulos y lecciones)
// ============================================

/**
 * Mostrar campo de currículum simplificado
 */
function cb_dokan_add_curriculum_field( $post = null, $post_id = 0 ) {
    
    // Determinar product_id
    if ( ! $post_id && $post instanceof WP_Post ) {
        $product_id = $post->ID;
    } else {
        $product_id = $post_id;
    }
    
    $curriculum = $product_id ? get_post_meta( $product_id, '_course_curriculum', true ) : array();
    
    // Convertir array a texto simple
    $lines = array();
    if ( ! empty( $curriculum ) && is_array( $curriculum ) ) {
        foreach ( $curriculum as $module ) {
            $module_name = isset( $module['name'] ) ? $module['name'] : '';
            $lessons     = isset( $module['lessons'] ) ? (array) $module['lessons'] : array();
            
            $line = $module_name;
            if ( ! empty( $lessons ) ) {
                $line .= ' : ' . implode( ' | ', $lessons );
            }
            $lines[] = $line;
        }
    }
    
    $value = implode( "\n", $lines );
    ?>
    
    <div class="dokan-form-group">
        <label class="form-label"><?php esc_html_e( 'Currículum del curso', 'cursobarato' ); ?></label>
        <textarea 
            name="course_curriculum_text" 
            class="dokan-form-control" 
            rows="6"
            placeholder="Ejemplo:
Módulo 1: Introducción | Primeros pasos
Módulo 2: Estrategias avanzadas | Casos prácticos"
        ><?php echo esc_textarea( $value ); ?></textarea>
        <p class="help-block">
            <?php esc_html_e( 'Un módulo por línea. Formato: Nombre del módulo : lección 1 | lección 2 | lección 3', 'cursobarato' ); ?>
        </p>
    </div>
    
    <?php
}
add_action( 'dokan_product_edit_after_product_tags', 'cb_dokan_add_curriculum_field', 15, 2 );
add_action( 'dokan_new_product_after_product_tags', 'cb_dokan_add_curriculum_field', 15, 2 );

/**
 * Guardar currículum
 */
function cb_dokan_save_curriculum_field( $product_id ) {
    
    if ( ! isset( $_POST['course_curriculum_text'] ) ) {
        return;
    }
    
    $text  = wp_unslash( $_POST['course_curriculum_text'] );
    $lines = preg_split( '/\r\n|\r|\n/', $text );
    
    $curriculum = array();
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( '' === $line ) {
            continue;
        }
        
        $parts       = explode( ':', $line, 2 );
        $module_name = trim( $parts[0] );
        $lessons     = array();
        
        if ( isset( $parts[1] ) && '' !== trim( $parts[1] ) ) {
            $lessons_raw = explode( '|', $parts[1] );
            foreach ( $lessons_raw as $lesson ) {
                $lesson = trim( $lesson );
                if ( '' !== $lesson ) {
                    $lessons[] = sanitize_text_field( $lesson );
                }
            }
        }
        
        if ( '' !== $module_name ) {
            $curriculum[] = array(
                'name'    => sanitize_text_field( $module_name ),
                'lessons' => $lessons,
                'locked'  => false,
            );
        }
    }
    
    if ( ! empty( $curriculum ) ) {
        update_post_meta( $product_id, '_course_curriculum', $curriculum );
    } else {
        delete_post_meta( $product_id, '_course_curriculum' );
    }
}
add_action( 'dokan_new_product_added', 'cb_dokan_save_curriculum_field', 10, 1 );
add_action( 'dokan_product_updated', 'cb_dokan_save_curriculum_field', 10, 1 );


// ============================================
// CAMPO: Fecha de actualización personalizada
// ============================================

/**
 * Mostrar campos de fecha de actualización
 */
function cb_dokan_add_update_date_field( $post = null, $post_id = 0 ) {
    
    // Determinar product_id
    if ( ! $post_id && $post instanceof WP_Post ) {
        $product_id = $post->ID;
    } else {
        $product_id = $post_id;
    }
    
    $update_date = $product_id ? get_post_meta( $product_id, '_product_update_date', true ) : '';
    $update_text = $product_id ? get_post_meta( $product_id, '_product_update_text', true ) : 'Actualizado';
    ?>
    
    <div class="dokan-form-group">
        <label class="form-label"><?php esc_html_e( 'Fecha de actualización del curso', 'cursobarato' ); ?></label>
        <input 
            type="date" 
            name="product_update_date" 
            class="dokan-form-control" 
            value="<?php echo esc_attr( $update_date ); ?>"
        >
        <p class="help-block"><?php esc_html_e( 'Fecha que se mostrará como "última actualización" en el curso.', 'cursobarato' ); ?></p>
    </div>
    
    <div class="dokan-form-group">
        <label class="form-label"><?php esc_html_e( 'Texto de actualización', 'cursobarato' ); ?></label>
        <input 
            type="text" 
            name="product_update_text" 
            class="dokan-form-control" 
            value="<?php echo esc_attr( $update_text ); ?>"
            placeholder="Actualizado"
        >
        <p class="help-block"><?php esc_html_e( 'Texto que acompaña la fecha (ej: "Actualizado", "Última revisión").', 'cursobarato' ); ?></p>
    </div>
    
    <?php
}
add_action( 'dokan_product_edit_after_product_tags', 'cb_dokan_add_update_date_field', 20, 2 );
add_action( 'dokan_new_product_after_product_tags', 'cb_dokan_add_update_date_field', 20, 2 );

/**
 * Guardar fecha de actualización
 */
function cb_dokan_save_update_date_field( $product_id ) {
    
    if ( isset( $_POST['product_update_date'] ) ) {
        $date = sanitize_text_field( $_POST['product_update_date'] );
        update_post_meta( $product_id, '_product_update_date', $date );
    }
    
    if ( isset( $_POST['product_update_text'] ) ) {
        $text = sanitize_text_field( $_POST['product_update_text'] );
        update_post_meta( $product_id, '_product_update_text', $text );
    }
}
add_action( 'dokan_new_product_added', 'cb_dokan_save_update_date_field', 10, 1 );
add_action( 'dokan_product_updated', 'cb_dokan_save_update_date_field', 10, 1 );
