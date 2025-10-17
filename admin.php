<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost","root","","cava_y_oro");
if ($conn->connect_error) die("Conexión fallida");
$conn->set_charset('utf8mb4');

// agregar producto
$add_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock  = (int)($_POST['stock'] ?? 0);
    $imagen = trim($_POST['imagen'] ?? '');
    if ($nombre === '' || $precio <= 0) $add_msg = 'Nombre y precio requeridos.';
    else {
        $sql = "INSERT INTO productos (nombre, precio, imagen, stock) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sdsi", $nombre, $precio, $imagen, $stock);
            $stmt->execute();
            $stmt->close();
            $add_msg = 'Producto agregado.';
        } else $add_msg = 'Error al agregar producto.';
    }
}

// listar productos
$productos = [];
$res = $conn->query("SELECT id, nombre, precio, imagen, stock FROM productos ORDER BY id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) $productos[] = $r;
    $res->close();
}

// listar ordenes (si existen tablas ordenes/orden_items)
$ordenes = [];
$res2 = $conn->query("SELECT id, usuario_id, total, creado_at FROM ordenes ORDER BY creado_at DESC LIMIT 50");
if ($res2) {
    while ($o = $res2->fetch_assoc()) $ordenes[] = $o;
    $res2->close();
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Cava&Oro</title>
<link rel="stylesheet" href="cyocss.css">
</head>
<body>
<div class="layout">
  <div class="contenido">
    <h1>Panel Admin</h1>
    <p><?php echo htmlspecialchars($add_msg); ?></p>

    <h2>Agregar producto</h2>
    <form method="post" action="admin.php">
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="text" name="precio" placeholder="Precio" required>
      <input type="text" name="imagen" placeholder="URL imagen (opcional)">
      <input type="number" name="stock" placeholder="Stock" value="0" min="0">
      <button type="submit" name="add_product">Agregar</button>
    </form>

    <h2>Inventario</h2>
    <table>
      <thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr></thead>
      <tbody>
        <?php foreach($productos as $p): ?>
        <tr>
          <td><?php echo $p['id']; ?></td>
          <td><?php echo htmlspecialchars($p['nombre']); ?></td>
          <td><?php echo number_format($p['precio'],2); ?></td>
          <td><?php echo $p['stock']; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>Órdenes recientes</h2>
    <?php if (empty($ordenes)): ?>
      <p>No hay órdenes registradas.</p>
    <?php else: ?>
      <ul>
        <?php foreach($ordenes as $o): ?>
          <li>Orden #<?php echo $o['id']; ?> - Usuario: <?php echo $o['usuario_id']; ?> - Total: $<?php echo number_format($o['total'],2); ?> - <?php echo $o['creado_at']; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <p><a href="todoenuno.php">Volver a la tienda</a></p>
  </div>
</div>
</body>
</html>