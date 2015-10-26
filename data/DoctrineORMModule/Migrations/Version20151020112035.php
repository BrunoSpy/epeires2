<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changement des noms des champs de la catégorie Alarmes
 */
class Version20151020112035 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
    
    /**
     * Create fields for previously created alarm categories
     * @param Schema $schema
     */
    public function postUp(Schema $schema){
    	$stmt = $this->connection->executeQuery("SELECT * FROM AlarmCategory");
    	$categories = $stmt->fetchAll();
    	$catid;
    	$deltabeginid;
    	$deltaendid;
    	foreach ($categories as $cat){
    		$catid = $cat['id']; //only one alarm category possible at this point
    		if($cat['deltabeginField_id'] !== NULL){
    			$this->connection->update('customfields', array('name' => "Delta p/r début (min)"), array('id' => $cat['deltabeginField_id']));
    		}
    		if($cat['deltaendField_id'] !== NULL){
    			$this->connection->update('customfields', array('name' => "Delta p/r fin (min)"), array('id' => $cat['deltaendField_id']));
    		}
    	}
    }
}
