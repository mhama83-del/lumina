<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployerModel extends Model
{
    protected $table         = 'employers';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'company_name','country','city','industry','sector',
        'company_type','company_size','is_synthetic','created_at',
    ];
}
