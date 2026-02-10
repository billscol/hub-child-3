# 📚 Sistema de Cursos - Documentación Completa

## 🎯 Descripción

Sistema completo de gestión de cursos online integrado con WooCommerce. Permite crear cursos con lecciones, trackear progreso de usuarios, y otorgar acceso mediante compras.

---

## 📁 Estructura de Archivos

```
includes/courses-system/
├── loader.php                          # 🔧 Cargador principal del sistema
├── README.md                           # 📖 Este archivo
├── database/
│   └── tables.php                      # 💾 3 tablas: progress, lessons_completed, course_access
├── core/
│   ├── class-course-manager.php        # 🎓 Gestión de cursos
│   ├── class-lesson-manager.php        # 📝 Gestión de lecciones
│   └── progress.php                    # 📊 Sistema de progreso
├── post-types/
│   ├── course-post-type.php            # 📚 CPT Course
│   └── lesson-post-type.php            # 📄 CPT Lesson
├── admin/
│   ├── course-metabox.php              # ⚙️ Metabox de cursos
│   ├── lesson-metabox.php              # ⚙️ Metabox de lecciones
│   └── columns.php                     # 📋 Columnas admin
├── frontend/
│   ├── course-display.php              # 🎨 Display de cursos
│   ├── lesson-display.php              # 🎨 Display de lecciones
│   ├── progress-bar.php                # 📊 Barra de progreso
│   └── navigation.php                  # 🧭 Navegación entre lecciones
├── shortcodes/
│   ├── course-list.php                 # [courses_list]
│   ├── course-single.php               # [course_content]
│   ├── user-courses.php                # [my_courses]
│   └── lesson-content.php              # [lesson_content]
├── integration/
│   └── woocommerce-integration.php     # 🔗 Integración con WooCommerce
├── ajax/
│   └── course-ajax.php                 # ⚡ Handlers AJAX
└── assets/
    ├── courses.css                     # 🎨 Estilos frontend
    ├── courses.js                      # ⚡ JavaScript frontend
    ├── admin.css                       # 🎨 Estilos admin
    └── admin.js                        # ⚡ JavaScript admin
```

---

## 🗄️ Base de Datos

### Tabla: `wp_course_progress`
Progreso general del usuario en cada curso.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `user_id` | BIGINT | ID del usuario |
| `course_id` | BIGINT | ID del curso |
| `lessons_completed` | INT | Número de lecciones completadas |
| `total_lessons` | INT | Total de lecciones del curso |
| `percentage` | DECIMAL | Porcentaje completado (0-100) |
| `status` | VARCHAR(20) | Estado: 'in_progress', 'completed' |
| `started_at` | DATETIME | Fecha de inicio |
| `completed_at` | DATETIME | Fecha de completado (NULL si no) |
| `last_accessed` | DATETIME | Último acceso |

### Tabla: `wp_lessons_completed`
Detalle de lecciones completadas por usuario.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `user_id` | BIGINT | ID del usuario |
| `lesson_id` | BIGINT | ID de la lección |
| `course_id` | BIGINT | ID del curso padre |
| `completed_at` | DATETIME | Fecha de completado |

### Tabla: `wp_course_access`
Control de acceso de usuarios a cursos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `user_id` | BIGINT | ID del usuario |
| `course_id` | BIGINT | ID del curso |
| `product_id` | BIGINT | ID del producto WC que otorgó acceso |
| `order_id` | BIGINT | ID del pedido WC |
| `access_type` | VARCHAR(20) | 'purchase', 'manual', 'free' |
| `granted_at` | DATETIME | Fecha de otorgamiento |
| `expires_at` | DATETIME | Fecha de expiración (NULL = sin límite) |

---

## 🎓 Custom Post Types

### Course (Curso)
- **Slug**: `course`
- **Supports**: title, editor, thumbnail, excerpt
- **Hierarchical**: No
- **Public**: Yes
- **Has archive**: Yes

### Lesson (Lección)
- **Slug**: `lesson`
- **Supports**: title, editor, thumbnail, page-attributes
- **Hierarchical**: Yes (parent: course)
- **Public**: Yes
- **Show in menu**: No (se gestiona desde cursos)

---

## 🔧 Funciones Principales

### Gestión de Cursos

```php
// Obtener instancia del manager
$course_manager = Course_Manager::get_instance();

// Crear curso
$course_id = $course_manager->create_course(array(
    'title' => 'Título del curso',
    'description' => 'Descripción',
    'product_id' => 123 // ID del producto WC asociado
));

// Verificar acceso
if (courses_user_has_access($user_id, $course_id)) {
    // Usuario tiene acceso
}

// Otorgar acceso
$course_manager->grant_access($user_id, $course_id, $product_id, $order_id);

// Obtener lecciones de un curso
$lessons = $course_manager->get_lessons($course_id);

// Obtener estadísticas
$stats = $course_manager->get_stats($course_id);
```

### Gestión de Lecciones

```php
// Obtener instancia
$lesson_manager = Lesson_Manager::get_instance();

// Crear lección
$lesson_id = $lesson_manager->create_lesson(array(
    'title' => 'Lección 1',
    'content' => 'Contenido de la lección',
    'course_id' => $course_id,
    'order' => 1
));

// Marcar como completada
courses_complete_lesson($user_id, $lesson_id, $course_id);

// Verificar si está completada
if (courses_is_lesson_completed($user_id, $lesson_id)) {
    // Lección completada
}

// Obtener siguiente lección
$next_lesson = $lesson_manager->get_next_lesson($course_id, $current_lesson_id);

// Obtener lección anterior
$prev_lesson = $lesson_manager->get_previous_lesson($course_id, $current_lesson_id);
```

### Sistema de Progreso

```php
// Obtener progreso de usuario
$progress = courses_get_user_progress($user_id, $course_id);
// Retorna: array con 'percentage', 'completed', 'total', 'status'

// Obtener porcentaje
$percentage = courses_get_progress_percentage($user_id, $course_id);
// Retorna: float (0-100)

// Verificar si curso está completado
if (courses_is_course_completed($user_id, $course_id)) {
    // Curso completado al 100%
}

// Obtener cursos del usuario
$user_courses = courses_get_user_courses($user_id);

// Resetear progreso
courses_reset_progress($user_id, $course_id);
```

---

## 📝 Shortcodes

### `[courses_list]`
Muestra listado de cursos.

```php
[courses_list limit="9" category="programacion" orderby="date"]
```

**Atributos:**
- `limit`: Número de cursos (default: -1, todos)
- `category`: Slug de categoría de curso
- `orderby`: date, title, menu_order
- `order`: ASC, DESC
- `style`: grid, list

### `[course_content]`
Muestra contenido de un curso específico.

```php
[course_content id="123"]
```

**Atributos:**
- `id`: ID del curso (requerido)
- `show_lessons`: yes/no (default: yes)
- `show_progress`: yes/no (default: yes)

### `[my_courses]`
Muestra cursos del usuario actual.

```php
[my_courses status="in_progress"]
```

**Atributos:**
- `status`: all, in_progress, completed (default: all)
- `style`: grid, list (default: grid)

### `[lesson_content]`
Muestra contenido de una lección.

```php
[lesson_content id="456"]
```

**Atributos:**
- `id`: ID de la lección (requerido)
- `show_navigation`: yes/no (default: yes)
- `show_complete_button`: yes/no (default: yes)

---

## 🔗 Integración con WooCommerce

### Asociar Curso a Producto

1. En el metabox del curso, selecciona el producto WC
2. Al completar la compra, se otorga acceso automáticamente
3. El acceso queda registrado en `wp_course_access`

### Hooks Disponibles

```php
// Al otorgar acceso a un curso
do_action('courses_access_granted', $user_id, $course_id, $product_id);

// Al completar una lección
do_action('courses_lesson_completed', $user_id, $lesson_id, $course_id);

// Al completar un curso
do_action('courses_course_completed', $user_id, $course_id);

// Al iniciar un curso
do_action('courses_course_started', $user_id, $course_id);
```

---

## 🎨 Personalización

### Templates Override

Puedes sobrescribir templates creando archivos en tu tema:

```
tu-tema/
└── courses-system/
    ├── single-course.php
    ├── single-lesson.php
    ├── course-list.php
    └── progress-bar.php
```

### CSS Classes

```css
/* Contenedor principal de curso */
.course-container {}

/* Listado de lecciones */
.course-lessons-list {}

/* Lección individual */
.course-lesson-item {}

/* Lección completada */
.course-lesson-item.completed {}

/* Barra de progreso */
.course-progress-bar {}

/* Navegación entre lecciones */
.lesson-navigation {}

/* Botón de completar */
.lesson-complete-btn {}
```

---

## ⚡ AJAX Endpoints

### Completar Lección

```javascript
jQuery.ajax({
    url: coursesData.ajaxurl,
    type: 'POST',
    data: {
        action: 'courses_complete_lesson',
        nonce: coursesData.nonce,
        lesson_id: 123,
        course_id: 456
    },
    success: function(response) {
        if (response.success) {
            // Lección completada
            console.log(response.data.progress);
        }
    }
});
```

### Obtener Progreso

```javascript
jQuery.ajax({
    url: coursesData.ajaxurl,
    type: 'POST',
    data: {
        action: 'courses_get_progress',
        nonce: coursesData.nonce,
        course_id: 456
    },
    success: function(response) {
        if (response.success) {
            console.log('Progreso: ' + response.data.percentage + '%');
        }
    }
});
```

---

## 🔒 Seguridad

- ✅ Verificación de nonce en todos los AJAX
- ✅ Validación de permisos de usuario
- ✅ Sanitización de inputs
- ✅ Escape de outputs
- ✅ Prevención de SQL injection
- ✅ Control de acceso por usuario

---

## 📊 Uso Básico

### 1. Crear un Curso

1. Ir a **Cursos > Añadir nuevo**
2. Agregar título y descripción
3. Seleccionar producto WooCommerce asociado
4. Publicar

### 2. Crear Lecciones

1. Ir a **Lecciones > Añadir nuevo**
2. Agregar título y contenido
3. Seleccionar curso padre
4. Establecer orden
5. Publicar

### 3. Usuario Accede al Curso

1. Usuario compra el producto en WooCommerce
2. Sistema otorga acceso automáticamente
3. Usuario puede ver el curso y sus lecciones
4. Al completar lecciones, se actualiza el progreso

---

## 🎯 Casos de Uso

### Curso Gratuito

```php
// No asociar producto, otorgar acceso manual
$course_manager->grant_access($user_id, $course_id, 0, 0, 'free');
```

### Curso Premium

```php
// Asociar producto WC en el metabox
// El acceso se otorga automáticamente al completar compra
```

### Curso con Expiración

```php
$expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
$course_manager->grant_access($user_id, $course_id, $product_id, $order_id, 'purchase', $expires_at);
```

---

## 📈 Estadísticas

```php
// Estadísticas del curso
$stats = $course_manager->get_stats($course_id);
/*
Array(
    'total_students' => 150,
    'active_students' => 89,
    'completed_students' => 61,
    'average_progress' => 67.5,
    'total_lessons' => 12
)
*/

// Estadísticas del usuario
$user_stats = courses_get_user_stats($user_id);
/*
Array(
    'total_courses' => 5,
    'completed_courses' => 2,
    'in_progress_courses' => 3,
    'total_lessons_completed' => 45
)
*/
```

---

## 🚀 Próximas Características

- [ ] Certificados de completado
- [ ] Quizzes y evaluaciones
- [ ] Foros de discusión
- [ ] Recursos descargables
- [ ] Videos embebidos
- [ ] Drip content (liberación programada)

---

## 💡 Tips

1. **Orden de lecciones**: Usa el campo `menu_order` para ordenar
2. **Progreso automático**: Se actualiza al completar cada lección
3. **Acceso múltiple**: Un usuario puede tener acceso por varios productos
4. **Performance**: Las consultas están optimizadas con índices
5. **Compatible con**: Elementor, Gutenberg, Classic Editor

---

¡Sistema de Cursos listo para crear tu plataforma de educación online! 🎓🚀
