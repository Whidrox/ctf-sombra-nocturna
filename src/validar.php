<?php
// src/validar.php - Validador de banderas del laboratorio Operación Sombra Nocturna
session_start();

// Hashes SHA-256 de las 18 banderas (no se guardan en texto plano).
$BANDERAS = [
    '9594aa0972bd5eac1e38f6d8f81ce3ead604d84977c5e6f4dac7e18f816689f4' => 'F01 — Reconocimiento pasivo (pcap)',
    '56960143e50072ef1d646b5860fbcb148df07394949c8b3e17676f4c031a815d' => 'F02 — Banner FTP',
    '5a2b98c99ee7a74615a7bed9795cdd25fcd469c3902ba7f6da1f42cfd20fb2b2' => 'F03 — Ruta oculta (robots.txt)',
    '5032f1894f16849fdb9b0dd8d83b8990527a9211991b12f1ce78ac04ab991325' => 'F04 — Bypass login (SQLi)',
    '5cb66132cf201bb477eae8b01cd93c3c80ac5feb04812c95a191cd53f06138ea' => 'F05 — UNION-based SQLi',
    '7728f27cb90cd82ec6688ac34eb0214c93551a3922d469554c724604b1f6f01c' => 'F06 — Metadatos EXIF',
    'a6445ec64a76443e40b14d21080a6b52b5648c2b8b20203c795680263cae5673' => 'F07 — Bypass de extensión',
    '60c2991e97195802055f168f44fcb510d87a2346111db483e3a216b3033ee792' => 'F08 — RCE confirmado',
    'acbb2c7876cae05d55addfe950c7ef2cc02b5673e6160e32668f6086320b96ab' => 'F09 — Bandera de usuario',
    '10aaeacfc8d49ba8d8229afcd8bb4fb4e3757b63a4424d75fade21de255c1465' => 'F10 — Fuga FTP (.env.bak)',
    'ba656b626c28f210d9f3e38bc00c5af2453c1ea6c68e65dc75055ea5c2c5857e' => 'F11 — Cron inseguro',
    '1c48d15cb2dafe0d9608d3bc19e37504ee136815d186fe5c8c89245fe2c1530c' => 'F12 — Variable de entorno',
    '67dfcabd546e445ee7c91573c2b055e57b0590d3a888e3444dbf5c21e0b3641a' => 'F13 — Binario SUID',
    'fc9e2135d0208a5c0162a3e97e2dc6c187e596f2eb4a0b73199e26ef4533997f' => 'F14 — Root vía SUID (vim)',
    '8029863cb9a6a66583b3344117dc0be0e13a20e6e3c3e43bf20ce88a449ce1f8' => 'F15 — Registro final (base64)',
    '0dea757d3c7fd3e3602b11d307dfee73fb4300289e07e4d538cdce96a63a8211' => 'F16 — SSH credenciales débiles',
    '26e946eadcc50a7839b9d73e11f7d9a3e6bc7db6f174b162713c27ba8dfd6e25' => 'F17 — XSS reflejado',
    'd3502c9b93a14f3083263ef81b15f40ec3252853a80d3842e626a68fcee6ac5a' => 'F18 — Escape socket Docker',
];

$TOTAL = count($BANDERAS);
if (!isset($_SESSION['encontradas'])) {
    $_SESSION['encontradas'] = [];
}

$feedback = '';
$feedback_ok = false;

if (isset($_POST['bandera'])) {
    $entrada = trim($_POST['bandera']);
    if ($entrada === '') {
        $feedback = 'Ingresa una bandera para validar.';
    } else {
        $hash = hash('sha256', $entrada);
        if (isset($BANDERAS[$hash])) {
            $reto = $BANDERAS[$hash];
            if (in_array($hash, $_SESSION['encontradas'])) {
                $feedback = "Bandera correcta ($reto), pero ya la habías registrado.";
                $feedback_ok = true;
            } else {
                $_SESSION['encontradas'][] = $hash;
                $feedback = "¡Bandera correcta! Reto resuelto: $reto";
                $feedback_ok = true;
            }
        } else {
            $feedback = 'Bandera incorrecta. Verifica el formato CTF{...} y vuelve a intentarlo.';
        }
    }
}

$hechas = count($_SESSION['encontradas']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sombra Nocturna - Validador de banderas</title>
<link rel="icon" type="image/png" href="assets/escudo-udec.png">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="sunrise" aria-hidden="true"></div>

<header class="topbar">
  <img src="assets/escudo-udec.png" alt="Escudo de la Universidad de Cundinamarca">
  <div>
    <div class="name">Sombra Nocturna</div>
    <div class="sub">Validador de banderas CTF</div>
  </div>
  <div class="session">
    <span>Progreso</span>
    <strong class="role"><?php echo $hechas; ?> / <?php echo $TOTAL; ?></strong>
    <a class="logout" href="javascript:history.back()">Volver</a>
    <a class="logout" href="login.php">Salir</a>
  </div>
</header>

<main class="main">
  <h1>Validación de banderas</h1>

  <section class="panel">
    <h2>Envía una bandera</h2>
    <form action="" method="post">
      <div class="field">
        <label for="bandera">Bandera</label>
        <input type="text" id="bandera" name="bandera" placeholder="CTF{...}" autocomplete="off">
      </div>
      <button type="submit" class="btn">Validar</button>
    </form>

    <?php if ($feedback !== ''): ?>
      <p class="<?php echo $feedback_ok ? 'alert' : 'error'; ?>" role="status">
        <?php echo htmlspecialchars($feedback); ?>
      </p>
    <?php endif; ?>

    <?php if ($hechas === $TOTAL): ?>
      <p class="alert" role="status">
        <span class="audit">Has resuelto las <?php echo $TOTAL; ?> banderas. Caso cerrado.</span>
      </p>
    <?php endif; ?>
  </section>

  <?php if ($hechas > 0): ?>
  <section class="panel" style="margin-top: 28px;">
    <h2>Retos resueltos</h2>
    <ul>
      <?php foreach ($_SESSION['encontradas'] as $h): ?>
        <li><?php echo htmlspecialchars($BANDERAS[$h]); ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <p>&copy; 2026 <strong>Sergio Alexander Prieto Castiblanco</strong> y <strong>Laura Valentina Bautista Vargas</strong>. Todos los derechos reservados.</p>
  <p>Universidad de Cundinamarca, extensión Chía. Hacking Ético.</p>
</footer>
</body>
</html>