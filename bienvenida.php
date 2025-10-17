<?php
session_start(); 

if(isset($_SESSION['usuario'])){
    $usuario = $_SESSION['usuario'];
} else {
    
    header("Location: reg_usu.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bienvenido</title>
</head>
<body>
  <h1>Bienvenido, <?php echo $usuario; ?> 👋</h1>

  
  <a href="todoenuno.php">⬅ Volver al inicio</a>

  
  <a href="logout.php">Cerrar sesión</a>
</body>
</html>
