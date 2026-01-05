<?php
// app/xml_fep.php
function ymdNoDash(?string $dateYmd): ?string {
  if (!$dateYmd) return null;
  return str_replace('-', '', $dateYmd); // YYYYMMDD
}

function appendTipoPersona(DOMDocument $doc, DOMElement $tipoPersonaNode, string $NS, array $p): void {
  switch ($p['tipo']) {
    case 'FISICA': {
      $pf = $tipoPersonaNode->appendChild($doc->createElementNS($NS, 'fep:persona_fisica'));
      $pf->appendChild($doc->createElementNS($NS, 'fep:nombre', $p['nombre']));
      $pf->appendChild($doc->createElementNS($NS, 'fep:apellido_paterno', $p['apellido_paterno']));
      $pf->appendChild($doc->createElementNS($NS, 'fep:apellido_materno', $p['apellido_materno']));
      $pf->appendChild($doc->createElementNS($NS, 'fep:fecha_nacimiento', ymdNoDash($p['fecha'])));
      $pf->appendChild($doc->createElementNS($NS, 'fep:rfc', $p['rfc']));
      $pf->appendChild($doc->createElementNS($NS, 'fep:pais_nacionalidad', $p['pais']));
      $pf->appendChild($doc->createElementNS($NS, 'fep:actividad_economica', $p['actividad']));
      return;
    }
    case 'MORAL': {
      $pm = $tipoPersonaNode->appendChild($doc->createElementNS($NS, 'fep:persona_moral'));
      $pm->appendChild($doc->createElementNS($NS, 'fep:denominacion_razon', $p['denominacion_razon']));
      $pm->appendChild($doc->createElementNS($NS, 'fep:fecha_constitucion', ymdNoDash($p['fecha'])));
      $pm->appendChild($doc->createElementNS($NS, 'fep:rfc', $p['rfc']));
      $pm->appendChild($doc->createElementNS($NS, 'fep:pais_nacionalidad', $p['pais']));
      $pm->appendChild($doc->createElementNS($NS, 'fep:giro_mercantil', $p['giro']));
      return;
    }
    case 'FIDEICOMISO': {
      $f = $tipoPersonaNode->appendChild($doc->createElementNS($NS, 'fep:fideicomiso'));
      $f->appendChild($doc->createElementNS($NS, 'fep:denominacion_razon', $p['denominacion_razon']));
      $f->appendChild($doc->createElementNS($NS, 'fep:rfc', $p['rfc']));
      $f->appendChild($doc->createElementNS($NS, 'fep:identificador_fideicomiso', $p['identificador_fideicomiso']));
      return;
    }
    default:
      throw new InvalidArgumentException('Tipo no soportado: ' . $p['tipo']);
  }
}

function buildFepXml(array $d): string {
  $NS = 'http://www.uif.shcp.gob.mx/recepcion/fep';

  $doc = new DOMDocument('1.0', 'UTF-8');
  $doc->formatOutput = true;

  $archivo = $doc->createElementNS($NS, 'fep:archivo');
  $archivo->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', $NS . ' fep.xsd');
  $doc->appendChild($archivo);

  $informe = $archivo->appendChild($doc->createElementNS($NS, 'fep:informe'));
  $informe->appendChild($doc->createElementNS($NS, 'fep:mes_reportado', $d['mes_reportado']));

  $so = $informe->appendChild($doc->createElementNS($NS, 'fep:sujeto_obligado'));
  $so->appendChild($doc->createElementNS($NS, 'fep:clave_entidad_colegiada', $d['clave_entidad_colegiada']));
  $so->appendChild($doc->createElementNS($NS, 'fep:clave_sujeto_obligado', $d['clave_sujeto_obligado']));
  $so->appendChild($doc->createElementNS($NS, 'fep:clave_actividad', 'FEP'));

  $aviso = $informe->appendChild($doc->createElementNS($NS, 'fep:aviso'));
  $aviso->appendChild($doc->createElementNS($NS, 'fep:referencia_aviso', $d['referencia_aviso']));
  $aviso->appendChild($doc->createElementNS($NS, 'fep:prioridad', (string)$d['prioridad']));

  if (!empty($d['es_modificatorio'])) {
    $m = $aviso->appendChild($doc->createElementNS($NS, 'fep:modificatorio'));
    $m->appendChild($doc->createElementNS($NS, 'fep:folio_modificacion', $d['folio_modificacion']));
    $m->appendChild($doc->createElementNS($NS, 'fep:descripcion_modificacion', $d['descripcion_modificacion']));
  }

  $alerta = $aviso->appendChild($doc->createElementNS($NS, 'fep:alerta'));
  $alerta->appendChild($doc->createElementNS($NS, 'fep:tipo_alerta', $d['tipo_alerta']));
  $alerta->appendChild($doc->createElementNS($NS, 'fep:descripcion_alerta', $d['descripcion_alerta']));

  $pa = $aviso->appendChild($doc->createElementNS($NS, 'fep:persona_aviso'));
  $pa->appendChild($doc->createElementNS($NS, 'fep:nombre', $d['persona_aviso']['nombre']));
  $pa->appendChild($doc->createElementNS($NS, 'fep:apellido_paterno', $d['persona_aviso']['apellido_paterno']));
  $pa->appendChild($doc->createElementNS($NS, 'fep:apellido_materno', $d['persona_aviso']['apellido_materno']));
  $pa->appendChild($doc->createElementNS($NS, 'fep:fecha_nacimiento', ymdNoDash($d['persona_aviso']['fecha_nacimiento'])));
  $pa->appendChild($doc->createElementNS($NS, 'fep:rfc', $d['persona_aviso']['rfc']));
  $pa->appendChild($doc->createElementNS($NS, 'fep:curp', $d['persona_aviso']['curp']));

  $detalle = $aviso->appendChild($doc->createElementNS($NS, 'fep:detalle_operaciones'));
  $op = $detalle->appendChild($doc->createElementNS($NS, 'fep:datos_operacion'));
  $op->appendChild($doc->createElementNS($NS, 'fep:instrumento_publico', $d['instrumento_publico']));
  $op->appendChild($doc->createElementNS($NS, 'fep:fecha_operacion', ymdNoDash($d['fecha_operacion'])));

  $tipoAct = $op->appendChild($doc->createElementNS($NS, 'fep:tipo_actividad'));
  $cesion = $tipoAct->appendChild($doc->createElementNS($NS, 'fep:cesion_derechos_fideicomitente_fideicomisario'));

  $cesion->appendChild($doc->createElementNS($NS, 'fep:identificador_fideicomiso', $d['identificador_fideicomiso']));
  $cesion->appendChild($doc->createElementNS($NS, 'fep:rfc', $d['rfc_fiduciario']));
  $cesion->appendChild($doc->createElementNS($NS, 'fep:denominacion_razon', $d['denominacion_razon']));
  $cesion->appendChild($doc->createElementNS($NS, 'fep:tipo_cesion', (string)$d['tipo_cesion']));

  $dc = $cesion->appendChild($doc->createElementNS($NS, 'fep:datos_cedente'));
  $tpC = $dc->appendChild($doc->createElementNS($NS, 'fep:tipo_persona'));
  appendTipoPersona($doc, $tpC, $NS, $d['cedente']);

  $ds = $cesion->appendChild($doc->createElementNS($NS, 'fep:datos_cesionario'));
  $tpS = $ds->appendChild($doc->createElementNS($NS, 'fep:tipo_persona'));
  appendTipoPersona($doc, $tpS, $NS, $d['cesionario']);

  $datosCesion = $cesion->appendChild($doc->createElementNS($NS, 'fep:datos_cesion'));
  $datosCesion->appendChild($doc->createElementNS($NS, 'fep:monto_cesion', number_format((float)$d['monto_cesion'], 2, '.', '')));

  return $doc->saveXML();
}
