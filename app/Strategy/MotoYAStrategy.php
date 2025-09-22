<?php
namespace App\Strategy;

use App\Adapter\DeliveryAdapterInterface;
use App\Adapter\MotoYAAdapter;

// Estrategia para seleccionar MotoYA.
// Para peso<=1200g (puesto en el código original) y que NO sean: pedido=express y fragilidad=(Debil/Alta).
class MotoYAStrategy implements DeliveryStrategyInterface
{
    public function matches(array $orderData): bool
    {
        return ($orderData['total_weight'] <= 1200) && !($orderData['priority'] === 'express' && $orderData['fragility'] !== 'ninguna');
    }

    public function getProviderName(): string
    {
        return 'MotoYA';
    }

    public function getAdapter(): DeliveryAdapterInterface
    {
        return new MotoYAAdapter();
    }
}