<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO Usuario (nombre, email, contrasena) VALUES ('$nombre', '$email', '$password')";    
    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado con éxito.";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
