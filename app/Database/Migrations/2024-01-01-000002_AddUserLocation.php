<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserLocation extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'city');
    }
}