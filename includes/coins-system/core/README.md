# Core - Sistema de Coins

## Archivos

### class-coins-manager.php
Clase principal para gestionar coins:
- Obtener saldo
- Agregar coins
- Restar coins
- Historial de transacciones

### coins-functions.php
Funciones auxiliares:
- Formatear coins
- Validaciones
- Helpers

## Uso

```php
// Instancia del manager
$manager = coins_manager();

// Obtener saldo
$saldo = $manager->get_coins($user_id);

// Agregar coins
$manager->add_coins($user_id, 100, 'Compra de curso');
```
