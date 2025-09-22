<?php
namespace App\Strategy;

use App\Adapter\DeliveryAdapterInterface;

// Interfaz para estrategias de selección de proveedor de entrega.
interface DeliveryStrategyInterface
{
    /**
    * Determina si la estrategia aplica para los datos del pedido.
    *
    * @param array $orderData Datos del pedido resueltos por el Builder
    * @return bool True si la estrategia debe usarse
    */
    public function matches(array $orderData): bool;

    /**
    * Devuelve el nombre del proveedor de entrega asociado a la estrategia.
    *
    * @return string
    */
    public function getProviderName(): string;

    /**
    * Devuelve el adaptador que implementa la integración con el proveedor.
    *
    * @return DeliveryAdapterInterface
    */
    public function getAdapter(): DeliveryAdapterInterface;
}