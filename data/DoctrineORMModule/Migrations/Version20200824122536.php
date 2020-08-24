<?php

declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824122536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_7A2EA925727ACA70');
        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_7A2EA9257975B7E7');
        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_7A2EA9259E6B1585');
        $this->addSql('DROP INDEX idx_7a2ea9259e6b1585 ON switchobjects');
        $this->addSql('CREATE INDEX IDX_C535A60E9E6B1585 ON switchobjects (organisation_id)');
        $this->addSql('DROP INDEX uniq_7a2ea9257975b7e7 ON switchobjects');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C535A60E7975B7E7 ON switchobjects (model_id)');
        $this->addSql('DROP INDEX idx_7a2ea925727aca70 ON switchobjects');
        $this->addSql('CREATE INDEX IDX_C535A60E727ACA70 ON switchobjects (parent_id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_7A2EA925727ACA70 FOREIGN KEY (parent_id) REFERENCES switchobjects (id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_7A2EA9257975B7E7 FOREIGN KEY (model_id) REFERENCES PredefinedEvent (id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_7A2EA9259E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_C535A60E9E6B1585');
        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_C535A60E7975B7E7');
        $this->addSql('ALTER TABLE switchobjects DROP FOREIGN KEY FK_C535A60E727ACA70');
        $this->addSql('DROP INDEX uniq_c535a60e7975b7e7 ON switchobjects');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A2EA9257975B7E7 ON switchobjects (model_id)');
        $this->addSql('DROP INDEX idx_c535a60e9e6b1585 ON switchobjects');
        $this->addSql('CREATE INDEX IDX_7A2EA9259E6B1585 ON switchobjects (organisation_id)');
        $this->addSql('DROP INDEX idx_c535a60e727aca70 ON switchobjects');
        $this->addSql('CREATE INDEX IDX_7A2EA925727ACA70 ON switchobjects (parent_id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_C535A60E9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_C535A60E7975B7E7 FOREIGN KEY (model_id) REFERENCES PredefinedEvent (id)');
        $this->addSql('ALTER TABLE switchobjects ADD CONSTRAINT FK_C535A60E727ACA70 FOREIGN KEY (parent_id) REFERENCES switchobjects (id)');
    }
}
