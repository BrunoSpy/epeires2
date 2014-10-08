<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changement de gestion des droits admin : ajout des droits admin.access et admin.users
 */
class Version20141008152347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT `id` FROM `permissions` WHERE `name`='admin.access'");
        $results = $stmt->fetchAll();
        if(count($results) == 0){
            $this->connection->insert('permissions', array('name' => 'admin.access'));
            $stmt = $this->connection->executeQuery("SELECT `id` FROM `permissions` WHERE `name`='admin.access'");
            $results = $stmt->fetchAll();
        }
        $perm_id = $results[0]['id'];
        
        $stmt = $this->connection->executeQuery("SELECT `id` FROM `roles` WHERE `name`='admin'");
        $results = $stmt->fetchAll();
        foreach ($results as $id){
            $this->connection->insert('roles_permissions', array('role_id' => $id['id'], 'permission_id' => $perm_id));
        }

        $stmt = $this->connection->executeQuery("SELECT `id` FROM `permissions` WHERE `name`='admin.users'");
        $results = $stmt->fetchAll();
        if(count($results) == 0){
            $this->connection->insert('permissions', array('name' => 'admin.users'));
            $stmt = $this->connection->executeQuery("SELECT `id` FROM `permissions` WHERE `name`='admin.users'");
            $results = $stmt->fetchAll();
        }
        $perm_id = $results[0]['id'];
        
        $stmt = $this->connection->executeQuery("SELECT `id` FROM `roles` WHERE `name`='admin'");
        $results = $stmt->fetchAll();
        foreach ($results as $id){
            $this->connection->insert('roles_permissions', array('role_id' => $id['id'], 'permission_id' => $perm_id));
        }
        
    }

    public function down(Schema $schema)
    {
        //useless

    }
}
