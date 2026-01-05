<?php
// app/views/layout.php
/** @var array $config */
/** @var PDO $db */
/** @var array|null $user */
/** @var string|null $title */
/** @var string $content */

$brand = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$settings = [];
foreach ($brand as $b) $settings[$b['setting_key']] = $b['setting_value'];

$primary   = $settings['brand_primary_color'] ?? '#6ea8ff';
$secondary = $settings['brand_secondary_color'] ?? '#7c5cff';
$logo      = $settings['brand_logo_path'] ?? '';

$baseUrl = rtrim($config['app']['base_url'] ?? '', '/'); // ex: /eve360
$asset = function(string $path) use ($baseUrl) {
  $path = '/' . ltrim($path, '/');
  return $baseUrl . $path;
};

// IMPORTANT: your app uses two_factor_ok (0/1)
$twoFactorOk = (int)($_SESSION['two_factor_ok'] ?? 0) === 1;

// Shell only when logged in + 2FA verified
$showShell = (!empty($_SESSION['user_id']) && $twoFactorOk);

// Safe title
$pageTitle = $title ?? ($config['app']['name'] ?? 'EVE360');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($pageTitle) ?></title>

  <!-- Primary stylesheet (this is the one you should edit for UI) -->
  <link rel="stylesheet" href="<?= e($asset('/assets/styles.css')) ?>" />

  <!-- Minimal base theme + branding variables (keeps things consistent even if CSS is partial) -->
  <style>
    :root{
      --primary: <?= e($primary) ?>;
      --secondary: <?= e($secondary) ?>;
      --border: rgba(255,255,255,.10);
      --shadow: 0 12px 30px rgba(0,0,0,.35);
      --radius: 18px;
    }

    /* Fallback base styles (if styles.css is missing/partial) */
    body{ margin:0; font: 15px/1.45 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; }
    .container{ width:min(1100px, 92vw); margin: 22px auto 56px; }
    .muted{ opacity:.8; }

    /* Ensure auth pages look good even if styles.css didn’t load */
    .auth-wrap{ width:min(560px, 92vw); margin: 40px auto 70px; }
    .card{
      border:1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
    }
    .auth-card{ width:100%; }
    .auth-head h1{ margin:0 0 10px; font-size:32px; letter-spacing:-.3px; }
    .auth-head p{ margin:0 0 14px; }
    .actions{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:14px; }
    .help{ margin-top: 12px; }

    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      gap:8px; padding:10px 14px; border-radius:14px;
      border:1px solid var(--border);
      background: linear-gradient(135deg, rgba(110,168,255,.92), rgba(124,92,255,.92));
      color:#fff; cursor:pointer; text-decoration:none; font-weight:650;
    }
    .btn-ghost{
      background: rgba(255,255,255,.06);
      color: inherit;
    }
  </style>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <div class="brand-mark">
      <?php if (!empty($logo)): ?>
        <img src="<?= e($logo) ?>" alt="Logo" />
      <?php endif; ?>
    </div>
    <div>
      <div class="brand-title"><?= e($config['app']['name'] ?? 'EVE360') ?></div>
      <div class="brand-subtitle">Notarios</div>
    </div>
  </div>

  <?php if ($showShell): ?>
    <div class="topbar-right">
      <span class="pill"><?= e($_SESSION['user_name'] ?? 'Usuario') ?></span>
      <a class="btn btn-ghost" href="<?= e(app_url('/logout')) ?>">Salir</a>
    </div>
  <?php endif; ?>
</header>

<?php if ($showShell): ?>
  <nav class="nav">
    <a href="<?= e(app_url('/dashboard')) ?>">Dashboard</a>
    <a href="<?= e(app_url('/clientes')) ?>">Clientes</a>
    <a href="<?= e(app_url('/avisos')) ?>">Avisos</a>
    <a href="<?= e(app_url('/config')) ?>">Configuración</a>
    <a href="<?= e(app_url('/audit')) ?>">Auditoría</a>
  </nav>

  <div class="container">
    <div class="layout">
      <div id="notifPanel" class="notif-panel">
        <div class="notif-title">Notificaciones</div>
        <div class="muted" id="notifLoading">Cargando…</div>
        <div id="notifItems"></div>
      </div>

      <main class="main">
        <?= $content ?>
      </main>
    </div>
  </div>

<?php else: ?>
  <!-- Auth-only pages (login/forgot/verify/reset). No menu/nav/notifications -->
  <div class="auth-wrap">
    <div class="card">
      <?= $content ?>
    </div>
  </div>
<?php endif; ?>

<script>
  window.__CONFIG__ = { baseUrl: "<?= e($baseUrl) ?>" };
</script>
<script src="<?= e($asset('/assets/app.js')) ?>"></script>

<script>
(function(){
  const loading = document.getElementById('notifLoading');
  const list = document.getElementById('notifItems');
  if (!loading || !list) return;

  function apiUrl(route){
    const base = (window.__CONFIG__ && window.__CONFIG__.baseUrl) ? window.__CONFIG__.baseUrl : "";
    return base.replace(/\/+$/,'') + "/index.php?path=" + encodeURIComponent(route);
  }
  function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function renderSection(title, items, renderItem){
    if (!items || items.length === 0) return;
    const h = document.createElement('div');
    h.className = 'notif-title';
    h.style.marginTop = '12px';
    h.textContent = title;
    list.appendChild(h);

    for (const it of items){
      const div = document.createElement('div');
      div.className = 'notif-item';
      div.innerHTML = renderItem(it);
      list.appendChild(div);
    }
  }

  async function load(){
    try{
      const res = await fetch(apiUrl('/api/notifications'), { credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      const counts = data.counts || {};
      const total =
        (counts.expiring_docs||0) +
        (counts.missing_clients||0) +
        (counts.draft_avisos||0) +
        (counts.required_avisos||0);

      list.innerHTML = '';
      if (!total){
        loading.textContent = 'Sin notificaciones.';
        return;
      }
      loading.textContent = '';

      renderSection('Documentos por vencer', data.expiring_docs, (x) =>
        `<div><b>${esc(x.cliente)}</b></div>
         <div class="muted">${esc(x.doc_tipo)} — vence: ${esc(x.vence)}</div>`
      );

      renderSection('Clientes con información faltante', data.missing_clients, (x) =>
        `<div><b>${esc(x.cliente)}</b> <span class="pill">${esc(x.tipo)}</span></div>
         <div class="muted">Falta: ${esc((x.missing||[]).join(', '))}</div>`
      );

      renderSection('Avisos en borrador', data.draft_avisos, (x) =>
        `<div><b>${esc(x.cliente)}</b></div>
         <div class="muted">${esc(x.mes_reportado)} — ${esc(x.referencia_aviso)}</div>`
      );

      renderSection('Avisos requeridos', data.required_avisos, (x) =>
        `<div><b>${esc(x.cliente)}</b> <span class="pill">${esc(x.tipo)}</span></div>
         <div class="muted">${esc(x.reason)} — Mes: ${esc(x.mes_reportado)}</div>`
      );

    }catch(err){
      console.error('Notifications error:', err);
      loading.textContent = 'No se pudieron cargar notificaciones.';
    }
  }

  load();
})();
</script>

</body>
</html>
