<?php ob_start(); ?>
<h1>Configuración</h1>

<div class="cards">
  <div class="card">
    <div class="card-title">Usuarios</div>
    <div class="card-body">
      <a class="btn" href="<?= e(app_url('/config/users')) ?>">Administrar</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Branding</div>
    <div class="card-body">
      <a class="btn" href="<?= e(app_url('/config/branding')) ?>">Logo y colores</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Cambiar contraseña</div>
    <div class="card-body">
      <a class="btn" href="<?= e(app_url('/config/password')) ?>">Cambiar</a>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Alertas</div>
    <div class="card-body">
      <a class="btn" href="<?= e(app_url('/config/alerts')) ?>">Reglas de notificaciones</a>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
