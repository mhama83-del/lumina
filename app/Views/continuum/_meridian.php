<?php
/** @var array $map from MeridianMapService::build(). Read defensively. */
$map = $map ?? ($data['map'] ?? ['legend'=>['dashed'=>'','filled'=>'','note'=>''],'axes'=>[],'text_alternative'=>[]]);
$axes = $map['axes'] ?? []; $n = max(1, count($axes)); $cx=150; $cy=150; $R=104;
$pt = function ($i,$r) use ($n,$cx,$cy,$R){ $a=-M_PI/2 + $i*2*M_PI/$n; return [$cx+cos($a)*$R*$r, $cy+sin($a)*$R*$r]; };
$reflection=$evidence=[];
foreach ($axes as $i=>$ax){ $reflection[]=$pt($i,max(0.03,$ax['reflection_layer']??0)); $evidence[]=$pt($i,max(0.0,$ax['evidence_layer']??0)); }
$poly = fn($pts)=>implode(' ', array_map(fn($p)=>round($p[0],1).','.round($p[1],1), $pts));
?>
<div class="card">
  <h3>Meridian Map</h3>
  <div class="meridian-wrap">
    <div>
      <svg viewBox="0 0 300 300" width="100%" height="auto" role="img"
        aria-label="Meridian Map: the dashed indigo outline is self-described reflection coverage; the filled teal shape is source-backed evidence coverage across the five EDGE areas.">
        <defs><radialGradient id="mg" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#0ea5a4" stop-opacity=".22"/><stop offset="100%" stop-color="#0ea5a4" stop-opacity=".08"/>
        </radialGradient></defs>
        <g fill="none" stroke="#e7eaef">
          <?php for($ring=1;$ring<=3;$ring++): $rp=[]; for($i=0;$i<$n;$i++){$rp[]=$pt($i,$ring/3);} ?>
            <polygon points="<?= $poly($rp) ?>"></polygon>
          <?php endfor; ?>
          <?php for($i=0;$i<$n;$i++): [$ex,$ey]=$pt($i,1); ?><line x1="150" y1="150" x2="<?= round($ex,1) ?>" y2="<?= round($ey,1) ?>"></line><?php endfor; ?>
        </g>
        <?php if($evidence): ?><polygon points="<?= $poly($evidence) ?>" fill="url(#mg)" stroke="#047857" stroke-width="2"></polygon><?php endif; ?>
        <?php if($reflection): ?><polygon points="<?= $poly($reflection) ?>" fill="none" stroke="#4f46e5" stroke-width="2" stroke-dasharray="4 4"></polygon><?php endif; ?>
        <?php foreach($axes as $i=>$ax): [$lx,$ly]=$pt($i,1.2); ?>
          <text x="<?= round($lx) ?>" y="<?= round($ly)+3 ?>" font-size="9" font-family="Inter,sans-serif" fill="#727d8e" text-anchor="middle"><?= esc(explode(' ',$ax['label'])[0]) ?></text>
        <?php endforeach; ?>
      </svg>
    </div>
    <div>
      <p class="small muted" style="margin-top:0"><?= esc($map['legend']['note'] ?? '') ?></p>
      <div class="small" style="display:flex;flex-direction:column;gap:7px;margin:10px 0">
        <span style="display:flex;align-items:center;gap:8px"><svg width="22" height="8"><line x1="0" y1="4" x2="22" y2="4" stroke="#4f46e5" stroke-width="2" stroke-dasharray="4 4"/></svg> Reflection · self-described</span>
        <span style="display:flex;align-items:center;gap:8px"><svg width="22" height="12"><rect width="22" height="12" rx="2" fill="rgba(4,120,87,.18)" stroke="#047857"/></svg> Evidence · source-backed</span>
      </div>
    </div>
  </div>
  <details>
    <summary>Text alternative</summary>
    <table>
      <thead><tr><th>EDGE area</th><th>Reflection</th><th>Evidence</th></tr></thead>
      <tbody>
      <?php foreach (($map['text_alternative'] ?? []) as $row): ?>
        <tr><td><?= esc($row['area']) ?></td><td><?= esc($row['reflection']) ?></td><td><?= esc($row['evidence']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </details>
</div>
