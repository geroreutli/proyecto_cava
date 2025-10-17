<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', 0);

$conn = new mysqli("localhost", "root", "", "cava_y_oro");
if ($conn->connect_error) { http_response_code(500); exit('Conexión fallida'); }
$conn->set_charset('utf8mb4');

/**
 * Búsqueda robusta: detecta si tus productos están en 'productos' o 'inventario'
 * y mapea automáticamente las columnas (nombre, descripcion, precio, imagen, stock).
 */

$queryRaw = trim((string)($_GET['query'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$productos = [];
$total = 0;
$terms = [];

// detectar tabla con datos de productos
$tablesToCheck = ['productos','inventario','items','producto','productos_tienda'];
$table = null;
foreach ($tablesToCheck as $t) {
    $r = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($t)."'");
    if ($r && $r->num_rows > 0) { $table = $t; $r->close(); break; }
    if ($r) $r->close();
}
if ($table === null) {
    // si no encontró tabla estándar, intentar cualquier tabla que contenga 'prod' en el nombre
    $r = $conn->query("SHOW TABLES");
    while ($r && ($row = $r->fetch_row())) {
        if (stripos($row[0], 'prod') !== false) { $table = $row[0]; break; }
    }
    if ($r) $r->close();
}

if ($table === null) {
    // nothing to search
    $error = "No se encontró tabla de productos (busqué: productos, inventario, items...).";
} else {
    // obtener columnas y elegir mapeo
    $colsRes = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($table)."`");
    $cols = [];
    while ($colsRes && ($c = $colsRes->fetch_assoc())) $cols[] = $c['Field'];
    if ($colsRes) $colsRes->close();

    // candidatos para cada campo
    $nameKey = null; $descKey = null; $priceKey = null; $imgKey = null; $stockKey = null; $idKey = null;
    foreach ($cols as $c) {
        $lc = strtolower($c);
        if (!$idKey && in_array($lc, ['id','id_producto','producto_id','pk'])) $idKey = $c;
        if (!$nameKey && preg_match('/^nombre$|producto|titulo|title|name/i', $c)) $nameKey = $c;
        if (!$descKey && preg_match('/desc|descripcion|detalle|details/i', $c)) $descKey = $c;
        if (!$priceKey && preg_match('/precio|price|cost/i', $c)) $priceKey = $c;
        if (!$imgKey && preg_match('/imagen|img|image|foto/i', $c)) $imgKey = $c;
        if (!$stockKey && preg_match('/stock|cantidad|qty|existencia/i', $c)) $stockKey = $c;
    }
    // asegurar defaults mínimos
    $idKey = $idKey ?? $cols[0] ?? 'id';
    $nameKey = $nameKey ?? ($cols[1] ?? null);
    $descKey = $descKey ?? null;
    $priceKey = $priceKey ?? null;
    $imgKey = $imgKey ?? null;
    $stockKey = $stockKey ?? null;

    // preparar terms
    if ($queryRaw !== '') {
        $queryNorm = preg_replace('/\s+/', ' ', $queryRaw);
        $parts = preg_split('/\s+/', $queryNorm);
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '' && mb_strlen($p) >= 2) $terms[] = $p;
        }
    }

    if (!empty($terms)) {
        // columnas para buscar (usar las que existen)
        $searchCols = [];
        if ($nameKey) $searchCols[] = $nameKey;
        if ($descKey) $searchCols[] = $descKey;
        if (empty($searchCols)) $searchCols[] = $nameKey ?? $cols[0];

        // construir WHERE dinámico con LIKE (AND entre términos, OR entre columnas)
        $whereParts = [];
        $params = [];
        $types = '';
        foreach ($terms as $t) {
            $sub = [];
            $like = '%'.$t.'%';
            foreach ($searchCols as $c) {
                $sub[] = "`$c` LIKE ?";
                $params[] = $like;
                $types .= 's';
            }
            $whereParts[] = '(' . implode(' OR ', $sub) . ')';
        }
        $where = implode(' AND ', $whereParts);

        // total
        $sqlCount = "SELECT COUNT(*) FROM `".$conn->real_escape_string($table)."` WHERE $where";
        $stmt = $conn->prepare($sqlCount);
        $refs = []; $refs[] = & $types;
        for ($i=0;$i<count($params);$i++) $refs[] = & $params[$i];
        call_user_func_array([$stmt, 'bind_param'], $refs);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        // select
        $selectCols = array_filter([$idKey, $nameKey, $descKey, $priceKey, $imgKey, $stockKey]);
        $selectSQL = implode(', ', array_map(function($c){ return "`$c`"; }, $selectCols));
        $sql = "SELECT $selectSQL FROM `".$conn->real_escape_string($table)."` WHERE $where ORDER BY `$idKey` ASC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        $types2 = $types . 'ii';
        $params[] = $perPage;
        $params[] = $offset;
        $refs = []; $refs[] = & $types2;
        for ($i=0;$i<count($params);$i++) $refs[] = & $params[$i];
        call_user_func_array([$stmt, 'bind_param'], $refs);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            // normalizar keys a los nombres esperados por la UI
            $productos[] = [
                'id' => $r[$idKey] ?? null,
                'nombre' => $r[$nameKey] ?? ($r[$cols[0]] ?? ''),
                'descripcion' => $r[$descKey] ?? '',
                'precio' => $r[$priceKey] ?? 0,
                'imagen' => $r[$imgKey] ?? '',
                'stock' => $r[$stockKey] ?? 0,
            ];
        }
        $stmt->close();
    }
} // end table detected

// helper resaltado
function highlight($text, $terms) {
    if (empty($terms)) return htmlspecialchars($text);
    $escaped = htmlspecialchars($text);
    foreach ($terms as $t) {
        $tEsc = preg_quote($t, '/');
        $escaped = preg_replace_callback("/($tEsc)/iu", function($m){ return '<mark>'.$m[0].'</mark>'; }, $escaped);
    }
    return $escaped;
}

$pages = ($total > 0) ? (int)ceil($total / $perPage) : 1;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Búsqueda: <?php echo htmlspecialchars($queryRaw); ?></title>
  <link rel="stylesheet" href="cyocss.css">
</head>
<body>
  <div class="layout">
    <div class="contenido">
      <p><a href="todoenuno.php" class="btn-volver">⬅ Volver</a></p>
      <h1>Resultados de búsqueda</h1>

      <?php if (isset($error)): ?>
        <p style="color:#c00"><?php echo htmlspecialchars($error); ?></p>
        <p>Ejecuta en phpMyAdmin: <code>SHOW TABLES</code> y <code>DESCRIBE nombre_tabla</code></p>
      <?php else: ?>
        <form action="buscar.php" method="get" style="margin-bottom:12px">
          <input type="search" name="query" value="<?php echo htmlspecialchars($queryRaw); ?>" placeholder="Buscar productos..." required style="width:60%;padding:8px">
          <button type="submit">Buscar</button>
        </form>

        <?php if ($queryRaw === ''): ?>
          <p>Ingrese un término de búsqueda.</p>
        <?php elseif (empty($productos)): ?>
          <p>No se encontraron productos para "<?php echo htmlspecialchars($queryRaw); ?>".</p>
          <p>Tabla usada: <?php echo htmlspecialchars($table ?? 'n/a'); ?> — Total encontrados: <?php echo (int)$total; ?></p>
        <?php else: ?>
          <p>Resultados: <?php echo (int)$total; ?> — Página <?php echo $page; ?> / <?php echo $pages; ?></p>
          <div class="productos-grid">
            <?php foreach ($productos as $p): ?>
              <article class="producto-card">
                <?php if (!empty($p['imagen'])): ?>
                  <img src="<?php echo htmlspecialchars($p['imagen']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                <?php endif; ?>
                <h3><?php echo highlight($p['nombre'], $terms); ?></h3>
                <p class="descripcion"><?php echo highlight(mb_strimwidth($p['descripcion'], 0, 200, '...'), $terms); ?></p>
                <p class="precio">$<?php echo number_format((float)$p['precio'],2); ?></p>
                <p class="stock"><?php echo ((int)$p['stock'] > 0) ? "Stock: ".(int)$p['stock'] : "<span style='color:#c00'>Agotado</span>"; ?></p>

                <?php if ((int)$p['stock'] > 0): ?>
                  <form method="post" action="add_to_cart.php" class="add-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$p['stock']; ?>" class="qty-input">
                    <button type="submit" class="btn-add-cart">Agregar al carrito</button>
                  </form>
                <?php else: ?>
                  <button class="btn-add-cart" disabled>Agotado</button>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>

          <div class="pagination">
            <?php if ($page > 1): ?>
              <a href="?query=<?php echo urlencode($queryRaw); ?>&page=<?php echo $page-1; ?>" class="btn-header">← Anterior</a>
            <?php endif; ?>
            <span>Página <?php echo $page; ?> de <?php echo $pages; ?></span>
            <?php if ($page < $pages): ?>
              <a href="?query=<?php echo urlencode($queryRaw); ?>&page=<?php echo $page+1; ?>" class="btn-header">Siguiente →</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
