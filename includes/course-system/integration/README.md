# Integration - Integraciones

## woocommerce-integration.php

Integración con WooCommerce:

### Funcionalidades

1. **Productos tipo curso**
   - Los productos con categoría "Cursos" son cursos
   - Metadatos sincronizados

2. **Acceso automático**
   - Al completar compra, se da acceso al curso
   - Inscripción automática

3. **Revocación**
   - En reembolsos, se revoca acceso
   - En cancelaciones, se suspende

4. **Visualización**
   - Cursos comprados en Mi Cuenta
   - Progreso visible en pedidos

### Hooks

```php
// Después de completar pedido
add_action('woocommerce_order_status_completed', 'enroll_user_in_courses');

// En reembolso
add_action('woocommerce_order_refunded', 'revoke_course_access');
```

## elementor-widgets.php

Widgets personalizados para Elementor:

### Widgets Disponibles

1. **Course Grid** - Grilla de cursos
2. **Course List** - Lista de cursos
3. **Course Info** - Información del curso
4. **Course Curriculum** - Currículum
5. **Course Progress** - Barra de progreso
6. **Instructor Card** - Tarjeta del instructor

### Uso

Los widgets aparecen automáticamente en Elementor bajo la categoría "Cursos".

## Otras Integraciones

### LearnDash
Compatibilidad con LearnDash para migrar cursos.

### LifterLMS
Importador de cursos desde LifterLMS.

### Zoom
Integración con Zoom para clases en vivo.
