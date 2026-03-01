<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nombre_rol' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->createTable('roles');
        
        // Insertar roles básicos
        $data = [
            ['nombre_rol' => 'administrador', 'descripcion' => 'Acceso completo al sistema'],
            ['nombre_rol' => 'vigilante', 'descripcion' => 'Control de acceso y vigilancia'],
            ['nombre_rol' => 'propietario', 'descripcion' => 'Residente del conjunto'],
        ];
        
        $this->db->table('roles')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}
