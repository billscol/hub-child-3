# Sistema de Cursos

## 📋 Descripción

Sistema completo de gestión de cursos online integrado con WooCommerce.

## 🏗️ Estructura

```
course-system/
├── loader.php                      # Cargador principal
├── post-types/
│   ├── course-cpt.php              # Custom Post Type de cursos
│   └── taxonomies.php              # Categorías y etiquetas
├── admin/
│   ├── metaboxes/
│   │   ├── course-info.php         # Info del curso
│   │   ├── instructor-info.php     # Info del instructor
│   │   └── course-settings.php     # Configuraciones
│   └── admin-columns.php           # Columnas personalizadas
├── frontend/
│   ├── template-loader.php         # Cargador de templates
│   ├── templates/
│   │   ├── single-course.php       # Template individual
│   │   ├── archive-course.php      # Listado de cursos
│   │   └── lesson-player.php       # Reproductor de lección
│   └── course-display.php          # Funciones de visualización
├── progress/
│   ├── course-progress.php         # Progreso del curso
│   └── lesson-completion.php       # Completar lecciones
├── certificates/
│   ├── certificate-generator.php   # Generar certificados
│   └── templates/
│       └── default-certificate.php # Template de certificado
├── integration/
│   ├── woocommerce-integration.php # Integración WC
│   └── elementor-widgets.php       # Widgets Elementor
├── access/
│   ├── enrollment.php              # Inscripciones
│   └── access-control.php          # Control de acceso
└── legacy/
    └── init.php                    # Compatibilidad
```

## 🎯 Funcionalidades

### Gestión de Cursos
- Custom Post Type `course`
- Módulos y lecciones
- Currículum estructurado
- Contenido multimedia

### Sistema de Progreso
- Tracking de lecciones completadas
- Porcentaje de avance
- Historial de progreso
- Marcadores de finalización

### Certificados
- Generación automática al completar
- Plantillas personalizables
- Descarga en PDF
- Verificación de autenticidad

### Integración WooCommerce
- Cursos como productos
- Control de acceso por compra
- Inscripción automática
- Renovaciones y suscripciones

## 🔧 Uso

### En el Theme

```php
// Cargar sistema de cursos
require_once get_stylesheet_directory() . '/includes/course-system/loader.php';
```

### Funciones Principales

```php
// Verificar si usuario tiene acceso
if (user_has_course_access($course_id, $user_id)) {
    // Mostrar contenido
}

// Obtener progreso
$progress = get_course_progress($course_id, $user_id);
echo $progress['percentage'] . '%';

// Marcar lección como completada
mark_lesson_complete($lesson_id, $user_id);

// Generar certificado
$certificate_url = generate_course_certificate($course_id, $user_id);
```

## 📦 Custom Post Type

### course

**Registrado con:**
- Soporte para: título, editor, thumbnail, excerpt
- Jerarquía: No
- Público: Sí
- Menú: Icono de graduación

**Taxonomías:**
- `course_category` - Categorías de cursos
- `course_tag` - Etiquetas
- `course_level` - Nivel (principiante, intermedio, avanzado)

## 📊 Metaboxes

### Información del Curso
- Duración
- Número de lecciones
- Nivel
- Idioma
- Requisitos previos

### Instructor
- Nombre
- Biografía
- Avatar
- Enlaces sociales

### Configuraciones
- Habilitar/deshabilitar certificado
- Modo de progreso (lineal o libre)
- Restricciones de tiempo

## 🎨 Templates Frontend

### Jerarquía de Templates

1. `single-course.php` - Página individual del curso
2. `archive-course.php` - Listado de cursos
3. `taxonomy-course_category.php` - Cursos por categoría

### Shortcodes Disponibles

```php
// Listado de cursos
[course_grid category="programacion" limit="6"]

// Curso individual
[course_info id="123"]

// Progreso del usuario
[my_course_progress]

// Certificados obtenidos
[my_certificates]
```

## 🔗 Integraciones

### WooCommerce
- Los productos de tipo "curso" se sincronizan
- Acceso automático al comprar
- Revocación en reembolsos

### Elementor
- Widget de listado de cursos
- Widget de currículum
- Widget de progreso
- Widget de certificados

### Dokan (Multi-vendor)
- Vendedores pueden crear cursos
- Panel de instructor
- Estadísticas de estudiantes

## 🔄 Migración

### Estado Actual
- ✅ Estructura base creada
- ⏳ Pendiente: Migrar código legacy
- ⏳ Pendiente: Implementar progreso avanzado

### TODO
- [ ] Mover código de init.php a módulos
- [ ] Crear sistema de quiz/exámenes
- [ ] Dashboard de estudiante
- [ ] Analytics del curso
- [ ] Gamificación (badges, logros)

## 🐛 Debugging

```php
define('COURSE_SYSTEM_DEBUG', true);
```

## 📝 Notas

- Compatible con WooCommerce
- Soporte multi-idioma (WPML, Polylang)
- Responsive design
- Accesibilidad WCAG 2.1
