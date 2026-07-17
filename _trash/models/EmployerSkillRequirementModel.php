<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployerSkillRequirementModel extends Model
{
    protected $table         = 'employer_skill_requirements';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['role_id','skill_name','skill_code','skill_category','importance','weight'];

    public function forRole(int $roleId): array
    {
        return $this->where('role_id', $roleId)->findAll();
    }
}
