# Payment Gateway - Coins

## class-coins-gateway.php

Gateway de pago personalizado que permite pagar con coins.

### Características
- Integración con WooCommerce
- Validación de saldo
- Procesamiento de pagos
- Reembolsos

### Configuración

El gateway se registra automáticamente en WooCommerce cuando:
1. WooCommerce está activo
2. El sistema de coins está habilitado

### Uso

Los usuarios pueden seleccionar "Pagar con Coins" en el checkout si tienen saldo suficiente.
