<?php ob_start(); ?>
<h1>Clientes</h1>

<div class="toolbar">
  <a class="btn" href="<?= e(app_url('/clientes/new')) ?>">Nuevo cliente</a>
</div>

<table class="table">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>RFC</th>
      <th>Tipo</th>
      <th>Documentos</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($clientes as $c): ?>
    <tr>
      <td><?= e($c['nombre']) ?></td>
      <td><?= e($c['rfc']) ?></td>
      <td><?= e($c['tipo']) ?></td>
      <td><?= e($c['docs_count']) ?></td>
      <td>
        <a class="btn-link" href="<?= e(app_url('/clientes/edit?id=' . (int)$c['id'])) ?>">Editar</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
