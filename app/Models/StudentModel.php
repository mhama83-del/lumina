<?php
namespace App\Models;
use CodeIgniter\Model;
class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','age','stage','university','faculty','programme',
        'cgpa','target_domain','work_animal','evidence_text','has_resume','salary_target'];
    public function withSkills(int $id): ?array
    {
        $s = $this->find($id);
        if (!$s) return null;
        $s['skills'] = $this->db->table('student_skills ss')
            ->select('sk.code, sk.label, ss.source, ss.confidence')
            ->join('skills sk','sk.id = ss.skill_id')
            ->where('ss.student_id',$id)->get()->getResultArray();
        return $s;
    }
}
