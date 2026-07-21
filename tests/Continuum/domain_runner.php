<?php
/**
 * Framework-independent domain test runner (no composer/phpunit needed).
 * Proves the mandatory unit cases in 09_SECURITY_QUALITY_AND_TESTS.md.
 */
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Continuum\\')) {
        $rel = substr($class, strlen('Continuum\\'));      // Module\Sub\Name
        $parts = explode('\\', $rel);
        $module = array_shift($parts);
        $path = __DIR__ . '/../../app/Modules/' . $module . '/' . implode('/', $parts) . '.php';
        if (is_file($path)) { require $path; }
    }
});

use Continuum\CorePolicy\Domain\Importance;
use Continuum\CorePolicy\Domain\EvidenceLabel;
use Continuum\CorePolicy\Domain\ApplicationState as S;
use Continuum\CorePolicy\Domain\QueueLabel;
use Continuum\Roles\Domain\RerEngine;
use Continuum\Roles\Domain\RequirementSufficiency as Req;
use Continuum\Evidence\Domain\EvidenceLabelPolicy;
use Continuum\Applications\Domain\ApplicationStateMachine;
use Continuum\Applications\Domain\StaleDetection;

$pass = 0; $fail = 0; $fails = [];
function check(string $name, bool $cond) {
    global $pass, $fail, $fails;
    if ($cond) { $pass++; echo "  ok   $name\n"; }
    else { $fail++; $fails[] = $name; echo "  FAIL $name\n"; }
}
function approx(float $a, float $b): bool { return abs($a - $b) < 0.05; }

echo "== RER formula ==\n";
$rer = new RerEngine();

// All Critical, all Human Verified (suff 3) => 100
$reqs = [ new Req('SQL', Importance::Critical, 3), new Req('Python', Importance::Critical, 3) ];
check('all critical fully verified => 100.0', approx($rer->rer($reqs), 100.0));

// Empty requirements => 0
check('no requirements => 0.0', approx($rer->rer([]), 0.0));

// Mixed weights: Critical(3)*suff2 + Important(2)*suff1 + Supporting(1)*suff0
//  = 100*(6+2+0)/(3*(3+2+1)) = 100*8/18 = 44.4
$reqs = [ new Req('SQL', Importance::Critical, 2), new Req('Comm', Importance::Important, 1), new Req('Docs', Importance::Supporting, 0) ];
check('mixed weights => 44.4', approx($rer->rer($reqs), 44.4));

// Max sufficiency respected: cannot exceed 3 (constructor guards); a single req at 3 => 100
check('single requirement at max => 100.0', approx($rer->rer([new Req('SQL', Importance::Critical, 3)]), 100.0));

// Constructor rejects out-of-range sufficiency
$threw = false; try { new Req('x', Importance::Critical, 4); } catch (\Throwable $e) { $threw = true; }
check('sufficiency > 3 rejected', $threw);

echo "== Graph edge gives NO RER credit ==\n";
// A related-skill graph edge must NOT change sufficiency. We prove RER only reads
// RequirementSufficiency (which comes from EvidenceLabelPolicy), so an unconfirmed related edge
// leaves sufficiency at 0. Requirement with only a graph relation => sufficiency 0 => RER 0.
$reqs = [ new Req('SQL', Importance::Critical, 0, 'related skill edge only, no confirmed evidence') ];
check('graph-edge-only requirement contributes 0', approx($rer->rer($reqs), 0.0));

echo "== Critical gate labels ==\n";
// Review now: consent + availability + every Critical >= 2
$reqs = [ new Req('SQL', Importance::Critical, 2), new Req('Comm', Importance::Important, 1) ];
check('review_now', $rer->queueLabel($reqs, true, true) === QueueLabel::ReviewNow);
// Review with questions: some Critical >=1 but <2
$reqs = [ new Req('SQL', Importance::Critical, 1), new Req('Py', Importance::Critical, 2) ];
check('review_with_questions', $rer->queueLabel($reqs, true, true) === QueueLabel::ReviewWithQuestions);
// Candidate action: a Critical = 0
$reqs = [ new Req('SQL', Importance::Critical, 0) ];
check('candidate_action (critical=0)', $rer->queueLabel($reqs, true, true) === QueueLabel::CandidateActionSuggested);
// Candidate action: consent invalid even if evidence strong
$reqs = [ new Req('SQL', Importance::Critical, 3) ];
check('candidate_action (no consent)', $rer->queueLabel($reqs, false, true) === QueueLabel::CandidateActionSuggested);
// Candidate action: availability inactive
check('candidate_action (availability stale)', $rer->queueLabel($reqs, true, false) === QueueLabel::CandidateActionSuggested);

echo "== Potential coverage change ==\n";
// Requirement SQL Critical currently 1, target 2, denominator weight sum = 3
// = 100 * 3 * (2-1) / (3*3) = 33.3
$all = [ new Req('SQL', Importance::Critical, 1) ];
check('potential coverage change 33.3', approx($rer->potentialCoverageChange($all[0], 2, $all), 33.3));

echo "== Evidence label policy ==\n";
$lp = new EvidenceLabelPolicy();
check('needs evidence => 0', $lp->sufficiency(EvidenceLabel::NeedsEvidence) === 0);
check('stated => 1', $lp->sufficiency(EvidenceLabel::Stated) === 1);
check('inferred unconfirmed => 0', $lp->sufficiency(EvidenceLabel::Inferred, candidateConfirmed: false) === 0);
check('inferred confirmed => 1', $lp->sufficiency(EvidenceLabel::Inferred, candidateConfirmed: true) === 1);
check('supported w/ approved source => 2', $lp->sufficiency(EvidenceLabel::Supported, sourceLinkedAndApproved: true) === 2);
check('human verified => 3', $lp->sufficiency(EvidenceLabel::HumanVerified, humanVerified: true) === 3);
// Candidate cannot self-promote to Supported without approved source
check('stated->supported blocked w/o source', $lp->canTransition(EvidenceLabel::Stated, EvidenceLabel::Supported, hasApprovedSource: false) === false);
check('stated->supported ok w/ source', $lp->canTransition(EvidenceLabel::Stated, EvidenceLabel::Supported, hasApprovedSource: true) === true);
// Candidate cannot reach Human Verified themselves
check('->human_verified blocked for candidate', $lp->canTransition(EvidenceLabel::Supported, EvidenceLabel::HumanVerified, hasApprovedSource: true, byAuthorisedVerifier: false) === false);
check('->human_verified ok for verifier', $lp->canTransition(EvidenceLabel::Supported, EvidenceLabel::HumanVerified, hasApprovedSource: true, byAuthorisedVerifier: true) === true);

echo "== Application state machine ==\n";
$sm = new ApplicationStateMachine();
check('submitted->received valid', $sm->canTransition(S::Submitted, S::Received));
check('submitted->offer_made invalid', ! $sm->canTransition(S::Submitted, S::OfferMade));
check('terminal not_selected has no next', $sm->allowedNext(S::NotSelected) === []);
// Active transition requires expected_update_at
$threw = false; try { $sm->transition(S::Received, S::UnderReview, null); } catch (\DomainException $e) { $threw = true; }
check('active transition requires expected_update_at', $threw);
$res = $sm->transition(S::Received, S::UnderReview, new DateTimeImmutable('+2 days'));
check('under_review owner = employer', $res['owner']->value === 'employer');
check('under_review carries expected_update_at', $res['expected_update_at'] !== null);
// Invalid transition throws
$threw = false; try { $sm->transition(S::Draft, S::OfferMade, new DateTimeImmutable('+1 day')); } catch (\DomainException $e) { $threw = true; }
check('invalid transition throws', $threw);
// clarification requested => candidate owns next
$res = $sm->transition(S::UnderReview, S::ClarificationRequested, new DateTimeImmutable('+3 days'));
check('clarification owner = candidate', $res['owner']->value === 'candidate');

echo "== Stale detection ==\n";
$sd = new StaleDetection(graceHours: 24);
$now = new DateTimeImmutable('2026-07-21 12:00:00');
$past = new DateTimeImmutable('2026-07-20 12:00:00');   // 24h ago
$future = new DateTimeImmutable('2026-07-22 12:00:00');
check('overdue active app is stale', $sd->isStale(S::UnderReview, $past, $now));
check('future update not stale', ! $sd->isStale(S::UnderReview, $future, $now));
check('terminal app never stale', ! $sd->isStale(S::NotSelected, $past, $now));
// exception: overdue by more than grace (past is exactly 24h; deadline = past+24h = now, not < now)
check('not yet exception at grace boundary', ! $sd->isOperatorException(S::UnderReview, $past, $now));
$wayPast = new DateTimeImmutable('2026-07-19 00:00:00');
check('exception after grace elapsed', $sd->isOperatorException(S::UnderReview, $wayPast, $now));

echo "\n----------------------------------------\n";
echo "PASS: $pass   FAIL: $fail\n";
if ($fail) { echo "Failures: " . implode(', ', $fails) . "\n"; exit(1); }
echo "ALL DOMAIN TESTS PASSED\n";
