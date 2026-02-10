# Frontend - Visualización de Cursos

## template-loader.php

Cargador de templates personalizados para cursos.

### Templates Disponibles

```
templates/
├── single-course.php       # Página individual del curso
├── archive-course.php      # Listado de cursos
├── lesson-player.php       # Reproductor de lección
└── parts/
    ├── course-header.php   # Cabecera del curso
    ├── course-sidebar.php  # Barra lateral con currículum
    └── course-footer.php   # Pie del curso
```

## course-display.php

Funciones para mostrar información del curso:

```php
// Mostrar currículum
display_course_curriculum($course_id);

// Mostrar info del instructor
display_instructor_info($course_id);

// Mostrar progreso
display_course_progress($course_id, $user_id);

// Mostrar reseñas
display_course_reviews($course_id);
```

## Personalización

Para personalizar templates, copia los archivos a tu theme:

```
tu-theme/
├── course-templates/
    ├── single-course.php
    └── archive-course.php
```

El sistema buscará primero en tu theme antes de usar los templates por defecto.
