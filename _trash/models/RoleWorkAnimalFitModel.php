<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleWorkAnimalFitModel extends Model
{
    protected $table         = 'role_work_animal_fit';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'role_id','preferred_primary_animal','preferred_secondary_animal',
        'acceptable_animals_json','poor_fit_risk','team_fit_note',
    ];

    public function forRole(int $roleId): ?array
    {
        return $this->where('role_id', $roleId)->first();
    }
}
