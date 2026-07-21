<?php
namespace App\Controllers;

use Continuum\Evidence\Domain\MeridianMapService;
use Continuum\Roles\Service\RoleReadinessService;
use Continuum\Applications\Service\ApplicationService;
use Continuum\Applications\Domain\ConsentPreview;
use Continuum\CorePolicy\Domain\ApplicationState;

class Candidate extends ContinuumController
{
    public function home()
    {
        $cid = $this->ctx->subjectId;
        $c = $this->db->table('candidates')->where('id', $cid)->get()->getRowArray();
        // Top application for the decision strip.
        $app = $this->db->table('applications')->where('candidate_id', $cid)
            ->orderBy('updated_at', 'DESC')->get(1)->getRowArray();
        $view = $app ? (new ApplicationService($this->db, $this->audit))->candidateView((int) $app['id']) : null;
        return $this->shell('candidate_home', ['candidate' => $c, 'appView' => $view, 'appId' => $app['id'] ?? null]);
    }

    public function evidence()
    {
        $cid = $this->ctx->subjectId;
        // Reflection coverage per signal from survey responses.
        $answered = [];
        foreach ($this->db->table('survey_responses')->select('signal, COUNT(*) c')
                     ->where('candidate_id', $cid)->groupBy('signal')->get()->getResultArray() as $r) {
            $answered[$r['signal']] = (int) $r['c'];
        }
        // Evidence coverage per signal.
        $cov = [];
        foreach ($this->db->table('evidence_claims')->where('candidate_id', $cid)->get()->getResultArray() as $cl) {
            $sig = $cl['signal'];
            $cov[$sig] ??= ['covered' => 0, 'total' => 0];
            $cov[$sig]['total']++;
            $hasSrc = $this->db->table('evidence_links')->where('claim_id', $cl['id'])
                ->where('provenance', 'candidate_approved')->countAllResults() > 0;
            if ($hasSrc) { $cov[$sig]['covered']++; }
        }
        $map = (new MeridianMapService())->build($answered, $cov);
        $claims = $this->db->table('evidence_claims')->where('candidate_id', $cid)->get()->getResultArray();
        return $this->shell('candidate_evidence', ['map' => $map, 'claims' => $claims]);
    }

    public function roleContext(string $slug)
    {
        $role = $this->db->table('roles')->where('slug', $slug)->get()->getRowArray();
        if (! $role) { return $this->shell('empty', ['message' => 'Role not found']); }
        $rvId = (int) $role['current_version_id'];
        $rv = $this->db->table('role_versions')->where('id', $rvId)->get()->getRowArray();
        $r = (new RoleReadinessService($this->db))->readiness($this->ctx->subjectId, $rvId);
        return $this->shell('candidate_role_context', ['role' => $role, 'version' => $rv, 'readiness' => $r]);
    }

    public function apply(string $slug)
    {
        $role = $this->db->table('roles')->where('slug', $slug)->get()->getRowArray();
        $rvId = (int) $role['current_version_id'];
        $cid  = $this->ctx->subjectId;
        $claims = $this->db->table('evidence_claims')->where('candidate_id', $cid)->get()->getResultArray();

        // Build the EXACT preview the employer will see (candidate == employer).
        $allowed = array_map(fn ($c) => (int) $c['id'], $claims);
        $all = array_map(function ($cl) {
            $src = $this->db->table('evidence_links el')->select('es.excerpt')
                ->join('evidence_sources es', 'es.id = el.source_id')
                ->where('el.claim_id', $cl['id'])->get()->getRowArray();
            return ['id' => (int) $cl['id'], 'signal' => $cl['signal'], 'label' => $cl['label'],
                    'claim_text' => $cl['claim_text'], 'source_excerpt' => $src['excerpt'] ?? null, 'requirement' => null];
        }, $claims);
        $cp = new ConsentPreview();
        $summary = $cp->summary($all, $allowed);

        if ($this->request->getMethod() === 'post') {
            $hash = $cp->previewHash($summary, $rvId);
            $this->db->table('consent_snapshots')->insert([
                'candidate_id' => $cid, 'role_version_id' => $rvId,
                'allowed_claim_ids' => json_encode($allowed), 'preview_hash' => $hash,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            $consentId = $this->db->insertID();
            $this->db->table('applications')->insert([
                'candidate_id' => $cid, 'role_version_id' => $rvId,
                'employer_tenant_id' => (int) $role['employer_tenant_id'], 'consent_snapshot_id' => $consentId,
                'state' => 'submitted', 'current_owner' => 'operator',
                'expected_update_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'last_verified_action' => 'Application submitted',
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $appId = $this->db->insertID();
            $this->db->table('application_events')->insert([
                'application_id' => $appId, 'type' => 'submitted', 'actor' => $this->ctx->identityKey,
                'visible_to' => 'candidate,employer', 'payload_json' => json_encode(['note' => 'Application submitted']),
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
            $this->audit->record($this->ctx, 'consent.snapshot_created', 'consent_snapshot', (string) $consentId);
            return redirect()->to('/candidate/applications/' . $appId);
        }
        return $this->shell('candidate_apply', ['role' => $role, 'version' => $rvId, 'preview' => $summary]);
    }

    public function application(int $id)
    {
        $app = $this->db->table('applications')->where('id', $id)->get()->getRowArray();
        if (! $app || (int) $app['candidate_id'] !== $this->ctx->subjectId) {
            return $this->shell('denied', []);
        }
        $view = (new ApplicationService($this->db, $this->audit))->candidateView($id);
        return $this->shell('candidate_application', ['appView' => $view, 'appId' => $id]);
    }

    /**
     * Guided Evidence Survey (15Q). GET renders the bank; POST persists reflections to
     * survey_responses (candidate_private). Answering drives the Meridian *reflection* layer.
     * Reflections never award points and never call any personality logic.
     */
    public function survey()
    {
        $cfg = new \Config\EdgeSurvey();
        if ($this->request->getMethod() === 'post') {
            $cid = $this->ctx->subjectId;
            foreach ($cfg->questions as $q) {
                $example = trim((string) $this->request->getPost('ex_' . $q['key']));
                $none    = $this->request->getPost('none_' . $q['key']) ? 0 : 1;
                if ($example === '' && $none === 1) {
                    continue; // unanswered and not explicitly skipped -> store nothing
                }
                // Upsert-by-hand: remove any prior answer for this question, then insert.
                $this->db->table('survey_responses')
                    ->where('candidate_id', $cid)->where('question_key', $q['key'])->delete();
                $this->db->table('survey_responses')->insert([
                    'candidate_id'  => $cid,
                    'survey_version'=> $cfg->version,
                    'question_key'  => $q['key'],
                    'signal'        => $q['signal'],
                    'reflection_choice' => null,
                    'short_example' => $example !== '' ? $example : null,
                    'has_experience'=> $none,
                    'visibility'    => 'candidate_private',
                    'answered_at'   => date('Y-m-d H:i:s'),
                ]);
            }
            $this->audit->record($this->ctx, 'survey.responses_saved', 'candidate', (string) $cid);
            return redirect()->to('/candidate/evidence')->with('ok', 'Survey saved — your reflection layer is updated.');
        }
        // Prefill any existing answers.
        $existing = [];
        foreach ($this->db->table('survey_responses')->where('candidate_id', $this->ctx->subjectId)->get()->getResultArray() as $r) {
            $existing[$r['question_key']] = $r;
        }
        return $this->shell('candidate_survey', ['cfg' => $cfg, 'existing' => $existing]);
    }

    /**
     * Add a candidate-confirmed evidence claim, optionally with a candidate-approved source.
     * Label is derived honestly: a claim with an approved source is Supported; otherwise Stated.
     * The candidate can NEVER self-assign Human Verified here (EvidenceLabelPolicy, 05 §3).
     */
    public function addEvidence()
    {
        $cid    = $this->ctx->subjectId;
        $signal = (string) $this->request->getPost('signal');
        $text   = trim((string) $this->request->getPost('claim_text'));
        $skill  = $this->request->getPost('taxonomy_skill_id');
        $srcEx  = trim((string) $this->request->getPost('source_excerpt'));
        if ($text === '' || $signal === '') {
            return redirect()->to('/candidate/evidence')->with('error', 'Add a short description of what you did.');
        }
        $label = $srcEx !== '' ? 'supported' : 'stated';
        $this->db->table('evidence_claims')->insert([
            'candidate_id'=>$cid,'signal'=>$signal,'label'=>$label,'claim_text'=>$text,
            'confirmation_state'=>'confirmed','taxonomy_skill_id'=>$skill ?: null,'created_at'=>date('Y-m-d H:i:s'),
        ]);
        $claimId = $this->db->insertID();
        if ($srcEx !== '') {
            $this->db->table('evidence_sources')->insert([
                'candidate_id'=>$cid,'type'=>'link','locator'=>(string) ($this->request->getPost('source_locator') ?: 'candidate-provided'),
                'excerpt'=>$srcEx,'added_by'=>'candidate','consentable'=>1,'created_at'=>date('Y-m-d H:i:s'),
            ]);
            $this->db->table('evidence_links')->insert([
                'claim_id'=>$claimId,'source_id'=>$this->db->insertID(),'rationale'=>'Candidate-linked source',
                'provenance'=>'candidate_approved','verified_by'=>null,'created_at'=>date('Y-m-d H:i:s'),
            ]);
        }
        $this->audit->record($this->ctx, 'evidence.claim_added', 'evidence_claim', (string) $claimId);
        return redirect()->to('/candidate/evidence')->with('ok', 'Evidence added.');
    }

    public function respondClarification(int $id)
    {
        $svc = new ApplicationService($this->db, $this->audit);
        $svc->changeState($this->ctx, $id, ApplicationState::UnderReview,
            new \DateTimeImmutable('+3 days'), 'Candidate responded to clarification');
        return redirect()->to('/candidate/applications/' . $id);
    }
}
