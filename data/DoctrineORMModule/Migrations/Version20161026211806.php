<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161026211806 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sectorsgroupsrelations (id INT AUTO_INCREMENT NOT NULL, sector_id INT DEFAULT NULL, sectorgroup_id INT DEFAULT NULL, place INT DEFAULT NULL, INDEX IDX_84892FADDE95C867 (sector_id), INDEX IDX_84892FAD2091AB1D (sectorgroup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sectorsgroupsrelations ADD CONSTRAINT FK_84892FADDE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id)');
        $this->addSql('ALTER TABLE sectorsgroupsrelations ADD CONSTRAINT FK_84892FAD2091AB1D FOREIGN KEY (sectorgroup_id) REFERENCES sectorgroups (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sectors_groups (sector_id INT NOT NULL, sectorgroup_id INT NOT NULL, INDEX IDX_D0A3ACDDE95C867 (sector_id), INDEX IDX_D0A3ACD2091AB1D (sectorgroup_id), PRIMARY KEY(sector_id, sectorgroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sectors_groups ADD CONSTRAINT FK_D0A3ACD2091AB1D FOREIGN KEY (sectorgroup_id) REFERENCES sectorgroups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sectors_groups ADD CONSTRAINT FK_D0A3ACDDE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE sectorsgroupsrelations');
    }

    public function postUp(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT * FROM sectors_groups");
        $sectors_groups = $stmt->fetchAll();
        foreach ($sectors_groups as $sectors_group) {
            $this->connection->insert('sectorsgroupsrelations', array(
                'sector_id' => $sectors_group['sector_id'],
                'sectorgroup_id' => $sectors_group['sectorgroup_id'],
                'place' => 0
            ));
        }
        $this->connection->executeQuery("DROP TABLE sectors_groups");
    }
}
