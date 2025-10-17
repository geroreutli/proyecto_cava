<?php
// ...existing code...
$conn = new mysqli("localhost","root","", "cava_y_oro");
$conn->set_charset("utf8mb4");

session_start();

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "cava_y_oro");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Manejo del login (seguro: prepared statements + password_verify)
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $login_msg = "Por favor complete email y contraseña.";
    } else {
        $sql = "SELECT nombre, password FROM usuario WHERE email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // si la columna password está en texto plano en tu tabla, sigue funcionando:
                // preferible que esté hasheada; si lo está, usamos password_verify
                $hash = $row['password'];
                if (password_needs_rehash($hash, PASSWORD_DEFAULT) || password_verify($pass, $hash)) {
                    // Si estaba en texto plano, password_verify fallará; para compatibilidad:
                    if (!password_verify($pass, $hash)) {
                        // intentar comparar en texto plano (compatibilidad por si aún tienes contraseñas sin hash)
                        if ($pass !== $hash) {
                            $login_msg = "Email o contraseña incorrectos";
                            $stmt->close();
                        } else {
                            // contraseña en texto plano: migrar a hash
                            $new_hash = password_hash($pass, PASSWORD_DEFAULT);
                            $upd = $conn->prepare("UPDATE usuario SET password = ? WHERE email = ?");
                            if ($upd) {
                                $upd->bind_param("ss", $new_hash, $email);
                                $upd->execute();
                                $upd->close();
                                $hash = $new_hash;
                            }
                            session_regenerate_id(true);
                            $_SESSION['usuario'] = $row['nombre'];
                            $login_msg = "¡Bienvenido, " . $row['nombre'] . "!";
                            $stmt->close();
                        }
                    } else {
                        // login correcto con hash
                        session_regenerate_id(true);
                        $_SESSION['usuario'] = $row['nombre'];
                        $login_msg = "¡Bienvenido, " . $row['nombre'] . "!";
                        $stmt->close();
                    }
                } else {
                    // password_verify falló y no es texto plano
                    $login_msg = "Email o contraseña incorrectos";
                    $stmt->close();
                }
            } else {
                $login_msg = "Email o contraseña incorrectos";
                $stmt->close();
            }
        } else {
            $login_msg = "Error en la consulta de login.";
        }
    }
}

// Manejo del registro (seguro: prepared statements + password_hash)
if (isset($_POST['register'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || $pass === '') {
        $register_msg = "Complete todos los campos para registrarse.";
    } else {
        // comprobar existencia
        $sql_check = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql_check)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res_check = $stmt->get_result();
            if ($res_check->num_rows == 0) {
                $stmt->close();
                // insertar con hash
                $password_hash = password_hash($pass, PASSWORD_DEFAULT);
                $sql_ins = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
                if ($ins = $conn->prepare($sql_ins)) {
                    $ins->bind_param("sss", $nombre, $email, $password_hash);
                    if ($ins->execute()) {
                        session_regenerate_id(true);
                        $_SESSION['usuario'] = $nombre;
                        $register_msg = "Usuario registrado con éxito. ¡Bienvenido, $nombre!";
                    } else {
                        $register_msg = "Error al registrar usuario.";
                    }
                    $ins->close();
                } else {
                    $register_msg = "Error en la consulta de registro.";
                }
            } else {
                $register_msg = "El email ya está registrado";
                $stmt->close();
            }
        } else {
            $register_msg = "Error en la verificación de email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cava&Oro - Tienda Online</title>
  <link rel="stylesheet" href="cyocss.css">

</head>
<body>

<div class="layout">

  <!--barra lateral -->
  <div class="zona-hover"></div>
  <aside class="barra-lateral" id="barraLateral">
    <h2>Categorías</h2>
    <nav class="menu-categorias">
      <ul>
        <li><a href="categoria.php?id=1">🍷 Vinos</a></li>
        <li><a href="categoria.php?id=2">🍺 Cervezas</a></li>
        <li><a href="categoria.php?id=3">🥤 Gaseosas</a></li>
        <li><a href="categoria.php?id=4">💧 Aguas</a></li>
        <li><a href="categoria.php?id=5">🥃 Destilados</a></li>
        <li><a href="categoria.php?id=6">🍿 Snacks</a></li>
      </ul>
    </nav>
  </aside>

  
  <div class="contenido">

  
    <header class="header">
      <div class="logo">
        <a href="todoenuno.php">
          <img src="img/cavayoro.jpeg" alt="Logo de Cava&Oro">
        </a>
      </div>
      <div class="buscador">
        <form action="buscar.php" method="GET">
  <input type="text" name="query" placeholder="Buscar productos..." required>
  <button type="submit">Buscar</button>
</form>
      </div>
      <div class="acciones-header">
        <button id="toggle-theme">🌙</button>

        <?php if(isset($_SESSION['usuario'])): ?>
          <span>👤 Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
          <a href="logout.php" class="btn-header">Cerrar sesión</a>
        <?php else: ?>
          <!-- Usar <button> para que respete exactamente el estilo de los demás botones -->
          <button id="btn-login" class="btn-header" type="button" onclick="window.location.href='login.php'">👤 Cuenta</button>
        <?php endif; ?>

        <button class="btn-header" onclick="window.location.href='carrito.php'">🛒 Carrito</button>

      </div>
    </header>

    <main>
      <section class="bienvenida">
        <h1>Bienvenidos a Cava&Oro 🍷</h1>
        <p>Descubrí nuestros productos destacados, promociones y más.</p>
        <p>Explorá todas nuestras categorías desde la barra lateral izquierda.</p>
        <?php if(!empty($login_msg)): ?>
          <p class="msg-login"><?php echo htmlspecialchars($login_msg); ?></p>
        <?php endif; ?>
        <?php if(!empty($register_msg)): ?>
          <p class="msg-register"><?php echo htmlspecialchars($register_msg); ?></p>
        <?php endif; ?>
      </section>

      <section class="slider">
        <div class="slides">
          <div class="slide active">
            <img src="img/escabio0.jpg" alt="Foto 1">
          </div>
          <div class="slide">
            <img src="img/escabio1.jpg" alt="Foto 2">
          </div>
        </div>
        <button class="prev">❮</button>
        <button class="next">❯</button>
      </section>

      <section class="confianza">
        <h2>Quiénes somos</h2>
        <div class="confianza-grid">
          <div class="confianza-item">
            <img src="img/calidad.png" alt="Calidad">
            <h3>Productos de Calidad</h3>
            <p>Seleccionamos cuidadosamente cada producto para garantizarte la mejor experiencia.</p>
          </div>
          <div class="confianza-item">
            <img src="img/5años.png" alt="Experiencia">
            <h3>5 años de experiencia</h3>
            <p>Somos expertos en bebidas y tenemos amplia trayectoria en el mercado.</p>
          </div>
          <div class="confianza-item">
            <img src="img/comprasegura.png" alt="Seguridad">
            <h3>Compra segura</h3>
            <p>Tu información y tus pagos están protegidos con la más alta seguridad.</p>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="footer-info">
        <p>&copy; 2025 Cava&Oro - Todos los derechos reservados</p>
        <p>Métodos de pago: 💳 💵 🍷</p>
        <p>Seguinos en redes: 📱</p>
      </div>
      <span id="fecha-hora"></span>
    </footer>
  </div>
</div>
<?php if(!isset($_SESSION['usuario'])): ?>
<?php endif; ?>

<script src="cyojs.js"></script>
</body>
</html>
<?php
