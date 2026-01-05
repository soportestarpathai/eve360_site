<?php ob_start(); ?>
<h1>Cambiar contraseña</h1>

<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(app_url('/config/password')) ?>" class="card" style="margin-top:12px;">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

  <label>Contraseña actual</label>
  <input type="password" name="old" required autocomplete="current-password" />

  <label>Nueva contraseña</label>
  <input type="password" name="new" minlength="8" required autocomplete="new-password" />

  <label>Confirmar nueva contraseña</label>
  <input type="password" name="new2" minlength="8" required autocomplete="new-password" />

  <div class="actions" style="margin-top:14px;">
    <button class="btn" type="submit">Actualizar</button>
    <a class="btn btn-ghost" href="<?= e(app_url('/config')) ?>">Volver</a>
  </div>
</form>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
