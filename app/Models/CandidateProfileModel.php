<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateProfileModel extends Model
{
    protected $table         = 'candidate_profiles';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'analysis_id','session_key','name','stage','programme','cgpa',
        'target_domain','animal','verified','evidence_text','skills_json','readiness',
    ];
}
