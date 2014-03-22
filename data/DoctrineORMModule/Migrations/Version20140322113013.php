<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140322113013 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE antennas ADD model_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE antennas ADD CONSTRAINT FK_4FD1E3647975B7E7 FOREIGN KEY (model_id) REFERENCES PredefinedEvent (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_4FD1E3647975B7E7 ON antennas (model_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE antennas DROP FOREIGN KEY FK_4FD1E3647975B7E7");
        $this->addSql("DROP INDEX UNIQ_4FD1E3647975B7E7 ON antennas");
        $this->addSql("ALTER TABLE antennas DROP model_id");
    }
}
