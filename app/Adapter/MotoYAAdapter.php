<?php
namespace App\Adapter;

// Adaptador para simular la API de MotoYA.
class MotoYAAdapter implements DeliveryAdapterInterface
{
    public function requestPickup(array $orderData): string
    {
        return 'MYA-' . strtoupper(bin2hex(random_bytes(3)));
    }
}