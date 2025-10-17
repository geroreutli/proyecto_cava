<?php
$conn = new mysqli("localhost","root","","cava_y_oro");
$conn->set_charset("utf8mb4");

$sql = "SELECT p.id, p.nombre, p.imagen, p.precio, SUM(vd.cantidad) AS total_vendido
        FROM inventario p
        JOIN venta_detalle vd ON vd.producto_id = p.id
        GROUP BY p.id
        ORDER BY total_vendido DESC
        LIMIT 10";

$result = $conn->query($sql);
$productos_populares = [];
if($result){
    while($row = $result->fetch_assoc()){
        $productos_populares[] = $row;
    }
}
?>
