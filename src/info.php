<?php
// src/info.php - Briefing e informacion del laboratorio (sin spoilers de solucion)
session_start();
$progreso = isset($_SESSION['encontradas']) ? count($_SESSION['encontradas']) : 0;
$TOTAL_BANDERAS = 18;

// Fases narrativas -> retos (titulo visible + que debe lograr el jugador, SIN solucion)
$FASES = [
  [
    'titulo' => 'Fase 1 — Reconocimiento e inteligencia',
    'intro'  => 'Antes de tocar el objetivo, estudia lo que ya tienes en la mano.',
    'retos'  => [
      ['F01', 'Reconocimiento pasivo', 'Se interceptó tráfico de red del portal. Analízalo para descubrir por dónde y cómo se comunica el sistema.'],
      ['F02', 'Enumeración de servicios', 'El objetivo no solo expone la web. Enumera sus servicios; alguno revela información de más apenas te conectas.'],
      ['F03', 'Enumeración web', 'El portal esconde rutas que no aparecen en la interfaz. Encuentra lo que no quieren que veas.'],
    ],
  ],
  [
    'titulo' => 'Fase 2 — Infiltración del portal',
    'intro'  => 'La puerta principal está cerrada. Encuentra la grieta.',
    'retos'  => [
      ['F04', 'Gestión de sesiones', 'El acceso operativo exige credenciales que no tienes. Encuentra la forma de entrar igual.'],
      ['F05', 'Análisis HTTP', 'La misma debilidad del acceso permite leer datos internos de la base de datos. Extráelos.'],
      ['F06', 'Esteganografía / metadatos', 'Las imágenes del panel no son tan inocentes. Revisa lo que llevan por dentro.'],
    ],
  ],
  [
    'titulo' => 'Fase 3 — Ejecución de código',
    'intro'  => 'Ya estás dentro. Ahora haz que el servidor trabaje para ti.',
    'retos'  => [
      ['F07', 'Carga de archivos', 'El panel permite subir archivos con restricciones. Burla la política de carga.'],
      ['F08', 'Inyección de comandos', 'Confirma que puedes ejecutar órdenes del sistema operativo en el servidor.'],
      ['F09', 'Compromiso de usuario', 'Captura la credencial de acceso de bajo nivel: la primera prueba formal del compromiso.'],
    ],
  ],
  [
    'titulo' => 'Fase 4 — Expansión y post-explotación',
    'intro'  => 'Un punto de apoyo abre muchas puertas. Busca lo que dejaron olvidado.',
    'retos'  => [
      ['F10', 'Fuga de credenciales', 'Un archivo de configuración olvidado filtra llaves hacia otro servicio. Úsalas.'],
      ['F11', 'Tareas programadas', 'El sistema ejecuta tareas automáticas. Alguna corre con más privilegios de los que debería.'],
      ['F12', 'Secretos en el entorno', 'Los procesos del servidor guardan secretos en su entorno de ejecución. Encuéntralos.'],
    ],
  ],
  [
    'titulo' => 'Fase 5 — Escalada a control total',
    'intro'  => 'De invitado a dueño del sistema.',
    'retos'  => [
      ['F13', 'Permisos mal configurados', 'Hay binarios con permisos peligrosos que leen lo que tú no deberías poder leer.'],
      ['F14', 'Escalada a root', 'Aprovecha un permiso mal puesto para tomar el control total de la máquina.'],
      ['F15', 'Registro final', 'Con el control total, recupera el último registro oculto de la operación.'],
    ],
  ],
  [
    'titulo' => 'Fase 6 — Vectores adicionales',
    'intro'  => 'No todo pasa por la web. Hay otras puertas.',
    'retos'  => [
      ['F16', 'Servicios de red (SSH)', 'Una cuenta de soporte protege su acceso con una llave demasiado simple.'],
      ['F17', 'XSS', 'El buscador del panel confía demasiado en lo que escribes. Haz que ejecute lo que no debe.'],
      ['F18', 'Escape de contenedor', 'El contenedor expone más de lo que un contenedor debería. Escápate de él.'],
    ],
  ],
];

// Conteo de categorias distintas (solo informativo)
$categorias = [];
foreach ($FASES as $f) {
  foreach ($f['retos'] as $r) { $categorias[$r[1]] = true; }
}
$num_categorias = count($categorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sombra Nocturna - Expediente del caso</title>
<link rel="icon" type="image/png" href="assets/escudo-udec.png">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="sunrise" aria-hidden="true"></div>

<header class="topbar">
  <img src="assets/escudo-udec.png" alt="Escudo de la Universidad de Cundinamarca">
  <div>
    <div class="name">Sombra Nocturna</div>
    <div class="sub">Expediente del caso</div>
  </div>
  <div class="session">
    <span>Progreso</span>
    <strong class="role"><?php echo $progreso; ?> / <?php echo $TOTAL_BANDERAS; ?></strong>
    <a class="logout" href="validar.php">Validar</a>
    <a class="logout" href="login.php">Salir</a>
  </div>
</header>

<main class="main">
  <h1>Expediente: Operación Sombra Nocturna</h1>

  <section class="panel">
    <h2>Briefing de la misión</h2>
    <p>La organización conocida como <strong>"Sombra Nocturna"</strong> coordina una red de logística
       de contrabando desde un portal de mando y control (C3I). Durante meses movieron cargamentos
       bajo la fachada de una empresa de transporte, usando este portal para gestionar manifiestos,
       rutas y entregas.</p>
    <p>Nuestra unidad interceptó parte de su tráfico y obtuvo acceso al perímetro de su
       infraestructura. <strong>Tu misión, como analista de la unidad</strong>, es infiltrar el
       portal, escalar privilegios dentro de sus sistemas y recolectar <strong>18 piezas de
       evidencia</strong> — cada una confirma una brecha en su seguridad — hasta reconstruir toda la
       operación y cerrar el caso.</p>
    <p>No necesitas saber de antemano cómo entrar: el propio entorno fue descuidado y va dejando
       rastros. Observa, enumera y encadena cada hallazgo. Registra cada evidencia en el
       <a href="validar.php">validador de banderas</a>. Cuando reúnas las 18, el expediente quedará
       cerrado.</p>
  </section>

  <section class="panel" style="margin-top: 28px;">
    <h2>Datos del operativo</h2>
    <ul>
      <li><strong><?php echo $TOTAL_BANDERAS; ?> evidencias (banderas)</strong> a recolectar, con formato <code>CTF{...}</code>.</li>
      <li><strong>6 fases</strong> de operación, del reconocimiento al escape final.</li>
      <li><strong><?php echo $num_categorias; ?> técnicas distintas</strong> de seguridad ofensiva involucradas.</li>
      <li>Las evidencias son <strong>independientes</strong>: no hay un orden obligatorio, pero seguir las fases facilita el camino.</li>
      <li>Validación en tiempo real: cada bandera correcta suma a tu progreso; una incorrecta se rechaza.</li>
    </ul>
  </section>

  <?php foreach ($FASES as $fase): ?>
  <section class="panel" style="margin-top: 28px;">
    <h2><?php echo htmlspecialchars($fase['titulo']); ?></h2>
    <p class="help" style="color:var(--mist);margin-top:-6px;"><?php echo htmlspecialchars($fase['intro']); ?></p>
    <ul>
      <?php foreach ($fase['retos'] as $r): ?>
        <li style="margin-bottom:12px;">
          <strong class="role" style="display:inline-block;margin-bottom:4px;"><?php echo htmlspecialchars($r[0]); ?> · <?php echo htmlspecialchars($r[1]); ?></strong><br>
          <?php echo htmlspecialchars($r[2]); ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endforeach; ?>

  <section class="panel" style="margin-top: 28px;">
    <h2>Reglas de enfrentamiento</h2>
    <ul>
      <li>Este es un entorno de laboratorio aislado y con datos ficticios: nada de lo que aquí ocurre afecta sistemas reales.</li>
      <li>El objetivo es aprender técnicas de hacking ético en un entorno controlado y autorizado.</li>
      <li>Cada pista que encuentres en el entorno es intencional; sigue el rastro, no adivines.</li>
    </ul>
  </section>

</main>

<footer class="site-footer">
  <p>&copy; 2026 <strong>Sergio Alexander Prieto Castiblanco</strong> y <strong>Laura Valentina Bautista Vargas</strong>. Todos los derechos reservados.</p>
  <p>Universidad de Cundinamarca, extensión Chía. Hacking Ético.</p>
</footer>
</body>
</html>