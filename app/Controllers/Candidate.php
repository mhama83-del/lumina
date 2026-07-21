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

    public function respondClarification(int $id)
    {
        $svc = new ApplicationService($this->db, $this->audit);
        $svc->changeState($this->ctx, $id, ApplicationState::UnderReview,
            new \DateTimeImmutable('+3 days'), 'Candidate responded to clarification');
        return redirect()->to('/candidate/applications/' . $id);
    }
}
