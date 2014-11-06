<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout des champs delta à la catégorie Alarme
 */
class Version20141104081541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE AlarmCategory ADD deltabeginField_id INT DEFAULT NULL, ADD deltaendField_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE AlarmCategory ADD CONSTRAINT FK_16E5F175C923DCF3 FOREIGN KEY (deltabeginField_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AlarmCategory ADD CONSTRAINT FK_16E5F17528A13DD2 FOREIGN KEY (deltaendField_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16E5F175C923DCF3 ON AlarmCategory (deltabeginField_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16E5F17528A13DD2 ON AlarmCategory (deltaendField_id)');
        
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE AlarmCategory DROP FOREIGN KEY FK_16E5F175C923DCF3');
        $this->addSql('ALTER TABLE AlarmCategory DROP FOREIGN KEY FK_16E5F17528A13DD2');
        $this->addSql('DROP INDEX UNIQ_16E5F175C923DCF3 ON AlarmCategory');
        $this->addSql('DROP INDEX UNIQ_16E5F17528A13DD2 ON AlarmCategory');
        $this->addSql('ALTER TABLE AlarmCategory DROP deltabeginField_id, DROP deltaendField_id');
        $this->addSql('ALTER TABLE PredefinedEvent ADD startdatedelta INT DEFAULT NULL');
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
            if($cat['deltabeginField_id'] == NULL){
                $this->connection->insert('customfields', 
                        array('category_id' => $cat['id'],
                            'type_id' => 1,
                            'name' => "Delta p/r début",
                            'place' => 1,
                            'defaultvalue' => "",
                            'tooltip' => "Optionnel. Si présent, la date de début n'est pas prise en compte."));
                $deltabeginid = $this->connection->lastInsertId();
                $this->connection->update('AlarmCategory', array('deltabeginField_id' => $deltabeginid), array('id' => $cat['id']));
            }
            if($cat['deltaendField_id'] == NULL){
                $this->connection->insert('customfields', 
                        array('category_id' => $cat['id'],
                            'type_id' => 1,
                            'name' => "Delta p/r fin",
                            'place' => 2,
                            'defaultvalue' => "",
                            'tooltip' => "Optionnel. Si présent, la date de début et le delta pr/ au début ne sont pas pris en compte."));
                $deltaendid = $this->connection->lastInsertId();
                $this->connection->update('AlarmCategory', array('deltaendField_id' => $deltaendid), array('id' => $cat['id']));
            }
            $this->connection->update('customfields', array('place' => 3), array('id' => $cat['namefield_id']));
            $this->connection->update('customfields', array('place' => 4), array('id' => $cat['textfield_id']));
        }

        //now transform startdatedelta into customfield
        $stmt = $this->connection->executeQuery("SELECT * from events WHERE `category_id` = ? AND `discr` = 'model'", array($catid));
        foreach ($stmt->fetchAll() as $model){
            $predefinedrow = $this->connection->fetchArray('SELECT * FROM PredefinedEvent WHERE `id` = ?', array($model['id']));
            $this->connection->insert('customfieldvalues', array('event_id' => $model['id'], 'customfield_id' => $deltabeginid, 'value' => $predefinedrow[4]));
        }
         $this->addSql('ALTER TABLE PredefinedEvent DROP startdatedelta');
    }
}
