<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210407133519 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE switchobjects ADD place INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE switchobjects DROP place');
    }

    public function postUp(Schema $schema): void
    {
        $types = $this->connection->fetchAllAssociative("SELECT Distinct `type` FROM `switchobjects` WHERE 1");
        foreach ($types as $type) {
            $parents = $this->connection->fetchAllAssociative("SELECT * FROM `switchobjects` WHERE `type` = '".$type['type'] ."' AND `parent_id` IS NULL AND `decommissionned` = 0 ORDER BY `name` ASC");
            $place = 0;
            foreach ($parents as $parent) {
                $this->connection->update('switchobjects', array('place'=> $place), array('id'=>$parent['id']));
                $children = $this->connection->fetchAllAssociative("SELECT * FROM `switchobjects` WHERE `parent_id` ='".$parent['id']."' ORDER BY `name` ASC");
                $childplace = 0;
                foreach ($children as $child) {
                    $this->connection->executeQuery("UPDATE `switchobjects` SET `place` = '".$childplace."' WHERE `id` = '".$child['id']."'");
                    $childplace++;
                }
                $place++;
            }
        }
    }
}
