<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemLocationCondition extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'description',
            ],
            'condition' => [
                'type'       => 'ENUM',
                'constraint' => ['new', 'used', 'refurbished'],
                'null'       => true,
                'default'    => 'used',
                'after'      => 'location',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items', ['location', 'condition']);
    }
}