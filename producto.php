<?php
session_start();

include("conexion.php");

$conn->select_db("Cava_y_Oro");

if (!isset($_GET['id'])) {
    die("No se especificó producto");
}

$id = intval($_GET['id']); 

$sql = "SELECT * FROM productos WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Producto no encontrado");
}

$producto = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $producto['nombre']; ?></title>
</head>
<body>
    <a href="productos.php">⬅ Volver a la lista</a>
    
    <h1><?php echo $producto['nombre']; ?></h1>
    <img src="<?php echo $producto['imagen']; ?>" width="200">
    <p><strong>Precio:</strong> $<?php echo $producto['precio']; ?></p>
    <p><strong>Stock:</strong> <?php echo $producto['stock']; ?></p>
    <p><strong>Descripción:</strong> <?php echo $producto['descripcion']; ?></p>

 
    <form action="carrito.php" method="POST">
        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
        <input type="number" name="cantidad" value="1" min="1">
        <button type="submit">Agregar al carrito</button>
    </form>
</body>
</html>
