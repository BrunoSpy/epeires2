<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140406191124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FrequencyCategory ADD defaultfrequencycategory TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE RadarCategory ADD defaultradarcategory TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE AntennaCategory ADD defaultantennacategory TINYINT(1) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE AntennaCategory DROP defaultantennacategory");
        $this->addSql("ALTER TABLE FrequencyCategory DROP defaultfrequencycategory");
        $this->addSql("ALTER TABLE RadarCategory DROP defaultradarcategory");
    }
    
    public function postUp(Schema $schema) {
        //initialize datas with default = first row
        
        $stmt = $this->connection->executeQuery("SELECT id FROM FrequencyCategory WHERE 1 LIMIT 1");
        $freqid = $stmt->fetch()['id'];
        if($freqid){
            $this->connection->update('FrequencyCategory', array('defaultfrequencycategory' => true), array('id' => $freqid));
        }
        
        $stmt = $this->connection->executeQuery("SELECT id FROM RadarCategory WHERE 1 LIMIT 1");
        $radarid = $stmt->fetch()['id'];
        if($radarid){
            $this->connection->update('RadarCategory', array('defaultradarcategory' => true), array('id' => $radarid));
        }
        
        $stmt = $this->connection->executeQuery("SELECT id FROM AntennaCategory WHERE 1 LIMIT 1");
        $antennaid = $stmt->fetch()['id'];
        if($antennaid){
            $this->connection->update('AntennaCategory', array('defaultantennacategory' => true), array('id' => $antennaid));
        }
    }
}
