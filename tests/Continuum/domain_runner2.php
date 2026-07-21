<?php
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Continuum\\')) {
        $parts = explode('\\', substr($class, strlen('Continuum\\')));
        $module = array_shift($parts);
        $path = __DIR__ . '/../../app/Modules/' . $module . '/' . implode('/', $parts) . '.php';
        if (is_file($path)) require $path;
    }
});
use Continuum\Evidence\Domain\MeridianMapService;
use Continuum\Applications\Domain\ConsentPreview;

$pass=0;$fail=0;$fails=[];
function check($n,$c){global $pass,$fail,$fails; if($c){$pass++;echo "  ok   $n\n";}else{$fail++;$fails[]=$n;echo "  FAIL $n\n";}}

echo "== Meridian Map aggregation ==\n";
$m = new MeridianMapService();
$map = $m->build(
    ['reasoning_judgement'=>3,'delivery_reliability'=>1,'collaboration_communication'=>0,'learning_adaptation'=>2,'initiative_ownership'=>0],
    ['reasoning_judgement'=>['covered'=>2,'total'=>4],'delivery_reliability'=>['covered'=>0,'total'=>1]]
);
check('5 axes produced', count($map['axes'])===5);
check('legend has dashed+filled', isset($map['legend']['dashed'],$map['legend']['filled']));
check('text alternative present', count($map['text_alternative'])===5);
$r = $map['axes'][0]; // reasoning, answered 3 => reflection 1.0; covered 2/4 => 0.5
check('reflection layer 3/3 => 1.0', abs($r['reflection_layer']-1.0)<0.001);
check('evidence layer 2/4 => 0.5', abs($r['evidence_layer']-0.5)<0.001);
$collab = $map['axes'][2];
check('untouched signal marked empty', $collab['empty']===true);
check('empty signal text respectful', $map['text_alternative'][2]['reflection']==='Not yet discussed');
// no numeric verdict/personality field leaks
check('no personality/score key', !isset($r['score']) && !isset($r['verdict']) && !isset($r['strength']));

echo "== Consent preview == employer view ==\n";
$cp = new ConsentPreview();
$allClaims = [
  ['id'=>1,'signal'=>'reasoning_judgement','label'=>'supported','claim_text'=>'Built sales dashboard','source_excerpt'=>'github.com/.../dash','requirement'=>'SQL'],
  ['id'=>2,'signal'=>'delivery_reliability','label'=>'stated','claim_text'=>'Managed 3 deadlines','source_excerpt'=>null,'requirement'=>null],
  ['id'=>3,'signal'=>'initiative_ownership','label'=>'stated','claim_text'=>'Private note candidate did not share','source_excerpt'=>null,'requirement'=>null],
];
$allowed = [1,2];
$candidateSummary = $cp->summary($allClaims, $allowed);
check('unshared claim excluded from preview', count($candidateSummary)===2);
check('unshared claim id 3 absent', !in_array(3, array_column($candidateSummary,'id'), true));
$roleVersion = 42;
$hash = $cp->previewHash($candidateSummary, $roleVersion);

// Employer rebuilds from the SAME allowed set -> must match.
$employerSummary = $cp->summary($allClaims, $allowed);
check('employer view matches candidate preview', $cp->matchesEmployerView($employerSummary, $roleVersion, $hash));

// If employer somehow includes an extra (unconsented) claim -> mismatch (tamper detected).
$tampered = $cp->summary($allClaims, [1,2,3]);
check('extra unconsented claim breaks the hash', !$cp->matchesEmployerView($tampered, $roleVersion, $hash));
// Different role version -> mismatch (role-version-specific consent).
check('different role version breaks the hash', !$cp->matchesEmployerView($employerSummary, 99, $hash));
// Raw survey text is never a field
check('no raw survey field in summary', !array_key_exists('survey_response', $candidateSummary[0]));

echo "\n----------------------------------------\n";
echo "PASS: $pass   FAIL: $fail\n";
if($fail){echo "Failures: ".implode(', ',$fails)."\n";exit(1);}
echo "ALL PART-2 DOMAIN TESTS PASSED\n";
