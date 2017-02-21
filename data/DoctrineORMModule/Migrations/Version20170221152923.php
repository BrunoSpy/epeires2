<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170221152923 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE AfisCategory (id INT NOT NULL, statefield_id INT DEFAULT NULL, afisfield_id INT DEFAULT NULL, defaultafiscategory TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_16B615DF761F9C6B (statefield_id), UNIQUE INDEX UNIQ_16B615DFB58AB200 (afisfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FlightPlanCategory (id INT NOT NULL, aircraftidfield_id INT DEFAULT NULL, typeavionfield_id INT DEFAULT NULL, startfield_id INT DEFAULT NULL, destinationfield_id INT DEFAULT NULL, estimatedtimeofarrivalfield_id INT DEFAULT NULL, defaultflightplancategory TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9BF2BD2F2ACC60AE (aircraftidfield_id), UNIQUE INDEX UNIQ_9BF2BD2F74BFCC4E (typeavionfield_id), UNIQUE INDEX UNIQ_9BF2BD2FC0BABA1E (startfield_id), UNIQUE INDEX UNIQ_9BF2BD2F5E46D9B5 (destinationfield_id), UNIQUE INDEX UNIQ_9BF2BD2F339997A6 (estimatedtimeofarrivalfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DF761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DFB58AB200 FOREIGN KEY (afisfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DFBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2F2ACC60AE FOREIGN KEY (aircraftidfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2F74BFCC4E FOREIGN KEY (typeavionfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2FC0BABA1E FOREIGN KEY (startfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2F5E46D9B5 FOREIGN KEY (destinationfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2F339997A6 FOREIGN KEY (estimatedtimeofarrivalfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FlightPlanCategory ADD CONSTRAINT FK_9BF2BD2FBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE flightplans');
        $this->addSql('ALTER TABLE afis DROP state');
        $this->addSql('ALTER TABLE customfields CHANGE tooltip tooltip LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE flightplans (id INT AUTO_INCREMENT NOT NULL, aircraftid VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, destinationterrain VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, startterrain VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, timeofarrival DATETIME DEFAULT NULL, estimatedtimeofarrival DATETIME DEFAULT NULL, typealerte VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, comment VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, typeavion VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE AfisCategory');
        $this->addSql('DROP TABLE FlightPlanCategory');
        $this->addSql('ALTER TABLE afis ADD state TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE customfields CHANGE tooltip tooltip LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->insert('customfieldtypes', array('name' => 'Afis', 'type' => 'afis'));
        $this->connection->insert('customfieldtypes', array('name' => 'Plan de Vol', 'type' => 'flightplan'));
    }
}
