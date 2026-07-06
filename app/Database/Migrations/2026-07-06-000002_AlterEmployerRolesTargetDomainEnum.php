<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * employer_roles.target_domain was a MySQL ENUM restricted to the original
 * 4 domains (Data/Engineering/Design/Business). Inserting any of the 7 new
 * domains (Education, Arts & Humanities, Social Sciences, Natural Sciences,
 * Agriculture & Veterinary, Health & Welfare, Services) silently coerced to
 * an empty string under non-strict SQL mode — this migration widens the
 * ENUM to include all 11 Lumina domains.
 */
class AlterEmployerRolesTargetDomainEnum extends Migration
{
    private array $all11 = [
        'Data', 'Engineering', 'Design', 'Business',
        'Education', 'Arts & Humanities', 'Social Sciences', 'Natural Sciences',
        'Agriculture & Veterinary', 'Health & Welfare', 'Services',
    ];

    private array $original4 = ['Data', 'Engineering', 'Design', 'Business'];

    public function up()
    {
        $this->forge->modifyColumn('employer_roles', [
            'target_domain' => [
                'name'       => 'target_domain',
                'type'       => 'ENUM',
                'constraint' => $this->all11,
                'default'    => 'Business',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('employer_roles', [
            'target_domain' => [
                'name'       => 'target_domain',
                'type'       => 'ENUM',
                'constraint' => $this->original4,
                'default'    => 'Business',
            ],
        ]);
    }
}
