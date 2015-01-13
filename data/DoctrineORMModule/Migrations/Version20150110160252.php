<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150110160252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customfields CHANGE visible multiple TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE AntennaCategory ADD frequencies_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE AntennaCategory ADD CONSTRAINT FK_35A5CC3517593985 FOREIGN KEY (frequencies_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_35A5CC3517593985 ON AntennaCategory (frequencies_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE AntennaCategory DROP FOREIGN KEY FK_35A5CC3517593985');
        $this->addSql('DROP INDEX UNIQ_35A5CC3517593985 ON AntennaCategory');
        $this->addSql('ALTER TABLE AntennaCategory DROP frequencies_id');
        $this->addSql('ALTER TABLE customfields CHANGE multiple visible TINYINT(1) NOT NULL');
    }
    
    public function postUp(Schema $schema) {
        $stmt = $this->connection->executeQuery("SELECT * FROM AntennaCategory");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat){
            $this->connection->insert('customfields', 
                        array('category_id' => $cat['id'],
                            'type_id' => 5,
                            'name' => "Fréquences impactées",
                            'place' => 3,
                            'defaultvalue' => "",
                            'multiple' => true,
                            'tooltip' => ""));
            $fieldid = $this->connection->lastInsertId();
            $this->connection->update('AntennaCategory', array('frequencies_id' => $fieldid), array('id' => $cat['id']));
        }
    }
}
