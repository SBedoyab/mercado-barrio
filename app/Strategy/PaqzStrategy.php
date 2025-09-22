<?php
namespace App\Strategy;

use App\Adapter\DeliveryAdapterInterface;
use App\Adapter\PaqzAdapter;

// Estrategia por defecto para PaqueteríaZ.
// Cubre si peso>1200.
class PaqzStrategy implements DeliveryStrategyInterface
{
    public function matches(array $orderData): bool
    {
        return true; // Fallback: siempre aplica si ninguna otra estrategia coincide
    }

    public function getProviderName(): string
    {
        return 'PaqueteriaZ';
    }

    public function getAdapter(): DeliveryAdapterInterface
    {
        return new PaqzAdapter();
    }
}