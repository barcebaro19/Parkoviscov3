<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuRolesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'roles_idroles' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'contraseña' => [
                'type' => 'VARCHAR',
                'constraint' => '8',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('usuarios_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('roles_idroles', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('usu_roles');
    }

    public function down()
    {
        $this->forge->dropTable('usu_roles');
    }
}
