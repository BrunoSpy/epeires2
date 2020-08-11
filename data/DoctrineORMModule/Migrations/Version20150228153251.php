<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150228153251 extends AbstractMigration
{
    public function up(Schema $schema) : void {
		$this->connection->update('status', array('name' => 'Fin confirmée'), array('id' => 3));
    }

    public function down(Schema $schema) : void {
    	$this->connection->update('status', array('name' => 'Terminé'), array('id' => 3));
    }
}
