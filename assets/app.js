// public/assets/app.js
async function loadNotifications(){
  const el = document.getElementById('notifList');
  if(!el) return;
  try{
    const r = await fetch('/api/notifications', {headers:{'Accept':'application/json'}});
    if(!r.ok) throw new Error('HTTP ' + r.status);
    const data = await r.json();

    const items = [];
    if(data.counts.expiring_docs){
      const top = data.expiring_docs.slice(0,3).map(d => `${d.cliente}: ${d.doc_tipo} vence ${d.vence}`).join('<br/>');
      items.push(`<div class="notif-item"><b>Documentos por vencer (${data.counts.expiring_docs})</b><div class="muted">${top}</div></div>`);
    }
    if(data.counts.missing_clients){
      const top = data.missing_clients.slice(0,3).map(c => `${c.cliente} (${c.tipo}) — falta: ${c.missing.join(', ')}`).join('<br/>');
      items.push(`<div class="notif-item"><b>Clientes incompletos (${data.counts.missing_clients})</b><div class="muted">${top}</div></div>`);
    }
    if(data.counts.required_avisos){
      const top = data.required_avisos.slice(0,3).map(a => `${a.cliente}: ${a.reason}`).join('<br/>');
      items.push(`<div class="notif-item"><b>Avisos requeridos (${data.counts.required_avisos})</b><div class="muted">${top}</div></div>`);
    }
    if(data.counts.draft_avisos){
      const top = data.draft_avisos.slice(0,3).map(a => `${a.cliente}: ${a.mes_reportado} / ${a.referencia_aviso}`).join('<br/>');
      items.push(`<div class="notif-item"><b>Avisos pendientes (${data.counts.draft_avisos})</b><div class="muted">${top}</div></div>`);
    }

    el.innerHTML = items.length ? items.join('') : '<div class="muted">Sin notificaciones.</div>';
  }catch(e){
    el.innerHTML = '<div class="muted">No se pudieron cargar notificaciones.</div>';
  }
}

loadNotifications();
setInterval(loadNotifications, 60000);


async function fetchClient(id){
  const r = await fetch('/api/client?id=' + encodeURIComponent(id), {headers:{'Accept':'application/json'}});
  if(!r.ok) throw new Error('HTTP ' + r.status);
  return await r.json();
}
function setVal(name, val){
  const el = document.querySelector(`[name="${name}"]`);
  if(el && (val !== null && val !== undefined)) el.value = val;
}
async function bindClientPickers(){
  const cedSel = document.getElementById('cedenteClient');
  const cesSel = document.getElementById('cesionarioClient');
  if(!cedSel && !cesSel) return;

  async function apply(role, clientId){
    if(!clientId) return;
    const c = await fetchClient(clientId);
    setVal(role + '_tipo', c.tipo);
    if(c.tipo === 'FISICA'){
      setVal(role + '_nombre', c.nombre);
      setVal(role + '_rfc', c.rfc);
      setVal(role + '_pais', c.pais_nacionalidad || 'MX');
      setVal(role + '_actividad', c.actividad_economica || '');
      setVal(role + '_fecha', c.fecha_base || '');
    } else if(c.tipo === 'MORAL'){
      setVal(role + '_nombre', c.nombre);
      setVal(role + '_rfc', c.rfc);
      setVal(role + '_pais', c.pais_nacionalidad || 'MX');
      setVal(role + '_giro', c.giro_mercantil || '');
      setVal(role + '_fecha', c.fecha_base || '');
    } else if(c.tipo === 'FIDEICOMISO'){
      setVal(role + '_nombre', c.nombre);
      setVal(role + '_rfc', c.rfc);
    }
  }

  if(cedSel) cedSel.addEventListener('change', async ()=>{ try{ await apply('cedente', cedSel.value);}catch(e){} });
  if(cesSel) cesSel.addEventListener('change', async ()=>{ try{ await apply('cesionario', cesSel.value);}catch(e){} });
}
bindClientPickers();
