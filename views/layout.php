<?php
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>MercadoBarrio • Starter</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <a class="brand" href="?r=home">🍜 MercadoBarrio</a>
    <nav class="nav"><a class="btn primary" href="?r=order/create">Confirmar pedido</a></nav>
  </div>
</header>
<main class="container">
  <?php if ($flash): ?><div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
  <section class="card"><?php include __DIR__ . '/' . $name . '.php'; ?></section>
  <footer class="muted center mt"><small>Patrones de diseño aplicados: • Creacional: Builder y singleton | Estructural: Adapter | Comportamiento: Strategy </small></footer>
</main>
</body>
</html>
