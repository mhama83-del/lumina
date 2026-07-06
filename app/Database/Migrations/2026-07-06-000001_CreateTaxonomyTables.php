<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Lumina Graph taxonomy layer (self-growing knowledge graph).
 * Additive — does not touch students / employer_roles.
 */
class CreateTaxonomyTables extends Migration
{
    public function up()
    {
        // ---------- taxonomy_skills ----------
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'                => ['type' => 'VARCHAR', 'constraint' => 60],
            'label'               => ['type' => 'VARCHAR', 'constraint' => 80],
            'domain'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'aliases_json'        => ['type' => 'JSON', 'null' => true],
            'related_skills_json' => ['type' => 'JSON', 'null' => true],
            'common_evidence_json'=> ['type' => 'JSON', 'null' => true],
            'frequency'           => ['type' => 'INT', 'default' => 0],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('code');
        $this->forge->createTable('taxonomy_skills', true);

        // ---------- taxonomy_patterns ----------
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pattern_key'          => ['type' => 'VARCHAR', 'constraint' => 140],
            'domain'               => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'programme'            => ['type' => 'VARCHAR', 'constraint' => 140, 'null' => true],
            'typical_skills_json'  => ['type' => 'JSON', 'null' => true],
            'typical_animals_json' => ['type' => 'JSON', 'null' => true],
            'evidence_keywords_json'=> ['type' => 'JSON', 'null' => true],
            'sample_count'         => ['type' => 'INT', 'default' => 0],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pattern_key');
        $this->forge->createTable('taxonomy_patterns', true);
    }

    public function down()
    {
        $this->forge->dropTable('taxonomy_patterns', true);
        $this->forge->dropTable('taxonomy_skills', true);
    }
}
