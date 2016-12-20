<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219150515 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE flightplans ADD typeavion VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE afis ADD code VARCHAR(4) NOT NULL, ADD contacts VARCHAR(255) NOT NULL, CHANGE shortname openedhours VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93B7088077153098 ON afis (code)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_93B7088077153098 ON afis');
        $this->addSql('ALTER TABLE afis ADD shortname VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP code, DROP openedhours, DROP contacts');
        $this->addSql('ALTER TABLE flightplans DROP typeavion');
    }
}
