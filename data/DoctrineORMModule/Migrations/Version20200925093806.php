<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200925093806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE milcategorylastupdates (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, lastUpdate DATETIME NOT NULL, day VARCHAR(255) NOT NULL, INDEX IDX_9420E11312469DE2 (category_id), UNIQUE INDEX search_idx (category_id, day), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE milcategorylastupdates ADD CONSTRAINT FK_9420E11312469DE2 FOREIGN KEY (category_id) REFERENCES MilCategory (id)');
        $this->addSql('ALTER TABLE MilCategory DROP lastUpdateDate, DROP lastUpdateSequence');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE milcategorylastupdates');
        $this->addSql('ALTER TABLE MilCategory ADD lastUpdateDate DATETIME DEFAULT NULL, ADD lastUpdateSequence INT DEFAULT NULL');
    }
}
