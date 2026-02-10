# 🪙 Sistema de Coins

## 📋 Descripción

Sistema completo de monedas virtuales (coins) para cursos online. Permite a los usuarios ganar y canjear coins por cursos gratuitos.

## 📁 Estructura de Archivos

```
coins-system/
├── loader.php                          # 🔧 Cargador principal
├── README.md                           # 📖 Esta documentación
├── database/
│   └── tables.php                      # 💾 Creación de tablas DB
├── core/
│   ├── class-coins-manager.php         # 🎯 Clase principal
│   ├── balance.php                     # 💰 Gestión de saldo
│   └── transactions.php                # 📊 Historial de transacciones
├── gateway/
│   └── class-coins-gateway.php         # 💳 Pasarela de pago WC
├── rewards/
│   ├── purchases.php                   # 🛒 Recompensas por compra
│   ├── reviews.php                     # ⭐ Recompensas por reseñas
│   └── social-shares.php               # 📱 Recompensas por compartir
├── admin/
│   ├── metabox.php                     # ⚙️ Metabox en productos
│   └── columns.php                     # 📋 Columnas personalizadas
├── frontend/
│   ├── display.php                     # 👁️ Display de coins
│   ├── modal.php                       # 🔔 Modal de coins
│   └── user-dropdown.php               # 👤 Dropdown usuario
└── integration/
    └── woocommerce-hooks.php           # 🔗 Hooks WooCommerce
```

## 🎯 Funcionalidades

### 1. Gestión de Coins
- ✅ Saldo de coins por usuario
- ✅ Historial completo de transacciones
- ✅ Agregar/Restar coins
- ✅ Validaciones de saldo

### 2. Sistema de Recompensas
- 🛒 **Compras**: 1 coin por cada curso premium comprado
- ⭐ **Reseñas**: 1 coin por cada reseña verificada y aprobada
- 📱 **Compartir**: Coins por compartir en redes sociales

### 3. Canje de Coins
- 🎁 Productos gratuitos canjeables con coins
- 💳 Pasarela de pago integrada en WooCommerce
- ✅ Validación de saldo antes de canje
- 🔒 Sistema anti-fraude

### 4. Integración WooCommerce
- 🛍️ Productos con costo en coins
- 💰 Checkout con coins
- 📦 Procesamiento de pedidos
- 📧 Notificaciones automáticas

## 🗄️ Tablas de Base de Datos

### `wp_coins_historial`
Registra todas las transacciones de coins.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint(20) | ID único |
| user_id | bigint(20) | ID del usuario |
| tipo | varchar(20) | Tipo de transacción |
| cantidad | decimal(10,2) | Cantidad de coins |
| saldo_anterior | decimal(10,2) | Saldo antes |
| saldo_nuevo | decimal(10,2) | Saldo después |
| descripcion | text | Descripción |
| order_id | bigint(20) | ID del pedido (si aplica) |
| fecha | datetime | Fecha y hora |

### `wp_coins_reviews_rewarded`
Controla recompensas por reseñas (evita duplicados).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint(20) | ID único |
| user_id | bigint(20) | ID del usuario |
| comment_id | bigint(20) | ID del comentario |
| product_id | bigint(20) | ID del producto |
| coins_otorgados | decimal(10,2) | Coins otorgados |
| fecha | datetime | Fecha y hora |

### `wp_coins_social_shares`
Registra compartidos en redes sociales.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint(20) | ID único |
| user_id | bigint(20) | ID del usuario |
| product_id | bigint(20) | ID del producto |
| platform | varchar(20) | Plataforma (facebook, twitter, etc) |
| coins_otorgados | decimal(10,2) | Coins otorgados |
| fecha | datetime | Fecha y hora |

## 🔧 Uso

### Obtener saldo de un usuario
```php
$coins_manager = Coins_Manager::get_instance();
$saldo = $coins_manager->get_coins($user_id);
```

### Agregar coins
```php
$coins_manager->add_coins(
    $user_id,
    1,
    'compra',
    'Compra de curso: ' . $producto_nombre,
    $order_id
);
```

### Restar coins
```php
$coins_manager->subtract_coins(
    $user_id,
    2,
    'canje',
    'Canje por curso: ' . $producto_nombre,
    $order_id
);
```

### Verificar si tiene coins suficientes
```php
$tiene_coins = $coins_manager->user_has_coins($user_id, $cantidad_necesaria);
```

## ⚙️ Configuración

### Costo en Coins de un Producto
1. Ir a Productos → Editar producto
2. En el metabox "Coins para Canje"
3. Establecer el costo en coins
4. Guardar

### Cantidad de Coins por Recompensa
Editar en: `includes/coins-system/rewards/purchases.php`

```php
// Línea ~25
$coins_to_add = 1; // Cambiar cantidad aquí
```

## 🎨 Personalización

### Cambiar Icono de Coin
Editar: `frontend/display.php` y `frontend/modal.php`

```php
$coin_icon_url = 'https://tu-sitio.com/coin.png';
```

### Cambiar Colores
Todos los estilos usan variables CSS:
- Color principal: `#da0480`
- Color secundario: `#b00368`

## 🐛 Debugging

### Ver historial de un usuario
```php
$transacciones = coins_get_user_transactions($user_id, $limit = 50);
foreach ($transacciones as $t) {
    echo "{$t->tipo}: {$t->cantidad} coins - {$t->descripcion}<br>";
}
```

### Verificar integridad de tablas
```php
coins_check_database_version();
```

## 📊 Estadísticas

- **Versión**: 2.0.0
- **Archivos**: 15
- **Tablas DB**: 3
- **Hooks WC**: 8+
- **Líneas de código**: ~2,000

## 🔐 Seguridad

✅ Validación de nonce en formularios
✅ Sanitización de inputs
✅ Escape de outputs
✅ Verificación de permisos
✅ Prevención de inyección SQL
✅ Control de duplicados

## 🆘 Soporte

Si encuentras algún problema:
1. Verifica que WooCommerce esté activo
2. Revisa los logs de error de WordPress
3. Verifica que las tablas existan en la base de datos

## 📝 Changelog

### Version 2.0.0 (Febrero 2026)
- ✅ Reorganización completa del código
- ✅ Estructura modular
- ✅ Documentación completa
- ✅ Mejoras de rendimiento

### Version 1.0.0
- 🎉 Versión inicial (código mezclado en functions.php)

## 👥 Créditos

Desarrollado para [CursoBarato.co](https://cursobarato.co)
