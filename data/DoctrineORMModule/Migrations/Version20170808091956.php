<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170808091956 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE radars ADD model_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE radars ADD CONSTRAINT FK_7A2EA9257975B7E7 FOREIGN KEY (model_id) REFERENCES PredefinedEvent (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A2EA9257975B7E7 ON radars (model_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE radars DROP FOREIGN KEY FK_7A2EA9257975B7E7');
        $this->addSql('DROP INDEX UNIQ_7A2EA9257975B7E7 ON radars');
        $this->addSql('ALTER TABLE radars DROP model_id');
    }
}
