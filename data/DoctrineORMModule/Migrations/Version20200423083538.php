<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change type of description field of an action
 */
final class Version20200423083538 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $stmt = $this->connection->executeQuery("SELECT id FROM categories WHERE shortname = ?", array('Action'));
        $catid = $stmt->fetch()['id'];

        $stmt = $this->connection->executeQuery("SELECT id FROM customfieldtypes WHERE type = ?", array('text'));
        $typeid = $stmt->fetch()['id'];

        $this->connection->update('customfields', array('type_id' => $typeid), array('category_id' => $catid, 'name' => 'Nom'));
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

}
