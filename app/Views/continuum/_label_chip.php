<?php
/** @var string $label evidence label enum value */
$map = ['needs_evidence'=>['lbl-needs','Needs Evidence'],'stated'=>['lbl-stated','Stated'],
        'inferred'=>['lbl-inferred','Inferred'],'supported'=>['lbl-supported','Supported'],
        'human_verified'=>['lbl-verified','Human Verified']];
[$cls,$txt] = $map[$label] ?? ['lbl-stated', ucfirst($label)];
?>
<span class="chip <?= $cls ?>"><span class="dot"></span><?= esc($txt) ?></span>
