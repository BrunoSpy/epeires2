<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170222175413 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE AlertCategory (id INT NOT NULL, typefield_id INT DEFAULT NULL, flightplanfield_id INT DEFAULT NULL, defaultalertcategory TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A65F76DD185E8938 (typefield_id), UNIQUE INDEX UNIQ_A65F76DD6588A2C0 (flightplanfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DD185E8938 FOREIGN KEY (typefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DD6588A2C0 FOREIGN KEY (flightplanfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DDBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE AlertCategory');
    }
}
