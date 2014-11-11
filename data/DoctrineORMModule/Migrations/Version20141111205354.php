<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141111205354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE MilCategory (id INT NOT NULL, zonesRegex VARCHAR(255) NOT NULL, nmB2B TINYINT(1) NOT NULL, lastUpdateDate DATETIME DEFAULT NULL, lastUpdateSequence INT DEFAULT NULL, onMilPage TINYINT(1) NOT NULL, upperLevelField_id INT DEFAULT NULL, lowerLevelField_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7BE61CF05F4867FB (upperLevelField_id), UNIQUE INDEX UNIQ_7BE61CF0A5C271D3 (lowerLevelField_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE MilCategory ADD CONSTRAINT FK_7BE61CF05F4867FB FOREIGN KEY (upperLevelField_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE MilCategory ADD CONSTRAINT FK_7BE61CF0A5C271D3 FOREIGN KEY (lowerLevelField_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE MilCategory ADD CONSTRAINT FK_7BE61CF0BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customfields ADD visible TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP TABLE MilCategory');
        $this->addSql('ALTER TABLE customfields DROP visible');
    }
}
