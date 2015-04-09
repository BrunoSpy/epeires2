<?php
/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout dela configuration des onglets personnalisés
 */
class Version20150409083256 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tabs (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, place INT DEFAULT NULL, UNIQUE INDEX UNIQ_12CB60635E237E06 (name), UNIQUE INDEX UNIQ_12CB606364082763 (shortname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles_tabs_read (tab_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_8BE614B18D0C9323 (tab_id), INDEX IDX_8BE614B1D60322AC (role_id), PRIMARY KEY(tab_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tab_category (tab_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_D654A95E8D0C9323 (tab_id), INDEX IDX_D654A95E12469DE2 (category_id), PRIMARY KEY(tab_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE roles_tabs_read ADD CONSTRAINT FK_8BE614B18D0C9323 FOREIGN KEY (tab_id) REFERENCES tabs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE roles_tabs_read ADD CONSTRAINT FK_8BE614B1D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tab_category ADD CONSTRAINT FK_D654A95E8D0C9323 FOREIGN KEY (tab_id) REFERENCES tabs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tab_category ADD CONSTRAINT FK_D654A95E12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE MilCategory DROP onMilPage');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE roles_tabs_read DROP FOREIGN KEY FK_8BE614B18D0C9323');
        $this->addSql('ALTER TABLE tab_category DROP FOREIGN KEY FK_D654A95E8D0C9323');
        $this->addSql('DROP TABLE tabs');
        $this->addSql('DROP TABLE roles_tabs_read');
        $this->addSql('DROP TABLE tab_category');
        $this->addSql('ALTER TABLE MilCategory ADD onMilPage TINYINT(1) NOT NULL');
    }
}
