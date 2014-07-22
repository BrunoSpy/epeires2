<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout de la propriété <code>system</system> à l'entité <code>Category</code>
 */
class Version20140722101302 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE categories ADD system TINYINT(1) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE categories DROP system");
    }
    
    public function postUp(Schema $schema){
        $stmt = $this->connection->executeQuery("SELECT id FROM categories WHERE name = ?", array('Action'));
        $catid = $stmt->fetch()['id'];
        $this->connection->update('categories', array('system' => true), array('id' => $catid));
        
        $stmt = $this->connection->executeQuery("SELECT id FROM categories WHERE name = ?", array('Alarme'));
        $catid = $stmt->fetch()['id'];
        $this->connection->update('categories', array('system' => true), array('id' => $catid));
    }
}
