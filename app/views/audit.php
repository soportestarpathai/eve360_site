<?php ob_start(); ?>

<h1>Auditoría</h1>

<form method="get" action="<?= e(app_url('/audit')) ?>" class="card" style="margin-bottom:12px">
  <div class="grid3">
    <div>
      <label>Usuario</label>
      <select name="user_id">
        <option value="">-- Todos --</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= (int)$u['id'] ?>"
            <?= (!empty($filters['user_id']) && (int)$filters['user_id'] === (int)$u['id']) ? 'selected' : '' ?>>
            <?= e($u['name']) ?> (<?= e($u['email']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Acción</label>
      <input type="text" name="action" value="<?= e($filters['action'] ?? '') ?>" />
    </div>

    <div>
      <label>Entidad</label>
      <input type="text" name="entity" value="<?= e($filters['entity'] ?? '') ?>" />
    </div>

    <div>
      <label>ID Entidad</label>
      <input type="text" name="entity_id" value="<?= e($filters['entity_id'] ?? '') ?>" />
    </div>

    <div>
      <label>Desde</label>
      <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>" />
    </div>

    <div>
      <label>Hasta</label>
      <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>" />
    </div>
  </div>

  <div class="actions" style="margin-top:12px">
    <button class="btn" type="submit">Filtrar</button>
    <a class="btn btn-ghost" href="<?= e(app_url('/audit')) ?>">Limpiar</a>
  </div>
</form>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Usuario</th>
        <th>Acción</th>
        <th>Entidad</th>
        <th>ID</th>
        <th>IP</th>
        <th>Detalles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['created_at']) ?></td>
          <td><?= e($r['user_name']) ?></td>
          <td><?= e($r['action']) ?></td>
          <td><?= e($r['entity']) ?></td>
          <td><?= e($r['entity_id']) ?></td>
          <td><?= e($r['ip']) ?></td>
          <td>
            <details>
              <summary>Ver</summary>
              <pre style="white-space:pre-wrap"><?= e($r['payload_json']) ?></pre>
              <div class="muted"><?= e($r['user_agent']) ?></div>
            </details>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
