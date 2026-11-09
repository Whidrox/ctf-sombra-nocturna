<?php
// src/info.php - Briefing e informacion del laboratorio (sin spoilers de solucion)
session_start();
require_once 'ui.php';
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;

// Conteo de categorias distintas (solo informativo)
$categorias = [];
foreach ($SN_FASES as $f) {
  foreach ($f['retos'] as $r) { $categorias[$r[1]] = true; }
}
$num_categorias = count($categorias);
$rango = sn_rango($progreso);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<?php sn_head('Sombra Nocturna — Expediente del caso'); ?>
</head>
<body>
<?php sn_fondo(); ?>

<header class="topbar">
  <?php sn_marca('Expediente del caso'); ?>
  <span class="hud-clock">⏱ <b data-clock>--:--:--</b></span>
  <nav class="nav">
    <span class="agent-chip"><span class="dot"></span><?php echo $rango[1] . ' ' . htmlspecialchars($rango[0]); ?></span>
    <a class="nav-hot" href="validar.php">🚩 Banderas <?php echo $progreso; ?>/<?php echo $TOTAL_BANDERAS; ?></a>
    <?php sn_sonido_btn(); ?>
    <a href="login.php">⏻ Portal</a>
  </nav>
</header>

<main class="main">
  <h1 class="page-title">Expediente: Operación Sombra Nocturna</h1>
  <p class="page-kicker">&gt; <span class="typed" data-type="CLASIFICADO · Solo para agentes autorizados. Lee con atención, agente 007."></span></p>

  <section class="hud">
    <h2 class="hud-title"><span class="emoji">🎬</span> Transmisión de bienvenida <span class="tag">VIDEO</span></h2>
    <div class="video-frame">
      <span class="rec">REC · TRANSMISIÓN SEGURA</span>
      <video controls playsinline preload="metadata">
        <source src="assets/Video_intro.mp4" type="video/mp4">
        Tu navegador no puede reproducir el video.
        <a href="assets/Video_intro.mp4">Descárgalo aquí</a>.
      </video>
    </div>
  </section>

  <section class="hud dossier">
    <h2 class="hud-title"><span class="emoji">🕵️</span> Briefing de la misión <span class="tag">TOP SECRET</span></h2>
    <p>La organización conocida como <strong>"Sombra Nocturna"</strong> coordina una red de logística
       de contrabando desde un portal de mando y control (C3I). Durante meses movieron cargamentos
       bajo la fachada de una empresa de transporte, usando este portal para gestionar manifiestos,
       rutas y entregas.</p>
    <p>Nuestra unidad interceptó parte de su tráfico y obtuvo acceso al perímetro de su
       infraestructura. <strong>Tú eres el agente 007</strong>: tu misión es infiltrar el
       portal, escalar privilegios dentro de sus sistemas y recolectar <strong>18 piezas de
       evidencia</strong> — cada una confirma una brecha en su seguridad — hasta reconstruir toda la
       operación y capturar a su líder.</p>
    <p>No necesitas saber de antemano cómo entrar: el propio entorno fue descuidado y va dejando
       rastros. Observa, enumera y encadena cada hallazgo. Registra cada evidencia en el
       <a href="validar.php">tablero de misiones</a>. Cuando reúnas las 18, el caso quedará cerrado.</p>
  </section>

  <section class="hud">
    <h2 class="hud-title"><span class="emoji">📊</span> Datos del operativo <span class="tag">INTEL</span></h2>
    <div class="stats">
      <div class="stat"><b data-count-to="<?php echo $TOTAL_BANDERAS; ?>">0</b><span>evidencias <code>CTF{...}</code></span></div>
      <div class="stat"><b data-count-to="<?php echo count($SN_FASES); ?>">0</b><span>fases de operación</span></div>
      <div class="stat"><b data-count-to="<?php echo $num_categorias; ?>">0</b><span>técnicas ofensivas</span></div>
      <div class="stat"><b data-count-to="<?php echo $progreso; ?>">0</b><span>ya aseguradas por ti</span></div>
    </div>
    <ul class="rules" style="margin-top:18px;">
      <li>Las evidencias son <strong>independientes</strong>: no hay un orden obligatorio, pero seguir las fases facilita el camino.</li>
      <li>Validación en tiempo real: cada bandera correcta sube tu rango; una incorrecta se rechaza.</li>
    </ul>
  </section>

  <section class="hud">
    <h2 class="hud-title"><span class="emoji">🗺️</span> Ruta de la operación <span class="tag">6 FASES</span></h2>
    <ol class="timeline">
      <?php foreach ($SN_FASES as $fase): ?>
      <li>
        <h3><?php echo $fase['icono'] . ' ' . htmlspecialchars($fase['titulo']); ?></h3>
        <p class="muted"><?php echo htmlspecialchars($fase['intro']); ?></p>
        <ul>
          <?php foreach ($fase['retos'] as $r): ?>
            <li><b><?php echo htmlspecialchars($r[0]); ?> · <?php echo htmlspecialchars($r[1]); ?></b> — El agente 007 está en busca de <?php echo htmlspecialchars($r[3]); ?></li>
          <?php endforeach; ?>
        </ul>
      </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <section class="hud">
    <h2 class="hud-title"><span class="emoji">⚖️</span> Reglas de enfrentamiento <span class="tag">ROE</span></h2>
    <ul class="rules">
      <li>Este es un entorno de laboratorio aislado y con datos ficticios: nada de lo que aquí ocurre afecta sistemas reales.</li>
      <li>El objetivo es aprender técnicas de hacking ético en un entorno controlado y autorizado.</li>
      <li>Cada pista que encuentres en el entorno es intencional; sigue el rastro, no adivines.</li>
    </ul>
  </section>

</main>

<?php sn_pie(); ?>
</body>
</html>
