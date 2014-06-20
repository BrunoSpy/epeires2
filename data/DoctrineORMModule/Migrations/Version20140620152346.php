<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140620152346 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Event DROP star");
        $this->addSql("ALTER TABLE Event ADD archived TINYINT(1) NOT NULL");
        $this->addSql("DELETE FROM `status` WHERE `id` = 5");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Event DROP archived");
        $this->addSql("ALTER TABLE Event ADD star TINYINT(1) NOT NULL");
        $this->addSql("INSERT INTO `status` VALUES(5, 0, 0, 'Archivé', 0)");

    }

}
