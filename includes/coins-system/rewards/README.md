# Rewards - Sistema de Recompensas

## Archivos

### purchase-rewards.php
Recompensas por compra de cursos:
- Otorgar coins cuando se completa un pedido
- Configurar cantidad de coins por compra

### review-rewards.php
Recompensas por escribir reseñas:
- Otorgar coins por reseñas aprobadas
- Evitar duplicados
- Validación de reseñas

### social-rewards.php
Recompensas por compartir en redes sociales:
- Facebook, Twitter, WhatsApp, etc.
- Tracking de compartidos
- Límites diarios

## Configuración

Cantidades de recompensas se pueden configurar en:
- `wp-admin > Coins > Configuración`

## Hooks Disponibles

```php
// Modificar cantidad de coins por compra
add_filter('coins_purchase_reward_amount', function($amount, $order) {
    return $amount * 2; // Duplicar recompensa
}, 10, 2);

// Modificar cantidad por reseña
add_filter('coins_review_reward_amount', function($amount) {
    return 50; // 50 coins por reseña
});
```
