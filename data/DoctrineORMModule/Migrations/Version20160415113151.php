<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout des tours de service pour les superviseurs opérationnels
 */
class Version20160415113151 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE shifthours (id INT AUTO_INCREMENT NOT NULL, opsuptype_id INT NOT NULL, qualificationzone_id INT DEFAULT NULL, hour TIME NOT NULL, INDEX IDX_E206B2598C0B1D99 (opsuptype_id), INDEX IDX_E206B2598C1970A5 (qualificationzone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shifthours ADD CONSTRAINT FK_E206B2598C0B1D99 FOREIGN KEY (opsuptype_id) REFERENCES opsuptypes (id)');
        $this->addSql('ALTER TABLE shifthours ADD CONSTRAINT FK_E206B2598C1970A5 FOREIGN KEY (qualificationzone_id) REFERENCES qualifzones (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE shifthours');
    }
}
