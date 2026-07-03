<?php
namespace App\Models;
use CodeIgniter\Model;
class SkillModel extends Model
{
    protected $table = 'skills';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code','label','domain','high_value'];
}
