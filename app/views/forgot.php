<?php ob_start(); ?>

<div class="auth-card">
  <div class="auth-head">
    <h1>Recuperar contraseña</h1>
    <p class="muted">Te enviaremos un enlace para restablecer tu contraseña.</p>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert ok"><?= e($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(app_url('/forgot')) ?>" class="form">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="username" placeholder="tu@correo.com" />

    <div class="actions">
      <button class="btn" type="submit">Enviar enlace</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/login')) ?>">Volver</a>
    </div>
  </form>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
