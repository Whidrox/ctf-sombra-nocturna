<?php
// src/login.php - Formulario con SQL Injection clásica en el campo usuario
session_start();
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;

require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABILIDAD CRÍTICA: Concatenación directa sin sanitizar (SQLi)
    $query = "SELECT * FROM usuarios WHERE usuario = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header("Location: index.php?role=" . $row['rol']);
        exit();
    } else {
        $message = "Credenciales inválidas. Intento registrado por la red.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sombra Nocturna - Portal de Mando</title>
<link rel="icon" type="image/png" href="assets/escudo-udec.png">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="sunrise" aria-hidden="true"></div>

<main class="auth">
  <div class="auth-card">

    <section class="auth-intro">
      <img class="crest" src="assets/escudo-udec.png" alt="Escudo de la Universidad de Cundinamarca">
      <h1>Sombra Nocturna</h1>
      <p class="subtitle">Portal de mando C3I</p>
    </section>

    <section class="auth-form">
      <h2>Acceso operativo</h2>
      <p class="help">Ingresa con el usuario y la clave asignados a tu unidad.</p>

      <form method="POST">
        <div class="field">
          <label for="username">Usuario operativo</label>
          <input type="text" id="username" name="username" placeholder="Usuario operativo" autocomplete="username">
        </div>
        <div class="field">
          <label for="password">Clave de encriptación</label>
          <input type="password" id="password" name="password" placeholder="Clave de encriptación" autocomplete="current-password">
        </div>
        <button type="submit" class="btn">Ingresar al sistema</button>
      </form>

      <?php if ($message !== ''): ?>
        <p class="error" role="alert"><?php echo $message; ?></p>
      <?php endif; ?>
    </section>

  </div>
</main>

<footer class="site-footer">
  <p>&copy; 2026 <strong>Sergio Alexander Prieto Castiblanco</strong> y <strong>Laura Valentina Bautista Vargas</strong>. Todos los derechos reservados.</p>
  <p>Universidad de Cundinamarca, extensión Chía. Hacking Ético.</p>
</footer>

<a href="validar.php" style="position:fixed;right:20px;bottom:20px;z-index:50;padding:12px 18px;background:#38bdf8;color:#04121f;font-weight:700;border-radius:6px;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,.4);">Validar banderas (<?php echo $progreso; ?>/<?php echo $TOTAL_BANDERAS; ?>)</a>

</body>
</html>