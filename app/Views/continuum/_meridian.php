<?php /** @var array $map from MeridianMapService::build() */ ?>
<div class="card">
  <h3 style="margin-top:0">Meridian Map</h3>
  <p class="small muted"><?= esc($map['legend']['dashed']) ?><br><?= esc($map['legend']['filled']) ?><br>
     <strong><?= esc($map['legend']['note']) ?></strong></p>
  <?php
    $axes = $map['axes']; $n = count($axes); $cx = 160; $cy = 150; $R = 110;
    $pt = function ($i, $r) use ($n, $cx, $cy, $R) {
        $a = -M_PI/2 + $i * 2*M_PI/$n;
        return [$cx + cos($a)*$R*$r, $cy + sin($a)*$R*$r];
    };
    $reflection = $evidence = [];
    foreach ($axes as $i => $ax) { $reflection[] = $pt($i, max(0.02,$ax['reflection_layer'])); $evidence[] = $pt($i, max(0.0,$ax['evidence_layer'])); }
    $poly = fn($pts) => implode(' ', array_map(fn($p)=>round($p[0],1).','.round($p[1],1), $pts));
  ?>
  <svg viewBox="0 0 470 300" width="100%" height="auto" role="img"
       aria-label="Meridian Map: dashed line is reflection coverage, filled shape is source-backed evidence coverage across five EDGE areas.">
    <g fill="none" stroke="#dfe4ea">
      <?php for ($ring=1;$ring<=3;$ring++): $rp=[]; for($i=0;$i<$n;$i++){$rp[]=$pt($i,$ring/3);} ?>
        <polygon points="<?= $poly($rp) ?>"></polygon>
      <?php endfor; ?>
    </g>
    <polygon points="<?= $poly($evidence) ?>" fill="rgba(23,114,69,.20)" stroke="#177245" stroke-width="2"></polygon>
    <polygon points="<?= $poly($reflection) ?>" fill="none" stroke="#2b5ce6" stroke-width="2" stroke-dasharray="5 4"></polygon>
    <?php foreach ($axes as $i => $ax): [$lx,$ly]=$pt($i,1.18); ?>
      <text x="<?= round($lx) ?>" y="<?= round($ly) ?>" font-size="9" fill="#5b6675" text-anchor="middle"><?= esc(explode(' ',$ax['label'])[0]) ?></text>
    <?php endforeach; ?>
    <g font-size="10" fill="#1a2230">
      <rect x="320" y="40" width="12" height="0" stroke="#2b5ce6" stroke-dasharray="5 4"></rect>
      <line x1="320" y1="48" x2="338" y2="48" stroke="#2b5ce6" stroke-width="2" stroke-dasharray="5 4"></line>
      <text x="344" y="51">Reflection (self-described)</text>
      <rect x="320" y="62" width="18" height="10" fill="rgba(23,114,69,.20)" stroke="#177245"></rect>
      <text x="344" y="71">Evidence (source-backed)</text>
    </g>
  </svg>
  <details>
    <summary>Text alternative (accessible table)</summary>
    <table>
      <thead><tr><th>EDGE area</th><th>Reflection</th><th>Evidence</th></tr></thead>
      <tbody>
      <?php foreach ($map['text_alternative'] as $row): ?>
        <tr><td><?= esc($row['area']) ?></td><td><?= esc($row['reflection']) ?></td><td><?= esc($row['evidence']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </details>
</div>
