<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200903085544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add MAPD capability';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE MilCategory ADD origin VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE MilCategory ADD nmB2B TINYINT(1) NOT NULL, DROP origin');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE `MilCategory` SET `origin` = "nmb2b" WHERE `nmB2B` = true');
        $this->connection->executeQuery('ALTER TABLE `MilCategory` DROP `nmB2B`');
    }
}
