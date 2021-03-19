<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317141040 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE MilCategory ADD internalidField_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE MilCategory ADD CONSTRAINT FK_7BE61CF0D2CF749D FOREIGN KEY (internalidField_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BE61CF0D2CF749D ON MilCategory (internalidField_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE MilCategory DROP FOREIGN KEY FK_7BE61CF0D2CF749D');
        $this->addSql('DROP INDEX UNIQ_7BE61CF0D2CF749D ON MilCategory');
        $this->addSql('ALTER TABLE MilCategory DROP internalidField_id');
    }
}
