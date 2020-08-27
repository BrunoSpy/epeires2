<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824124941 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_4F30218647431654');
        $this->addSql('DROP INDEX UNIQ_451B654C47431654 ON SwitchObjectCategory');
        $this->addSql('ALTER TABLE SwitchObjectCategory DROP defaultradarcategory, CHANGE radarfield_id switchobjectfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_451B654C298E31B8 FOREIGN KEY (switchobjectfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_451B654C298E31B8 ON SwitchObjectCategory (switchobjectfield_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_451B654C298E31B8');
        $this->addSql('DROP INDEX UNIQ_451B654C298E31B8 ON SwitchObjectCategory');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD defaultradarcategory TINYINT(1) NOT NULL, CHANGE switchobjectfield_id radarfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_4F30218647431654 FOREIGN KEY (radarfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_451B654C47431654 ON SwitchObjectCategory (radarfield_id)');
    }
}
