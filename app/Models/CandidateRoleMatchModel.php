<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateRoleMatchModel extends Model
{
    protected $table         = 'candidate_role_matches';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        // legacy (Fasa 0)
        'profile_id','role_key','match_score','readiness','matched_json','gap_json','reason','created_at',
        // Fasa 4B extensions
        'employer_role_id','fit_label','skill_match_score','evidence_strength_score',
        'learning_velocity_score','animal_fit_score','domain_fit_score','academic_fit_score',
        'skill_overlap_json','missing_skills_json','explanation',
    ];
}
