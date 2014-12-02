<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141121151026 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql("ALTER TABLE customfieldvalues DROP FOREIGN KEY FK_C78CFA5371F7E88B");     
        $this->addSql('ALTER TABLE customfieldvalues CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE PredefinedEvent DROP startdatedelta');
        $this->addSql("ALTER TABLE customfieldvalues ADD CONSTRAINT FK_C78CFA5371F7E88B FOREIGN KEY (event_id) REFERENCES events (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE customfieldvalues CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE PredefinedEvent ADD startdatedelta INT DEFAULT NULL');
    }
}
