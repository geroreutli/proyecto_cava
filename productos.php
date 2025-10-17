<?php
session_start();

include("conexion.php");

$conn->select_db("Cava_y_Oro");

$sql = "SELECT * FROM productos";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<div class='producto'>";
    
    echo "<h3><a href='producto.php?id=".$row['id']."'>" . $row['nombre'] . "</a></h3>";
    
    echo "<p>Precio: $" . $row['precio'] . "</p>";
    echo "<p>Stock: " . $row['stock'] . "</p>";
    echo "<img src='" . $row['imagen'] . "' width='100'>";
    
    echo "<form action='carrito.php' method='POST'>
            <input type='hidden' name='producto_id' value='" . $row['id'] . "'>
            <input type='number' name='cantidad' value='1' min='1'>
            <button type='submit'>Agregar al carrito</button>
          </form>";
    
    echo "</div><hr>";
}

session_start();
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
?>
