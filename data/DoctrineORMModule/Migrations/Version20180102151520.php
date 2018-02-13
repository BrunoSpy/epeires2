<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180102151520 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ATFCMCategory (id INT NOT NULL, tvs VARCHAR(255) NOT NULL, reasonField_id INT DEFAULT NULL, internalId_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_AEA7407E717FCCB3 (reasonField_id), UNIQUE INDEX UNIQ_AEA7407EF205083 (internalId_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407E717FCCB3 FOREIGN KEY (reasonField_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407EF205083 FOREIGN KEY (internalId_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE ATFCMCategory ADD CONSTRAINT FK_AEA7407EBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customfields ADD hidden TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ATFCMCategory');
        $this->addSql('ALTER TABLE customfields DROP hidden');
    }
}
