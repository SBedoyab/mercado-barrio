<?php
namespace App\Strategy;

use App\Adapter\DeliveryAdapterInterface;
use App\Adapter\EcoBikeAdapter;

// Estrategia para seleccionar EcoBike.
// Aplica para pedido=express y fragilidad=(debil/alta).
class EcoBikeStrategy implements DeliveryStrategyInterface
{
    public function matches(array $orderData): bool
    {
        return $orderData['priority'] === 'express' && $orderData['fragility'] !== 'ninguna';
    }

    public function getProviderName(): string
    {
        return 'EcoBike';
    }

    public function getAdapter(): DeliveryAdapterInterface
    {
        return new EcoBikeAdapter();
    }
}