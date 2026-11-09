<?php
// src/index.php - Panel de Gestión de Manifiestos
session_start();
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;
require_once 'ui.php';

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
<?php sn_head('Sombra Nocturna — Centro de operaciones'); ?>
</head>
<body>
<!--
  [AUDITORIA INTERNA - no borrar antes de produccion]
  Acceso admin confirmado via panel de mando.
  F04: CTF{f04_bypass_admin_sqli_2026}
-->
<?php sn_fondo(); $rango = sn_rango($progreso); $pct = round($progreso / $TOTAL_BANDERAS * 100); ?>

<header class="topbar">
  <?php sn_marca('Centro de operaciones'); ?>
  <span class="hud-clock">⏱ <b data-clock>--:--:--</b></span>
  <nav class="nav">
    <span class="agent-chip"><span class="dot"></span>Sesión: <strong class="role"><?php echo htmlspecialchars($_GET['role']); ?></strong></span>
    <a href="info.php">📁 Expediente</a>
    <a class="nav-hot" href="validar.php">🚩 Banderas <?php echo $progreso; ?>/<?php echo $TOTAL_BANDERAS; ?></a>
    <?php sn_sonido_btn(); ?>
    <a href="login.php">⏻ Cerrar sesión</a>
  </nav>
</header>

<div class="ticker" aria-hidden="true">
  <span class="ticker-tag">INTERCEPTADO</span>
  <div class="ticker-track">
    <span>» Envío ENV-2026-0472 desviado a bodega 7 «</span>
    <span>» "El pájaro vuela a las 03:00" — remitente desconocido «</span>
    <span>» Contenedor MSCU en tránsito · ruta sin confirmar «</span>
    <span>» Alerta: actividad inusual en el portal de logística «</span>
    <span>» Envío ENV-2026-0472 desviado a bodega 7 «</span>
    <span>» "El pájaro vuela a las 03:00" — remitente desconocido «</span>
    <span>» Contenedor MSCU en tránsito · ruta sin confirmar «</span>
    <span>» Alerta: actividad inusual en el portal de logística «</span>
  </div>
</div>

<main class="main">
  <h1 class="page-title">Panel de operaciones de logística</h1>
  <p class="page-kicker">&gt; <span class="typed" data-type="Agente 007 conectado. El cartel mueve su carga desde aquí… revisa cada rincón."></span></p>

  <div class="ops-grid">

    <div class="stack">
      <section class="hud">
        <h2 class="hud-title"><span class="emoji">📤</span> Cargar fotografías de entregas y manifiestos <span class="tag">UPLOAD</span></h2>
        <form action="" method="post" enctype="multipart/form-data">
          <label class="dropzone">
            <span class="dz-icon">🗂️</span>
            <span class="dz-text">Arrastra aquí la evidencia o elige un archivo</span>
            <input type="file" name="fileToUpload">
          </label>
          <button type="submit" class="btn btn-auto" name="submit" value="Enviar Archivo">🚀 Enviar archivo</button>
        </form>

        <?php if ($message !== ''): ?>
          <p class="alert" role="status"><?php echo $message; ?></p>
        <?php endif; ?>
      </section>

      <section class="hud">
        <h2 class="hud-title"><span class="emoji">🛰️</span> Buscar manifiesto por código de envío <span class="tag">RASTREO</span></h2>
        <form method="get" class="search-row">
          <div class="input-wrap">
            <span class="ico">🔍</span>
            <input type="text" name="q" placeholder="Ej: ENV-2026-0472" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
          </div>
          <input type="hidden" name="role" value="<?php echo htmlspecialchars($_GET['role']); ?>">
          <button type="submit" class="btn">Rastrear</button>
        </form>
        <?php if ($search_result !== ''): ?>
          <p class="alert" role="status"><?php echo $search_result; ?></p>
        <?php endif; ?>
      </section>

      <section class="hud">
        <h2 class="hud-title"><span class="emoji">📸</span> Archivos disponibles <span class="tag">EVIDENCIAS</span></h2>
        <div class="evidence-list" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
          <a class="polaroid" href="uploads/evidencia_envio.jpg">
            <span class="pin"></span>
            <img src="uploads/evidencia_envio.jpg" alt="" loading="lazy">
            <span class="ev-tag">EVIDENCIA #1</span>
            <span class="cap"><span>Foto de inspección 1</span><span>JPG</span></span>
          </a>
          <a class="polaroid" href="uploads/manifiesto_alpha.jpg">
            <span class="pin"></span>
            <img src="uploads/manifiesto_alpha.jpg" alt="" loading="lazy">
            <span class="ev-tag">EVIDENCIA #2</span>
            <span class="cap"><span>Manifiesto fotográfico Alpha</span><span>JPG</span></span>
          </a>
        </div>
      </section>
    </div>

    <aside class="stack">
      <section class="hud rank-card">
        <?php sn_anillo($progreso, $TOTAL_BANDERAS); ?>
        <div>
          <div class="muted">Tu rango</div>
          <div class="rank-name"><?php echo $rango[1] . ' ' . htmlspecialchars($rango[0]); ?></div>
          <div class="muted">Cada bandera te acerca al líder.</div>
        </div>
      </section>

      <section class="wanted" style="--blur:<?php echo round(12 * (1 - $progreso / $TOTAL_BANDERAS), 1); ?>px;--gray:<?php echo round(1 - $progreso / $TOTAL_BANDERAS, 2); ?>;--pct:<?php echo $pct; ?>%">
        <h3>SE BUSCA</h3>
        <div class="sub">LÍDER DE "SOMBRA NOCTURNA"</div>
        <div class="photo"><?php sn_sospechoso(); ?></div>
        <?php if ($progreso >= $TOTAL_BANDERAS): ?><div class="caught">CAPTURADO</div><?php endif; ?>
        <div class="sub">IDENTIFICACIÓN: <?php echo $pct; ?>%</div>
        <div class="meter"><i></i></div>
        <div class="reward">RECOMPENSA: 18 BANDERAS</div>
      </section>
    </aside>

  </div>
</main>

<div class="overlay" id="welcome" role="dialog" aria-label="Acceso concedido">
  <div class="overlay-box" data-agent data-state="idle">
    <?php sn_agente(); ?>
    <h2>ACCESO CONCEDIDO</h2>
    <p>Bienvenido, agente <strong style="color:var(--orange)"><?php echo htmlspecialchars($_GET['role']); ?></strong>. Estás dentro.</p>
    <span class="stamp green">APROBADO</span>
    <p class="hint">(clic para continuar)</p>
  </div>
</div>

<?php sn_pie(); ?>
</body>
</html>