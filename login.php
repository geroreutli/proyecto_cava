<?php
// Evitar notice si ya hay sesión
if (session_status() === PHP_SESSION_NONE) session_start();

include 'conexion.php';

$info_msg = '';
$error_msg = '';
$open_tab = 'login'; // pestaña por defecto

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $open_tab = $action === 'register' ? 'register' : 'login';

    if ($action === 'register') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $pass   = $_POST['password'] ?? '';

        if ($nombre === '' || $email === '' || $pass === '') {
            $error_msg = 'Complete todos los campos.';
        } else {
            $sql_check = "SELECT COUNT(*) FROM usuario WHERE email = ?";
            if ($stmt = $conn->prepare($sql_check)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($cnt);
                $stmt->fetch();
                $stmt->close();

                if ($cnt == 0) {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $sql_ins = "INSERT INTO usuario (nombre, email, password) VALUES (?, ?, ?)";
                    if ($ins = $conn->prepare($sql_ins)) {
                        $ins->bind_param("sss", $nombre, $email, $hash);
                        if ($ins->execute()) {
                            session_regenerate_id(true);
                            $_SESSION['usuario'] = $nombre;
                            $_SESSION['role'] = 'user'; // por defecto
                            $ins->close();
                            header('Location: todoenuno.php');
                            exit;
                        } else {
                            $error_msg = 'Error al registrar usuario.';
                            $ins->close();
                        }
                    } else {
                        $error_msg = 'Error en la consulta de registro.';
                    }
                } else {
                    $error_msg = 'El email ya está registrado.';
                }
            } else {
                $error_msg = 'Error en la verificación de email.';
            }
        }
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($email === '' || $pass === '') {
            $error_msg = 'Complete email y contraseña.';
        } else {
            // ahora seleccionamos role
            $sql = "SELECT nombre, password, role FROM usuario WHERE email = ? LIMIT 1";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($nombre_db, $hash, $role_db);
                if ($stmt->fetch()) {
                    if (password_verify($pass, $hash) || $pass === $hash) {
                        if (!password_verify($pass, $hash)) {
                            $new_hash = password_hash($pass, PASSWORD_DEFAULT);
                            if ($upd = $conn->prepare("UPDATE usuario SET password = ? WHERE email = ?")) {
                                $upd->bind_param("ss", $new_hash, $email);
                                $upd->execute();
                                $upd->close();
                            }
                        }
                        session_regenerate_id(true);
                        $_SESSION['usuario'] = $nombre_db;
                        $_SESSION['role'] = $role_db ?? 'user';
                        $stmt->close();
                        header('Location: todoenuno.php');
                        exit;
                    } else {
                        $error_msg = 'Email o contraseña incorrectos.';
                    }
                } else {
                    $error_msg = 'Email o contraseña incorrectos.';
                }
                $stmt->close();
            } else {
                $error_msg = 'Error en la consulta de login.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cuenta de Usuario</title>
  <link rel="stylesheet" href="reg_usu.css">
</head>
<body>

<div class="auth-container">
  <a href="todoenuno.php" class="btn-volver">⬅ Volver al inicio</a>

  <h2>Cuenta de Usuario</h2>

  <div class="tabs">
    <button class="tab-link <?php echo $open_tab === 'login' ? 'active' : '' ?>" data-tab="login">Iniciar Sesión</button>
    <button class="tab-link <?php echo $open_tab === 'register' ? 'active' : '' ?>" data-tab="register">Registrarse</button>
  </div>

  <?php if ($error_msg): ?>
    <div class="msg error"><?php echo htmlspecialchars($error_msg); ?></div>
  <?php endif; ?>
  <?php if ($info_msg): ?>
    <div class="msg info"><?php echo htmlspecialchars($info_msg); ?></div>
  <?php endif; ?>

  <div id="login" class="tab-content <?php echo $open_tab === 'login' ? 'active' : '' ?>">
    <form action="login.php" method="POST" autocomplete="off">
      <input type="hidden" name="action" value="login">
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit">Iniciar Sesión</button>
    </form>
  </div>

  <div id="register" class="tab-content <?php echo $open_tab === 'register' ? 'active' : '' ?>">
    <form action="login.php" method="POST" autocomplete="off">
      <input type="hidden" name="action" value="register">
      <input type="text" name="nombre" placeholder="Nombre completo" required>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="submit">Registrarse</button>
    </form>
  </div>
</div>



<script>
const tabLinks = document.querySelectorAll(".tab-link");
const tabContents = document.querySelectorAll(".tab-content");
tabLinks.forEach(link => {
  link.addEventListener("click", () => {
    tabLinks.forEach(l => l.classList.remove("active"));
    tabContents.forEach(c => c.classList.remove("active"));
    link.classList.add("active");
    document.getElementById(link.dataset.tab).classList.add("active");
  });
});
</script>

</body>
</html>