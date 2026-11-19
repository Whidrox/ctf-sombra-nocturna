<?php
// src/validar.php - Validador de banderas del laboratorio Operación Sombra Nocturna
session_start();
require_once 'ui.php';

// Hashes SHA-256 de las 18 banderas (no se guardan en texto plano).
$BANDERAS = [
    '5d915caa9d6b20182069d4b37f7ab213b0bc8c345bcfab66f6d8a0fe34319c46' => 'F01 — Reconocimiento pasivo (pcap)',
    'a44d9464b0039722c24bca004b9c6c59c05445458bc0de024328451481b4d1d2' => 'F02 — Banner FTP',
    '6b508a5fc94c2f50f0db64c9dffbe100976e20b6ba24b5128b077382703c9dd9' => 'F03 — Ruta oculta (robots.txt)',
    '0ce359c5fb54833de63aaf2cf3c5c5f49edb4dfabe1dfb9a019c07a5e5cb3ff9' => 'F04 — Bypass login (SQLi)',
    'd784dd938661c4219becf616b8a941f69a7a70c26037a8ef9441857b7e393dcf' => 'F05 — UNION-based SQLi',
    'be24d7fa2c625ee9267fddad317862a7f37bfb8d2a13aab8ee542a5ee4bb9ef9' => 'F06 — Metadatos EXIF',
    '1d69460511382c33df8e72d880134f9b7b0fa954db8656cdde6cbecc2dc010da' => 'F07 — Bypass de extensión',
    'ffc051f7a3ea4301049affbf585412fef87d3de75438ec26f27a78453b6967b6' => 'F08 — RCE confirmado',
    '92394f271f0fdc7786a5f2f926d33a1249cd7df4bde43af8890917092a1c630c' => 'F09 — Bandera de usuario',
    'c0755843a7b5119c449f4b623f956feb0d16a715af9910ddfeb32a62d8ee244e' => 'F10 — Fuga FTP (.env.bak)',
    'be42eefad0e4e933753a295030a09d2e7494e81f1bd24a5ba0c63e41a383fb70' => 'F11 — Cron inseguro',
    'be1c75d093817013883edfe703af8116e6d903d84915733d2f7bd67519031ba7' => 'F12 — Variable de entorno',
    '32ddf010de4db31aaa64bdcce362f65fc6debeccecf2cb1e8923c3c2e268e998' => 'F13 — Binario SUID',
    '101cb9e9525cc1abf0c9d74389b502707960f08bfe0b33e11bd3d7baa9d22b3c' => 'F14 — Root vía SUID (vim)',
    '5915be5058c8c16561e94e0366538fcac004863fdc3e26ab277f3e7ca13b1b67' => 'F15 — Registro final (base64)',
    '9e6e2e97ade333d1def51309430e0baa3f6cb6b882b39bc2c03474c83efae8e0' => 'F16 — SSH credenciales débiles',
    '481f26629a1436721717fbaae9e8607f3da43360e65510f3c951cf011fa90820' => 'F17 — XSS reflejado',
    '744086ba3475a6ec62412a73e11a2357744abe9e3ac31cc86398e17ff6cf7a07' => 'F18 — Escape socket Docker',
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
            $feedback = 'Bandera incorrecta. Verifica el formato {...} y vuelve a intentarlo.';
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
            <input type="text" id="bandera" name="bandera" placeholder="{...}" autocomplete="off" autofocus>
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
                  echo 'Pega aquí cada evidencia {...} que encuentres. Yo la verifico. 🕵️';
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