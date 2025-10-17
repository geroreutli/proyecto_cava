<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$conn = new mysqli("localhost", "root", "", "cava_y_oro");
$conn->set_charset('utf8mb4');

if (!$conn) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verifica que haya un ID en la URL
if (!isset($_GET['id'])) {
    die("Producto no especificado");
}

$id_producto = (int) $_GET['id']; // Convertir a número para seguridad

$sql = "SELECT * FROM inventario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $result->fetch_assoc();
?>

<h1><?= $producto['nombre'] ?></h1>
<p>Precio: $<?= $producto['precio'] ?></p>
<p>Stock: <?= $producto['stock'] ?></p>
<p>Descripción: <?= $producto['descripcion'] ?></p>
