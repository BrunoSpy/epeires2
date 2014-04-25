<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


class Version20140425150558 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE file_event DROP FOREIGN KEY FK_912DBA9071F7E88B");
        $this->addSql("DROP INDEX IDX_912DBA9071F7E88B ON file_event");
        $this->addSql("ALTER TABLE file_event DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE file_event CHANGE event_id abstractevent_id INT NOT NULL");
        $this->addSql("ALTER TABLE file_event ADD CONSTRAINT FK_912DBA90A7086F66 FOREIGN KEY (abstractevent_id) REFERENCES events (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_912DBA90A7086F66 ON file_event (abstractevent_id)");
        $this->addSql("ALTER TABLE file_event ADD PRIMARY KEY (file_id, abstractevent_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE file_event DROP FOREIGN KEY FK_912DBA90A7086F66");
        $this->addSql("DROP INDEX IDX_912DBA90A7086F66 ON file_event");
        $this->addSql("ALTER TABLE file_event DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE file_event CHANGE abstractevent_id event_id INT NOT NULL");
        $this->addSql("ALTER TABLE file_event ADD CONSTRAINT FK_912DBA9071F7E88B FOREIGN KEY (event_id) REFERENCES Event (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_912DBA9071F7E88B ON file_event (event_id)");
        $this->addSql("ALTER TABLE file_event ADD PRIMARY KEY (file_id, event_id)");
    }
}
