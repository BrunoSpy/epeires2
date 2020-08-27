<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824123110 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE RadarCategory RENAME TO SwitchObjectCategory ');
        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_4F30218647431654');
        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_4F302186761F9C6B');
        $this->addSql('DROP INDEX uniq_4f302186761f9c6b ON SwitchObjectCategory');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_451B654C761F9C6B ON SwitchObjectCategory (statefield_id)');
        $this->addSql('DROP INDEX uniq_4f30218647431654 ON SwitchObjectCategory');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_451B654C47431654 ON SwitchObjectCategory (radarfield_id)');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_4F30218647431654 FOREIGN KEY (radarfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_4F302186761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SwitchObjectCategory RENAME TO RadarCategory');
        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_451B654C761F9C6B');
        $this->addSql('ALTER TABLE SwitchObjectCategory DROP FOREIGN KEY FK_451B654C47431654');
        $this->addSql('DROP INDEX uniq_451b654c761f9c6b ON SwitchObjectCategory');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F302186761F9C6B ON SwitchObjectCategory (statefield_id)');
        $this->addSql('DROP INDEX uniq_451b654c47431654 ON SwitchObjectCategory');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F30218647431654 ON SwitchObjectCategory (radarfield_id)');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_451B654C761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE SwitchObjectCategory ADD CONSTRAINT FK_451B654C47431654 FOREIGN KEY (radarfield_id) REFERENCES customfields (id)');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE `categories` SET `discr`="switch" WHERE `discr` = "radar"');
    }
}
