<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changement d'une valeur dans la base de données :
 * Le champ titre des mémos passe de "Nom" à "Titre"
 */
class Version20140919083405 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT `namefield_id` FROM `AlarmCategory` WHERE 1");
        $results = $stmt->fetchAll();
        foreach ($results as $id){
            $this->connection->update('customfields', array('name' => 'Titre'), array('id' => $id['namefield_id']));
        }
        
    }

    public function down(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT `namefield_id` FROM `AlarmCategory` WHERE 1");
        $results = $stmt->fetchAll();
        foreach ($results as $id){
            $this->connection->update('customfields', array('name' => 'Nom'), array('id' => $id['namefield_id']));
        }
    }
}
