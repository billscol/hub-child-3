# Sistema de Coins

## 📋 Descripción

Sistema completo de monedas virtuales (coins) para la plataforma de cursos.

## 🏗️ Estructura

```
coins-system/
├── loader.php                    # Cargador principal
├── database/
│   └── tables.php                # Creación de tablas BD
├── core/
│   ├── class-coins-manager.php   # Clase principal
│   └── coins-functions.php       # Funciones auxiliares
├── payment/
│   └── class-coins-gateway.php   # Gateway de pago
├── rewards/
│   ├── purchase-rewards.php      # Recompensas por compra
│   ├── review-rewards.php        # Recompensas por reseñas
│   └── social-rewards.php        # Recompensas por compartir
├── hooks/
│   └── coins-hooks.php           # Hooks de WooCommerce
├── admin/
│   ├── coins-metabox.php         # Metabox de coins
│   └── coins-admin.php           # Panel admin
├── frontend/
│   └── coins-display.php         # Widgets y displays
└── legacy/
    └── (archivos antiguos)       # Compatibilidad
```

## 🎯 Funcionalidades

### Gestión de Saldo
- Consultar saldo de usuario
- Agregar coins
- Restar coins
- Historial de transacciones

### Recompensas
- **Por compra**: X coins por cada curso comprado
- **Por reseña**: X coins por reseña verificada
- **Por compartir**: X coins por compartir en redes sociales

### Sistema de Canje
- Canjear coins por cursos
- Validación de saldo
- Gateway de pago personalizado

### Administración
- Metabox en productos para configurar precio en coins
- Panel de gestión de coins
- Reportes de transacciones

## 🔧 Uso

### En el Theme

```php
// Cargar sistema de coins
require_once get_stylesheet_directory() . '/includes/coins-system/loader.php';
```

### Funciones Principales

```php
// Obtener saldo
$saldo = coins_manager()->get_coins($user_id);

// Agregar coins
coins_manager()->add_coins($user_id, 100, 'Compra de curso');

// Restar coins
coins_manager()->subtract_coins($user_id, 50, 'Canje de curso');

// Formatear coins
$formatted = coins_manager()->format_coins($cantidad);
```

## 📊 Base de Datos

### Tablas

1. **wp_coins_historial**
   - Historial de todas las transacciones
   - Campos: id, user_id, tipo, cantidad, saldo_anterior, saldo_nuevo, descripcion, order_id, fecha

2. **wp_coins_reviews_rewarded**
   - Registro de recompensas por reseñas
   - Campos: id, user_id, comment_id, product_id, coins_otorgados, fecha

3. **wp_coins_social_shares**
   - Registro de recompensas por compartir
   - Campos: id, user_id, product_id, platform, coins_otorgados, fecha

## 🔄 Migración

### Estado Actual
- ✅ Estructura base creada
- ⏳ Pendiente: Migrar código legacy a nueva estructura
- ⏳ Pendiente: Crear pruebas unitarias

### TODO
- [ ] Mover código de archivos legacy a módulos organizados
- [ ] Crear API REST para coins
- [ ] Implementar sistema de niveles/badges
- [ ] Dashboard de coins para usuarios

## 📝 Notas

- Sistema compatible con WooCommerce
- Soporta múltiples tipos de recompensas
- Historial completo de transacciones
- Gateway de pago integrado

## 🐛 Debugging

Para activar logs de coins:

```php
define('COINS_DEBUG', true);
```

## 🔗 Enlaces

- [WooCommerce Payment Gateway API](https://woocommerce.github.io/code-reference/classes/WC-Payment-Gateway.html)
- [Custom User Meta](https://developer.wordpress.org/plugins/users/working-with-user-metadata/)
