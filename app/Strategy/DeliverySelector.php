<?php
namespace App\Strategy;

class DeliverySelector
{
    /**
    * @var DeliveryStrategyInterface[]
    */
    private array $strategies;

    /**
    * @param DeliveryStrategyInterface[] $strategies Conjunto de estrategias disponibles
    */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
    * Selecciona la PRIMERA ESTRATEGIA que coincida con los datos del pedido.
    * Si ninguna coincide, devuelve la última como fallback.
    *
    * @param array $orderData
    * @return DeliveryStrategyInterface
    */
    public function select(array $orderData): DeliveryStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->matches($orderData)) {
                return $strategy;
            }
        }
        // fallback: última estrategia
        return end($this->strategies);
    }
}