<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200825130042 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE switchobjects_categories (switchobjectcategory_id INT NOT NULL, switchobject_id INT NOT NULL, INDEX IDX_F9682B4E27F4566A (switchobjectcategory_id), INDEX IDX_F9682B4E2F5836B2 (switchobject_id), PRIMARY KEY(switchobjectcategory_id, switchobject_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE switchobjects_categories ADD CONSTRAINT FK_F9682B4E27F4566A FOREIGN KEY (switchobjectcategory_id) REFERENCES SwitchObjectCategory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE switchobjects_categories ADD CONSTRAINT FK_F9682B4E2F5836B2 FOREIGN KEY (switchobject_id) REFERENCES switchobjects (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE switchobjects_categories');
    }
}
