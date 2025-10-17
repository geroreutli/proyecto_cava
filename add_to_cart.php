<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión
$conn = new mysqli("localhost", "root", "", "cava_y_oro");
if ($conn->connect_error) die("Conexión fallida");
$conn->set_charset('utf8mb4');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($product_id <= 0) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'todoenuno.php'));
    exit;
}

// comprobar que el producto exista
$stmt = $conn->prepare("SELECT id FROM productos WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'todoenuno.php'));
        exit;
    }
    $stmt->close();
}

// inicializar carrito en sesión
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// sumar cantidad si ya existe
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// volver a la página anterior
$back = $_SERVER['HTTP_REFERER'] ?? 'todoenuno.php';
header("Location: $back");
exit;
?>