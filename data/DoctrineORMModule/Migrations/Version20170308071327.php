<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170308071327 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE FieldCategory (id INT NOT NULL, namefield_id INT DEFAULT NULL, codefield_id INT DEFAULT NULL, latfield_id INT DEFAULT NULL, longfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_D628F4BF3814A373 (namefield_id), UNIQUE INDEX UNIQ_D628F4BF8D8A251 (codefield_id), UNIQUE INDEX UNIQ_D628F4BF8336905D (latfield_id), UNIQUE INDEX UNIQ_D628F4BF4398856F (longfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE FieldCategory ADD CONSTRAINT FK_D628F4BF3814A373 FOREIGN KEY (namefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FieldCategory ADD CONSTRAINT FK_D628F4BF8D8A251 FOREIGN KEY (codefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FieldCategory ADD CONSTRAINT FK_D628F4BF8336905D FOREIGN KEY (latfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FieldCategory ADD CONSTRAINT FK_D628F4BF4398856F FOREIGN KEY (longfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE FieldCategory ADD CONSTRAINT FK_D628F4BFBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE FieldCategory');
    }
}
