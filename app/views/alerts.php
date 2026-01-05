<?php ob_start(); ?>

<h1>Configuración de alertas</h1>

<p class="muted">
  Reglas para <b>documentos requeridos por tipo</b> y <b>avisos requeridos</b>.
  Se guardan como JSON en la tabla <code>settings</code>.
</p>

<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="margin-top:14px;">
  <form method="post" action="<?= e(app_url('/config/alerts')) ?>">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />

    <label for="doc_expiry_days">Días para "documentos por vencer"</label>
    <input id="doc_expiry_days" name="doc_expiry_days" value="<?= e($doc_expiry_days) ?>" />

    <label for="required_docs_json">Documentos requeridos (JSON)</label>
    <textarea id="required_docs_json" name="required_docs_json" rows="10"><?= e($required_docs_json) ?></textarea>

    <label for="required_avisos_json">Reglas de avisos requeridos (JSON)</label>
    <textarea id="required_avisos_json" name="required_avisos_json" rows="8"><?= e($required_avisos_json) ?></textarea>

    <div class="actions">
      <button class="btn" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="<?= e(app_url('/config')) ?>">Volver</a>
    </div>
  </form>
</div>

<div class="card" style="margin-top:14px;">
  <h2>Ejemplos</h2>

  <p class="muted" style="margin-top:0;">
    Documentos requeridos por tipo:
  </p>

  <pre style="white-space:pre-wrap; margin:0;"><?=
e('{
  "FISICA": ["INE","RFC"],
  "MORAL": ["Acta constitutiva","RFC"],
  "FIDEICOMISO": ["Contrato fideicomiso","RFC fiduciario"]
}')
?></pre>

  <p class="muted" style="margin-top:14px;">
    Reglas de avisos requeridos:
  </p>

  <pre style="white-space:pre-wrap; margin:0;"><?=
e('{
  "require_monthly": true,
  "month_offset": 0,
  "status_required": "XML_GENERATED"
}')
?></pre>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
