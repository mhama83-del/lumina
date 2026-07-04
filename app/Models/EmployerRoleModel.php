<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployerRoleModel extends Model
{
    protected $table         = 'employer_roles';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employer_id','jd_code','role_title','role_family','role_level','target_domain',
        'availability_type','work_arrangement','location_country','location_city','region',
        'salary_band','suitable_programmes_json','role_summary','responsibilities_json',
        'keywords_for_matching_json','evidence_required_json','learning_velocity_need',
        'minimum_cgpa_category','match_weighting_json','source_reference','synthetic_jd_text',
        'notes_for_lumina_matching','is_synthetic','created_at',
    ];

    /** Roles joined with employer identity, with optional filters. */
    public function browse(array $filters = [], int $limit = 60, int $offset = 0): array
    {
        $b = $this->db->table('employer_roles r')
            ->select('r.id, r.jd_code, r.role_title, r.role_family, r.role_level, r.target_domain,
                      r.availability_type, r.work_arrangement, r.salary_band, r.learning_velocity_need,
                      e.company_name, e.country, e.city, e.industry, e.sector, e.company_type')
            ->join('employers e', 'e.id = r.employer_id');

        if (! empty($filters['domain']))   $b->where('r.target_domain', $filters['domain']);
        if (! empty($filters['level']))    $b->where('r.role_level', $filters['level']);
        if (! empty($filters['country']))  $b->where('e.country', $filters['country']);
        if (! empty($filters['sector']))   $b->where('e.sector', $filters['sector']);
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $b->groupStart()
              ->like('r.role_title', $q)->orLike('e.company_name', $q)->orLike('r.role_family', $q)
              ->groupEnd();
        }
        return $b->orderBy('r.id', 'ASC')->limit($limit, $offset)->get()->getResultArray();
    }

    public function fullRole(int $id): ?array
    {
        $r = $this->db->table('employer_roles r')
            ->select('r.*, e.company_name, e.country AS emp_country, e.city AS emp_city, e.industry, e.sector, e.company_type, e.company_size')
            ->join('employers e', 'e.id = r.employer_id')
            ->where('r.id', $id)->get()->getRowArray();
        if (! $r) return null;

        $r['skills'] = $this->db->table('employer_skill_requirements')
            ->where('role_id', $id)->get()->getResultArray();
        $r['animal'] = $this->db->table('role_work_animal_fit')
            ->where('role_id', $id)->get()->getRowArray();
        return $r;
    }
}
