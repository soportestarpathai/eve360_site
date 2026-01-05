<?php ob_start(); ?>

<div class="auth-card">
  <div class="auth-head">
    <h1>Verificación por correo</h1>
    <p class="muted">
      Te enviamos un código de 6 dígitos a tu correo. Ingresa el código para continuar.
    </p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert ok"><?= e($_GET['msg']) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(app_url('/verify')) ?>" class="form">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

    <label for="code">Código</label>
    <input
      id="code"
      name="code"
      inputmode="numeric"
      autocomplete="one-time-code"
      maxlength="6"
      placeholder="123456"
      required
    />

    <div class="checkline" style="display:flex; align-items:center; gap:10px; margin-top:12px;">
      <input id="trust_device" type="checkbox" name="trust_device" value="1" />
      <label for="trust_device" style="margin:0;">
        Confiar en este dispositivo por 30 días
      </label>
    </div>
    <p class="muted" style="margin-top:8px;">
      No lo actives en equipos públicos o compartidos.
    </p>

    <div class="actions">
      <button class="btn" type="submit">Verificar</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/resend-code')) ?>">Reenviar código</a>
    </div>

    <div class="help">
      <a href="<?= e(app_url('/logout')) ?>" class="muted-link">Salir</a>
    </div>
  </form>
</div>

<script>
  // Small UX: focus code input
  (function(){
    const el = document.getElementById('code');
    if (el) el.focus();
  })();
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
