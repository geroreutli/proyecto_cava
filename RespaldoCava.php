<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cava&Oro - Tienda Online</title>
  <link rel="stylesheet" href="estilorespaldo.css">  
</head>
<body>
<!-- Barra lateral-->
<div class="zona-hover"></div>
<div class="layout">
  <div class="barra-lateral" id="barraLateral">
    <h2>Nuestros productos más vendidos</h2>
    <?php
      $productos = [
          ["nombre" => "Agua Mineral", "precio" => 1200, "imagen" => "img/agua.png"],
          ["nombre" => "Coca-Cola 1.5L", "precio" => 2500, "imagen" => "img/cocacola.png"],
          ["nombre" => "Fernet Branca 750ml", "precio" => 12000, "imagen" => "img/fernet.png"],
          ["nombre" => "Vino Malbec", "precio" => 4200, "imagen" => "img/vinomalbec.png"],
          ["nombre" => "Cerveza Heineken 1L", "precio" => 2200, "imagen" => "img/heineken.png"],
          ["nombre" => "Sprite 1.5L", "precio" => 2500, "imagen" => "img/sprite.png"],
          ["nombre" => "Red Bull", "precio" => 1900, "imagen" => "img/redbull.png"],
          ["nombre" => "Agua Saborizada", "precio" => 2200, "imagen" => "img/aguasaborizada.webp"],
      ];

      foreach ($productos as $producto):
          $nombreProductoUrl = urlencode($producto['nombre']);
          $link = "producto.php?nombre={$nombreProductoUrl}";
    ?>
          <div class="producto">
            <img src="<?= $producto['imagen'] ?>" alt="Imagen de <?= $producto['nombre'] ?>">
            <h3><?= $producto['nombre'] ?></h3>
            <p>$<?= number_format($producto['precio'], 2, ',', '.') ?></p>
          </div>
    <?php endforeach; ?>
  </div>

  <!-- Contenido principal -->
  <div class="contenido">
    <header>
      <div class="header-content">
        <button id="toggle-theme">Cambiar tema</button>
        <a href="RespaldoCava.php">
          <img src="img/cavayoro.jpeg" alt="Logo de Cava&Oro" class="header-img">
        </a>
        <button class="btn-header" onclick="alert('Próximamente Carrito de Compras')">Carrito</button>
      </div>
      <div class="cuadro-busqueda">
  <form action="buscar.php" method="GET">
    <input type="text" name="query" placeholder="Buscar productos..." required>
    <button type="submit">Buscar</button>
  </form>
</div>
    </header>

    <main>
      <p>Bienvenidos a nuestra tienda online. Descubrí nuestros productos destacados, promociones y más.</p>
      <p>Podés recorrer todas nuestras categorías desde la barra lateral izquierda.</p>
      <p>Gracias por elegirnos 🤝</p>
      <button>
      <img src="img/sale.png" alt="Ofertas" width="60" height="60">
</button>
<br>
<button>
  <img src="img/atencion.jpg" alt="Atención al cliente" width="60" height="60">
</button>
    </main>

    <footer>
      <span id="fecha-hora"></span>
    </footer>
  </div>
</div>
<script src="scriptrespaldo.js"></script>
</body>
</html>
