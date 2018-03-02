<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180226172217 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory ADD regulationStateField_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407E6C7C7649 FOREIGN KEY (regulationStateField_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AEA7407E6C7C7649 ON ATFCMCategory (regulationStateField_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ATFCMCategory DROP FOREIGN KEY FK_AEA7407E6C7C7649');
        $this->addSql('DROP INDEX UNIQ_AEA7407E6C7C7649 ON ATFCMCategory');
        $this->addSql('ALTER TABLE ATFCMCategory DROP regulationStateField_id');
    }
}
