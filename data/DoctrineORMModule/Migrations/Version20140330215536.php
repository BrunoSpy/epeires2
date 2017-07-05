<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140330215536 extends AbstractMigration
{
        
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE ActionCategory (id INT NOT NULL, namefield_id INT DEFAULT NULL, textfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_9CB46AD33814A373 (namefield_id), UNIQUE INDEX UNIQ_9CB46AD314DEC9F2 (textfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE ActionCategory ADD CONSTRAINT FK_9CB46AD33814A373 FOREIGN KEY (namefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE ActionCategory ADD CONSTRAINT FK_9CB46AD314DEC9F2 FOREIGN KEY (textfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE ActionCategory ADD CONSTRAINT FK_9CB46AD3BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
           
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE ActionCategory");
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
        
        $this->connection->insert('categories', array('fieldname_id' => $nameid, 'shortname' => 'Action', 'color' => '#000000',
                                                        'compactmode' => 0, 'timeline' => 0, 'name' => 'Action', 'discr' => 'action'));
        $catid = $this->connection->lastInsertId();
        
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $nameid));
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $commentid));
        
        $this->connection->insert('ActionCategory', array('id' => $catid, 'namefield_id' => $nameid, 'textfield_id' => $commentid));
    }
}
