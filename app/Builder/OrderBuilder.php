<?php
namespace App\Builder;

use PDO;
use RuntimeException;

/*
* Lógica para construir un pedido listo para despacho.
* Calcula el peso total, genera un código de paquete,
* y estima la hora de recogida.
*/
class OrderBuilder
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Construye los datos del pedido a partir de la entrada.
     *
     * @param string $email        Correo electrónico del cliente
     * @param string $address      Dirección de entrega
     * @param string $priority     Prioridad del envío (normal/express)
     * @param string $fragility    Nivel de fragilidad (ninguna/debil/alta)
     * @param array  $items        Items del pedido en formato [product_id => quantity]
     *
     * @return array Estructura con los datos resueltos del pedido
     * @throws RuntimeException Si no hay items válidos
     */
    public function build(string $email, string $address, string $priority, string $fragility, array $items): array
    {
        $resolved   = [];
        $totalWeight = 0;
        $stmt = $this->pdo->prepare("SELECT id, weight_grams, fragile FROM products WHERE id=?");
        foreach ($items as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }
            $stmt->execute([$productId]);
            $row = $stmt->fetch();
            if (!$row) {
                continue;
            }
            $weight = (int) $row['weight_grams'];
            $totalWeight += $weight * $qty;
            $resolved[] = [
                'product_id'  => (int) $row['id'],
                'quantity'    => $qty,
                'weight_grams'=> $weight,
                'fragile'     => (bool) $row['fragile'],
            ];
        }
        if (empty($resolved)) {
            throw new RuntimeException('El pedido no tiene items válidos');
        }

        $packageCode = 'PKG-' . strtoupper(bin2hex(random_bytes(3)));

        $hasFragileItem = false;
        foreach ($resolved as $item) {
            if ($item['fragile']) {
                $hasFragileItem = true;
                break;
            }
        }
        $handlingLabel = ($fragility !== 'ninguna' || $hasFragileItem) ? 'FRAGIL' : 'NORMAL';

        $pickupDatetime = date('Y-m-d H:i:s', strtotime('+6 hour'));

        return [
            'customer_email' => $email,
            'address'        => $address,
            'priority'       => $priority,
            'fragility'      => $fragility,
            'total_weight'   => $totalWeight,
            'package_code'   => $packageCode,
            'handling_label' => $handlingLabel,
            'items'          => $resolved,
        ];
    }
}