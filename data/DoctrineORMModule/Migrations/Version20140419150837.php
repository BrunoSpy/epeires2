<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


class Version20140419150837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE BrouillageCategory (id INT NOT NULL, frequencyfield_id INT DEFAULT NULL, levelfield_id INT DEFAULT NULL, rnavfield_id INT DEFAULT NULL, distancefield_id INT DEFAULT NULL, azimutfield_id INT DEFAULT NULL, originfield_id INT DEFAULT NULL, typefield_id INT DEFAULT NULL, causebrouillagefield_id INT DEFAULT NULL, commentairebrouillagefield_id INT DEFAULT NULL, causeinterferencefield_id INT DEFAULT NULL, commentaireinterferencefield_id INT DEFAULT NULL, defaultbrouillagecategory TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_5FAD0021116C550F (frequencyfield_id), UNIQUE INDEX UNIQ_5FAD002191D7C3D2 (levelfield_id), UNIQUE INDEX UNIQ_5FAD00212D8F69F2 (rnavfield_id), UNIQUE INDEX UNIQ_5FAD002165A0664D (distancefield_id), UNIQUE INDEX UNIQ_5FAD0021F2438440 (azimutfield_id), UNIQUE INDEX UNIQ_5FAD00218508F3A0 (originfield_id), UNIQUE INDEX UNIQ_5FAD0021185E8938 (typefield_id), UNIQUE INDEX UNIQ_5FAD002118ED2AFA (causebrouillagefield_id), UNIQUE INDEX UNIQ_5FAD0021B7C0B311 (commentairebrouillagefield_id), UNIQUE INDEX UNIQ_5FAD0021175C2EB3 (causeinterferencefield_id), UNIQUE INDEX UNIQ_5FAD002123058059 (commentaireinterferencefield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021116C550F FOREIGN KEY (frequencyfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002191D7C3D2 FOREIGN KEY (levelfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD00212D8F69F2 FOREIGN KEY (rnavfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002165A0664D FOREIGN KEY (distancefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021F2438440 FOREIGN KEY (azimutfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD00218508F3A0 FOREIGN KEY (originfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021185E8938 FOREIGN KEY (typefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002118ED2AFA FOREIGN KEY (causebrouillagefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021B7C0B311 FOREIGN KEY (commentairebrouillagefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021175C2EB3 FOREIGN KEY (causeinterferencefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD002123058059 FOREIGN KEY (commentaireinterferencefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE BrouillageCategory ADD CONSTRAINT FK_5FAD0021BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE BrouillageCategory");
    }
}
