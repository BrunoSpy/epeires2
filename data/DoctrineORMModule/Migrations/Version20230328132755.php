<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230328132755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Event ADD efnesent TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE afis CHANGE openedhours openedhours LONGTEXT NOT NULL, CHANGE contacts contacts LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE eventupdates CHANGE text text LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE afis CHANGE openedhours openedhours TEXT NOT NULL, CHANGE contacts contacts TEXT NOT NULL');
        $this->addSql('ALTER TABLE Event DROP efnesent');
        $this->addSql('ALTER TABLE eventupdates CHANGE text text TEXT NOT NULL');
    }
}
