<?php
// subida_productos.php
// Formulario independiente para subir productos a la tabla "inventario" en la base de datos cava_y_oro

$conn = new mysqli('localhost', 'root', '', 'cava_y_oro');
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_producto = $_POST['nombre_producto'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $id_categoria = $_POST['id_categoria'] ?? null; // ahora se ingresa manualmente

    $imagen_ruta = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $nombre_img = basename($_FILES['imagen']['name']);
        $ruta_destino = 'imagenes_productos/' . $nombre_img;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_ruta = $ruta_destino;
        }
    }

    $stmt = $conn->prepare('INSERT INTO inventario (nombre_producto, descripcion, precio, stock, id_categoria, fecha_agregado, imagen) VALUES (?, ?, ?, ?, ?, NOW(), ?)');
    $stmt->bind_param('ssdiis', $nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_ruta);

    if ($stmt->execute()) {
        echo '<p style="color:green">Producto agregado correctamente al inventario.</p>';
    } else {
        echo '<p style="color:red">Error al agregar el producto: ' . $stmt->error . '</p>';
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subida de Productos al Inventario</title>
     <link rel="stylesheet" href="subida_productos.css">
   </head>
<body>
<form action="todoenuno.php" method="get" style="margin-top: 15px;">
<button type="submit">Volver al menú principal</button>
</form>
<h2>Agregar Producto al Inventario</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <label>Nombre del producto:</label>
    <input type="text" name="nombre_producto" required>

    <label>Descripción:</label>
    <textarea name="descripcion" rows="3"></textarea>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" required>

    <label>Stock:</label>
    <input type="number" name="stock" required>

    <label>ID de Categoría:</label>
    <input type="number" name="id_categoria" required>

    <label>Imagen del producto:</label>
    <input type="file" name="imagen" accept="image/*">

    <button type="submit">Subir producto</button>
</form>

</body>
</html>
