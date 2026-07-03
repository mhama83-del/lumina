<?php
namespace App\Models;
use CodeIgniter\Model;
class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title','domain','company','location','salary_band','high_income'];
    public function requiredSkills(int $roleId): array
    {
        return $this->db->table('role_skills rs')
            ->select('sk.code')->join('skills sk','sk.id = rs.skill_id')
            ->where('rs.role_id',$roleId)->where('rs.required',1)
            ->get()->getResultArray();
    }
}
