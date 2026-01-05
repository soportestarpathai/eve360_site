<?php ob_start(); ?>
<h1><?= $userEdit ? 'Editar usuario' : 'Nuevo usuario' ?></h1>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(app_url($userEdit ? '/config/users/update' : '/config/users/create')) ?>" class="card" style="margin-top:12px;">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
  <?php if ($userEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$userEdit['id'] ?>" />
  <?php endif; ?>

  <label>Nombre</label>
  <input name="name" required value="<?= e($userEdit['name'] ?? '') ?>" />

  <label>Email</label>
  <input name="email" type="email" required value="<?= e($userEdit['email'] ?? '') ?>" />

  <?php if (!$userEdit): ?>
    <label>Contraseña inicial</label>
    <input name="password" type="password" minlength="8" required placeholder="Mínimo 8 caracteres" />
  <?php endif; ?>

  <label>Bloqueado</label>
  <?php $b = $userEdit['is_blocked'] ?? 0; ?>
  <select name="is_blocked">
    <option value="0" <?= (int)$b === 0 ? 'selected' : '' ?>>No</option>
    <option value="1" <?= (int)$b === 1 ? 'selected' : '' ?>>Sí</option>
  </select>

  <label>Rol</label>
  <select name="role_id" required>
    <?php foreach ($roles as $r): ?>
      <option value="<?= (int)$r['id'] ?>" <?= ((int)$userRoleId === (int)$r['id']) ? 'selected' : '' ?>>
        <?= e($r['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <div class="actions" style="margin-top:14px;">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn btn-ghost" href="<?= e(app_url('/config/users')) ?>">Volver</a>
  </div>
</form>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
