<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170825094102 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE PostItCategory (id INT NOT NULL, namefield_id INT DEFAULT NULL, textfield_id INT DEFAULT NULL, colorfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_97E4D5093814A373 (namefield_id), UNIQUE INDEX UNIQ_97E4D50914DEC9F2 (textfield_id), UNIQUE INDEX UNIQ_97E4D50963E96BE3 (colorfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE PostItCategory ADD CONSTRAINT FK_97E4D5093814A373 FOREIGN KEY (namefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE PostItCategory ADD CONSTRAINT FK_97E4D50914DEC9F2 FOREIGN KEY (textfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE PostItCategory ADD CONSTRAINT FK_97E4D50963E96BE3 FOREIGN KEY (colorfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE PostItCategory ADD CONSTRAINT FK_97E4D509BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE PostItCategory');
    }
    
    public function postUp(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT id FROM customfieldtypes WHERE type = ?", array('string'));
        $stringid = $stmt->fetch()['id'];
    
        $stmt = $this->connection->executeQuery("SELECT id FROM customfieldtypes WHERE type = ?", array('text'));
        $textid = $stmt->fetch()['id'];
    
        $this->connection->insert('customfields', array('type_id' => $stringid, 'name' => 'Nom', 'place' => 1, 'defaultvalue' => ''));
        $nameid = $this->connection->lastInsertId();
    
        $this->connection->insert('customfields', array('type_id' => $textid, 'name' => 'Commentaire', 'place' => 2, 'defaultvalue' => ''));
        $commentid = $this->connection->lastInsertId();
    
        $this->connection->insert('customfields', array(
            'type_id' => $stringid,
            'name' => "Couleur",
            'place' => 3,
            'defaultvalue' => "",
            'multiple' => 0,
            'tooltip' => ""
        ));
        
        $colorid = $this->connection->lastInsertId();
        
        $this->connection->insert('categories', array(
            'fieldname_id' => $nameid, 'shortname' => 'PostIt', 'color' => '#000000',
            'compactmode' => 0, 'name' => 'PostIt', 'discr' => 'postit', 'system' => 1, 'exclude' => 1, 'timelineconfirmed' => 0, 'archived' => 0));
        $catid = $this->connection->lastInsertId();
    
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $nameid));
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $commentid));
    
        $this->connection->update('customfields', array('category_id' => $catid), array('id' => $colorid));
        
        $this->connection->insert('PostItCategory', array('id' => $catid, 'namefield_id' => $nameid, 'textfield_id' => $commentid, 'colorfield_id' => $colorid));
    }
}
