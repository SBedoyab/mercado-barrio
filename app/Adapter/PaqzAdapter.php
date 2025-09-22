<?php
namespace App\Adapter;

// Adaptador para simular la API de PaqueteríaZ.
class PaqzAdapter implements DeliveryAdapterInterface
{
    public function requestPickup(array $orderData): string
    {
        return 'PAQ-' . strtoupper(bin2hex(random_bytes(3)));
    }
}