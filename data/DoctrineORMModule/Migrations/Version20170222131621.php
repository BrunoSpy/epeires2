<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170222131621 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE FlightPlanCategory DROP FOREIGN KEY FK_9BF2BD2F74BFCC4E');
        $this->addSql('DROP INDEX UNIQ_9BF2BD2F74BFCC4E ON FlightPlanCategory');
        $this->addSql('ALTER TABLE FlightPlanCategory DROP typeavionfield_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE FlightPlanCategory ADD typeavionfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2F74BFCC4E FOREIGN KEY (typeavionfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BF2BD2F74BFCC4E ON FlightPlanCategory (typeavionfield_id)');
    }
}
