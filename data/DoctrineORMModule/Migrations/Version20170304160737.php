<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170304160737 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE field DROP FOREIGN KEY FK_5BF54558BE5A87C9');
        $this->addSql('CREATE TABLE InterrogationPlanCategory (id INT NOT NULL, typefield_id INT DEFAULT NULL, alertfield_id INT DEFAULT NULL, latfield_id INT DEFAULT NULL, longfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3ADF038C185E8938 (typefield_id), UNIQUE INDEX UNIQ_3ADF038CAE2C410F (alertfield_id), UNIQUE INDEX UNIQ_3ADF038C8336905D (latfield_id), UNIQUE INDEX UNIQ_3ADF038C4398856F (longfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE InterrogationPlanCategory ADD CONSTRAINT FK_3ADF038C185E8938 FOREIGN KEY (typefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE InterrogationPlanCategory ADD CONSTRAINT FK_3ADF038CAE2C410F FOREIGN KEY (alertfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE InterrogationPlanCategory ADD CONSTRAINT FK_3ADF038C8336905D FOREIGN KEY (latfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE InterrogationPlanCategory ADD CONSTRAINT FK_3ADF038C4398856F FOREIGN KEY (longfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE InterrogationPlanCategory ADD CONSTRAINT FK_3ADF038CBF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE field');
        $this->addSql('DROP TABLE interplan');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE field (id INT AUTO_INCREMENT NOT NULL, interplan_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, comment VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, intTime DATETIME DEFAULT NULL, INDEX IDX_5BF54558BE5A87C9 (interplan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE interplan (id INT AUTO_INCREMENT NOT NULL, startTime DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, typeAlerte VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, firSource VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, firDest VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, comment VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF54558BE5A87C9 FOREIGN KEY (interplan_id) REFERENCES interplan (id)');
        $this->addSql('DROP TABLE InterrogationPlanCategory');
    }
}
