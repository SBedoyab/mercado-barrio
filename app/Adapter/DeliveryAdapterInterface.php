<?php
namespace App\Adapter;

// Interfaz que define la integración con proveedores externos.
interface DeliveryAdapterInterface
{
    /**
     * Solicita la recogida del paquete al proveedor y devuelve un identificador de seguimiento.
     *
     * @param array $orderData Datos del pedido (incluye order_id, peso, etc.)
     * @return string Código de tracking
     */
    public function requestPickup(array $orderData): string;
}