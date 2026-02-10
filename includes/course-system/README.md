# 📚 Sistema de Cursos

## Descripción

Sistema completo de gestión de cursos para WordPress + WooCommerce.

---

## 📁 Estructura

```
includes/course-system/
├── loader.php                  # 🔧 Cargador principal
├── README.md                   # 📖 Este archivo
├── curriculum/                 # 📑 Módulos y lecciones
│   ├── metabox.php            # Backend: metabox en productos
│   ├── display.php            # Frontend: display automático
│   └── shortcode.php          # Shortcode [course_curriculum]
├── reviews/                    # ⭐ Sistema de reseñas
│   ├── form.php               # Formulario de reseñas
│   ├── display.php            # Display de reseñas
│   └── shortcode.php          # Shortcode [resenas_producto]
├── reports/                    # ⚠️ Sistema de reportes
│   ├── cpt.php                # Custom Post Type
│   ├── button.php             # Botón de reporte
│   └── handler.php            # Procesamiento AJAX
├── support/                    # 🎫 Tickets de soporte
│   ├── cpt.php                # CPT de tickets
│   ├── endpoint.php           # Endpoint /soporte/
│   └── template.php           # Template del endpoint
├── dashboard/                  # 🏠 Mi Cuenta personalizado
│   ├── customization.php      # Personalización del dashboard
│   └── styles.php             # CSS personalizado
├── shortcodes/                 # 🎨 Shortcodes
│   ├── filtros-cursos.php     # Filtros de cursos
│   ├── grid-cursos.php        # Grid de cursos
│   └── video-producto.php     # Video en producto
└── integration/                # 🔗 Integraciones
    └── dokan.php              # Integración con Dokan
```

---

## 🚀 Activación

En `functions.php`:

```php
// Cargar sistema de cursos
if (file_exists(get_stylesheet_directory() . '/includes/course-system/loader.php')) {
    require_once get_stylesheet_directory() . '/includes/course-system/loader.php';
}
```

---

## 📑 Curriculum (Módulos y Lecciones)

### Backend

- **Metabox** en productos tipo curso
- Agregar/eliminar módulos
- Agregar/eliminar lecciones
- Bloquear módulos (solo visible para compradores)
- Contador automático de módulos y lecciones

### Frontend

- Display automático en páginas de producto
- Acordeón interactivo
- Módulos bloqueados muestran "Contenido Privado"
- Shortcode para usar en cualquier parte

### Shortcode

```
[course_curriculum]
```

---

## ⭐ Sistema de Reseñas

### Características

- Formulario personalizado con estrellas
- Validación de compra (solo compradores)
- Display destacado de mejor reseña
- Integración con avatar de WordPress
- Procesamiento con nonce de seguridad

### Shortcode

```
[resenas_producto]
```

---

## ⚠️ Sistema de Reportes

### Tipos de Reportes

- 📅 Curso desactualizado
- ❌ Error en el curso
- 🔗 Enlace roto
- ℹ️ Información incorrecta
- 🔧 Otro problema

### Características

- Botón flotante en productos
- Modal AJAX
- CPT en admin
- Email de resolución
- Columnas personalizadas

### Shortcode

```
[boton_reporte]
```

---

## 🎫 Tickets de Soporte

### Características

- Custom Post Type `support_ticket`
- Endpoint `/mi-cuenta/soporte/`
- Template personalizado
- Estados: Abierto/Resuelto
- Solo visible para el autor

---

## 🏠 Dashboard Personalizado

### Características

- Estadísticas visuales
- Renombrar items del menú:
  - "Orders" → "Mis Cursos"
  - "Downloads" → "Recursos"
  - "Dashboard" → "Inicio"
- Agregar endpoint "Soporte"
- CSS moderno con gradientes
- Responsive 100%

---

## 🎨 Shortcodes Disponibles

### 1. Filtros de Cursos

```
[filtros_cursos]
```

Muestra filtros de:
- Categoría
- Precio
- Nivel
- Duración

### 2. Grid de Cursos

```
[grid_cursos limit="12" categoria=""]
```

Atributos:
- `limit`: Cantidad de cursos
- `categoria`: Slug de categoría

### 3. Video de Producto

```
[video_producto]
```

Modal con video del curso.

---

## 🔗 Integración con Dokan

### Características

- Redirección de "Add Product" → `/publicar-curso/`
- Campos personalizados en formulario de vendedor
- Compatible con multivendor

---

## 🔒 Seguridad

- ✅ Nonce en todos los formularios
- ✅ Sanitización de inputs
- ✅ Escape de outputs
- ✅ Verificación de permisos
- ✅ Validación AJAX

---

## 🎨 Estilos

Todos los estilos usan:
- Color principal: `#da0480` (rosa)
- Gradientes modernos
- Bordes redondeados
- Animaciones suaves
- Responsive mobile-first

---

## 📧 Emails

- Email de resolución de reportes
- Template personalizado
- Incluye link al curso

---

## 🐛 Debugging

Activar logs:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Ver logs en: `wp-content/debug.log`

---

## 📝 To-Do

- [ ] Sistema de progreso de cursos
- [ ] Certificados al completar
- [ ] Quiz/evaluaciones
- [ ] Gamificación

---

## 📞 Soporte

Para soporte o consultas, visita `/mi-cuenta/soporte/`

---

✅ **Sistema 100% funcional y organizado**
