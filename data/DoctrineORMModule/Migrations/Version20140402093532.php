<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140402093532 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE categories ADD place INT DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE categories DROP place");
    }
    
    public function postUp(Schema $schema) {
        $stmt = $this->connection->executeQuery("SELECT DISTINCT `parent_id` FROM `categories` ORDER BY `parent_id`");
        $result = $stmt->fetchAll();
        foreach ($result as $parent){
            $i = 0;
            if($parent['parent_id'] == null){
                $stmt = $this->connection->executeQuery("SELECT * FROM `categories` WHERE `parent_id` is null");
            } else {
                $stmt = $this->connection->executeQuery("SELECT * FROM `categories` WHERE `parent_id` = ?", array($parent['parent_id']));
            }
            $cats = $stmt->fetchAll();
            foreach ($cats as $cat){
                $this->connection->update('categories', array('place' => $i), array('id' => $cat['id']));
                $i++;
            }
        }
    }
}
