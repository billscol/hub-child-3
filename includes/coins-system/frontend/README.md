# Frontend - Visualización de Coins

## coins-display.php

Componentes frontend para mostrar coins:

### Widgets
- Saldo actual del usuario
- Historial reciente
- Productos canjeables

### Shortcodes

```php
// Mostrar saldo
[coins_balance]

// Mostrar historial
[coins_history limit="10"]

// Productos canjeables
[coins_products category="cursos"]
```

### Integraciones

#### Mi Cuenta (WooCommerce)
Pestaña personalizada con:
- Saldo actual
- Historial de transacciones
- Recompensas ganadas

#### Página de Producto
- Badge de "Canjeable con X coins"
- Botón de canje

## Estilos

Clases CSS disponibles:
- `.coins-balance` - Contenedor de saldo
- `.coins-amount` - Cantidad de coins
- `.coins-icon` - Icono de coin
- `.coins-history` - Lista de historial
