<?php ob_start(); ?>
<h1><?= $aviso ? 'Editar aviso' : 'Nuevo aviso' ?></h1>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(app_url($aviso ? '/avisos/update' : '/avisos/create')) ?>">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
  <?php if ($aviso): ?><input type="hidden" name="id" value="<?= (int)$aviso['id'] ?>" /><?php endif; ?>

  <label>Cliente</label>
  <select name="cliente_id" required>
    <option value="">-- Selecciona --</option>
    <?php foreach ($clientes as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= ($aviso && (int)$aviso['cliente_id']===(int)$c['id'])?'selected':'' ?>>
        <?= e($c['nombre']) ?> (<?= e($c['rfc']) ?>)
      </option>
    <?php endforeach; ?>
  </select>

  <div class="grid2">
    <div>
      <label>Mes reportado (AAAAMM)</label>
      <input name="mes_reportado" maxlength="6" required value="<?= e($aviso['mes_reportado'] ?? '') ?>" />
    </div>
    <div>
      <label>Referencia aviso</label>
      <input name="referencia_aviso" required value="<?= e($aviso['referencia_aviso'] ?? '') ?>" />
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>Prioridad</label>
      <select name="prioridad" required>
        <?php $p = $aviso['prioridad'] ?? '1'; ?>
        <option value="1" <?= $p=='1'?'selected':'' ?>>1</option>
        <option value="2" <?= $p=='2'?'selected':'' ?>>2</option>
      </select>
    </div>
    <div>
      <label>Tipo alerta</label>
      <input name="tipo_alerta" required value="<?= e($aviso['tipo_alerta'] ?? '') ?>" />
    </div>
  </div>

  <label>Descripción alerta</label>
  <textarea name="descripcion_alerta" rows="2"><?= e($aviso['descripcion_alerta'] ?? '') ?></textarea>

  <div class="grid2">
    <div>
      <label>RFC entidad colegiada</label>
      <input name="clave_entidad_colegiada" maxlength="13" required value="<?= e($aviso['clave_entidad_colegiada'] ?? '') ?>" />
    </div>
    <div>
      <label>RFC sujeto obligado</label>
      <input name="clave_sujeto_obligado" maxlength="13" required value="<?= e($aviso['clave_sujeto_obligado'] ?? '') ?>" />
    </div>
  </div>

  <h2>Persona que solicita (persona_aviso)</h2>
  <div class="grid2">
    <div><label>Nombre(s)</label><input name="pa_nombre" required value="<?= e($aviso['pa_nombre'] ?? '') ?>" /></div>
    <div><label>RFC</label><input name="pa_rfc" maxlength="13" required value="<?= e($aviso['pa_rfc'] ?? '') ?>" /></div>
  </div>
  <div class="grid2">
    <div><label>Apellido paterno</label><input name="pa_ap" required value="<?= e($aviso['pa_ap'] ?? '') ?>" /></div>
    <div><label>CURP</label><input name="pa_curp" maxlength="18" required value="<?= e($aviso['pa_curp'] ?? '') ?>" /></div>
  </div>
  <div class="grid2">
    <div><label>Apellido materno</label><input name="pa_am" required value="<?= e($aviso['pa_am'] ?? '') ?>" /></div>
    <div><label>Fecha nacimiento (YYYY-MM-DD)</label><input name="pa_fn" required value="<?= e($aviso['pa_fn'] ?? '') ?>" /></div>
  </div>

  <h2>Detalle de la operación</h2>
  <div class="grid2">
    <div><label>Instrumento público</label><input name="instrumento_publico" required value="<?= e($aviso['instrumento_publico'] ?? '') ?>" /></div>
    <div><label>Fecha operación (YYYY-MM-DD)</label><input name="fecha_operacion" required value="<?= e($aviso['fecha_operacion'] ?? '') ?>" /></div>
  </div>

  <h2>Cesión</h2>
  <div class="grid2">
    <div><label>Identificador fideicomiso</label><input name="identificador_fideicomiso" required value="<?= e($aviso['identificador_fideicomiso'] ?? '') ?>" /></div>
    <div><label>RFC fiduciario</label><input name="rfc_fiduciario" maxlength="13" required value="<?= e($aviso['rfc_fiduciario'] ?? '') ?>" /></div>
  </div>
  <div class="grid2">
    <div><label>Denominación razón (fiduciario)</label><input name="denominacion_razon" required value="<?= e($aviso['denominacion_razon'] ?? '') ?>" /></div>
    <div><label>Tipo cesión</label><input name="tipo_cesion" required value="<?= e($aviso['tipo_cesion'] ?? '') ?>" /></div>
  </div>

  <div class="grid2">
    <div><label>Monto cesión</label><input name="monto_cesion" required value="<?= e($aviso['monto_cesion'] ?? '') ?>" /></div>
    <div>
      <label>Modificatorio</label>
      <?php $m = $aviso['es_modificatorio'] ?? '0'; ?>
      <select name="es_modificatorio">
        <option value="0" <?= $m=='0'?'selected':'' ?>>No</option>
        <option value="1" <?= $m=='1'?'selected':'' ?>>Sí</option>
      </select>
    </div>
  </div>

  <div class="grid2">
    <div><label>Folio modificación</label><input name="folio_modificacion" value="<?= e($aviso['folio_modificacion'] ?? '') ?>" /></div>
    <div><label>Descripción modificación</label><input name="descripcion_modificacion" value="<?= e($aviso['descripcion_modificacion'] ?? '') ?>" /></div>
  </div>

  <h2>Cedente</h2>
  <label>Elegir cliente (opcional)</label>
  <?php $cedSel = (int)($aviso['cedente_client_id'] ?? 0); ?>
  <select name="cedente_client_id" id="cedenteClient">
    <option value="">-- (captura manual) --</option>
    <?php foreach ($clientes as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= ($cedSel && (int)$c['id']===$cedSel) ? 'selected' : '' ?>>
        <?= e($c['nombre']) ?> (<?= e($c['rfc']) ?>)
      </option>
    <?php endforeach; ?>
  </select>

  <label>Tipo de persona</label>
  <?php $ct = $aviso['cedente_tipo'] ?? 'FISICA'; ?>
  <select name="cedente_tipo" required>
    <option value="FISICA" <?= $ct==='FISICA'?'selected':'' ?>>Persona física</option>
    <option value="MORAL" <?= $ct==='MORAL'?'selected':'' ?>>Persona moral</option>
    <option value="FIDEICOMISO" <?= $ct==='FIDEICOMISO'?'selected':'' ?>>Fideicomiso</option>
  </select>

  <div class="grid3">
    <div><label>Nombre / Denominación</label><input name="cedente_nombre" value="<?= e($aviso['cedente_nombre'] ?? '') ?>" /></div>
    <div><label>RFC</label><input name="cedente_rfc" value="<?= e($aviso['cedente_rfc'] ?? '') ?>" /></div>
    <div><label>Identificador fideicomiso</label><input name="cedente_identificador_fideicomiso" value="<?= e($aviso['cedente_identificador_fideicomiso'] ?? '') ?>" /></div>
  </div>
  <div class="grid3">
    <div><label>Apellido paterno</label><input name="cedente_ap" value="<?= e($aviso['cedente_ap'] ?? '') ?>" /></div>
    <div><label>Apellido materno</label><input name="cedente_am" value="<?= e($aviso['cedente_am'] ?? '') ?>" /></div>
    <div><label>Fecha base (YYYY-MM-DD)</label><input name="cedente_fecha" value="<?= e($aviso['cedente_fecha'] ?? '') ?>" /></div>
  </div>
  <div class="grid3">
    <div><label>País nacionalidad (ISO2)</label><input name="cedente_pais" value="<?= e($aviso['cedente_pais'] ?? '') ?>" /></div>
    <div><label>Actividad económica (código)</label><input name="cedente_actividad" value="<?= e($aviso['cedente_actividad'] ?? '') ?>" /></div>
    <div><label>Giro mercantil (código)</label><input name="cedente_giro" value="<?= e($aviso['cedente_giro'] ?? '') ?>" /></div>
  </div>

  <h2>Cesionario</h2>
  <label>Elegir cliente (opcional)</label>
  <?php $cesSel = (int)($aviso['cesionario_client_id'] ?? 0); ?>
  <select name="cesionario_client_id" id="cesionarioClient">
    <option value="">-- (captura manual) --</option>
    <?php foreach ($clientes as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= ($cesSel && (int)$c['id']===$cesSel) ? 'selected' : '' ?>>
        <?= e($c['nombre']) ?> (<?= e($c['rfc']) ?>)
      </option>
    <?php endforeach; ?>
  </select>

  <?php $st = $aviso['cesionario_tipo'] ?? 'FISICA'; ?>
  <label>Tipo de persona</label>
  <select name="cesionario_tipo" required>
    <option value="FISICA" <?= $st==='FISICA'?'selected':'' ?>>Persona física</option>
    <option value="MORAL" <?= $st==='MORAL'?'selected':'' ?>>Persona moral</option>
    <option value="FIDEICOMISO" <?= $st==='FIDEICOMISO'?'selected':'' ?>>Fideicomiso</option>
  </select>

  <div class="grid3">
    <div><label>Nombre / Denominación</label><input name="cesionario_nombre" value="<?= e($aviso['cesionario_nombre'] ?? '') ?>" /></div>
    <div><label>RFC</label><input name="cesionario_rfc" value="<?= e($aviso['cesionario_rfc'] ?? '') ?>" /></div>
    <div><label>Identificador fideicomiso</label><input name="cesionario_identificador_fideicomiso" value="<?= e($aviso['cesionario_identificador_fideicomiso'] ?? '') ?>" /></div>
  </div>
  <div class="grid3">
    <div><label>Apellido paterno</label><input name="cesionario_ap" value="<?= e($aviso['cesionario_ap'] ?? '') ?>" /></div>
    <div><label>Apellido materno</label><input name="cesionario_am" value="<?= e($aviso['cesionario_am'] ?? '') ?>" /></div>
    <div><label>Fecha base (YYYY-MM-DD)</label><input name="cesionario_fecha" value="<?= e($aviso['cesionario_fecha'] ?? '') ?>" /></div>
  </div>
  <div class="grid3">
    <div><label>País nacionalidad (ISO2)</label><input name="cesionario_pais" value="<?= e($aviso['cesionario_pais'] ?? '') ?>" /></div>
    <div><label>Actividad económica (código)</label><input name="cesionario_actividad" value="<?= e($aviso['cesionario_actividad'] ?? '') ?>" /></div>
    <div><label>Giro mercantil (código)</label><input name="cesionario_giro" value="<?= e($aviso['cesionario_giro'] ?? '') ?>" /></div>
  </div>

  <div class="actions">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn btn-ghost" href="<?= e(app_url('/avisos')) ?>">Volver</a>
  </div>
</form>

<?php if ($aviso): ?>
  <hr />
  <form method="post" action="<?= e(app_url('/avisos/generate-xml')) ?>">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
    <input type="hidden" name="id" value="<?= (int)$aviso['id'] ?>" />
    <button class="btn" type="submit">Generar XML</button>
  </form>
<?php endif; ?>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
