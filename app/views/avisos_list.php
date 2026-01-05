<?php ob_start(); ?>
<h1>Avisos</h1>

<div class="toolbar">
  <a class="btn" href="<?= e(app_url('/avisos/new')) ?>">Nuevo aviso</a>
</div>

<table class="table">
  <thead>
    <tr>
      <th>Cliente</th>
      <th>Mes</th>
      <th>Referencia</th>
      <th>Estatus</th>
      <th>XML</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($avisos as $a): ?>
    <tr>
      <td><?= e($a['cliente_nombre']) ?></td>
      <td><?= e($a['mes_reportado']) ?></td>
      <td><?= e($a['referencia_aviso']) ?></td>
      <td><?= e($a['status']) ?></td>

      <td>
        <?php if (!empty($a['xml_path'])): ?>
          <?php
            // xml_path is stored like "/storage/avisos/file.xml"
            // Under /eve360 it must become "/eve360/storage/avisos/file.xml"
            $xmlHref = $a['xml_path'];
            if (is_string($xmlHref) && $xmlHref !== '' && $xmlHref[0] === '/') {
              $xmlHref = rtrim($config['app']['base_url'] ?? '', '/') . $xmlHref;
            }
          ?>
          <a class="btn-link" href="<?= e($xmlHref) ?>" target="_blank" rel="noopener">Descargar</a>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>

      <td>
        <a class="btn-link" href="<?= e(app_url('/avisos/edit?id=' . (int)$a['id'])) ?>">Editar</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
