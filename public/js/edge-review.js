// Lumina EDGE — Review what Lumina saw (Fasa 2c)
var EDGE_ADD = [], EDGE_HINTS = {}, EDGE_SIGNAMES = {};
function edgeChip(key, label){ return '<span class="edge-chip" data-sig="'+key+'">'+(label||key)+'</span>'; }
function renderEdgeReview(data){
  var box = document.getElementById('rEdgeReview');
  if(!box || !data.edge_suggestions){ return; }
  EDGE_ADD = data.edge_add || []; EDGE_HINTS = data.edge_hints || {}; EDGE_SIGNAMES = data.edge_signals || {};
  function rowHtml(s){
    var chips = (s.signals||[]).map(function(k){ return edgeChip(k, EDGE_SIGNAMES[k]); }).join(' ');
    var st = (s.status||'inferred'); var stLabel = st.charAt(0).toUpperCase()+st.slice(1);
    return '<tr data-id="'+s.id+'"><td class="edge-quote">"'+s.quote+'"</td><td>'+chips+'</td>'+
      '<td><span class="edge-status edge-'+st+'">'+stLabel+'</span></td>'+
      '<td class="edge-actions"><button type="button" class="edge-btn edge-edit">Edit</button> '+
      '<button type="button" class="edge-btn edge-remove">Remove</button></td></tr>';
  }
  var rows = data.edge_suggestions.map(rowHtml).join('');
  box.innerHTML = '<div class="section-label">Review what Lumina saw</div>'+
    '<table class="edge-review"><thead><tr><th>From your CV</th><th>Lumina links to</th><th>Status</th><th></th></tr></thead>'+
    '<tbody id="edgeRows">'+rows+'</tbody></table>'+
    '<button type="button" class="btn btn-ghost btn-sm" id="edgeAddBtn">+ Add your own example</button>'+
    '<div id="edgeAddBox"></div><p class="edge-footnote">'+(data.edge_footnote||'')+'</p>';
  wireEdgeReview();
}
function wireEdgeReview(){
  document.querySelectorAll('#edgeRows .edge-remove').forEach(function(b){
    b.onclick = function(){ var tr=b.closest('tr'); var id=tr.getAttribute('data-id'); tr.parentNode.removeChild(tr); edgeAction('remove',{id:id}); };
  });
  document.querySelectorAll('#edgeRows .edge-edit').forEach(function(b){
    b.onclick = function(){ openEdgeEdit(b.closest('tr')); };
  });
  var ab = document.getElementById('edgeAddBtn'); if(ab) ab.onclick = openEdgeAdd;
}
function openEdgeEdit(tr){
  var id = tr.getAttribute('data-id');
  var hint = EDGE_HINTS.outcome || 'Add the outcome — what changed or improved?';
  var er = document.createElement('tr'); er.className='edge-editrow';
  er.innerHTML = '<td colspan="4"><div class="edge-edit-panel"><div class="edge-hint">'+hint+'</div>'+
    '<input type="text" class="edge-input" id="editOutcome" placeholder="e.g. cut manual roll-call time by 80%">'+
    '<div class="row" style="margin-top:8px"><button type="button" class="btn btn-primary btn-sm" id="editSave">Save as evidence</button> '+
    '<button type="button" class="btn btn-ghost btn-sm" id="editCancel">Cancel</button></div></div></td>';
  tr.parentNode.insertBefore(er, tr.nextSibling);
  er.querySelector('#editCancel').onclick = function(){ er.parentNode.removeChild(er); };
  er.querySelector('#editSave').onclick = function(){
    var o = er.querySelector('#editOutcome').value.trim();
    if(o){ var sc=tr.querySelector('.edge-status'); sc.className='edge-status edge-supported'; sc.textContent='Supported'; edgeAction('edit',{id:id,outcome:o}); }
    er.parentNode.removeChild(er);
  };
}
function openEdgeAdd(){
  var box = document.getElementById('edgeAddBox'); if(!box) return;
  if(box.innerHTML){ box.innerHTML=''; return; }
  var sugg = EDGE_ADD.map(function(a, idx){
    var chips = (a.signals||[]).map(function(k){ return edgeChip(k, EDGE_SIGNAMES[k]); }).join(' ');
    return '<div class="edge-add-sugg" data-idx="'+idx+'"><div class="edge-quote">"'+a.quote+'"</div><div>'+chips+'</div>'+
      '<button type="button" class="btn btn-ghost btn-sm edge-add-use">Add this</button></div>';
  }).join('');
  box.innerHTML = '<div class="edge-add-panel"><div class="edge-hint">Pick a ready example, or let these spark your own:</div>'+sugg+'</div>';
  box.querySelectorAll('.edge-add-use').forEach(function(b){
    b.onclick = function(){
      var idx = b.closest('.edge-add-sugg').getAttribute('data-idx'); var a = EDGE_ADD[idx];
      var tbody = document.getElementById('edgeRows'); var newId='add'+idx;
      var chips = (a.signals||[]).map(function(k){ return edgeChip(k, EDGE_SIGNAMES[k]); }).join(' ');
      var tr = document.createElement('tr'); tr.setAttribute('data-id', newId);
      tr.innerHTML = '<td class="edge-quote">"'+a.quote+'"</td><td>'+chips+'</td>'+
        '<td><span class="edge-status edge-supported">Supported</span></td>'+
        '<td class="edge-actions"><button type="button" class="edge-btn edge-remove">Remove</button></td>';
      tbody.appendChild(tr);
      tr.querySelector('.edge-remove').onclick = function(){ tr.parentNode.removeChild(tr); edgeAction('remove',{id:newId}); };
      edgeAction('add', {quote:a.quote, signals:a.signals}); box.innerHTML='';
    };
  });
}
function edgeAction(action, payload){
  payload.action = action;
  fetch(EDGE_ACTION_URL, {method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(payload)}).catch(function(){});
}
