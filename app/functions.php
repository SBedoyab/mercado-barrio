<?php

function products_all(): array {
  return db()->query("SELECT * FROM products ORDER BY name ASC")->fetchAll();
} // No modificada

function orders_latest(int $limit=5): array {
  $stmt = db()->query("SELECT * FROM orders ORDER BY id DESC LIMIT " . (int)$limit);
  return $stmt->fetchAll();
} // No modificada

function order_with_items(int $id): ?array {
  $stmt = db()->prepare("SELECT * FROM orders WHERE id=?");
  $stmt->execute([$id]);
  $order = $stmt->fetch();
  if (!$order) return null;
  $stmt = db()->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE order_id=?");
  $stmt->execute([$id]);
  $items = $stmt->fetchAll();
  return ['order'=>$order, 'items'=>$items];
} // No modificada

function shipment_by_order(int $orderId): ?array {
  $stmt = db()->prepare("SELECT * FROM shipments WHERE order_id=? ORDER BY id DESC LIMIT 1");
  $stmt->execute([$orderId]);
  $r = $stmt->fetch();
  return $r ?: null;
} // No modificada

function handle_create_order(array $input): int {
  $email = filter_var(trim((string)($input['customer_email'] ?? '')), FILTER_VALIDATE_EMAIL);
  $address = trim((string)($input['address'] ?? ''));
  $priority = (string)($input['priority'] ?? 'normal');
  $fragility = (string)($input['fragility'] ?? 'ninguna');
  $items = $input['items'] ?? [];

  if (!$email) throw new RuntimeException('Email inválido');
  if ($address==='') throw new RuntimeException('Dirección requerida');

  $pdo = db();
  $pdo->beginTransaction();
  try {
    // PATRÓN DE DISEÑO BUILDER:
    // Construcción de objeto Pedido.
    $builder = new \App\Builder\OrderBuilder($pdo);
    $orderData = $builder->build($email, $address, $priority, $fragility, $items);

    $stmt = $pdo->prepare("INSERT INTO orders (customer_email,address,priority,fragility,total_weight) VALUES (?,?,?,?,?)");
    $stmt->execute([$orderData['customer_email'],$orderData['address'],$orderData['priority'],$orderData['fragility'],$orderData['total_weight'],]);
    $orderId = (int)$pdo->lastInsertId();
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity) VALUES (?,?,?)");
    foreach ($orderData['items'] as $it) {
      $stmtItem->execute([$orderId, $it['product_id'], $it['quantity']]);
    }

    // PATRÓN DE DISEÑO STRATEGY:
    // Selección del proveedor con reglas de negocio definidas en los requisitos
    $selector = new \App\Strategy\DeliverySelector([new \App\Strategy\EcoBikeStrategy(),new \App\Strategy\MotoYAStrategy(),new \App\Strategy\PaqzStrategy(),]);
    $strategy = $selector->select($orderData);
    $provider = $strategy->getProviderName();

    // PATRÓN DE DISEÑO ADAPTER:
    // Solicitar la recogida vía Adapter
    // SIMULANDO QUE EN LA APLICACIÓN VERDADERA LOS DATOS VINIERAN
    // POR MEDIO DE 3 APIs DISTINTAS
    $adapter  = $strategy->getAdapter();
    $tracking = $adapter->requestPickup(array_merge($orderData, ['order_id' => $orderId]));
    $stmt = $pdo->prepare("INSERT INTO shipments (order_id,provider,tracking_id,status) VALUES (?,?,?,?)");
    $stmt->execute([$orderId, $provider, $tracking, 'CONFIRMADO']);

    // Registro de notificaciones
    $msg  = "Pedido #$orderId confirmado y asignado a $provider ($tracking)";
    $stmt = $pdo->prepare("INSERT INTO notifications (order_id,channel,message) VALUES (?,?,?)");
    $stmt->execute([$orderId,'email',$msg]);
    $stmt->execute([$orderId,'webhook',$msg]);

    $pdo->commit();
    return $orderId;
  } catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
}

/* 
LÓGICA ANTIGUA:
function select_provider_naive(string $priority, string $fragility, int $totalWeight): string {
  if ($priority === 'express' && $fragility !== 'ninguna') {
    return 'ecobike';
  }
  if ($totalWeight <= 1200) {
    return 'motoya';
  }
  return 'paqz';
}

function request_pickup_naive(string $provider, array $data): string {
  // Simula distintas formas de generar tracking por proveedor (sin interfaces comunes)
  if ($provider === 'motoya') {
    return 'MYA-' . strtoupper(bin2hex(random_bytes(3)));
  } elseif ($provider === 'ecobike') {
    return 'EBK-' . strtoupper(bin2hex(random_bytes(3)));
  } else {
    return 'PAQ-' . strtoupper(bin2hex(random_bytes(3)));
  }
}
*/