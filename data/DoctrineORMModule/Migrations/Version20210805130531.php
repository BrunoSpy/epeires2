<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210805130531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `customfieldlog` ADD INDEX(`object_id`)');
        $this->addSql('ALTER TABLE `customfieldlog` ADD INDEX(`object_class`)');
        $this->addSql('ALTER TABLE `log` ADD INDEX(`object_id`)');
        $this->addSql('ALTER TABLE `log` ADD INDEX(`object_class`)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
