<?php
// src/validar.php - Validador de banderas del laboratorio Operación Sombra Nocturna
session_start();
require_once 'ui.php';

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
$resultado = '';      // solo para animaciones: new | dup | bad | empty
$codigo_nuevo = '';

if (isset($_POST['bandera'])) {
    $entrada = trim($_POST['bandera']);
    if ($entrada === '') {
        $feedback = 'Ingresa una bandera para validar.';
        $resultado = 'empty';
    } else {
        $hash = hash('sha256', $entrada);
        if (isset($BANDERAS[$hash])) {
            $reto = $BANDERAS[$hash];
            if (in_array($hash, $_SESSION['encontradas'])) {
                $feedback = "Bandera correcta ($reto), pero ya la habías registrado.";
                $feedback_ok = true;
                $resultado = 'dup';
            } else {
                $_SESSION['encontradas'][] = $hash;
                $feedback = "¡Bandera correcta! Reto resuelto: $reto";
                $feedback_ok = true;
                $resultado = 'new';
                $codigo_nuevo = substr($reto, 0, 3);
            }
        } else {
            $feedback = 'Bandera incorrecta. Verifica el formato CTF{...} y vuelve a intentarlo.';
            $resultado = 'bad';
        }
    }
}

$hechas = count($_SESSION['encontradas']);

// Códigos ya resueltos (F01, F02...) para pintar el tablero
$resueltos = [];
foreach ($_SESSION['encontradas'] as $h) {
    if (isset($BANDERAS[$h])) { $resueltos[substr($BANDERAS[$h], 0, 3)] = true; }
}
$rango = sn_rango($hechas);
$completo = ($hechas === $TOTAL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<?php sn_head('Sombra Nocturna — Tablero de misiones'); ?>
</head>
<body data-result="<?php echo $resultado; ?>" data-complete="<?php echo $completo ? '1' : '0'; ?>">
<?php sn_fondo(); ?>

<header class="topbar">
  <?php sn_marca('Validador de banderas CTF'); ?>
  <span class="hud-clock">⏱ <b data-clock>--:--:--</b></span>
  <nav class="nav">
    <span class="agent-chip"><span class="dot"></span><?php echo $rango[1] . ' ' . htmlspecialchars($rango[0]); ?></span>
    <a href="javascript:history.back()">↩ Volver</a>
    <a href="info.php">📁 Expediente</a>
    <?php sn_sonido_btn(); ?>
    <a href="login.php">⏻ Salir</a>
  </nav>
</header>

<main class="main">
  <h1 class="page-title">Tablero de misiones</h1>
  <p class="page-kicker">&gt; <span class="typed" data-type="Cada bandera es una pieza de evidencia. Reúne las <?php echo $TOTAL; ?> y el líder caerá."></span></p>

  <section class="hud">
    <div class="hq">
      <?php sn_anillo($hechas, $TOTAL, true); ?>

      <div>
        <h2 class="hud-title"><span class="emoji">🚩</span> Envía una bandera <span class="tag">VALIDADOR</span></h2>
        <form action="" method="post" class="terminal-form">
          <div class="input-wrap">
            <span class="ico">⌨️</span>
            <label for="bandera" class="sr-only" style="position:absolute;left:-9999px;">Bandera</label>
            <input type="text" id="bandera" name="bandera" placeholder="CTF{...}" autocomplete="off" autofocus>
          </div>
          <button type="submit" class="btn btn-hot">Validar</button>
        </form>

        <div class="agent-row" style="margin-top:18px;">
          <div class="mini-agent" id="val-agent" data-agent data-bubble="#val-bubble" data-state="<?php echo $resultado === 'new' ? 'approved' : 'idle'; ?>">
            <?php sn_agente(); ?>
          </div>
          <div class="bubble<?php echo $resultado === '' ? '' : ($feedback_ok ? ' good' : ' bad'); ?>" id="val-bubble" role="status">
            <span class="who">Agente 007 · Validación</span>
            <span data-say><?php
              if ($feedback !== '') {
                  echo htmlspecialchars($feedback);
              } else {
                  echo 'Pega aquí cada evidencia CTF{...} que encuentres. Yo la verifico. 🕵️';
              }
            ?></span>
          </div>
        </div>
      </div>

      <section class="wanted" style="--blur:<?php echo round(12 * (1 - $hechas / $TOTAL), 1); ?>px;--gray:<?php echo round(1 - $hechas / $TOTAL, 2); ?>;--pct:<?php echo round($hechas / $TOTAL * 100); ?>%">
        <h3>SE BUSCA</h3>
        <div class="photo"><?php sn_sospechoso(); ?></div>
        <?php if ($completo): ?><div class="caught">CAPTURADO</div><?php endif; ?>
        <div class="sub">IDENTIFICACIÓN: <?php echo round($hechas / $TOTAL * 100); ?>%</div>
        <div class="meter"><i></i></div>
      </section>
    </div>

    <?php if ($completo): ?>
      <p class="alert ok" role="status">
        <span class="audit">Has resuelto las <?php echo $TOTAL; ?> banderas. Caso cerrado.</span>
      </p>
    <?php endif; ?>
  </section>

  <?php $i = 0; foreach ($SN_FASES as $fase):
    $hechas_fase = 0;
    foreach ($fase['retos'] as $r) { if (isset($resueltos[$r[0]])) $hechas_fase++; }
  ?>
  <section class="phase">
    <div class="phase-head">
      <h2><?php echo $fase['icono'] . ' ' . htmlspecialchars($fase['titulo']); ?></h2>
      <span class="count">[<?php echo $hechas_fase; ?>/<?php echo count($fase['retos']); ?>]</span>
      <span class="muted"><?php echo htmlspecialchars($fase['intro']); ?></span>
    </div>
    <div class="mission-grid">
      <?php foreach ($fase['retos'] as $r):
        $ok = isset($resueltos[$r[0]]);
        $clases = 'mission' . ($ok ? ' solved' : '') . ($r[0] === $codigo_nuevo ? ' just-solved' : '');
      ?>
      <article class="<?php echo $clases; ?>" style="--i:<?php echo $i++; ?>">
        <div class="m-top">
          <span class="m-code"><?php echo $r[0]; ?></span>
          <span class="m-tech"><?php echo htmlspecialchars($r[1]); ?></span>
        </div>
        <h3>Operación «<?php echo htmlspecialchars($r[2]); ?>»</h3>
        <p>El <b>agente 007</b> está en busca de <?php echo htmlspecialchars($r[3]); ?></p>
        <span class="m-status"><?php echo $ok ? 'EVIDENCIA ASEGURADA' : 'EN CURSO…'; ?></span>
        <?php if ($ok): ?><span class="m-stamp">RESUELTO</span><?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</main>

<div class="overlay" id="evidence-ok" role="dialog" aria-label="Evidencia confirmada">
  <div class="overlay-box" data-agent data-state="idle">
    <?php sn_agente(); ?>
    <h2>¡EVIDENCIA CONFIRMADA!</h2>
    <p><?php echo htmlspecialchars($feedback); ?></p>
    <span class="stamp green"><?php echo htmlspecialchars($codigo_nuevo); ?> · RESUELTO</span>
    <p class="hint">(clic para continuar)</p>
  </div>
</div>

<div class="overlay" id="case-closed" role="dialog" aria-label="Caso cerrado">
  <div class="overlay-box" data-agent data-state="idle">
    <?php sn_agente(); ?>
    <h2>¡CASO CERRADO!</h2>
    <p>El líder de Sombra Nocturna ha sido capturado. Reuniste las <?php echo $TOTAL; ?> evidencias.</p>
    <span class="stamp">CAPTURADO</span>
    <p class="hint">Rango final: 🏆 Leyenda · (clic para continuar)</p>
  </div>
</div>

<?php sn_pie(); ?>
</body>
</html>