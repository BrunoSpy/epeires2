<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170223161633 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE AlertCategory (id INT NOT NULL, typefield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A65F76DD185E8938 (typefield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DD185E8938 FOREIGN KEY (typefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DDBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD alertfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2FAE2C410F FOREIGN KEY (alertfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BF2BD2FAE2C410F ON FlightPlanCategory (alertfield_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE AlertCategory');
        $this->addSql('ALTER TABLE FlightPlanCategory DROP FOREIGN KEY FK_9BF2BD2FAE2C410F');
        $this->addSql('DROP INDEX UNIQ_9BF2BD2FAE2C410F ON FlightPlanCategory');
        $this->addSql('ALTER TABLE FlightPlanCategory DROP alertfield_id');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->insert('customfieldtypes', array('name' => 'Alerte', 'type' => 'alert'));
        $this->connection->delete('customfieldtypes', array('name' => 'Plan de Vol', 'type' => 'flightplan'));
    }
}
