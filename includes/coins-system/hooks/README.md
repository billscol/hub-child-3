# Hooks - Sistema de Coins

## coins-hooks.php

Hooks y filtros de WooCommerce para integrar el sistema de coins.

### Hooks Implementados

#### Checkout
- Mostrar saldo de coins
- Aplicar descuentos
- Validar pagos

#### Pedidos
- Procesar recompensas al completar pedido
- Reembolsar coins en cancelaciones

#### Productos
- Mostrar precio en coins
- Calcular equivalencias

## Filtros Personalizados

```php
// Filtrar productos canjeables
add_filter('coins_redeemable_products', function($products) {
    // Retornar array de IDs
    return $products;
});

// Modificar tasa de conversión
add_filter('coins_conversion_rate', function($rate) {
    return 0.10; // 1 coin = $0.10
});
```
