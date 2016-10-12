<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160516193420 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE recurrenceexdates (id INT AUTO_INCREMENT NOT NULL, recurrence_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_55C814642C414CE8 (recurrence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recurrenceexdates ADD CONSTRAINT FK_55C814642C414CE8 FOREIGN KEY (recurrence_id) REFERENCES recurrences (id)');
        $this->addSql('ALTER TABLE recurrences ADD startdate DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE recurrenceexdates');
        $this->addSql('ALTER TABLE recurrences DROP startdate');
    }
}
