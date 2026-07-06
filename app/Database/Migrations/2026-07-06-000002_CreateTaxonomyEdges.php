<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Weighted co-occurrence edges — the graph's evolving relationships.
 */
class CreateTaxonomyEdges extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'a_code'     => ['type' => 'VARCHAR', 'constraint' => 60],
            'b_code'     => ['type' => 'VARCHAR', 'constraint' => 60],
            'weight'     => ['type' => 'INT', 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['a_code', 'b_code'], false, true); // unique
        $this->forge->addKey('a_code');
        $this->forge->createTable('taxonomy_edges', true);
    }

    public function down()
    {
        $this->forge->dropTable('taxonomy_edges', true);
    }
}
