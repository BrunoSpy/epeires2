<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140409212742 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE AlarmCategory (id INT NOT NULL, namefield_id INT DEFAULT NULL, textfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_16E5F1753814A373 (namefield_id), UNIQUE INDEX UNIQ_16E5F17514DEC9F2 (textfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE AlarmCategory ADD CONSTRAINT FK_16E5F1753814A373 FOREIGN KEY (namefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE AlarmCategory ADD CONSTRAINT FK_16E5F17514DEC9F2 FOREIGN KEY (textfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE AlarmCategory ADD CONSTRAINT FK_16E5F175BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE AlarmCategory");
    }
    
    public function postUp(Schema $schema) {
        $stmt = $this->connection->executeQuery("SELECT id FROM customfieldtypes WHERE type = ?", array('string'));
        $stringid = $stmt->fetch()['id'];
        
        $stmt = $this->connection->executeQuery("SELECT id FROM customfieldtypes WHERE type = ?", array('text'));
        $textid = $stmt->fetch()['id'];
        
        $this->connection->insert('customfields', array('type_id' => $stringid, 'name' => 'Nom', 'place' => 1, 'defaultvalue' => ''));
        $nameid = $this->connection->lastInsertId();

        $this->connection->insert('customfields', array('type_id' => $textid, 'name' => 'Commentaire', 'place' => 2, 'defaultvalue' => ''));
        $commentid = $this->connection->lastInsertId();
        
        $stmt = $this->connection->executeQuery("SELECT MAX(`place`) as place FROM `categories` WHERE `parent_id` is null");
        $place = $stmt->fetch()['place'] + 1;
        
        $this->connection->insert('categories', array('fieldname_id' => $nameid, 'shortname' => 'Alarme', 'color' => '#000000', 'place' => $place,
                                                        'compactmode' => 0, 'timeline' => 0, 'name' => 'Alarme', 'discr' => 'alarm'));
        $catid = $this->connection->lastInsertId();
        
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $nameid));
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $commentid));
        
        $this->connection->insert('AlarmCategory', array('id' => $catid, 'namefield_id' => $nameid, 'textfield_id' => $commentid));
    }
}
