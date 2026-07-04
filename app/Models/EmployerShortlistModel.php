<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployerShortlistModel extends Model
{
    protected $table         = 'employer_shortlists';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['session_key','employer_role_id','candidate_ref','created_at'];
}
