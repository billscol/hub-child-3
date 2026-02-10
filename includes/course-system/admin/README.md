# Admin - Panel de Administración de Cursos

## Metaboxes

### course-info.php
Información del curso:
- Duración total
- Número de lecciones
- Número de módulos
- Nivel recomendado
- Idioma
- Requisitos previos

### instructor-info.php
Información del instructor:
- Nombre completo
- Biografía
- Avatar/Foto
- Enlaces sociales (LinkedIn, Twitter, etc.)
- Website personal

### course-settings.php
Configuraciones del curso:
- Habilitar certificado al completar
- Modo de progreso (lineal o libre)
- Duración de acceso (ilimitado o X días)
- Restricciones de tiempo por lección
- Requiere aprobación para acceder

## Columnas Admin

### admin-columns.php

Columnas personalizadas en la lista de cursos:
- Miniatura
- Categoría
- Nivel
- Número de estudiantes
- Progreso promedio
- Estado de publicación

## Uso

```php
// Añadir un nuevo metabox
add_action('add_meta_boxes', 'my_custom_course_metabox');

function my_custom_course_metabox() {
    add_meta_box(
        'my_course_box',
        'Mi Metabox',
        'render_my_course_box',
        'course',
        'side',
        'default'
    );
}
```
