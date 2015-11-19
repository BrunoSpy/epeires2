<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Correction de l'ordre des modèles et des actions
 */
class Version20151119155413 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // correction ordre des modèles
        $stmt = $this->connection->executeQuery('SELECT * FROM events WHERE discr = "model" AND parent_id IS NULL ORDER BY category_id ASC, place ASC');
        $models = $stmt->fetchAll();
        $i = 0;
        $previousCatId = -1;
        foreach ($models as $model){
            if($model['category_id'] !== $previousCatId){
                $previousCatId = $model['category_id'];
                $i = 1;
            } else {
                $i++;
            }
            $this->connection->update('events',array('place' => $i), array('id' => $model['id']));
        }

        //correction ordre des actions
        $stmt = $this->connection->executeQuery("SELECT * FROM ActionCategory");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat) {
            $stmt2 = $this->connection->executeQuery("SELECT * FROM events WHERE category_id = " . $cat['id'] . " ORDER BY parent_id ASC, place ASC");
            $actions = $stmt2->fetchAll();
            $previousParent = -1;
            $i = 1;
            foreach ($actions as $action) {
                if($action['parent_id'] != $previousParent) {
                    $previousParent = $action['parent_id'];
                    $i = 1;
                } else {
                    $i++;
                }
                $this->connection->update('events', array('place' => $i), array('id' => $action['id']));
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
