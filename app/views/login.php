<?php ob_start(); ?>

<div class="auth-card">
  <div class="auth-head">
    <h1>Iniciar sesión</h1>
    <p class="muted">Accede con tu correo y contraseña.</p>
  </div>

  <?php if (!empty($_GET['blocked'])): ?>
    <div class="alert error">Tu usuario está bloqueado. Contacta al administrador.</div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert ok"><?= e($_GET['msg']) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(app_url('/login')) ?>" class="form">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="username" placeholder="tu@correo.com" />

    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" />

    <div class="actions">
      <button class="btn" type="submit">Entrar</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/forgot')) ?>">¿Olvidaste tu contraseña?</a>
    </div>
  </form>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
