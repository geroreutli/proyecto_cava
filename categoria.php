<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "Cava_y_Oro");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("<p>Error de conexión a la base de datos: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categoría de Productos</title>
    <link rel="stylesheet" href="categoria.css">
</head>
<body>

<?php
echo '<p><a href="todoenuno.php" class="btn-volver">⬅ Volver al inicio</a></p>';

if (isset($_GET['id'])) {
    $categoria_id = intval($_GET['id']); // seguridad

    $sql = "SELECT i.*, c.nombre AS categoria
FROM inventario i
JOIN categorias c ON i.id_categoria = c.id
WHERE c.id = ?";


    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (mysqli_sql_exception $e) {
        echo "<p>Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Verifique que las tablas <strong>inventario</strong> y <strong>categorias</strong> existan en la base de datos.</p>";
        exit;
    }

    if ($result && $result->num_rows > 0) {
        $row_first = $result->fetch_assoc();
        echo "<h1>Productos de la categoría: " . htmlspecialchars($row_first['categoria']) . "</h1>";
        echo "<div class='productos'>";

        // detectar automáticamente la columna de nombre del producto
        $name_key = null;
        $candidates = ['producto','nombre','titulo','nombre_producto','name','producto_nombre'];
        foreach ($candidates as $k) {
            if (array_key_exists($k, $row_first) && trim((string)$row_first[$k]) !== '') {
                $name_key = $k;
                break;
            }
        }
        if ($name_key === null) {
            // buscar por patrón entre todas las columnas
            foreach ($row_first as $k => $v) {
                if (preg_match('/nombre|prod|title|name/i', $k) && trim((string)$v) !== '') {
                    $name_key = $k;
                    break;
                }
            }
        }

        // DEBUG opcional: descomenta para ver qué columnas vienen en la fila
        // echo '<pre>'.htmlspecialchars(print_r(array_keys($row_first), true)).'</pre>';

        $nombre_prod = $name_key ? $row_first[$name_key] : 'Sin nombre';
        echo "<div class='producto'>";
        echo "<h3>" . htmlspecialchars($nombre_prod) . "</h3>";
        echo "<p class='precio'>Precio: $" . htmlspecialchars($row_first['precio']) . "</p>";
        echo "<p>Stock: " . htmlspecialchars($row_first['stock']) . "</p>";
        echo "</div>";

        // restantes
        while ($row = $result->fetch_assoc()) {
            $nombre_prod = $name_key && array_key_exists($name_key, $row) && trim((string)$row[$name_key]) !== '' ? $row[$name_key]
                         : ($row['producto'] ?? $row['nombre'] ?? $row['titulo'] ?? 'Sin nombre');
            echo "<div class='producto'>";
            echo "<h3>" . htmlspecialchars($nombre_prod) . "</h3>";
            echo "<p class='precio'>Precio: $" . htmlspecialchars($row['precio']) . "</p>";
            echo "<p>Stock: " . htmlspecialchars($row['stock']) . "</p>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<p>No hay productos en esta categoría.</p>";
    }

    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
} else {
    echo "<p>No se seleccionó categoría.</p>";
}

$conn->close();
?>

</body>
</html>