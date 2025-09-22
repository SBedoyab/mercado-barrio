<?php
namespace App\Adapter;

// Adaptador para simular la API de EcoBike.
class EcoBikeAdapter implements DeliveryAdapterInterface
{
    public function requestPickup(array $orderData): string
    {
        return 'EBK-' . strtoupper(bin2hex(random_bytes(3)));
    }
}