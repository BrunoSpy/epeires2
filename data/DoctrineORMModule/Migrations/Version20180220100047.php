<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180220100047 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory ADD normalRateField_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407E3FD80BCD FOREIGN KEY (normalRateField_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AEA7407E3FD80BCD ON ATFCMCategory (normalRateField_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory DROP FOREIGN KEY FK_AEA7407E3FD80BCD');
        $this->addSql('DROP INDEX UNIQ_AEA7407E3FD80BCD ON ATFCMCategory');
        $this->addSql('ALTER TABLE ATFCMCategory DROP normalRateField_id');
    }
}
