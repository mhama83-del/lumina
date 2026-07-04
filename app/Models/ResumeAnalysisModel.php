<?php

namespace App\Models;

use CodeIgniter\Model;

class ResumeAnalysisModel extends Model
{
    protected $table         = 'resume_analyses';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_key','source','name','raw_text','target_domain','career_cluster',
        'readiness','employability_band','animal_primary','animal_secondary','animal_growth',
        'skills_json','projects_json','leadership_json','feedback_json','next_action',
    ];
}
