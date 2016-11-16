<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116213752 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE field (id INT AUTO_INCREMENT NOT NULL, interplan_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, comment VARCHAR(255) NOT NULL, intTime DATETIME DEFAULT NULL, INDEX IDX_5BF54558BE5A87C9 (interplan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE afis (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, state TINYINT(1) NOT NULL, decommissionned TINYINT(1) NOT NULL, INDEX IDX_93B708809E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE interplan (id INT AUTO_INCREMENT NOT NULL, startTime DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, typeAlerte VARCHAR(255) NOT NULL, firSource VARCHAR(255) NOT NULL, firDest VARCHAR(255) NOT NULL, comment VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF54558BE5A87C9 FOREIGN KEY (interplan_id) REFERENCES interplan (id)');
        $this->addSql('ALTER TABLE afis ADD CONSTRAINT FK_93B708809E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE field DROP FOREIGN KEY FK_5BF54558BE5A87C9');
        $this->addSql('DROP TABLE field');
        $this->addSql('DROP TABLE afis');
        $this->addSql('DROP TABLE interplan');
    }
}
