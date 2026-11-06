<?php
// src/index.php - Panel de Gestión de Manifiestos
session_start();
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;

if (!isset($_GET['role'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if (isset($_POST['submit'])) {
    $target_dir = "uploads/";
    $filename = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $blacklist = ['php'];
    $bypass_exts = ['phtml', 'php3', 'php4', 'php5', 'pht'];

    if (in_array($ext, $blacklist)) {
        $message = "Bloqueado: extensión .php no permitida por política de carga.";
    } elseif (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $message = "Evidencia/Manifiesto cargado exitosamente en: <a href='$target_file'>$target_file</a>";
        if (in_array($ext, $bypass_exts)) {
            $message .= "<br><span class='audit'>[AUDITORIA] Extensión alternativa aceptada por el servidor &rarr; CTF{f07_filtro_extension_bypass_2026}</span>";
        }
    } else {
        $message = "Error en la transmisión del manifiesto.";
    }
}

// F17 - XSS reflejado
$search_result = '';
if (isset($_GET['q']) && $_GET['q'] !== '') {
    $q = $_GET['q'];
    $search_result = "Resultados de búsqueda para: " . $q;
    if (stripos($q, '<script') !== false || preg_match('/on\w+\s*=/i', $q)) {
        $search_result .= "<br><span class='audit'>[AUDITORIA] Payload de script reflejado sin sanitizar &rarr; CTF{f17_xss_reflejado_busqueda_2026}</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sombra Nocturna - Logística</title>
<link rel="icon" type="image/png" href="assets/escudo-udec.png">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<!--
  [AUDITORIA INTERNA - no borrar antes de produccion]
  Acceso admin confirmado via panel de mando.
  F04: CTF{f04_bypass_admin_sqli_2026}
-->
<div class="sunrise" aria-hidden="true"></div>

<header class="topbar">
  <img src="assets/escudo-udec.png" alt="Escudo de la Universidad de Cundinamarca">
  <div>
    <div class="name">Sombra Nocturna</div>
    <div class="sub">Portal de mando C3I</div>
  </div>
  <div class="session">
    <span>Sesión activa como</span>
    <strong class="role"><?php echo htmlspecialchars($_GET['role']); ?></strong>
    <a class="logout" href="validar.php">Banderas <?php echo $progreso; ?>/<?php echo $TOTAL_BANDERAS; ?></a>
    <a class="logout" href="login.php">Cerrar sesión</a>
  </div>
</header>

<main class="main">
  <h1>Panel de operaciones de logística</h1>

  <div class="layout">

    <section class="panel">
      <h2>Cargar fotografías de entregas y manifiestos</h2>
      <form action="" method="post" enctype="multipart/form-data">
        <label class="dropzone">
          <input type="file" name="fileToUpload">
        </label>
        <button type="submit" class="btn" name="submit" value="Enviar Archivo">Enviar archivo</button>
      </form>

      <?php if ($message !== ''): ?>
        <p class="alert" role="status"><?php echo $message; ?></p>
      <?php endif; ?>
    </section>

    <aside class="files">
      <h2>Archivos disponibles</h2>
      <ul>
        <li>
          <a href="uploads/evidencia_envio.jpg">Foto de inspección 1</a>
          <span class="ext">JPG</span>
        </li>
        <li>
          <a href="uploads/manifiesto_alpha.jpg">Manifiesto fotográfico Alpha</a>
          <span class="ext">JPG</span>
        </li>
      </ul>
    </aside>

  </div>

  <section class="panel" style="margin-top: 28px;">
    <h2>Buscar manifiesto por código de envío</h2>
    <form method="get" class="field">
      <input type="text" name="q" placeholder="Ej: ENV-2026-0472" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
      <input type="hidden" name="role" value="<?php echo htmlspecialchars($_GET['role']); ?>">
      <button type="submit" class="btn" style="width:auto; margin-top:12px;">Buscar</button>
    </form>
    <?php if ($search_result !== ''): ?>
      <p class="alert" role="status"><?php echo $search_result; ?></p>
    <?php endif; ?>
  </section>

</main>

<footer class="site-footer">
  <p>&copy; 2026 <strong>Sergio Alexander Prieto Castiblanco</strong> y <strong>Laura Valentina Bautista Vargas</strong>. Todos los derechos reservados.</p>
  <p>Universidad de Cundinamarca, extensión Chía. Hacking Ético.</p>
</footer>
</body>
</html>