<?php ob_start(); ?>

<h1>Dashboard</h1>
<p class="muted">Accesos rápidos</p>

<div class="cards">
  <div class="card">
    <div class="card-title">Clientes</div>
    <div class="card-body">
      <p>Administra información de clientes y documentos.</p>
      <a class="btn" href="<?= e(app_url('/clientes')) ?>">Ir a Clientes</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Avisos</div>
    <div class="card-body">
      <p>Crea avisos y genera XML FEP.</p>
      <a class="btn" href="<?= e(app_url('/avisos')) ?>">Ir a Avisos</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Configuración</div>
    <div class="card-body">
      <p>Usuarios, permisos y branding.</p>
      <a class="btn" href="<?= e(app_url('/config')) ?>">Ir a Configuración</a>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
