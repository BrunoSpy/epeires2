<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160512182405 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE recurrences (id INT AUTO_INCREMENT NOT NULL, recurrencePattern VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Event ADD recurrence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Event ADD CONSTRAINT FK_FA6F25A32C414CE8 FOREIGN KEY (recurrence_id) REFERENCES recurrences (id)');
        $this->addSql('CREATE INDEX IDX_FA6F25A32C414CE8 ON Event (recurrence_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Event DROP FOREIGN KEY FK_FA6F25A32C414CE8');
        $this->addSql('DROP TABLE recurrences');
        $this->addSql('DROP INDEX IDX_FA6F25A32C414CE8 ON Event');
        $this->addSql('ALTER TABLE Event DROP recurrence_id');
    }
}
