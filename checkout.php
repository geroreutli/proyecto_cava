<?php
session_start();
$conn = new mysqli("localhost","root","","cava_y_oro");
$conn->set_charset('utf8mb4');

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) die("El carrito está vacío");

$total = 0;
$products = [];
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$sql = "SELECT id, precio FROM productos WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$refs = [&$types];
foreach ($ids as $k => $v) $refs[] = &$ids[$k];
call_user_func_array([$stmt,'bind_param'],$refs);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $pid = (int)$row['id'];
    $qty = $cart[$pid];
    $subtotal = $row['precio'] * $qty;
    $total += $subtotal;
    $products[$pid] = ['cantidad'=>$qty,'precio'=>$row['precio']];
}
$stmt->close();

// insertar venta
$stmt = $conn->prepare("INSERT INTO ventas (usuario_id, total) VALUES (?, ?)");
$usuario_id = 1; // aquí tomarías el ID del usuario logueado
$stmt->bind_param("id", $usuario_id, $total);
$stmt->execute();
$venta_id = $stmt->insert_id;
$stmt->close();

// insertar detalle
$stmt = $conn->prepare("INSERT INTO venta_detalle (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
foreach($products as $pid => $p){
    $stmt->bind_param("iiid",$venta_id,$pid,$p['cantidad'],$p['precio']);
    $stmt->execute();
}
$stmt->close();

// vaciar carrito
unset($_SESSION['cart']);

echo "Compra realizada con éxito. Tu ID de pedido es: ".$venta_id;
