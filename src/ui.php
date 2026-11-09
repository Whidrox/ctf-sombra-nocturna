<?php
// src/ui.php - Piezas visuales compartidas del "juego" (solo interfaz).
// No contiene banderas ni lógica de los retos.

// Misiones del juego, agrupadas por fase. Texto SIN soluciones.
$SN_FASES = [
  [
    'titulo' => 'Fase 1 — Reconocimiento e inteligencia',
    'intro'  => 'Antes de tocar el objetivo, estudia lo que ya tienes en la mano.',
    'icono'  => '📡',
    'retos'  => [
      ['F01', 'Reconocimiento pasivo', 'Oído Fino', 'la señal oculta en el tráfico interceptado. Analízalo y descubre por dónde y cómo se comunica la guarida.'],
      ['F02', 'Enumeración de servicios', 'Puerta Trasera', 'los servicios que el cartel dejó abiertos. Alguno habla de más apenas tocas la puerta.'],
      ['F03', 'Enumeración web', 'Mapa del Tesoro', 'las rutas secretas del portal: lo que no aparece en el menú es justo lo que esconden.'],
    ],
  ],
  [
    'titulo' => 'Fase 2 — Infiltración del portal',
    'intro'  => 'La puerta principal está cerrada. Encuentra la grieta.',
    'icono'  => '🗝️',
    'retos'  => [
      ['F04', 'Gestión de sesiones', 'Llave Maestra', 'una forma de cruzar el acceso operativo sin tener credenciales. La cerradura tiene una grieta.'],
      ['F05', 'Análisis HTTP / SQL', 'Archivo Negro', 'los registros internos de operaciones. La misma grieta del acceso lleva hasta la base de datos.'],
      ['F06', 'Metadatos', 'Foto Delatora', 'lo que esconden las fotografías del panel. Una imagen vale más que mil palabras… y a veces lleva una nota.'],
    ],
  ],
  [
    'titulo' => 'Fase 3 — Ejecución de código',
    'intro'  => 'Ya estás dentro. Ahora haz que el servidor trabaje para ti.',
    'icono'  => '💻',
    'retos'  => [
      ['F07', 'Carga de archivos', 'Caballo de Troya', 'un hueco en la política de carga de evidencias. Haz pasar tu paquete por la aduana.'],
      ['F08', 'Inyección de comandos', 'Control Remoto', 'la prueba de que el servidor obedece sus órdenes.'],
      ['F09', 'Compromiso de usuario', 'Primer Topo', 'la credencial del primer infiltrado de bajo nivel: la prueba formal del compromiso.'],
    ],
  ],
  [
    'titulo' => 'Fase 4 — Expansión y post-explotación',
    'intro'  => 'Un punto de apoyo abre muchas puertas. Busca lo que dejaron olvidado.',
    'icono'  => '🕵️',
    'retos'  => [
      ['F10', 'Fuga de credenciales', 'Llavero Olvidado', 'un archivo de configuración olvidado que abre la puerta de otro servicio.'],
      ['F11', 'Tareas programadas', 'Reloj Suizo', 'la tarea automática que corre puntual… con más poder del que debería.'],
      ['F12', 'Secretos en el entorno', 'Bóveda Invisible', 'la llave de la billetera del cartel, guardada en el entorno de sus procesos.'],
    ],
  ],
  [
    'titulo' => 'Fase 5 — Escalada a control total',
    'intro'  => 'De invitado a dueño del sistema.',
    'icono'  => '👑',
    'retos'  => [
      ['F13', 'Permisos mal configurados', 'Sello Falso', 'binarios con permisos peligrosos que leen lo que nadie debería leer.'],
      ['F14', 'Escalada a root', 'Trono Vacío', 'el control total de la máquina. Un permiso mal puesto lo corona.'],
      ['F15', 'Registro final', 'Último Mensaje', 'el registro cifrado con el destino final del cargamento.'],
    ],
  ],
  [
    'titulo' => 'Fase 6 — Vectores adicionales',
    'intro'  => 'No todo pasa por la web. Hay otras puertas.',
    'icono'  => '🚪',
    'retos'  => [
      ['F16', 'Servicios de red (SSH)', 'Contraseña de Cajón', 'la cuenta de soporte que protege su acceso con una llave demasiado simple.'],
      ['F17', 'XSS', 'Eco Malicioso', 'un buscador que repite todo lo que le escribes… sin filtrar nada.'],
      ['F18', 'Escape de contenedor', 'Fuga de la Jaula', 'la salida del contenedor: expone más de lo que debería.'],
    ],
  ],
];

// Rango del jugador según banderas encontradas
function sn_rango($n) {
  if ($n >= 18) return ['Leyenda — Caso cerrado', '🏆'];
  if ($n >= 16) return ['Cazador de sombras', '🦅'];
  if ($n >= 13) return ['Comandante', '🎖️'];
  if ($n >= 10) return ['Agente especial', '🕶️'];
  if ($n >= 7)  return ['Inspector', '🔎'];
  if ($n >= 4)  return ['Detective', '🕵️'];
  if ($n >= 1)  return ['Cadete', '🎯'];
  return ['Recluta', '🔰'];
}

function sn_head($titulo) { ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($titulo); ?></title>
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?php }

// Capas de fondo animadas (partículas, cuadrícula, escáner)
function sn_fondo() { ?>
<canvas id="fx" aria-hidden="true"></canvas>
<div class="bg-grid" aria-hidden="true"></div>
<div class="bg-sweep" aria-hidden="true"></div>
<?php }

// Logo: lupa con ojo (reemplaza el escudo)
function sn_logo() { ?>
<svg class="logo" viewBox="0 0 64 64" aria-hidden="true">
  <circle cx="27" cy="27" r="19" fill="none" stroke="currentColor" stroke-width="4"/>
  <path d="M41 41 L57 57" stroke="var(--orange)" stroke-width="7" stroke-linecap="round"/>
  <path d="M14 27 Q27 15 40 27 Q27 39 14 27 Z" fill="none" stroke="currentColor" stroke-width="2.5"/>
  <circle class="logo-iris" cx="27" cy="27" r="5" fill="var(--orange)"/>
</svg>
<?php }

function sn_marca($sub) { ?>
<a class="brand" href="info.php" title="Expediente del caso">
  <?php sn_logo(); ?>
  <span>
    <span class="brand-name glitch" data-text="SOMBRA NOCTURNA">SOMBRA NOCTURNA</span>
    <span class="brand-sub"><?php echo htmlspecialchars($sub); ?></span>
  </span>
</a>
<?php }

function sn_sonido_btn() { ?>
<button type="button" class="icon-btn" data-sound-toggle title="Activar / silenciar efectos de sonido" aria-pressed="false">🔇</button>
<?php }

function sn_pie() { ?>
<footer class="site-footer">
  <p>&copy; 2026 <strong>Sergio Alexander Prieto Castiblanco</strong> y <strong>Laura Valentina Bautista Vargas</strong>. Todos los derechos reservados.</p>
  <p>Laboratorio de Hacking Ético · Entorno aislado con datos ficticios.</p>
</footer>
<script src="assets/game.js"></script>
<?php }

// Anillo de progreso circular
function sn_anillo($hechas, $total, $grande = false) {
  $r = 52; $c = 2 * M_PI * $r;
  $off = $c * (1 - ($total > 0 ? $hechas / $total : 0)); ?>
<div class="ring<?php echo $grande ? ' ring-lg' : ''; ?>" style="--ring-c:<?php echo round($c, 2); ?>;--ring-off:<?php echo round($off, 2); ?>">
  <svg viewBox="0 0 120 120" aria-hidden="true">
    <circle class="ring-track" cx="60" cy="60" r="<?php echo $r; ?>"/>
    <circle class="ring-ticks" cx="60" cy="60" r="58"/>
    <circle class="ring-bar" cx="60" cy="60" r="<?php echo $r; ?>"/>
  </svg>
  <div class="ring-label"><strong data-count-to="<?php echo (int)$hechas; ?>">0</strong><span>/<?php echo (int)$total; ?></span></div>
</div>
<?php }

// El agente animado (personaje)
function sn_agente($clase = '') { ?>
<svg class="agent <?php echo $clase; ?>" viewBox="0 0 200 240" aria-hidden="true">
  <defs>
    <linearGradient id="agCoat" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#3b4b66"/><stop offset="1" stop-color="#1b2436"/>
    </linearGradient>
    <linearGradient id="agHat" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#3a4760"/><stop offset="1" stop-color="#1c2537"/>
    </linearGradient>
    <radialGradient id="agLens" cx=".35" cy=".35" r=".8">
      <stop offset="0" stop-color="#a5f3fc" stop-opacity=".75"/><stop offset="1" stop-color="#22d3ee" stop-opacity=".12"/>
    </radialGradient>
  </defs>
  <ellipse class="ag-shadow" cx="100" cy="234" rx="58" ry="6"/>
  <g class="ag-all">
    <g class="ag-body">
      <path d="M42 240 C44 196 62 172 100 168 C138 172 156 196 158 240 Z" fill="url(#agCoat)"/>
      <path d="M88 172 L100 202 L112 172 Z" fill="#e2e8f0"/>
      <path d="M97 180 L103 180 L106 206 L100 214 L94 206 Z" fill="var(--orange)"/>
      <path d="M100 170 L78 176 L92 218 Z" fill="#4b5d7c"/>
      <path d="M100 170 L122 176 L108 218 Z" fill="#4b5d7c"/>
      <circle class="ag-badge" cx="132" cy="206" r="6" fill="var(--cyan)"/>
    </g>
    <g class="ag-head">
      <rect x="90" y="148" width="20" height="24" rx="6" fill="#e2ab84"/>
      <circle cx="57" cy="120" r="9" fill="#efbd96"/>
      <circle cx="143" cy="120" r="9" fill="#efbd96"/>
      <circle cx="100" cy="118" r="44" fill="#f5cba7"/>
      <ellipse cx="74" cy="134" rx="7" ry="4" fill="#f9a8a8" opacity=".55"/>
      <ellipse cx="126" cy="134" rx="7" ry="4" fill="#f9a8a8" opacity=".55"/>
      <g class="ag-eyes">
        <ellipse cx="82" cy="117" rx="9" ry="10" fill="#fff"/>
        <ellipse cx="118" cy="117" rx="9" ry="10" fill="#fff"/>
        <g class="ag-pupils">
          <circle cx="82" cy="118" r="4.8" fill="#0f172a"/>
          <circle cx="118" cy="118" r="4.8" fill="#0f172a"/>
          <circle cx="83.6" cy="116" r="1.5" fill="#fff"/>
          <circle cx="119.6" cy="116" r="1.5" fill="#fff"/>
        </g>
      </g>
      <g class="ag-brows" stroke="#3b2a1f" stroke-width="3.6" stroke-linecap="round" fill="none">
        <path class="ag-brow-l" d="M72 101 Q82 95 92 100"/>
        <path class="ag-brow-r" d="M108 100 Q118 95 128 101"/>
      </g>
      <path d="M94 128 Q100 132 106 128" stroke="#d99a76" stroke-width="2.5" fill="none" stroke-linecap="round"/>
      <path class="ag-mouth ag-mouth-neutral" d="M90 141 Q100 147 110 141" stroke="#7a3b2e" stroke-width="3.2" fill="none" stroke-linecap="round"/>
      <path class="ag-mouth ag-mouth-happy" d="M85 137 Q100 158 115 137 Z" fill="#7f1d1d" stroke="#7a3b2e" stroke-width="2" stroke-linejoin="round"/>
      <path class="ag-mouth ag-mouth-sad" d="M89 147 Q100 137 111 147" stroke="#7a3b2e" stroke-width="3.2" fill="none" stroke-linecap="round"/>
      <path class="ag-mouth ag-mouth-o" d="M100 136 a5 6 0 1 0 0.1 0 Z" fill="#7f1d1d"/>
      <g class="ag-shades">
        <path d="M66 108 h32 v8 q0 12 -16 12 q-16 0 -16 -12 z" fill="#0b1220"/>
        <path d="M102 108 h32 v8 q0 12 -16 12 q-16 0 -16 -12 z" fill="#0b1220"/>
        <path d="M98 111 h4" stroke="#0b1220" stroke-width="4"/>
        <path d="M72 112 l8 0 M108 112 l8 0" stroke="var(--cyan)" stroke-width="2.5" stroke-linecap="round" opacity=".9"/>
      </g>
      <g class="ag-hat">
        <ellipse cx="100" cy="84" rx="66" ry="11" fill="#1c2537"/>
        <path d="M60 85 C58 52 72 38 100 38 C128 38 142 52 140 85 Z" fill="url(#agHat)"/>
        <path d="M84 46 Q100 56 116 46" stroke="#141b29" stroke-width="3" fill="none"/>
        <path d="M61 70 Q100 76 139 70 L140 82 Q100 88 60 82 Z" fill="var(--orange)"/>
      </g>
    </g>
    <g class="ag-glass">
      <circle cx="154" cy="204" r="11" fill="#f5cba7"/>
      <path d="M156 198 L168 176" stroke="#7c3f12" stroke-width="7" stroke-linecap="round"/>
      <circle cx="176" cy="158" r="20" fill="url(#agLens)" stroke="#cbd5e1" stroke-width="5"/>
      <path d="M166 150 q5 -6 12 -6" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" opacity=".8"/>
    </g>
    <g class="ag-thumb">
      <rect x="28" y="182" width="26" height="22" rx="8" fill="#f5cba7"/>
      <rect x="31" y="160" width="10" height="28" rx="5" fill="#f5cba7"/>
      <path d="M34 204 L50 204" stroke="#d99a76" stroke-width="2"/>
      <rect x="30" y="202" width="22" height="16" rx="3" fill="#3b4b66"/>
    </g>
    <g class="ag-cover">
      <rect x="62" y="232" width="34" height="40" rx="10" fill="#3b4b66"/>
      <circle cx="80" cy="232" r="15" fill="#f5cba7"/>
      <rect x="104" y="232" width="34" height="40" rx="10" fill="#3b4b66"/>
      <circle cx="120" cy="232" r="15" fill="#f5cba7"/>
    </g>
  </g>
</svg>
<?php }

// Silueta del sospechoso para el cartel de "Se busca"
function sn_sospechoso() { ?>
<svg class="suspect" viewBox="0 0 160 170" aria-hidden="true">
  <rect width="160" height="170" fill="#8a7a63"/>
  <path d="M0 40 H160 M0 80 H160 M0 120 H160" stroke="#7a6a54" stroke-width="1"/>
  <g fill="#0a0806">
    <path d="M24 170 C26 132 50 114 80 112 C110 114 134 132 136 170 Z"/>
    <circle cx="80" cy="80" r="30"/>
    <ellipse cx="80" cy="54" rx="48" ry="8"/>
    <path d="M52 55 C52 30 62 20 80 20 C98 20 108 30 108 55 Z"/>
  </g>
  <g class="suspect-eyes" fill="#f43f5e">
    <ellipse cx="69" cy="80" rx="5" ry="2.4"/>
    <ellipse cx="91" cy="80" rx="5" ry="2.4"/>
  </g>
  <text x="80" y="104" text-anchor="middle" font-size="16" fill="#3a2f26" font-family="monospace">?</text>
</svg>
<?php }
