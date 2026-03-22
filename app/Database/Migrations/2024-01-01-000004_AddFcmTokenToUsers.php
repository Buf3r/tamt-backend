<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFcmTokenToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'fcm_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'city',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'fcm_token');
    }
}