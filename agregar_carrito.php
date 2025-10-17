<?php
session_start();

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$precio = $_POST['precio'];

$found = false;
foreach ($_SESSION['carrito'] as &$item) {
    if ($item['id'] == $id) {
        $item['cantidad']++;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['carrito'][] = [
        'id' => $id,
        'nombre' => $nombre,
        'precio' => $precio,
        'cantidad' => 1
    ];
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
