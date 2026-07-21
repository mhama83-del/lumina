<?php
namespace App\Controllers;

use Continuum\Applications\Service\ApplicationService;

/** Talentbank operator Control Tower. Resolves exceptions; NEVER makes a hiring decision. */
class Operator extends ContinuumController
{
    public function controlTower()
    {
        $svc = new ApplicationService($this->db, $this->audit);
        $exceptions = $svc->staleSweep(new \DateTimeImmutable());
        // Enrich for display.
        foreach ($exceptions as &$e) {
            $app = $this->db->table('applications')->where('id', $e['application_id'])->get()->getRowArray();
            $cand = $this->db->table('candidates')->where('id', $app['candidate_id'])->get()->getRowArray();
            $e['candidate'] = $cand['display_name'] ?? 'Candidate';
            $e['state'] = $app['state'];
        }
        return $this->shell('operator_control_tower', ['exceptions' => $exceptions], 'operator');
    }

    public function remind(int $applicationId)
    {
        $this->db->table('application_events')->insert([
            'application_id' => $applicationId, 'type' => 'operator_reminder', 'actor' => $this->ctx->identityKey,
            'visible_to' => 'employer,operator', 'payload_json' => json_encode(['note' => 'Operator reminded current owner']),
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->record($this->ctx, 'application.reminder_sent', 'application', (string) $applicationId);
        return redirect()->to('/operator/control-tower')->with('ok', 'Reminder sent to current owner.');
    }
}
