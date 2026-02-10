# 🗃️ Plan de Reorganización de Sistemas Restantes

## 📊 Estado Actual

### ✅ Completado (Fase 1)
- [x] Reorganización de **6 shortcodes** en carpetas modulares
- [x] Creación de **functions.php limpio**
- [x] Documentación completa
- [x] Sistema de carga modular

### 🗓️ Pendiente (Fase 2 - Opcional)
- [ ] Sistema de Coins
- [ ] Sistema de Cursos
- [ ] Personalización de Checkout

---

## 🪙 Sistema de Coins

### 📍 Estado Actual
Actualmente el código está mezclado en el functions.php original.

### 📊 Código Identificado
- Gestión de saldo de coins
- Historial de transacciones
- Recompensas por compras
- Recompensas por reseñas
- Sistema de canje
- Widgets y displays
- Tablas de base de datos

### 📁 Estructura Propuesta

```
includes/coins-system/
├── loader.php                 # Cargador principal
├── database/
│   ├── tables.php           # Creación de tablas
│   └── migrations.php       # Migraciones
├── core/
│   ├── coins-manager.php    # Clase principal
│   ├── balance.php          # Gestión de saldo
│   └── transactions.php     # Historial
├── rewards/
│   ├── purchases.php        # Recompensas por compra
│   ├── reviews.php          # Recompensas por reseñas
│   └── social-shares.php    # Recompensas por compartir
├── redemption/
│   ├── canje.php            # Sistema de canje
│   └── validation.php       # Validaciones
├── admin/
│   ├── metabox.php          # Metabox de coins
│   └── columns.php          # Columnas personalizadas
├── frontend/
│   ├── display.php          # Mostrar coins
│   ├── widgets.php          # Widgets
│   └── ajax-handlers.php    # Manejadores AJAX
└── api/
    ├── endpoints.php        # Endpoints REST
    └── webhooks.php         # Webhooks
```

### 🛠️ Cómo Reorganizar

1. **Extraer código del functions.php**
   - Buscar todas las funciones relacionadas con "coins"
   - Identificar dependencias
   - Agrupar por funcionalidad

2. **Crear estructura de carpetas**
   ```bash
   mkdir -p includes/coins-system/{database,core,rewards,redemption,admin,frontend,api}
   ```

3. **Distribuir código**
   - Mover cada función a su archivo correspondiente
   - Mantener namespaces consistentes
   - Añadir documentación

4. **Crear loader.php**
   ```php
   <?php
   // Cargar todos los módulos del sistema de coins
   require_once __DIR__ . '/database/tables.php';
   require_once __DIR__ . '/core/coins-manager.php';
   // ... etc
   ```

5. **Actualizar functions.php**
   ```php
   // Cargar sistema de coins
   require_once get_stylesheet_directory() . '/includes/coins-system/loader.php';
   ```

---

## 🏫 Sistema de Cursos

### 📍 Estado Actual
Código mezclado en functions.php para gestión de cursos.

### 📊 Código Identificado
- Custom Post Type de cursos
- Categorías y taxonomías
- Metaboxes de cursos
- Sistema de módulos y lecciones (ya movido parcialmente)
- Integración con WooCommerce
- Panel de instructor

### 📁 Estructura Propuesta

```
includes/course-system/
├── loader.php                 # Cargador principal
├── post-types/
│   ├── course.php           # CPT de cursos
│   ├── lesson.php           # CPT de lecciones
│   └── taxonomies.php       # Categorías y tags
├── admin/
│   ├── metaboxes/
│   │   ├── course-info.php  # Información del curso
│   │   ├── instructor.php   # Datos del instructor
│   │   └── settings.php     # Configuraciones
│   ├── columns.php          # Columnas admin
│   └── bulk-actions.php     # Acciones masivas
├── frontend/
│   ├── templates/
│   │   ├── single-course.php
│   │   ├── archive-course.php
│   │   └── lesson-player.php
│   ├── progress.php         # Sistema de progreso
│   └── certificates.php     # Certificados
├── integration/
│   ├── woocommerce.php      # Integración WC
│   ├── elementor.php        # Widgets Elementor
│   └── lms-plugins.php      # Otros plugins LMS
└── api/
    ├── enrollment.php       # Inscripciones
    └── access-control.php   # Control de acceso
```

---

## 🛍️ Personalización de Checkout

### 📍 Estado Actual
Customizaciones del checkout en functions.php.

### 📊 Código Identificado
- Campos personalizados
- Validaciones custom
- Hooks de WooCommerce
- Integración con pasarelas
- Emails personalizados
- Redirecciones

### 📁 Estructura Propuesta

```
includes/checkout-customization/
├── loader.php                 # Cargador principal
├── fields/
│   ├── billing.php          # Campos de facturación
│   ├── shipping.php         # Campos de envío
│   └── custom-fields.php    # Campos personalizados
├── validation/
│   ├── field-validation.php # Validación de campos
│   └── cart-validation.php  # Validación de carrito
├── templates/
│   ├── checkout-form.php   # Template del formulario
│   ├── order-summary.php   # Resumen del pedido
│   └── payment-methods.php # Métodos de pago
├── emails/
│   ├── order-confirmation.php
│   ├── order-processing.php
│   └── templates/
├── payment-gateways/
│   ├── custom-gateway.php
│   └── integrations.php
└── hooks/
    ├── pre-checkout.php    # Antes del checkout
    ├── post-checkout.php   # Después del checkout
    └── redirects.php       # Redirecciones
```

---

## ⌛ Estimación de Tiempo

| Sistema | Complejidad | Tiempo Estimado | Prioridad |
|---------|-------------|-----------------|----------|
| Coins System | Alta | 4-6 horas | Media |
| Course System | Media | 3-4 horas | Baja |
| Checkout | Baja | 2-3 horas | Baja |

**Total:** 9-13 horas aproximadamente

---

## 🚦 Prioridades

### 🔴 Alta Prioridad (Ya Completado)
- ✅ Shortcodes organizados
- ✅ functions.php limpio
- ✅ Documentación completa

### 🟡 Media Prioridad (Opcional)
- ⏳ Sistema de Coins
  - Más complejo, más código
  - Beneficio: mejor mantenimiento

### 🟯 Baja Prioridad (Puede Esperar)
- ⏳ Sistema de Cursos
  - Ya parcialmente organizado (currículum)
  - Menor urgencia

- ⏳ Checkout Customization
  - Código relativamente simple
  - Funciona bien como está

---

## 📝 Notas Importantes

1. **No es urgente**: Los shortcodes ya reorganizados son lo más crítico
2. **Todo funciona**: El código actual de estos sistemas funciona perfectamente
3. **Beneficio gradual**: Se puede hacer cuando haya tiempo disponible
4. **Sin presión**: Esto es para mejorar aún más, no porque sea necesario

---

## 🎉 Conclusión
### ✅ Lo que ya logramos
- **6 shortcodes** perfectamente organizados
- **functions.php** reducido de 5,047 a ~150 líneas
- **Estructura modular** implementada
- **Documentación** completa

### 🔮 Si quieres continuar
- Usa este documento como guía
- Sigue el mismo patrón de organización
- No hay prisa, hazlo cuando tengas tiempo

**¡Tu código ya está mucho mejor organizado!** 🚀

Los sistemas restantes son un "nice to have", no un "must have".
