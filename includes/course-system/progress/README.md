# Progress - Sistema de Progreso

## course-progress.php

Gestión del progreso general del curso:

```php
// Obtener progreso
$progress = get_course_progress($course_id, $user_id);
// Retorna: ['percentage' => 75, 'completed' => 15, 'total' => 20]

// Actualizar progreso
update_course_progress($course_id, $user_id);

// Verificar si está completado
if (is_course_completed($course_id, $user_id)) {
    // Generar certificado
}
```

## lesson-completion.php

Marcar lecciones como completadas:

```php
// Marcar lección completada
mark_lesson_complete($lesson_id, $user_id);

// Verificar si está completada
if (is_lesson_completed($lesson_id, $user_id)) {
    // Desbloquear siguiente lección
}

// Obtener lecciones completadas
$completed = get_completed_lessons($course_id, $user_id);
```

## Almacenamiento

El progreso se guarda en:
- User meta: `course_progress_{$course_id}`
- Formato: Array serializado con datos de progreso

## Hooks

```php
// Cuando se completa una lección
do_action('course_lesson_completed', $lesson_id, $user_id);

// Cuando se completa un curso
do_action('course_completed', $course_id, $user_id);
```
