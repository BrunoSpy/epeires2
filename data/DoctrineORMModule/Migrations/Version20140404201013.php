<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


class Version20140404201013 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE files CHANGE mime_type mime_type VARCHAR(255) DEFAULT NULL, CHANGE size size NUMERIC(10, 0) DEFAULT NULL");
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE files CHANGE mime_type mime_type VARCHAR(255) NOT NULL, CHANGE size size NUMERIC(10, 0) NOT NULL");
    }
}
