<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703124145 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX value ON customfieldvalues');
        $this->addSql('ALTER TABLE PredefinedEvent ADD color VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL COLLATE `utf8_bin`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4BE998C5E237E06 ON PredefinedEvent (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_D4BE998C5E237E06 ON PredefinedEvent');
        $this->addSql('ALTER TABLE PredefinedEvent DROP color, CHANGE name name VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('CREATE FULLTEXT INDEX value ON customfieldvalues (value)');
    }
}
