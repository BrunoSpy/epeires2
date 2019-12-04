<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180109151619 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory ADD descriptionfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407ED352F370 FOREIGN KEY (descriptionfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AEA7407ED352F370 ON ATFCMCategory (descriptionfield_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory DROP FOREIGN KEY FK_AEA7407ED352F370');
        $this->addSql('DROP INDEX UNIQ_AEA7407ED352F370 ON ATFCMCategory');
        $this->addSql('ALTER TABLE ATFCMCategory DROP descriptionfield_id');
    }
}
