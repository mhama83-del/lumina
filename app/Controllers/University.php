<?php
namespace App\Controllers;

/** University workspace — aggregate cohort view only by default (04 §University visibility). */
class University extends ContinuumController
{
    public function cohort(int $cohortId)
    {
        $cohort = $this->db->table('cohorts')->where('id', $cohortId)->get()->getRowArray();
        // Aggregate distribution only — no individual student list.
        $total = $this->db->table('candidates')->where('data_classification', 'synthetic_fixture')->countAllResults();
        $withSourceSql = 42; // synthetic aggregate for demo
        $interventions = $this->db->table('intervention_cases')->where('cohort_id', $cohortId)->get()->getResultArray();
        $agg = [
            'cohort_size' => 120,
            'source_backed_sql_rate' => 0.35,
            'source' => 'synthetic_fixture', 'refresh_date' => date('Y-m-d'), 'confidence' => 'demo',
        ];
        return $this->shell('university_cohort', ['cohort' => $cohort, 'agg' => $agg, 'interventions' => $interventions], 'university');
    }

    public function createIntervention(int $cohortId)
    {
        $this->db->table('intervention_cases')->insert([
            'cohort_id' => $cohortId, 'signal' => $this->request->getPost('signal') ?? 'Cohort evidence gap',
            'owner' => $this->ctx->identityKey, 'plan' => $this->request->getPost('plan'),
            'outcome_metric' => $this->request->getPost('metric'), 'status' => 'open', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit->record($this->ctx, 'intervention.created', 'cohort', (string) $cohortId);
        return redirect()->to('/university/cohorts/' . $cohortId);
    }
}
