<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200831072541 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Migrate Radar Tab';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema): void
    {
        $cats = $this->connection->fetchAll("SELECT id FROM `categories` WHERE `discr`='switch' ");
        if(count($cats) == 1) {
            $radarcatid = $cats[0]['id'];

            //create tab
            $this->connection->insert('tabs', array(
                'name' => "Radars",
                'shortname' => "Radars",
                'place' => 0,
                'onlyroot' => 0,
                'isDefault' =>0,
                'type'=>"switchlist",
                'horizontal' => 0
            ));
            $tabid = $this->connection->lastInsertId();

            $this->connection->insert('tab_category', array(
                'tab_id' => $tabid,
                'category_id' => $radarcatid
            ));

            $radars = $this->connection->executeQuery("SELECT id FROM `switchobjects` WHERE `type`='radar'");
            while($row = $radars->fetch()) {
                $this->connection->insert('switchobjects_categories', array(
                    'switchobjectcategory_id' => $radarcatid,
                    'switchobject_id' => $row['id']
                ));
            }

        }
    }
}
