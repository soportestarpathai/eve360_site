<?php ob_start(); ?>

<h1><?= $cliente ? 'Editar cliente' : 'Nuevo cliente' ?></h1>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>
<?php if (!empty($message)): ?>
  <div class="alert ok"><?= e($message) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(app_url($cliente ? '/clientes/update' : '/clientes/create')) ?>">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
  <?php if ($cliente): ?>
    <input type="hidden" name="id" value="<?= (int)$cliente['id'] ?>" />
  <?php endif; ?>

  <label>Tipo de cliente</label>
  <?php $t = $cliente['tipo'] ?? 'FISICA'; ?>
  <select name="tipo" id="tipoCliente" required>
    <option value="FISICA" <?= $t==='FISICA'?'selected':'' ?>>Persona física</option>
    <option value="MORAL" <?= $t==='MORAL'?'selected':'' ?>>Persona moral</option>
    <option value="FIDEICOMISO" <?= $t==='FIDEICOMISO'?'selected':'' ?>>Fideicomiso</option>
  </select>

  <h2 style="margin-top:16px;">Identificación</h2>

  <!-- Common -->
  <label>Nombre / Denominación o razón social</label>
  <input name="nombre" value="<?= e($cliente['nombre'] ?? '') ?>" required maxlength="300" />

  <div class="grid2">
    <div>
      <label>RFC</label>
      <input name="rfc" value="<?= e($cliente['rfc'] ?? '') ?>" required maxlength="13" />
    </div>
    <div>
      <label>Correo electrónico</label>
      <input name="email" type="email" value="<?= e($cliente['email'] ?? '') ?>" />
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>País de nacionalidad (ISO2)</label>
      <input name="pais_nacionalidad" value="<?= e($cliente['pais_nacionalidad'] ?? 'MX') ?>" maxlength="2" />
    </div>
    <div>
      <label>País de nacimiento (ISO2)</label>
      <input name="pais_nacimiento" value="<?= e($cliente['pais_nacimiento'] ?? '') ?>" maxlength="2" />
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>Extranjero</label>
      <?php $ext = (int)($cliente['extranjero'] ?? 0); ?>
      <select name="extranjero">
        <option value="0" <?= $ext===0?'selected':'' ?>>No</option>
        <option value="1" <?= $ext===1?'selected':'' ?>>Sí</option>
      </select>
    </div>
    <div>
      <label>CURP (si aplica)</label>
      <input name="curp" value="<?= e($cliente['curp'] ?? '') ?>" maxlength="18" />
    </div>
  </div>

  <!-- FISICA fields -->
  <div id="kycFisica" style="margin-top:16px;">
    <h2>Persona física</h2>
    <div class="grid3">
      <div>
        <label>Apellido paterno</label>
        <input name="apellido_paterno" value="<?= e($cliente['apellido_paterno'] ?? '') ?>" />
      </div>
      <div>
        <label>Apellido materno</label>
        <input name="apellido_materno" value="<?= e($cliente['apellido_materno'] ?? '') ?>" />
      </div>
      <div>
        <label>Nombre(s)</label>
        <input name="nombres" value="<?= e($cliente['nombres'] ?? '') ?>" />
      </div>
    </div>

    <div class="grid2">
      <div>
        <label>Fecha de nacimiento (YYYY-MM-DD)</label>
        <input name="fecha_base" value="<?= e($cliente['fecha_base'] ?? '') ?>" placeholder="YYYY-MM-DD" />
      </div>
      <div>
        <label>Actividad / ocupación / profesión</label>
        <input name="ocupacion" value="<?= e($cliente['ocupacion'] ?? '') ?>" maxlength="200" />
      </div>
    </div>
  </div>

  <!-- MORAL fields -->
  <div id="kycMoral" style="margin-top:16px;">
    <h2>Persona moral (nacionalidad mexicana)</h2>

    <div class="grid2">
      <div>
        <label>Fecha de constitución (YYYY-MM-DD)</label>
        <input name="fecha_base" value="<?= e($cliente['fecha_base'] ?? '') ?>" placeholder="YYYY-MM-DD" />
      </div>
      <div>
        <label>Actividad / objeto social preponderante</label>
        <input name="actividad_economica" value="<?= e($cliente['actividad_economica'] ?? '') ?>" maxlength="20" />
      </div>
    </div>

    <label>Objeto social (texto)</label>
    <textarea name="objeto_social" rows="3"><?= e($cliente['objeto_social'] ?? '') ?></textarea>

    <h3 style="margin-top:14px;">Representante legal / apoderado</h3>
    <div class="grid2">
      <div>
        <label>Nombre completo</label>
        <input name="rep_nombre" value="<?= e($cliente['rep_nombre'] ?? '') ?>" />
      </div>
      <div>
        <label>RFC representante</label>
        <input name="rep_rfc" value="<?= e($cliente['rep_rfc'] ?? '') ?>" maxlength="13" />
      </div>
    </div>
    <div class="grid2">
      <div>
        <label>CURP representante (si aplica)</label>
        <input name="rep_curp" value="<?= e($cliente['rep_curp'] ?? '') ?>" maxlength="18" />
      </div>
      <div>
        <label>Nacionalidad representante (ISO2)</label>
        <input name="rep_nacionalidad" value="<?= e($cliente['rep_nacionalidad'] ?? '') ?>" maxlength="2" />
      </div>
    </div>
    <div class="grid2">
      <div>
        <label>Fecha nacimiento representante (YYYY-MM-DD)</label>
        <input name="rep_fecha_nacimiento" value="<?= e($cliente['rep_fecha_nacimiento'] ?? '') ?>" placeholder="YYYY-MM-DD" />
      </div>
      <div></div>
    </div>
  </div>

  <!-- FIDEICOMISO fields (baseline) -->
  <div id="kycFideicomiso" style="margin-top:16px;">
    <h2>Fideicomiso</h2>
    <div class="grid2">
      <div>
        <label>Identificador de fideicomiso</label>
        <input name="fideicomiso_identificador" value="<?= e($cliente['fideicomiso_identificador'] ?? '') ?>" />
      </div>
      <div>
        <label>Fecha del contrato (YYYY-MM-DD) (opcional)</label>
        <input name="fecha_base" value="<?= e($cliente['fecha_base'] ?? '') ?>" placeholder="YYYY-MM-DD" />
      </div>
    </div>
    <div class="grid2">
      <div>
        <label>RFC Fiduciario</label>
        <input name="fiduciario_rfc" value="<?= e($cliente['fiduciario_rfc'] ?? '') ?>" maxlength="13" />
      </div>
      <div>
        <label>Nombre/razón social Fiduciario</label>
        <input name="fiduciario_nombre" value="<?= e($cliente['fiduciario_nombre'] ?? '') ?>" />
      </div>
    </div>
  </div>

  <h2 style="margin-top:16px;">Domicilio</h2>
  <div class="grid3">
    <div><label>Calle / Avenida / Vía</label><input name="dom_calle" value="<?= e($cliente['dom_calle'] ?? '') ?>" /></div>
    <div><label>Núm. exterior</label><input name="dom_num_ext" value="<?= e($cliente['dom_num_ext'] ?? '') ?>" /></div>
    <div><label>Núm. interior</label><input name="dom_num_int" value="<?= e($cliente['dom_num_int'] ?? '') ?>" /></div>
  </div>
  <div class="grid3">
    <div><label>Colonia</label><input name="dom_colonia" value="<?= e($cliente['dom_colonia'] ?? '') ?>" /></div>
    <div><label>Municipio / Demarcación</label><input name="dom_municipio" value="<?= e($cliente['dom_municipio'] ?? '') ?>" /></div>
    <div><label>Ciudad / Población</label><input name="dom_ciudad" value="<?= e($cliente['dom_ciudad'] ?? '') ?>" /></div>
  </div>
  <div class="grid2">
    <div><label>Estado / Entidad</label><input name="dom_estado" value="<?= e($cliente['dom_estado'] ?? '') ?>" /></div>
    <div><label>Código postal</label><input name="dom_cp" value="<?= e($cliente['dom_cp'] ?? '') ?>" maxlength="10" /></div>
  </div>

  <h2 style="margin-top:16px;">Contacto</h2>
  <div class="grid2">
    <div><label>Teléfono 1</label><input name="tel1" value="<?= e($cliente['tel1'] ?? '') ?>" /></div>
    <div><label>Extensión 1</label><input name="tel1_ext" value="<?= e($cliente['tel1_ext'] ?? '') ?>" /></div>
  </div>
  <div class="grid2">
    <div><label>Teléfono 2</label><input name="tel2" value="<?= e($cliente['tel2'] ?? '') ?>" /></div>
    <div><label>Extensión 2</label><input name="tel2_ext" value="<?= e($cliente['tel2_ext'] ?? '') ?>" /></div>
  </div>

  <div class="actions">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn btn-ghost" href="<?= e(app_url('/clientes')) ?>">Volver</a>
  </div>
</form>

<script>
(function(){
  const tipo = document.getElementById('tipoCliente');
  const f = document.getElementById('kycFisica');
  const m = document.getElementById('kycMoral');
  const fi = document.getElementById('kycFideicomiso');
  function refresh(){
    const v = (tipo && tipo.value) ? tipo.value : 'FISICA';
    if (f) f.style.display = (v === 'FISICA') ? '' : 'none';
    if (m) m.style.display = (v === 'MORAL') ? '' : 'none';
    if (fi) fi.style.display = (v === 'FIDEICOMISO') ? '' : 'none';
  }
  if (tipo) tipo.addEventListener('change', refresh);
  refresh();
})();
</script>

<?php if ($cliente): ?>
  <hr />

  <h2>Documentos</h2>

  <form method="post" action="<?= e(app_url('/clientes/docs/add')) ?>" enctype="multipart/form-data" class="card" style="margin-top:10px;">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token($config)) ?>" />
    <input type="hidden" name="cliente_id" value="<?= (int)$cliente['id'] ?>" />

    <label>Tipo de documento</label>
    <input name="doc_tipo" required maxlength="50" placeholder="INE, Pasaporte, Acta constitutiva, Poder, RFC, Domicilio..." />

    <label>Archivo</label>
    <input type="file" name="archivo" required />

    <label>Vence (YYYY-MM-DD)</label>
    <input name="vence" required placeholder="YYYY-MM-DD" />

    <div class="actions">
      <button class="btn" type="submit">Subir</button>
    </div>
  </form>

  <div class="card" style="margin-top:12px;">
    <table class="table">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Archivo</th>
          <th>Vence</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($docs as $d): ?>
          <?php
            $href = $d['file_path'] ?? '';
            if (is_string($href) && $href !== '' && $href[0] === '/') {
              $href = rtrim($config['app']['base_url'] ?? '', '/') . $href;
            }
          ?>
          <tr>
            <td><?= e($d['doc_tipo']) ?></td>
            <td><?= $href ? '<a class="btn-link" target="_blank" rel="noopener" href="'.e($href).'">Ver</a>' : '—' ?></td>
            <td><?= e($d['vence']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php $content = ob_get_clean(); require __DIR__ . '/layout.php'; ?>
