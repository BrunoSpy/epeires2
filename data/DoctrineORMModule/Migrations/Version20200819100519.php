<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200819100519 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE radars ADD parent_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE radars ADD CONSTRAINT FK_7A2EA925727ACA70 FOREIGN KEY (parent_id) REFERENCES radars (id)');
        $this->addSql('CREATE INDEX IDX_7A2EA925727ACA70 ON radars (parent_id)');
        $this->addSql('ALTER TABLE radars RENAME TO switchobjects');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE switchobjects RENAME TO radars');
        $this->addSql('ALTER TABLE radars DROP FOREIGN KEY FK_7A2EA925727ACA70');
        $this->addSql('DROP INDEX IDX_7A2EA925727ACA70 ON radars');
        $this->addSql('ALTER TABLE radars DROP parent_id, DROP type');
    }

    public function postUp(Schema $schema): void
    {
        $stmt = $this->connection->executeQuery("UPDATE `switchobjects` SET `type`='radar' WHERE 1");
    }
}
