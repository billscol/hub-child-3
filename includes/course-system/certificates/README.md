# Certificates - Sistema de Certificados

## certificate-generator.php

Generador de certificados de finalización:

```php
// Generar certificado
$certificate_url = generate_course_certificate($course_id, $user_id);

// Verificar si tiene certificado
if (user_has_certificate($course_id, $user_id)) {
    echo '<a href="' . $certificate_url . '">Descargar Certificado</a>';
}

// Verificar autenticidad
if (verify_certificate($certificate_code)) {
    echo 'Certificado válido';
}
```

## Templates

### default-certificate.php

Template HTML para el certificado con:
- Logo de la plataforma
- Nombre del estudiante
- Nombre del curso
- Fecha de finalización
- Código de verificación
- Firma digital

## Generación de PDF

Usa librería recomendada:
- TCPDF
- mPDF
- Dompdf

## Personalización

Para personalizar el diseño:

```php
add_filter('course_certificate_template', function($template, $course_id) {
    return get_stylesheet_directory() . '/certificate-templates/mi-template.php';
}, 10, 2);
```

## Almacenamiento

Certificados generados se guardan en:
- `/wp-content/uploads/certificates/`
- Nombre: `certificate-{user_id}-{course_id}-{timestamp}.pdf`
