<?php
// filepath: [carrito.php](http://_vscodecontentref_/1)
if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión
$conn = new mysqli("localhost", "root", "", "cava_y_oro");
if ($conn->connect_error) die("Conexión fallida");
$conn->set_charset('utf8mb4');

// acciones: update, remove, clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($id > 0 && isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] = $qty;
    } elseif ($action === 'remove') {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id > 0 && isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
    } elseif ($action === 'clear') {
        unset($_SESSION['cart']);
    }
    header('Location: carrito.php');
    exit;
}

// obtener ids del carrito
$cart = $_SESSION['cart'] ?? [];
$products = [];
$total = 0.0;
if (!empty($cart)) {
    $ids = array_keys($cart);
    // construir placeholder para IN (...)
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($placeholders)";
    if ($stmt = $conn->prepare($sql)) {
        // bind dinámico
        $refs = [];
        foreach ($ids as $k => $v) $refs[$k] = &$ids[$k];
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id'];
            $qty = $cart[$pid] ?? 0;
            $subtotal = $qty * (float)$row['precio'];
            $row['quantity'] = $qty;
            $row['subtotal'] = $subtotal;
            $total += $subtotal;
            $products[$pid] = $row;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carrito - Cava&Oro</title>
<link rel="stylesheet" href="cyocss.css">
</head>
<body>
<div class="layout">
  <div class="contenido">
    <h1>Tu carrito</h1>
    <?php if (empty($products)): ?>
      <p>Tu carrito está vacío. <a href="todoenuno.php">Seguir comprando</a></p>
    <?php else: ?>
      <table>
        <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $pid => $p): ?>
          <tr>
            <td>
              <?php if (!empty($p['imagen'])): ?><img src="<?php echo htmlspecialchars($p['imagen']); ?>" alt="" style="height:48px;margin-right:8px;"><?php endif; ?>
              <?php echo htmlspecialchars($p['nombre']); ?>
            </td>
            <td>$<?php echo number_format($p['precio'],2); ?></td>
            <td>
              <form method="post" style="display:inline-block">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?php echo $pid ?>">
                <input type="number" name="quantity" value="<?php echo $p['quantity'] ?>" min="1" style="width:60px">
                <button type="submit">Actualizar</button>
              </form>
            </td>
            <td>$<?php echo number_format($p['subtotal'],2) ?></td>
            <td>
              <form method="post" style="display:inline-block">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?php echo $pid ?>">
                <button type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Total: $<?php echo number_format($total,2) ?></h3>

      <form method="post">
        <input type="hidden" name="action" value="clear">
        <button type="submit">Vaciar carrito</button>
      </form>

      <p><a href="todoenuno.php">Seguir comprando</a> — (checkout no implementado todavía)</p>
    <?php endif; ?>
    <?php if (!empty($products)): ?>
  <form method="post" action="checkout.php">
    <button type="submit">Finalizar compra</button>
  </form>
<?php endif; ?>
  </div>
</div>
</body>
</html>