<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151221090315 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE FrequencyCategory ADD causefield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE FrequencyCategory ADD CONSTRAINT FK_84C21BD2AA500C6D FOREIGN KEY (causefield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84C21BD2AA500C6D ON FrequencyCategory (causefield_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE FrequencyCategory DROP FOREIGN KEY FK_84C21BD2AA500C6D');
        $this->addSql('DROP INDEX UNIQ_84C21BD2AA500C6D ON FrequencyCategory');
        $this->addSql('ALTER TABLE FrequencyCategory DROP causefield_id');
    }
    
    public function postUp(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT * FROM FrequencyCategory");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat) {
            $this->connection->insert('customfields', array(
                'category_id' => $cat['id'],
                'type_id' => 2,
                'name' => "Cause",
                'place' => 5,
                'defaultvalue' => "",
                'multiple' => false,
                'tooltip' => ""
            ));
            $fieldid = $this->connection->lastInsertId();
            $this->connection->update('FrequencyCategory', array(
                'causefield_id' => $fieldid
            ), array(
                'id' => $cat['id']
            ));
        }    
    }
}
