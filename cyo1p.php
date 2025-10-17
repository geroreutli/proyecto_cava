<?php
// Archivo con la lógica PHP - Solo datos y funciones

function obtenerProductos() {
    return [
        ["nombre" => "Agua Mineral", "precio" => 1200, "imagen" => "img/agua.png"],
        ["nombre" => "Coca-Cola 1.5L", "precio" => 2500, "imagen" => "img/cocacola.png"],
        ["nombre" => "Fernet Branca 750ml", "precio" => 12000, "imagen" => "img/fernet.png"],
        ["nombre" => "Vino Malbec", "precio" => 4200, "imagen" => "img/vinomalbec.png"],
        ["nombre" => "Cerveza Heineken 1L", "precio" => 2200, "imagen" => "img/heineken.png"],
        ["nombre" => "Sprite 1.5L", "precio" => 2500, "imagen" => "img/sprite.png"],
        ["nombre" => "Red Bull", "precio" => 1900, "imagen" => "img/redbull.png"],
        ["nombre" => "Agua Saborizada", "precio" => 2200, "imagen" => "img/aguasaborizada.webp"],
    ];
}

function formatearPrecio($precio) {
    return number_format($precio, 2, ',', '.');
}

function generarUrlProducto($nombreProducto) {
    $nombreProductoUrl = urlencode($nombreProducto);
    return "producto.php?nombre={$nombreProductoUrl}";
}

// Obtener los productos para usar en el HTML
$productos = obtenerProductos();
?>
