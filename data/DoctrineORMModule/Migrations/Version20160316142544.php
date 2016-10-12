<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160316142544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE opsuptypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles_opsuptypes (opsuptype_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_775DD0C58C0B1D99 (opsuptype_id), INDEX IDX_775DD0C5D60322AC (role_id), PRIMARY KEY(opsuptype_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE roles_opsuptypes ADD CONSTRAINT FK_775DD0C58C0B1D99 FOREIGN KEY (opsuptype_id) REFERENCES opsuptypes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE roles_opsuptypes ADD CONSTRAINT FK_775DD0C5D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE opsups ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE opsups ADD CONSTRAINT FK_7C31005AC54C8C93 FOREIGN KEY (type_id) REFERENCES opsuptypes (id)');
        $this->addSql('CREATE INDEX IDX_7C31005AC54C8C93 ON opsups (type_id)');
        $this->addSql('ALTER TABLE reportcategories CHANGE shortname shortname VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE roles_opsuptypes DROP FOREIGN KEY FK_775DD0C58C0B1D99');
        $this->addSql('ALTER TABLE opsups DROP FOREIGN KEY FK_7C31005AC54C8C93');
        $this->addSql('DROP TABLE opsuptypes');
        $this->addSql('DROP TABLE roles_opsuptypes');
        $this->addSql('DROP INDEX IDX_7C31005AC54C8C93 ON opsups');
        $this->addSql('ALTER TABLE opsups DROP type_id');
        $this->addSql('ALTER TABLE reportcategories CHANGE shortname shortname VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }

    public function postUp(Schema $schema)
    {
        //create fisrt opsup type
        $this->connection->insert('opsuptypes', array(
            'name' => 'Chef de salle',
            'shortname' => 'CdS'
        ));
        $opsupid = $this->connection->lastInsertId();

        //add it to each existing opsup
        $stmt = $this->connection->executeQuery("SELECT * FROM opsups");
        $opsups = $stmt->fetchAll();
        foreach ($opsups as $opsup) {
            $this->connection->update('opsups', array('type_id' => $opsupid), array('id' => $opsup['id']));
        }

        //add it to each existing role
        $stmt = $this->connection->executeQuery("SELECT * FROM roles");
        $roles = $stmt->fetchAll();
        foreach ($roles as $role) {
            $this->connection->insert('roles_opsuptypes', array(
                'opsuptype_id' => $opsupid,
                'role_id' => $role['id']
            ));
        }

    }

}
