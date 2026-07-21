<?php
namespace App\Controllers;

use Continuum\CorePolicy\Service\AccessPolicy;
use Continuum\EmployerReview\Service\ReviewService;
use Continuum\Roles\Service\RoleReadinessService;
use Continuum\Applications\Service\ApplicationService;
use Continuum\CorePolicy\Domain\ApplicationState;

class Employer extends ContinuumController
{
    public function roles()
    {
        $roles = $this->db->table('roles')->where('employer_tenant_id', $this->ctx->tenantId)->get()->getResultArray();
        return $this->shell('employer_roles', ['roles' => $roles], 'employer');
    }

    public function reviewQueue(int $roleId)
    {
        $role = $this->db->table('roles')->where('id', $roleId)->get()->getRowArray();
        (new AccessPolicy())->requireEmployerTenant($this->ctx, (int) $role['employer_tenant_id']);
        $rvId = (int) $role['current_version_id'];
        $apps = $this->db->table('applications')->where('role_version_id', $rvId)
            ->where('employer_tenant_id', $this->ctx->tenantId)->get()->getResultArray();

        $review = new ReviewService($this->db, new AccessPolicy(), new RoleReadinessService($this->db));
        $rows = [];
        foreach ($apps as $a) {
            try {
                $rows[] = $review->employerView($this->ctx, (int) $a['id'], new \DateTimeImmutable());
            } catch (\Throwable $e) {
                // Consent absent/expired => candidate action; show the gate without leaking data.
                $rows[] = ['application_id' => (int) $a['id'], 'queue_label' => 'candidate_action_suggested',
                           'queue_label_text' => 'Candidate action suggested', 'requirements' => [], 'evidence_summary' => [],
                           'questions_to_confirm' => [], 'availability' => 'unknown_stale', 'blocked' => true];
            }
        }
        return $this->shell('employer_review_queue', ['role' => $role, 'rows' => $rows], 'employer');
    }

    public function candidateReview(int $applicationId)
    {
        $review = new ReviewService($this->db, new AccessPolicy(), new RoleReadinessService($this->db));
        try {
            $view = $review->employerView($this->ctx, $applicationId, new \DateTimeImmutable());
        } catch (\Throwable $e) {
            return $this->shell('denied', ['reason' => $e->getMessage()], 'employer');
        }
        return $this->shell('employer_candidate_review', ['view' => $view], 'employer');
    }

    /** Load an application and assert it belongs to the acting employer's tenant, else deny. */
    private function ownTenantApp(int $applicationId): ?array
    {
        $app = $this->db->table('applications')->where('id', $applicationId)->get()->getRowArray();
        if (! $app) {
            return null;
        }
        try {
            (new AccessPolicy())->requireEmployerTenant($this->ctx, (int) $app['employer_tenant_id']);
        } catch (\Throwable $e) {
            return null;
        }
        return $app;
    }

    public function changeStatus(int $applicationId)
    {
        if (! $this->ownTenantApp($applicationId)) {
            return $this->shell('denied', ['reason' => 'Application is outside your tenant'], 'employer');
        }
        $to = ApplicationState::from($this->request->getPost('to') ?? 'under_review');
        $svc = new ApplicationService($this->db, $this->audit);
        $eua = $to->requiresOwnerAndExpectedUpdate() ? new \DateTimeImmutable('+3 days') : null;
        try {
            $svc->changeState($this->ctx, $applicationId, $to, $eua, $this->request->getPost('note') ?? $to->label());
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->to('/employer/review/' . $applicationId);
    }

    public function requestClarification(int $applicationId)
    {
        if (! $this->ownTenantApp($applicationId)) {
            return $this->shell('denied', ['reason' => 'Application is outside your tenant'], 'employer');
        }
        $svc = new ApplicationService($this->db, $this->audit);
        $q = $this->request->getPost('question') ?? 'Please add a source for your SQL example.';
        $svc->changeState($this->ctx, $applicationId, ApplicationState::ClarificationRequested,
            new \DateTimeImmutable('+3 days'), 'Clarification requested: ' . esc($q));
        return redirect()->to('/employer/review/' . $applicationId);
    }

    public function releaseFeedback(int $applicationId)
    {
        if (! $this->ownTenantApp($applicationId)) {
            return $this->shell('denied', ['reason' => 'Application is outside your tenant'], 'employer');
        }
        $this->db->table('feedback_records')->insert([
            'application_id' => $applicationId,
            'category' => $this->request->getPost('category') ?? 'role_requirement_not_yet_evidenced',
            'structured_reason' => $this->request->getPost('reason'),
            'visibility' => 'candidate', 'released_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->record($this->ctx, 'feedback.released_to_candidate', 'application', (string) $applicationId);
        return redirect()->to('/employer/review/' . $applicationId);
    }

    public function compose() { return $this->shell('employer_compose', [], 'employer'); }
    public function publish(int $roleId) { return redirect()->to('/employer/roles'); }
}
