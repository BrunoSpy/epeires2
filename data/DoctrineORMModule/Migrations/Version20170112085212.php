<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170112085212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE AfisCategory (id INT NOT NULL, statefield_id INT DEFAULT NULL, afisfield_id INT DEFAULT NULL, defaultafiscategory TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_16B615DF761F9C6B (statefield_id), UNIQUE INDEX UNIQ_16B615DFB58AB200 (afisfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DF761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DFB58AB200 FOREIGN KEY (afisfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE AfisCategory ADD CONSTRAINT FK_16B615DFBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE afis DROP state');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE AfisCategory');
        $this->addSql('ALTER TABLE afis ADD state TINYINT(1) NOT NULL');
    }
    
    public function postUp(Schema $schema)
    {
        $this->connection->insert('customfieldtypes', array('name' => 'Afis', 'type' => 'afis'));
    }
}
