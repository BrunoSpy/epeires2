<?php declare(strict_types=1);

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190820142540 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD0021175C2EB3');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD0021185E8938');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD002118ED2AFA');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD002123058059');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD00212D8F69F2');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD002165A0664D');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD00218508F3A0');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD002191D7C3D2');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD0021B7C0B311');
        $this->addSql('ALTER TABLE BrouillageCategory DROP FOREIGN KEY FK_5FAD0021F2438440');
        $this->addSql('DROP INDEX UNIQ_5FAD002118ED2AFA ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD002191D7C3D2 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD0021185E8938 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD00218508F3A0 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD002123058059 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD0021F2438440 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD0021175C2EB3 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD002165A0664D ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD0021B7C0B311 ON BrouillageCategory');
        $this->addSql('DROP INDEX UNIQ_5FAD00212D8F69F2 ON BrouillageCategory');
        $this->addSql('ALTER TABLE BrouillageCategory DROP levelfield_id, DROP rnavfield_id, DROP distancefield_id, DROP azimutfield_id, DROP originfield_id, DROP typefield_id, DROP causebrouillagefield_id, DROP commentairebrouillagefield_id, DROP causeinterferencefield_id, DROP commentaireinterferencefield_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BrouillageCategory ADD levelfield_id INT DEFAULT NULL, ADD rnavfield_id INT DEFAULT NULL, ADD distancefield_id INT DEFAULT NULL, ADD azimutfield_id INT DEFAULT NULL, ADD originfield_id INT DEFAULT NULL, ADD typefield_id INT DEFAULT NULL, ADD causebrouillagefield_id INT DEFAULT NULL, ADD commentairebrouillagefield_id INT DEFAULT NULL, ADD causeinterferencefield_id INT DEFAULT NULL, ADD commentaireinterferencefield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021175C2EB3 FOREIGN KEY (causeinterferencefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021185E8938 FOREIGN KEY (typefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002118ED2AFA FOREIGN KEY (causebrouillagefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002123058059 FOREIGN KEY (commentaireinterferencefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD00212D8F69F2 FOREIGN KEY (rnavfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002165A0664D FOREIGN KEY (distancefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD00218508F3A0 FOREIGN KEY (originfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002191D7C3D2 FOREIGN KEY (levelfield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021B7C0B311 FOREIGN KEY (commentairebrouillagefield_id) REFERENCES customfields (id)');
        $this->addSql('ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021F2438440 FOREIGN KEY (azimutfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD002118ED2AFA ON BrouillageCategory (causebrouillagefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD002191D7C3D2 ON BrouillageCategory (levelfield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD0021185E8938 ON BrouillageCategory (typefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD00218508F3A0 ON BrouillageCategory (originfield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD002123058059 ON BrouillageCategory (commentaireinterferencefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD0021F2438440 ON BrouillageCategory (azimutfield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD0021175C2EB3 ON BrouillageCategory (causeinterferencefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD002165A0664D ON BrouillageCategory (distancefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD0021B7C0B311 ON BrouillageCategory (commentairebrouillagefield_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FAD00212D8F69F2 ON BrouillageCategory (rnavfield_id)');
    }
}
