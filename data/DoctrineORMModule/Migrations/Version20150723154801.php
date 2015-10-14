<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Création de rapports IPO
 */
class Version20150723154801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE elements (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, category_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_444A075D71F7E88B (event_id), UNIQUE INDEX UNIQ_444A075D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reports (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, week INT NOT NULL, year INT NOT NULL, created_on DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report_element (report_id INT NOT NULL, element_id INT NOT NULL, INDEX IDX_21D923394BD2A4C0 (report_id), INDEX IDX_21D923391F1F2A24 (element_id), PRIMARY KEY(report_id, element_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reportcategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elements ADD CONSTRAINT FK_444A075D71F7E88B FOREIGN KEY (event_id) REFERENCES Event (id)');
        $this->addSql('ALTER TABLE elements ADD CONSTRAINT FK_444A075D12469DE2 FOREIGN KEY (category_id) REFERENCES reportcategories (id)');
        $this->addSql('ALTER TABLE report_element ADD CONSTRAINT FK_21D923394BD2A4C0 FOREIGN KEY (report_id) REFERENCES reports (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_element ADD CONSTRAINT FK_21D923391F1F2A24 FOREIGN KEY (element_id) REFERENCES elements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customfields CHANGE multiple multiple TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE report_element DROP FOREIGN KEY FK_21D923391F1F2A24');
        $this->addSql('ALTER TABLE report_element DROP FOREIGN KEY FK_21D923394BD2A4C0');
        $this->addSql('ALTER TABLE elements DROP FOREIGN KEY FK_444A075D12469DE2');
        $this->addSql('DROP TABLE elements');
        $this->addSql('DROP TABLE reports');
        $this->addSql('DROP TABLE report_element');
        $this->addSql('DROP TABLE reportcategories');
        $this->addSql('ALTER TABLE customfields CHANGE multiple multiple TINYINT(1) NOT NULL');
    }
}
