<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140319093259 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(32) NOT NULL, INDEX IDX_B63E2EC7727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE roles_permissions (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_CEC2E043D60322AC (role_id), INDEX IDX_CEC2E043FED90CCA (permission_id), PRIMARY KEY(role_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2DEDCC6F5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, zone_id INT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, displayName VARCHAR(50) DEFAULT NULL, password VARCHAR(128) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E99E6B1585 (organisation_id), INDEX IDX_1483A5E99F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE users_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_51498A8EA76ED395 (user_id), INDEX IDX_51498A8ED60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, fieldname_id INT DEFAULT NULL, shortname VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, compactmode TINYINT(1) NOT NULL, timeline TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_3AF34668727ACA70 (parent_id), UNIQUE INDEX UNIQ_3AF34668A260F757 (fieldname_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE roles_categories_read (category_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_B0C83D4C12469DE2 (category_id), INDEX IDX_B0C83D4CD60322AC (role_id), PRIMARY KEY(category_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, impact_id INT DEFAULT NULL, category_id INT NOT NULL, organisation_id INT NOT NULL, punctual TINYINT(1) NOT NULL, place INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_5387574A727ACA70 (parent_id), INDEX IDX_5387574AD128BC9B (impact_id), INDEX IDX_5387574A12469DE2 (category_id), INDEX IDX_5387574A9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE events_qualificationzones (abstractevent_id INT NOT NULL, qualificationzone_id INT NOT NULL, INDEX IDX_7FFBE3AFA7086F66 (abstractevent_id), INDEX IDX_7FFBE3AF8C1970A5 (qualificationzone_id), PRIMARY KEY(abstractevent_id, qualificationzone_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE antennas (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, INDEX IDX_4FD1E3649E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE AntennaCategory (id INT NOT NULL, statefield_id INT DEFAULT NULL, antennafield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_35A5CC35761F9C6B (statefield_id), UNIQUE INDEX UNIQ_35A5CC3566D8E8C (antennafield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE customfields (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, place INT NOT NULL, defaultvalue LONGTEXT NOT NULL, INDEX IDX_37D0F77D12469DE2 (category_id), INDEX IDX_37D0F77DC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE customfieldtypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE customfieldvalues (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, customfield_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_C78CFA5371F7E88B (event_id), INDEX IDX_C78CFA5337268F00 (customfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Event (id INT NOT NULL, status_id INT DEFAULT NULL, author_id INT DEFAULT NULL, startdate DATETIME DEFAULT NULL, enddate DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, last_modified_on DATETIME NOT NULL, star TINYINT(1) NOT NULL, INDEX IDX_FA6F25A36BF700BD (status_id), INDEX IDX_FA6F25A3F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE eventupdates (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, created_on DATETIME NOT NULL, INDEX IDX_8EE283E171F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE files (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, size NUMERIC(10, 0) NOT NULL, name VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6354059B548B0F (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE file_event (file_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_912DBA9093CB796C (file_id), INDEX IDX_912DBA9071F7E88B (event_id), PRIMARY KEY(file_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE frequencies (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, mainantenna_id INT DEFAULT NULL, backupantenna_id INT DEFAULT NULL, mainantennaclimax_id INT DEFAULT NULL, backupantennaclimax_id INT DEFAULT NULL, defaultsector_id INT DEFAULT NULL, value NUMERIC(6, 3) NOT NULL, othername VARCHAR(255) NOT NULL, INDEX IDX_282C52B89E6B1585 (organisation_id), INDEX IDX_282C52B8DBD68CDB (mainantenna_id), INDEX IDX_282C52B8A7A4F4B0 (backupantenna_id), INDEX IDX_282C52B8F94FA602 (mainantennaclimax_id), INDEX IDX_282C52B8CE030BB6 (backupantennaclimax_id), UNIQUE INDEX UNIQ_282C52B8F611BD49 (defaultsector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE FrequencyCategory (id INT NOT NULL, statefield_id INT DEFAULT NULL, currentcovfield_id INT DEFAULT NULL, frequencyfield_id INT DEFAULT NULL, otherfrequencyfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_84C21BD2761F9C6B (statefield_id), UNIQUE INDEX UNIQ_84C21BD29C0145A4 (currentcovfield_id), UNIQUE INDEX UNIQ_84C21BD2116C550F (frequencyfield_id), UNIQUE INDEX UNIQ_84C21BD2FFB00C8A (otherfrequencyfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE impact (id INT AUTO_INCREMENT NOT NULL, value INT NOT NULL, name VARCHAR(255) NOT NULL, style VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C409C0071D775834 (value), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ipos (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, current TINYINT(1) NOT NULL, INDEX IDX_18F2582B9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opsups (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, current TINYINT(1) NOT NULL, INDEX IDX_7C31005A9E6B1585 (organisation_id), INDEX IDX_7C31005A9F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE organisations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D7E459AC5E237E06 (name), UNIQUE INDEX UNIQ_D7E459AC64082763 (shortname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE PredefinedEvent (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, listable TINYINT(1) NOT NULL, searchable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE qualifzones (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B7CF1145E237E06 (name), UNIQUE INDEX UNIQ_B7CF11464082763 (shortname), INDEX IDX_B7CF1149E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE radars (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(255) NOT NULL, shortname VARCHAR(255) NOT NULL, INDEX IDX_7A2EA9259E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE RadarCategory (id INT NOT NULL, statefield_id INT DEFAULT NULL, radarfield_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_4F302186761F9C6B (statefield_id), UNIQUE INDEX UNIQ_4F30218647431654 (radarfield_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE sectors (id INT AUTO_INCREMENT NOT NULL, zone_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_B59406989F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE sectors_groups (sector_id INT NOT NULL, sectorgroup_id INT NOT NULL, INDEX IDX_D0A3ACDDE95C867 (sector_id), INDEX IDX_D0A3ACD2091AB1D (sectorgroup_id), PRIMARY KEY(sector_id, sectorgroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE sectorgroups (id INT AUTO_INCREMENT NOT NULL, zone_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, display TINYINT(1) NOT NULL, position INT NOT NULL, INDEX IDX_AD8E697D9F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE stacks (id INT AUTO_INCREMENT NOT NULL, zone_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_B628EF369F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, open TINYINT(1) NOT NULL, display TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, defaut TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7B00651C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE roles ADD CONSTRAINT FK_B63E2EC7727ACA70 FOREIGN KEY (parent_id) REFERENCES roles (id)");
        $this->addSql("ALTER TABLE roles_permissions ADD CONSTRAINT FK_CEC2E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)");
        $this->addSql("ALTER TABLE roles_permissions ADD CONSTRAINT FK_CEC2E043FED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id)");
        $this->addSql("ALTER TABLE users ADD CONSTRAINT FK_1483A5E99E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE users ADD CONSTRAINT FK_1483A5E99F2C3FAB FOREIGN KEY (zone_id) REFERENCES qualifzones (id)");
        $this->addSql("ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->addSql("ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id)");
        $this->addSql("ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id)");
        $this->addSql("ALTER TABLE categories ADD CONSTRAINT FK_3AF34668A260F757 FOREIGN KEY (fieldname_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE roles_categories_read ADD CONSTRAINT FK_B0C83D4C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE roles_categories_read ADD CONSTRAINT FK_B0C83D4CD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE events ADD CONSTRAINT FK_5387574A727ACA70 FOREIGN KEY (parent_id) REFERENCES events (id)");
        $this->addSql("ALTER TABLE events ADD CONSTRAINT FK_5387574AD128BC9B FOREIGN KEY (impact_id) REFERENCES impact (id)");
        $this->addSql("ALTER TABLE events ADD CONSTRAINT FK_5387574A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)");
        $this->addSql("ALTER TABLE events ADD CONSTRAINT FK_5387574A9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE events_qualificationzones ADD CONSTRAINT FK_7FFBE3AFA7086F66 FOREIGN KEY (abstractevent_id) REFERENCES events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE events_qualificationzones ADD CONSTRAINT FK_7FFBE3AF8C1970A5 FOREIGN KEY (qualificationzone_id) REFERENCES qualifzones (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE antennas ADD CONSTRAINT FK_4FD1E3649E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE AntennaCategory ADD CONSTRAINT FK_35A5CC35761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE AntennaCategory ADD CONSTRAINT FK_35A5CC3566D8E8C FOREIGN KEY (antennafield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE AntennaCategory ADD CONSTRAINT FK_35A5CC35BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE customfields ADD CONSTRAINT FK_37D0F77D12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)");
        $this->addSql("ALTER TABLE customfields ADD CONSTRAINT FK_37D0F77DC54C8C93 FOREIGN KEY (type_id) REFERENCES customfieldtypes (id)");
        $this->addSql("ALTER TABLE customfieldvalues ADD CONSTRAINT FK_C78CFA5371F7E88B FOREIGN KEY (event_id) REFERENCES events (id)");
        $this->addSql("ALTER TABLE customfieldvalues ADD CONSTRAINT FK_C78CFA5337268F00 FOREIGN KEY (customfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE Event ADD CONSTRAINT FK_FA6F25A36BF700BD FOREIGN KEY (status_id) REFERENCES status (id)");
        $this->addSql("ALTER TABLE Event ADD CONSTRAINT FK_FA6F25A3F675F31B FOREIGN KEY (author_id) REFERENCES users (id)");
        $this->addSql("ALTER TABLE Event ADD CONSTRAINT FK_FA6F25A3BF396750 FOREIGN KEY (id) REFERENCES events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE eventupdates ADD CONSTRAINT FK_8EE283E171F7E88B FOREIGN KEY (event_id) REFERENCES Event (id)");
        $this->addSql("ALTER TABLE file_event ADD CONSTRAINT FK_912DBA9093CB796C FOREIGN KEY (file_id) REFERENCES files (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE file_event ADD CONSTRAINT FK_912DBA9071F7E88B FOREIGN KEY (event_id) REFERENCES Event (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B89E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B8DBD68CDB FOREIGN KEY (mainantenna_id) REFERENCES antennas (id)");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B8A7A4F4B0 FOREIGN KEY (backupantenna_id) REFERENCES antennas (id)");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B8F94FA602 FOREIGN KEY (mainantennaclimax_id) REFERENCES antennas (id)");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B8CE030BB6 FOREIGN KEY (backupantennaclimax_id) REFERENCES antennas (id)");
        $this->addSql("ALTER TABLE frequencies ADD CONSTRAINT FK_282C52B8F611BD49 FOREIGN KEY (defaultsector_id) REFERENCES sectors (id)");
        $this->addSql("ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD2761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD29C0145A4 FOREIGN KEY (currentcovfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD2116C550F FOREIGN KEY (frequencyfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD2FFB00C8A FOREIGN KEY (otherfrequencyfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD2BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE ipos ADD CONSTRAINT FK_18F2582B9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE opsups ADD CONSTRAINT FK_7C31005A9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE opsups ADD CONSTRAINT FK_7C31005A9F2C3FAB FOREIGN KEY (zone_id) REFERENCES qualifzones (id)");
        $this->addSql("ALTER TABLE PredefinedEvent ADD CONSTRAINT FK_D4BE998CBF396750 FOREIGN KEY (id) REFERENCES events (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE qualifzones ADD CONSTRAINT FK_B7CF1149E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE radars ADD CONSTRAINT FK_7A2EA9259E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisations (id)");
        $this->addSql("ALTER TABLE RadarCategory ADD CONSTRAINT FK_4F302186761F9C6B FOREIGN KEY (statefield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE RadarCategory ADD CONSTRAINT FK_4F30218647431654 FOREIGN KEY (radarfield_id) REFERENCES customfields (id)");
        $this->addSql("ALTER TABLE RadarCategory ADD CONSTRAINT FK_4F302186BF396750 FOREIGN KEY (id) REFERENCES categories (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE sectors ADD CONSTRAINT FK_B59406989F2C3FAB FOREIGN KEY (zone_id) REFERENCES qualifzones (id)");
        $this->addSql("ALTER TABLE sectors_groups ADD CONSTRAINT FK_D0A3ACDDE95C867 FOREIGN KEY (sector_id) REFERENCES sectors (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE sectors_groups ADD CONSTRAINT FK_D0A3ACD2091AB1D FOREIGN KEY (sectorgroup_id) REFERENCES sectorgroups (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE sectorgroups ADD CONSTRAINT FK_AD8E697D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES qualifzones (id)");
        $this->addSql("ALTER TABLE stacks ADD CONSTRAINT FK_B628EF369F2C3FAB FOREIGN KEY (zone_id) REFERENCES qualifzones (id)");

        $this->addSql("INSERT INTO `customfieldtypes` VALUES(1, 'Texte', 'string')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(2, 'Texte long', 'text')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(3, 'Secteur', 'sector')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(4, 'Antenne', 'antenna')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(5, 'Fréquence', 'frequency')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(6, 'Radar', 'radar')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(7, 'Liste', 'select')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(8, 'Attente', 'stack')");
        $this->addSql("INSERT INTO `customfieldtypes` VALUES(9, 'Vrai/Faux', 'boolean')");

        $this->addSql("INSERT INTO `impact` VALUES(1, 100, 'Majeur', 'important')");
        $this->addSql("INSERT INTO `impact` VALUES(2, 80, 'Significatif', 'warning')");
        $this->addSql("INSERT INTO `impact` VALUES(3, 60, 'Mineur', 'info')");
        $this->addSql("INSERT INTO `impact` VALUES(4, 40, 'Sans impact', 'success')");
        $this->addSql("INSERT INTO `impact` VALUES(5, 10, 'Information', 'default')");

        $this->addSql("INSERT INTO `organisations` VALUES(1, 'CRNA-X', 'LFXX', '')");

        $this->addSql("INSERT INTO `permissions` VALUES(1, 'events.create')");
        $this->addSql("INSERT INTO `permissions` VALUES(4, 'events.mod-files')");
        $this->addSql("INSERT INTO `permissions` VALUES(5, 'events.mod-ipo')");
        $this->addSql("INSERT INTO `permissions` VALUES(6, 'events.mod-opsup')");
        $this->addSql("INSERT INTO `permissions` VALUES(3, 'events.status')");
        $this->addSql("INSERT INTO `permissions` VALUES(2, 'events.write')");
        $this->addSql("INSERT INTO `permissions` VALUES(7, 'frequencies.read')");

        $this->addSql("INSERT INTO `roles` VALUES(1, NULL, 'admin')");
        $this->addSql("INSERT INTO `roles` VALUES(2, 1, 'guest')");

        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 1)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 2)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 3)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 4)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 5)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 6)");
        $this->addSql("INSERT INTO `roles_permissions` VALUES(1, 7)");

        $this->addSql("INSERT INTO `status` VALUES(1, 1, 1, 'Nouveau', 1)");
        $this->addSql("INSERT INTO `status` VALUES(2, 1, 1, 'Confirmé', 0)");
        $this->addSql("INSERT INTO `status` VALUES(3, 0, 1, 'Terminé', 1)");
        $this->addSql("INSERT INTO `status` VALUES(4, 0, 1, 'Annulé', 0)");
        $this->addSql("INSERT INTO `status` VALUES(5, 0, 0, 'Archivé', 0)");

        $this->addSql("INSERT INTO `users` VALUES(1, 1, NULL, 'Admin', 'change@email', 'Admin', '$2y$14\$h1YYvsax.2cnrG4oJA8qkutz4/YwqQTr/kaO4NwJZplY6tjZjxQH6')");

        $this->addSql("INSERT INTO `users_roles` VALUES(1, 1)");
    }
    
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE roles DROP FOREIGN KEY FK_B63E2EC7727ACA70");
        $this->addSql("ALTER TABLE roles_permissions DROP FOREIGN KEY FK_CEC2E043D60322AC");
        $this->addSql("ALTER TABLE users_roles DROP FOREIGN KEY FK_51498A8ED60322AC");
        $this->addSql("ALTER TABLE roles_categories_read DROP FOREIGN KEY FK_B0C83D4CD60322AC");
        $this->addSql("ALTER TABLE roles_permissions DROP FOREIGN KEY FK_CEC2E043FED90CCA");
        $this->addSql("ALTER TABLE users_roles DROP FOREIGN KEY FK_51498A8EA76ED395");
        $this->addSql("ALTER TABLE Event DROP FOREIGN KEY FK_FA6F25A3F675F31B");
        $this->addSql("ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70");
        $this->addSql("ALTER TABLE roles_categories_read DROP FOREIGN KEY FK_B0C83D4C12469DE2");
        $this->addSql("ALTER TABLE events DROP FOREIGN KEY FK_5387574A12469DE2");
        $this->addSql("ALTER TABLE AntennaCategory DROP FOREIGN KEY FK_35A5CC35BF396750");
        $this->addSql("ALTER TABLE customfields DROP FOREIGN KEY FK_37D0F77D12469DE2");
        $this->addSql("ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD2BF396750");
        $this->addSql("ALTER TABLE RadarCategory DROP FOREIGN KEY FK_4F302186BF396750");
        $this->addSql("ALTER TABLE events DROP FOREIGN KEY FK_5387574A727ACA70");
        $this->addSql("ALTER TABLE events_qualificationzones DROP FOREIGN KEY FK_7FFBE3AFA7086F66");
        $this->addSql("ALTER TABLE customfieldvalues DROP FOREIGN KEY FK_C78CFA5371F7E88B");
        $this->addSql("ALTER TABLE Event DROP FOREIGN KEY FK_FA6F25A3BF396750");
        $this->addSql("ALTER TABLE PredefinedEvent DROP FOREIGN KEY FK_D4BE998CBF396750");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B8DBD68CDB");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B8A7A4F4B0");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B8F94FA602");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B8CE030BB6");
        $this->addSql("ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668A260F757");
        $this->addSql("ALTER TABLE AntennaCategory DROP FOREIGN KEY FK_35A5CC35761F9C6B");
        $this->addSql("ALTER TABLE AntennaCategory DROP FOREIGN KEY FK_35A5CC3566D8E8C");
        $this->addSql("ALTER TABLE customfieldvalues DROP FOREIGN KEY FK_C78CFA5337268F00");
        $this->addSql("ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD2761F9C6B");
        $this->addSql("ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD29C0145A4");
        $this->addSql("ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD2116C550F");
        $this->addSql("ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD2FFB00C8A");
        $this->addSql("ALTER TABLE RadarCategory DROP FOREIGN KEY FK_4F302186761F9C6B");
        $this->addSql("ALTER TABLE RadarCategory DROP FOREIGN KEY FK_4F30218647431654");
        $this->addSql("ALTER TABLE customfields DROP FOREIGN KEY FK_37D0F77DC54C8C93");
        $this->addSql("ALTER TABLE eventupdates DROP FOREIGN KEY FK_8EE283E171F7E88B");
        $this->addSql("ALTER TABLE file_event DROP FOREIGN KEY FK_912DBA9071F7E88B");
        $this->addSql("ALTER TABLE file_event DROP FOREIGN KEY FK_912DBA9093CB796C");
        $this->addSql("ALTER TABLE events DROP FOREIGN KEY FK_5387574AD128BC9B");
        $this->addSql("ALTER TABLE users DROP FOREIGN KEY FK_1483A5E99E6B1585");
        $this->addSql("ALTER TABLE events DROP FOREIGN KEY FK_5387574A9E6B1585");
        $this->addSql("ALTER TABLE antennas DROP FOREIGN KEY FK_4FD1E3649E6B1585");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B89E6B1585");
        $this->addSql("ALTER TABLE ipos DROP FOREIGN KEY FK_18F2582B9E6B1585");
        $this->addSql("ALTER TABLE opsups DROP FOREIGN KEY FK_7C31005A9E6B1585");
        $this->addSql("ALTER TABLE qualifzones DROP FOREIGN KEY FK_B7CF1149E6B1585");
        $this->addSql("ALTER TABLE radars DROP FOREIGN KEY FK_7A2EA9259E6B1585");
        $this->addSql("ALTER TABLE users DROP FOREIGN KEY FK_1483A5E99F2C3FAB");
        $this->addSql("ALTER TABLE events_qualificationzones DROP FOREIGN KEY FK_7FFBE3AF8C1970A5");
        $this->addSql("ALTER TABLE opsups DROP FOREIGN KEY FK_7C31005A9F2C3FAB");
        $this->addSql("ALTER TABLE sectors DROP FOREIGN KEY FK_B59406989F2C3FAB");
        $this->addSql("ALTER TABLE sectorgroups DROP FOREIGN KEY FK_AD8E697D9F2C3FAB");
        $this->addSql("ALTER TABLE stacks DROP FOREIGN KEY FK_B628EF369F2C3FAB");
        $this->addSql("ALTER TABLE frequencies DROP FOREIGN KEY FK_282C52B8F611BD49");
        $this->addSql("ALTER TABLE sectors_groups DROP FOREIGN KEY FK_D0A3ACDDE95C867");
        $this->addSql("ALTER TABLE sectors_groups DROP FOREIGN KEY FK_D0A3ACD2091AB1D");
        $this->addSql("ALTER TABLE Event DROP FOREIGN KEY FK_FA6F25A36BF700BD");
        $this->addSql("DROP TABLE roles");
        $this->addSql("DROP TABLE roles_permissions");
        $this->addSql("DROP TABLE permissions");
        $this->addSql("DROP TABLE users");
        $this->addSql("DROP TABLE users_roles");
        $this->addSql("DROP TABLE categories");
        $this->addSql("DROP TABLE roles_categories_read");
        $this->addSql("DROP TABLE events");
        $this->addSql("DROP TABLE events_qualificationzones");
        $this->addSql("DROP TABLE antennas");
        $this->addSql("DROP TABLE AntennaCategory");
        $this->addSql("DROP TABLE customfields");
        $this->addSql("DROP TABLE customfieldtypes");
        $this->addSql("DROP TABLE customfieldvalues");
        $this->addSql("DROP TABLE Event");
        $this->addSql("DROP TABLE eventupdates");
        $this->addSql("DROP TABLE files");
        $this->addSql("DROP TABLE file_event");
        $this->addSql("DROP TABLE frequencies");
        $this->addSql("DROP TABLE FrequencyCategory");
        $this->addSql("DROP TABLE impact");
        $this->addSql("DROP TABLE ipos");
        $this->addSql("DROP TABLE log");
        $this->addSql("DROP TABLE opsups");
        $this->addSql("DROP TABLE organisations");
        $this->addSql("DROP TABLE PredefinedEvent");
        $this->addSql("DROP TABLE qualifzones");
        $this->addSql("DROP TABLE radars");
        $this->addSql("DROP TABLE RadarCategory");
        $this->addSql("DROP TABLE sectors");
        $this->addSql("DROP TABLE sectors_groups");
        $this->addSql("DROP TABLE sectorgroups");
        $this->addSql("DROP TABLE stacks");
        $this->addSql("DROP TABLE status");
    }
}
