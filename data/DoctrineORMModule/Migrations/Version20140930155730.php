<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Changement d'une valeur dans la base de données :
 * Le titre du type de champ "boolean" passe de "Vrai/Faux" à "Oui/Non"
 */
class Version20140930155730 extends AbstractMigration {

    public function up(Schema $schema) {
        $this->connection->update('customfieldtypes', array('name' => 'Oui/Non'), array('type' => 'boolean'));
    }

    public function down(Schema $schema) {
        $this->connection->update('customfieldtypes', array('name' => 'Vrai/Faux'), array('type' => 'boolean'));
    }

}
