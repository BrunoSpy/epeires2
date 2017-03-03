<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170302195306 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE AlertCategory ADD causefield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE AlertCategory ADD CONSTRAINT FK_A65F76DDAA500C6D FOREIGN KEY (causefield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A65F76DDAA500C6D ON AlertCategory (causefield_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE AlertCategory DROP FOREIGN KEY FK_A65F76DDAA500C6D');
        $this->addSql('DROP INDEX UNIQ_A65F76DDAA500C6D ON AlertCategory');
        $this->addSql('ALTER TABLE AlertCategory DROP causefield_id');
    }
}
