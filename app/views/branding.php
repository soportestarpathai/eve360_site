<?php ob_start(); ?>
<h1>Branding</h1>

<p class="muted">
  Personaliza colores y logo de la aplicación.
</p>

<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<?php
  // If logo path is stored like "/storage/branding/xxx.png",
  // prefix with base_url so it works under /eve360
  $logoPath = $settings['brand_logo_path'] ?? '';
  if (is_string($logoPath) && $logoPath !== '' && $logoPath[0] === '/') {
    $logoPath = rtrim($config['app']['base_url'] ?? '', '/') . $logoPath;
  }
?>

<div class="card" style="margin-top:14px;">
  <form method="post" action="<?= e(app_url('/config/branding')) ?>" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

    <div class="grid2">
      <div>
        <label>Color primario</label>
        <input type="text" name="brand_primary_color"
               value="<?= e($settings['brand_primary_color'] ?? '#6ea8ff') ?>"
               placeholder="#6ea8ff" />
      </div>

      <div>
        <label>Color secundario</label>
        <input type="text" name="brand_secondary_color"
               value="<?= e($settings['brand_secondary_color'] ?? '#7c5cff') ?>"
               placeholder="#7c5cff" />
      </div>
    </div>

    <label style="margin-top:12px;">Logo (PNG/JPG)</label>
    <input type="file" name="logo" />

    <?php if (!empty($settings['brand_logo_path'])): ?>
      <div style="margin-top:12px;">
        <div class="muted" style="margin-bottom:8px;">Logo actual</div>
        <img src="<?= e($logoPath) ?>" alt="Logo actual"
             style="max-width:220px; height:auto; border-radius:14px; border:1px solid var(--border);" />
      </div>
    <?php endif; ?>

    <div class="actions" style="margin-top:14px;">
      <button class="btn" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/config')) ?>">Volver</a>
    </div>
  </form>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
