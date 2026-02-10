# 🪙 Sistema de Coins

## 📋 Descripción

Sistema completo de monedas virtuales (coins) para la plataforma de cursos. Permite a los usuarios ganar y canjear coins por diferentes acciones.

## 🗂️ Estructura de Carpetas

```
coins-system/
├── loader.php                    # Cargador principal
├── database/
│   └── tables.php                # Creación de tablas BD
├── core/
│   ├── coins-manager.php         # Clase principal
│   ├── balance.php               # Gestión de saldo
│   └── transactions.php          # Historial de transacciones
├── rewards/
│   ├── purchases.php             # Recompensas por compras
│   ├── reviews.php               # Recompensas por reseñas
│   └── social-shares.php         # Recompensas por compartir
├── redemption/
│   ├── canje.php                 # Sistema de canje
│   └── validation.php            # Validaciones
├── payment-gateway/
│   └── gateway.php               # Pasarela de pago WC
├── admin/
│   ├── metabox.php               # Metabox de coins
│   ├── columns.php               # Columnas personalizadas
│   └── settings.php              # Página de ajustes
├── frontend/
│   ├── display.php               # Mostrar coins al usuario
│   ├── widgets.php               # Widgets de coins
│   ├── ajax-handlers.php         # Manejadores AJAX
│   └── modal.php                 # Modal de coins
├── integration/
│   └── woocommerce.php           # Integración con WC
└── api/
    └── endpoints.php             # Endpoints REST API
```

## 🎯 Funcionalidades

### 💰 Gestión de Saldo
- Obtener saldo actual de un usuario
- Agregar coins al saldo
- Restar coins del saldo
- Historial completo de transacciones

### 🎁 Sistema de Recompensas
- **Por compras**: Gana coins al comprar cursos premium
- **Por reseñas**: Gana coins al dejar reseñas verificadas
- **Por compartir**: Gana coins al compartir en redes sociales

### 🔄 Sistema de Canje
- Canjear coins por cursos
- Validación de saldo suficiente
- Aplicar descuentos en checkout
- Historial de canjes

### 💳 Pasarela de Pago
- Pagar con coins en WooCommerce
- Validación de saldo
- Integración completa con checkout

### 📊 Panel de Admin
- Ver saldo de usuarios
- Agregar/quitar coins manualmente
- Historial de transacciones
- Configuración del sistema

### 🖥️ Frontend
- Mostrar saldo del usuario
- Widgets de coins
- Modal de canje
- Notificaciones

## 📚 Tablas de Base de Datos

### wp_coins_historial
Registra todas las transacciones de coins.

**Campos:**
- `id` - ID único
- `user_id` - ID del usuario
- `tipo` - Tipo de transacción (ganado/gastado/canjeado)
- `cantidad` - Cantidad de coins
- `saldo_anterior` - Saldo antes de la transacción
- `saldo_nuevo` - Saldo después de la transacción
- `descripcion` - Descripción de la transacción
- `order_id` - ID del pedido relacionado
- `fecha` - Fecha de la transacción

### wp_coins_reviews_rewarded
Registra recompensas por reseñas.

**Campos:**
- `id` - ID único
- `user_id` - ID del usuario
- `comment_id` - ID del comentario/reseña
- `product_id` - ID del producto
- `coins_otorgados` - Cantidad de coins otorgados
- `fecha` - Fecha

### wp_coins_social_shares
Registra recompensas por compartir.

**Campos:**
- `id` - ID único
- `user_id` - ID del usuario
- `product_id` - ID del producto compartido
- `platform` - Plataforma (facebook/twitter/whatsapp)
- `coins_otorgados` - Cantidad de coins otorgados
- `fecha` - Fecha

## 🔧 Uso

### Obtener saldo de un usuario
```php
$saldo = coins_manager()->get_balance($user_id);
```

### Agregar coins
```php
coins_manager()->add_coins($user_id, 10, 'Recompensa por compra');
```

### Restar coins
```php
coins_manager()->subtract_coins($user_id, 5, 'Canje de curso');
```

### Verificar si tiene suficientes coins
```php
if (coins_manager()->has_sufficient_balance($user_id, 10)) {
    // Usuario tiene 10 o más coins
}
```

## ⚙️ Configuración

El sistema se configura desde:
- **Admin** → **Coins Settings**
- Configurar cantidad de coins por acción
- Configurar productos canjeables
- Establecer reglas de recompensas

## 🔗 Integración

### WooCommerce
- Se integra automáticamente con el checkout
- Aparece como método de pago "Coins"
- Se actualiza el saldo después de cada compra

### Sistema de Reseñas
- Se integra con el shortcode `[resenas_producto]`
- Otorga coins automáticamente por reseñas verificadas

## 📝 Notas

- Los coins NO son transferibles entre usuarios
- El saldo nunca puede ser negativo
- Todas las transacciones quedan registradas
- Sistema completamente auditable

## 🔐 Seguridad

- Validación de nonces en todas las operaciones
- Sanitización de datos
- Verificación de permisos de usuario
- Prevención de duplicación de recompensas

## 📊 Métricas

- Total de coins en circulación
- Coins ganados por usuario
- Coins gastados por usuario
- Tasa de canje
- Productos más canjeados
