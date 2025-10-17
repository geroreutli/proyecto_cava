<?php
$servername = "localhost";
$username = "root"; // el usuario por defecto en XAMPP/MAMP
$password = "";     // si no le pusiste clave, dejalo vacío
$database = "Cava_y_Oro"; // el nombre exacto de tu base

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: configurar UTF-8
$conn->set_charset("utf8mb4");
?>