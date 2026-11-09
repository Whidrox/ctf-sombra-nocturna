/* ==========================================================
   SOMBRA NOCTURNA — animaciones e interacción del juego
   Solo interfaz: no cambia nada de cómo funcionan los retos.
   ========================================================== */
(function () {
  'use strict';

  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function store(kind) {
    return {
      get: function (k) { try { return window[kind].getItem(k); } catch (e) { return null; } },
      set: function (k, v) { try { window[kind].setItem(k, v); } catch (e) {} },
      del: function (k) { try { window[kind].removeItem(k); } catch (e) {} }
    };
  }
  var local = store('localStorage');
  var sess = store('sessionStorage');

  /* ---------------- Sonido (sintetizado, sin archivos) ---------------- */
  var soundOn = local.get('sn_sound') === '1';
  var actx = null;
  function tone(freq, dur, type, vol, when) {
    if (!soundOn) return;
    try {
      actx = actx || new (window.AudioContext || window.webkitAudioContext)();
      var t = actx.currentTime + (when || 0);
      var o = actx.createOscillator();
      var g = actx.createGain();
      o.type = type || 'square';
      o.frequency.setValueAtTime(freq, t);
      g.gain.setValueAtTime(vol || 0.05, t);
      g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
      o.connect(g); g.connect(actx.destination);
      o.start(t); o.stop(t + dur + 0.02);
    } catch (e) {}
  }
  var sfx = {
    key: function () { tone(900 + Math.random() * 400, 0.03, 'square', 0.015); },
    click: function () { tone(1400, 0.05, 'square', 0.025); },
    ok: function () { [523, 659, 784, 1046].forEach(function (f, i) { tone(f, 0.2, 'triangle', 0.07, i * 0.09); }); },
    bad: function () { tone(190, 0.25, 'sawtooth', 0.06); tone(120, 0.4, 'sawtooth', 0.06, 0.2); },
    scan: function () { for (var i = 0; i < 8; i++) tone(500 + i * 110, 0.08, 'sine', 0.04, i * 0.17); },
    win: function () { [392, 523, 659, 784, 659, 784, 1046].forEach(function (f, i) { tone(f, 0.25, 'triangle', 0.07, i * 0.13); }); }
  };
  function paintSound() {
    $$('[data-sound-toggle]').forEach(function (b) {
      b.textContent = soundOn ? '🔊' : '🔇';
      b.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
    });
  }
  $$('[data-sound-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      soundOn = !soundOn;
      local.set('sn_sound', soundOn ? '1' : '0');
      paintSound();
      sfx.click();
    });
  });
  paintSound();
  $$('.nav a, .btn, .quick-links a').forEach(function (a) { a.addEventListener('mouseenter', function () { tone(1800, 0.02, 'sine', 0.012); }); });

  /* ---------------- Reloj y contadores ---------------- */
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function tick() {
    var d = new Date();
    var txt = pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    $$('[data-clock]').forEach(function (el) { el.textContent = txt; });
  }
  tick(); setInterval(tick, 1000);

  // Dígitos tipo HUD que cambian solos
  $$('[data-digits]').forEach(function (el) {
    setInterval(function () {
      var s = '';
      for (var i = 0; i < 6; i++) s += Math.floor(Math.random() * 10);
      el.textContent = s;
    }, reduce ? 4000 : 140);
  });

  $$('[data-count-to]').forEach(function (el) {
    var to = parseInt(el.getAttribute('data-count-to'), 10) || 0;
    if (reduce || to === 0) { el.textContent = to; return; }
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min(1, (ts - start) / 1400);
      el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  });

  /* ---------------- Máquina de escribir ---------------- */
  function typeText(el, text, speed, done) {
    if (!el) return;
    if (el._typing) clearInterval(el._typing);
    if (reduce) { el.textContent = text; if (done) done(); return; }
    var i = 0;
    el.textContent = '';
    el._typing = setInterval(function () {
      el.textContent = text.slice(0, ++i);
      if (i % 3 === 0) sfx.key();
      if (i >= text.length) { clearInterval(el._typing); el._typing = null; if (done) done(); }
    }, speed || 26);
  }
  $$('[data-type]').forEach(function (el, k) {
    var text = el.getAttribute('data-type');
    el.textContent = '';
    setTimeout(function () { typeText(el, text, 32); }, 400 + k * 300);
  });

  /* ---------------- Fondo de partículas (red de nodos) ---------------- */
  var fx = $('#fx');
  if (fx && fx.getContext && !reduce) {
    var ctx = fx.getContext('2d');
    var W, H, pts = [], DPR = Math.min(window.devicePixelRatio || 1, 2);
    function size() {
      W = fx.clientWidth; H = fx.clientHeight;
      fx.width = W * DPR; fx.height = H * DPR;
      ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
      var n = Math.min(70, Math.floor(W * H / 22000));
      pts = [];
      for (var i = 0; i < n; i++) {
        pts.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35, c: Math.random() < .15 ? '251,146,60' : (Math.random() < .2 ? '232,121,249' : '34,211,238') });
      }
    }
    var mouse = { x: -999, y: -999 };
    window.addEventListener('mousemove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; });
    window.addEventListener('resize', size);
    size();
    (function loop() {
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < pts.length; i++) {
        var p = pts[i];
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
        var dm = Math.hypot(p.x - mouse.x, p.y - mouse.y);
        ctx.fillStyle = 'rgba(' + p.c + ',' + (dm < 140 ? .95 : .55) + ')';
        ctx.beginPath(); ctx.arc(p.x, p.y, dm < 140 ? 2.4 : 1.6, 0, 6.3); ctx.fill();
        for (var j = i + 1; j < pts.length; j++) {
          var q = pts[j], d = Math.hypot(p.x - q.x, p.y - q.y);
          if (d < 120) {
            ctx.strokeStyle = 'rgba(34,211,238,' + (0.16 * (1 - d / 120)) + ')';
            ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke();
          }
        }
        if (dm < 160) {
          ctx.strokeStyle = 'rgba(251,146,60,' + (0.35 * (1 - dm / 160)) + ')';
          ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke();
        }
      }
      requestAnimationFrame(loop);
    })();
  }

  /* ---------------- Confeti ---------------- */
  function confetti(amount) {
    if (reduce) return;
    var c = document.createElement('canvas');
    c.id = 'confetti';
    document.body.appendChild(c);
    var x = c.getContext('2d');
    c.width = innerWidth; c.height = innerHeight;
    var colors = ['#22d3ee', '#fb923c', '#e879f9', '#34d399', '#facc15', '#ffffff'];
    var bits = [];
    for (var i = 0; i < (amount || 160); i++) {
      bits.push({
        x: innerWidth / 2 + (Math.random() - .5) * 200, y: innerHeight * .45,
        vx: (Math.random() - .5) * 16, vy: -Math.random() * 16 - 4,
        r: Math.random() * 6.3, vr: (Math.random() - .5) * .3,
        w: 6 + Math.random() * 6, h: 4 + Math.random() * 8,
        col: colors[i % colors.length]
      });
    }
    var frames = 0;
    (function anim() {
      x.clearRect(0, 0, c.width, c.height);
      bits.forEach(function (b) {
        b.vy += .35; b.vx *= .99; b.x += b.vx; b.y += b.vy; b.r += b.vr;
        x.save(); x.translate(b.x, b.y); x.rotate(b.r);
        x.fillStyle = b.col; x.fillRect(-b.w / 2, -b.h / 2, b.w, b.h * Math.abs(Math.cos(b.r * 2)));
        x.restore();
      });
      if (++frames < 220) requestAnimationFrame(anim); else c.remove();
    })();
  }

  /* ---------------- Control del agente ---------------- */
  function Agent(wrap) {
    var bubble = wrap ? $(wrap.getAttribute('data-bubble') || '.bubble') : null;
    var textEl = bubble ? ($('[data-say]', bubble) || bubble) : null;
    return {
      set: function (s) { if (!wrap) return; wrap.setAttribute('data-state', s); wrap.setAttribute('data-mood', s); },
      look: function (x, y, tilt) {
        if (!wrap) return;
        wrap.style.setProperty('--lx', x + 'px');
        wrap.style.setProperty('--ly', (y || 0) + 'px');
        wrap.style.setProperty('--tilt', (tilt || 0) + 'deg');
      },
      say: function (text, kind) {
        if (!bubble) return;
        bubble.classList.remove('good', 'bad');
        if (kind) bubble.classList.add(kind);
        bubble.style.animation = 'none'; void bubble.offsetWidth; bubble.style.animation = '';
        typeText(textEl, text, 24);
      }
    };
  }

  // El agente sigue el puntero con la mirada cuando está tranquilo
  $$('[data-agent]').forEach(function (wrap) {
    var a = Agent(wrap);
    window.addEventListener('mousemove', function (e) {
      var st = wrap.getAttribute('data-state');
      if (st !== 'idle') return;
      var r = wrap.getBoundingClientRect();
      var dx = e.clientX - (r.left + r.width / 2), dy = e.clientY - (r.top + r.height / 2);
      var m = Math.max(1, Math.hypot(dx, dy));
      a.look(dx / m * 4, dy / m * 3);
    });
  });

  /* ---------------- LOGIN ---------------- */
  var form = $('#login-form');
  if (form) {
    var stage = $('#login-agent');
    var ag = Agent(stage);
    var user = $('#username'), pass = $('#password');
    var card = $('.auth-card');
    var idleLines = [
      'Identifícate, agente. La Sombra Nocturna no duerme…',
      'Psst… dicen que este portal tiene más grietas que un queso suizo 🧀',
      'Necesito tus credenciales para abrir el expediente.',
      'Mantén los ojos abiertos: cada rastro cuenta. 🔎'
    ];
    var idleTimer;
    function idleChat() {
      clearInterval(idleTimer);
      idleTimer = setInterval(function () {
        if (stage.getAttribute('data-state') === 'idle' && document.activeElement !== user && document.activeElement !== pass) {
          ag.say(idleLines[Math.floor(Math.random() * idleLines.length)]);
        }
      }, 9000);
    }

    if (form.hasAttribute('data-login-error')) {
      sess.del('sn_login');
      ag.set('denied');
      ag.say('¡ACCESO DENEGADO! Tu intento quedó registrado en la red. 🚨', 'bad');
      card.classList.add('shake');
      setTimeout(sfx.bad, 150);
      setTimeout(function () { ag.set('idle'); card.classList.remove('shake'); }, 3200);
    } else {
      ag.set('idle');
      setTimeout(function () { ag.say(idleLines[0]); }, 500);
    }
    idleChat();

    var suspicious = false;
    user.addEventListener('focus', function () { ag.set('watching'); ag.say('¿Quién eres? Escribe tu usuario operativo…'); });
    user.addEventListener('input', function () {
      sfx.key();
      var v = user.value;
      var x = Math.max(-5, Math.min(5, v.length * 0.45 - 5));
      ag.look(x, 3, x * 1.2);
      if (/['"#;]|--|\bor\b/i.test(v)) {
        if (!suspicious) { suspicious = true; ag.say('Hmm… ¿símbolos raros en un usuario? Muy sospechoso 🤨'); }
      } else if (suspicious) {
        suspicious = false;
      }
    });
    pass.addEventListener('focus', function () { ag.set('hiding'); ag.say('Tranquilo, no estoy mirando tu clave 🙈'); });
    pass.addEventListener('input', sfx.key);
    [user, pass].forEach(function (el) {
      el.addEventListener('blur', function () {
        setTimeout(function () {
          if (document.activeElement !== user && document.activeElement !== pass && stage.getAttribute('data-state') !== 'scanning') {
            ag.set('idle'); ag.look(0, 0, 0);
          }
        }, 50);
      });
    });

    var sending = false;
    form.addEventListener('submit', function (e) {
      if (sending) return;
      e.preventDefault();
      sending = true;
      ag.set('scanning'); ag.look(0, 0, 0);
      ag.say('Verificando huellas y credenciales… 🧬');
      $('.scanbar').classList.add('on');
      sfx.scan();
      sess.set('sn_login', '1');
      setTimeout(function () { form.submit(); }, reduce ? 200 : 1600);
    });
  }

  /* ---------------- Overlays ---------------- */
  function showOverlay(id, state, ms) {
    var ov = document.getElementById(id);
    if (!ov) return;
    var wrap = $('[data-agent]', ov) || ov.querySelector('.overlay-box');
    ov.classList.add('show');
    if (wrap) { wrap.setAttribute('data-state', 'idle'); setTimeout(function () { wrap.setAttribute('data-state', state || 'approved'); }, 250); }
    function close() { ov.classList.remove('show'); }
    ov.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    if (ms) setTimeout(close, ms);
  }

  // Panel: bienvenida tras iniciar sesión
  if ($('#welcome') && sess.get('sn_login') === '1') {
    sess.del('sn_login');
    showOverlay('welcome', 'approved', 4200);
    setTimeout(function () { confetti(120); sfx.ok(); }, 500);
  }

  /* ---------------- Validador ---------------- */
  var body = document.body;
  var result = body.getAttribute('data-result');
  var vAgent = $('#val-agent') ? Agent($('#val-agent')) : null;
  if (result === 'new') {
    if (body.getAttribute('data-complete') === '1') {
      showOverlay('case-closed', 'approved');
      setTimeout(function () { confetti(320); sfx.win(); }, 400);
      setTimeout(function () { confetti(200); }, 1600);
    } else {
      showOverlay('evidence-ok', 'approved', 4200);
      setTimeout(function () { confetti(170); sfx.ok(); }, 450);
    }
    var just = $('.mission.just-solved');
    if (just) setTimeout(function () { just.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }); }, 4300);
    if (vAgent) vAgent.set('approved');
  } else if (result === 'bad' || result === 'empty') {
    if (vAgent) vAgent.set('denied');
    var tf = $('.terminal-form');
    if (tf) tf.classList.add('shake');
    setTimeout(sfx.bad, 120);
    setTimeout(function () { if (vAgent) vAgent.set('idle'); }, 3000);
  } else if (result === 'dup') {
    if (vAgent) vAgent.set('watching');
    setTimeout(function () { if (vAgent) vAgent.set('idle'); }, 2500);
  }
  var flagInput = $('#bandera');
  if (flagInput) {
    flagInput.addEventListener('input', function () {
      sfx.key();
      if (!vAgent) return;
      var v = flagInput.value;
      $('#val-agent').setAttribute('data-state', v ? 'watching' : 'idle');
      if (/^CTF\{.+\}$/.test(v.trim())) vAgent.say('¡Eso tiene formato de evidencia! Envíala 👉', 'good');
    });
  }

  /* Tarjetas de misión con efecto 3D al pasar el mouse */
  if (!reduce) {
    $$('.mission').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        card.style.transform = 'rotateY(' + ((px - .5) * 10) + 'deg) rotateX(' + ((.5 - py) * 10) + 'deg) translateZ(6px)';
        card.style.setProperty('--mx', (px * 100) + '%');
        card.style.setProperty('--my', (py * 100) + '%');
      });
      card.addEventListener('mouseleave', function () { card.style.transform = ''; });
    });
  }

  /* Zona de carga: muestra el archivo elegido */
  $$('.dropzone').forEach(function (dz) {
    var input = $('input[type="file"]', dz), txt = $('.dz-text', dz);
    ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function () { dz.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function () { dz.classList.remove('drag'); }); });
    if (input && txt) input.addEventListener('change', function () {
      if (input.files && input.files[0]) { txt.textContent = '📎 Evidencia lista: ' + input.files[0].name; sfx.click(); }
    });
  });

  window.SN = { confetti: confetti, sfx: sfx };
})();
