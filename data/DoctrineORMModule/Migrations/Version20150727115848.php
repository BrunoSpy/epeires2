<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150727115848 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reportcategories ADD help LONGTEXT NOT NULL, ADD place INT DEFAULT NULL');
        
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(1, 'Écoulement du trafic', 'trafic', 'Points notables qui ont influé sur le bon écoulement du trafic.', 1)");
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(2, 'Mises en service / expérimentations', 'meso', 'Toutes les mises en service ou expérimentations jugées d’importance.', 2)");
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(3, 'Dysfonctionnements techniques', 'pannes', 'Tous les dysfonctionnements techniques qui ont eu un impact potentiel sur la sécurité ou la régularité.', 3)");
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(4, 'Sécurité', 'securite', 'Les incidents et accidents s’étant produit sur le territoire métropolitain ou DOM, POM, COM, TOM.', 4)");
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(5, 'Sûreté', 'surete', 'Événements relatifs à la sûreté (MASA, DPSA, alerte à la bombe, vandalisme, ...', 5)");
        $this->addSql("INSERT INTO `reportcategories` (`id`, `name`, `shortname`, `help`, `place`) VALUES(6, 'Divers', 'divers', 'Tout évènement ne rentrant pas dans les autres rubriques.', 6)");
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('TRUNCATE TABLE reportcategories');
        $this->addSql('ALTER TABLE reportcategories DROP help, DROP place');
    }
}
