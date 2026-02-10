# Estructura de Carpetas - Hub Child Theme

## 📚 Descripción

Esta carpeta contiene todos los módulos personalizados y sistemas organizados del child theme.

## 📁 Estructura de Carpetas

```
includes/
├── checkout-customization/      # Personalización del checkout
├── coins-system/               # Sistema de monedas/coins
├── course-system/              # Sistema de cursos
├── dokan-integration.php       # Integración con Dokan
├── shortcodes/                 # Todos los shortcodes organizados
│   ├── sp-auth/                # [sp_auth] - Login/Registro
│   ├── course-curriculum/      # [course_curriculum] - Currículum
│   ├── resenas-producto/       # [resenas_producto] - Reseñas
│   ├── video-producto/         # [video_producto] - Videos
│   ├── filtros-cursos.php      # Filtros de cursos (legacy)
│   ├── grid-cursos.php         # Grid de cursos (legacy)
│   └── filtros-cursos-js.php   # JavaScript filtros (legacy)
└── shortcodes-loader.php       # Loader central de shortcodes
```

## 📌 Shortcodes Disponibles

### [sp_auth]
**Ubicación:** `includes/shortcodes/sp-auth/`
**Descripción:** Sistema de autenticación con modal de login y registro
**Archivos:**
- `index.php` - Loader principal
- `shortcode.php` - Lógica del shortcode
- `modal.php` - Modal de autenticación
- `styles.php` - Estilos CSS
- `scripts.php` - Scripts JavaScript

### [course_curriculum]
**Ubicación:** `includes/shortcodes/course-curriculum/`
**Descripción:** Muestra el currículum del curso con módulos y lecciones
**Archivos:**
- `index.php` - Loader principal
- `backend-metabox.php` - Metabox en el admin
- `frontend-display.php` - Visualización en frontend
- `shortcode.php` - Shortcode para Elementor

### [resenas_producto]
**Ubicación:** `includes/shortcodes/resenas-producto/`
**Descripción:** Sistema de reseñas y valoraciones de productos
**Archivos:**
- `index.php` - Loader principal
- `process-review.php` - Procesamiento de reseñas
- `shortcode.php` - Visualización y formulario

### [video_producto]
**Ubicación:** `includes/shortcodes/video-producto/`
**Descripción:** Sistema de videos con autoplay y modal
**Archivos:**
- `index.php` - Loader principal
- `metabox.php` - Metabox para agregar videos
- `shortcode.php` - Visualización del video

## 🔧 Cómo Agregar un Nuevo Shortcode

1. Crear una nueva carpeta en `includes/shortcodes/nombre-shortcode/`
2. Crear los archivos necesarios:
   - `index.php` (loader principal)
   - Otros archivos según funcionalidad
3. Agregar el loader en `includes/shortcodes-loader.php`
4. Documentar el shortcode en este README

## 📝 Notas Importantes

- **NO modificar** el diseño o colores de los shortcodes existentes
- **Solo reorganizar** el código para mejor gestión
- Cada shortcode debe ser autocontenido en su carpeta
- Los archivos legacy (filtros-cursos, grid-cursos) se mantendrán por compatibilidad

## 👥 Mantenimiento

**Última Actualización:** Febrero 2026
**Versión:** 2.0.0
**Estado:** Reorganización completa finalizada
