<?php ob_start(); ?>
<h1>Usuarios</h1>

<div class="toolbar">
  <a class="btn" href="<?= e(app_url('/config/users/new')) ?>">Nuevo usuario</a>
</div>

<table class="table">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Email</th>
      <th>Bloqueado</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= (int)$u['is_blocked'] === 1 ? 'Sí' : 'No' ?></td>
        <td>
          <a class="btn-link" href="<?= e(app_url('/config/users/edit')) ?>&id=<?= (int)$u['id'] ?>">
            Editar
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
