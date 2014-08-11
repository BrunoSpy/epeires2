<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout de contraintes pour éviter des bugs
 */
class Version20140811142308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE customfields CHANGE tooltip tooltip LONGTEXT NOT NULL");
        $this->addSql("ALTER TABLE Event DROP FOREIGN KEY FK_FA6F25A3F675F31B");      
        $this->addSql("ALTER TABLE Event CHANGE author_id author_id INT NOT NULL");
        $this->addSql("ALTER TABLE Event ADD CONSTRAINT FK_FA6F25A3F675F31B FOREIGN KEY (author_id) REFERENCES users (id)");
        
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE customfields CHANGE tooltip tooltip LONGTEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE Event CHANGE author_id author_id INT DEFAULT NULL");
    }
}
