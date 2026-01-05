<?php ob_start(); ?>

<div class="auth-card">
  <div class="auth-head">
    <h1>Restablecer contraseña</h1>
    <p class="muted">Crea una contraseña nueva (mínimo 8 caracteres).</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <div class="alert ok"><?= e($message) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(app_url('/reset')) ?>" class="form">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>" />

    <label for="password">Nueva contraseña</label>
    <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password" placeholder="••••••••" />

    <label for="password2">Confirmar contraseña</label>
    <input id="password2" name="password2" type="password" minlength="8" required autocomplete="new-password" placeholder="••••••••" />

    <div class="actions">
      <button class="btn" type="submit">Cambiar</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/login')) ?>">Volver</a>
    </div>
  </form>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
