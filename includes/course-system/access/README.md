# Access Control - Control de Acceso

## enrollment.php

Gestión de inscripciones:

```php
// Inscribir usuario
enroll_user_in_course($user_id, $course_id);

// Desinscribir
unenroll_user_from_course($user_id, $course_id);

// Verificar inscripción
if (is_user_enrolled($user_id, $course_id)) {
    // Usuario inscrito
}

// Obtener cursos del usuario
$courses = get_user_courses($user_id);
```

## access-control.php

Control de acceso a contenido:

```php
// Verificar acceso al curso
if (user_has_course_access($user_id, $course_id)) {
    // Mostrar contenido
} else {
    // Mostrar mensaje de compra
}

// Verificar acceso a lección
if (user_can_access_lesson($user_id, $lesson_id)) {
    // Mostrar lección
}

// Bloquear contenido
block_course_content($course_id);
```

## Tipos de Acceso

1. **Gratuito** - Acceso libre
2. **Compra** - Requiere compra del curso
3. **Suscripción** - Acceso con membresía activa
4. **Tiempo limitado** - Acceso por X días

## Restricciones

### Por Rol
```php
add_filter('course_user_roles', function($roles) {
    return ['subscriber', 'customer'];
});
```

### Por Fecha
```php
// Curso disponible desde/hasta
set_course_availability($course_id, $start_date, $end_date);
```

### Por Prerequisitos
```php
// Requiere completar otro curso primero
set_course_prerequisite($course_id, $prerequisite_course_id);
```
