<?php
namespace App\Models;
use CodeIgniter\Model;
class ScoreModel extends Model
{
    protected $table = 'student_scores';
    protected $primaryKey = 'student_id';
    protected $allowedFields = ['student_id','readiness','gap_pct','industry_exposure',
        'risk_level','high_income','job_creator','outcomes_index','updated_at'];
    protected $useTimestamps = false;
}
