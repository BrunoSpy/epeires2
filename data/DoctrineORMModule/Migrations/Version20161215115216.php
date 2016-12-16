<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161215115216 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE frequency_frequency (frequency_source INT NOT NULL, frequency_target INT NOT NULL, INDEX IDX_8DDBCEFAF8CD0847 (frequency_source), INDEX IDX_8DDBCEFAE12858C8 (frequency_target), PRIMARY KEY(frequency_source, frequency_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE frequency_frequency ADD CONSTRAINT FK_8DDBCEFAF8CD0847 FOREIGN KEY (frequency_source) REFERENCES frequencies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE frequency_frequency ADD CONSTRAINT FK_8DDBCEFAE12858C8 FOREIGN KEY (frequency_target) REFERENCES frequencies (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE frequency_frequency');
    }
}
