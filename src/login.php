<?php
// src/login.php - Formulario con SQL Injection clásica en el campo usuario
session_start();
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;

require_once 'db.php';
require_once 'ui.php';

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
<?php sn_head('Sombra Nocturna — Acceso de agentes'); ?>
</head>
<body>
<?php sn_fondo(); ?>

<main class="auth">
  <div class="auth-shell">

    <div class="auth-top">
      <?php sn_marca('Portal de mando C3I'); ?>
      <div class="digits" aria-hidden="true">
        <span>CASO</span><span class="hot">#SN-2026</span><span>·</span><span data-digits>000000</span>
      </div>
      <div style="display:flex;gap:10px;align-items:center;">
        <span class="hud-clock">HORA LOCAL <b data-clock>--:--:--</b></span>
        <?php sn_sonido_btn(); ?>
      </div>
    </div>

    <div class="hud auth-card">

      <section class="auth-left">
        <div class="bubble" role="status" aria-live="polite"><span class="who">Agente 007 · Guardián</span><span data-say></span></div>
        <div class="radar-stage" id="login-agent" data-agent data-state="idle" data-mood="idle">
          <svg class="radar" viewBox="0 0 200 200" aria-hidden="true">
            <circle class="r1" cx="100" cy="100" r="97" fill="none" stroke="rgba(34,211,238,.45)" stroke-width="1.2" stroke-dasharray="2 5"/>
            <g class="r2">
              <circle cx="100" cy="100" r="86" fill="none" stroke="rgba(251,146,60,.85)" stroke-width="4" stroke-dasharray="26 40 8 30 40 60 14 52"/>
            </g>
            <g class="r3">
              <circle cx="100" cy="100" r="74" fill="none" stroke="rgba(59,130,246,.8)" stroke-width="7" stroke-dasharray="6 6 6 6 6 30 40 120"/>
            </g>
            <circle cx="100" cy="100" r="64" fill="none" stroke="rgba(34,211,238,.25)" stroke-width="1"/>
            <path d="M100 0 V14 M100 186 V200 M0 100 H14 M186 100 H200" stroke="var(--cyan)" stroke-width="2"/>
          </svg>
          <div class="radar-sweep"></div>
          <?php sn_agente(); ?>
        </div>
      </section>

      <section class="auth-right">
        <h1>ACCESO <span class="accent">OPERATIVO</span></h1>
        <div class="mission-strip"><span class="fp">🕵️</span><span>El agente 007 está en busca del líder de la red <b>Sombra Nocturna</b>. Infíltrate en su portal.</span></div>

        <form method="POST" id="login-form"<?php echo $message !== '' ? ' data-login-error' : ''; ?>>
          <div class="field">
            <label for="username">Usuario operativo</label>
            <div class="input-wrap">
              <span class="ico">👤</span>
              <input type="text" id="username" name="username" placeholder="Nombre en clave" autocomplete="username">
            </div>
          </div>
          <div class="field">
            <label for="password">Clave de encriptación</label>
            <div class="input-wrap">
              <span class="ico">🔐</span>
              <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password">
            </div>
          </div>
          <button type="submit" class="btn">🔎 Ingresar al sistema</button>
          <div class="scanbar" aria-hidden="true"><i></i></div>
        </form>

        <?php if ($message !== ''): ?>
          <p class="error" role="alert"><?php echo $message; ?></p>
        <?php endif; ?>

        <div class="quick-links">
          <a href="info.php">📁 Expediente del caso</a>
          <a href="validar.php">🚩 Banderas (<?php echo $progreso; ?>/<?php echo $TOTAL_BANDERAS; ?>)</a>
        </div>
      </section>

    </div>
  </div>
</main>

<?php sn_pie(); ?>
</body>
</html>