<?php
namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ajout champ couleur pour les actions
 */
class Version20151022111357 extends AbstractMigration
{

    /**
     *
     * @param Schema $schema            
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()
            ->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE ActionCategory ADD colorfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ActionCategory ADD CONSTRAINT FK_9CB46AD363E96BE3 FOREIGN KEY (colorfield_id) REFERENCES customfields (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9CB46AD363E96BE3 ON ActionCategory (colorfield_id)');
    }

    /**
     *
     * @param Schema $schema            
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()
            ->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE ActionCategory DROP FOREIGN KEY FK_9CB46AD363E96BE3');
        $this->addSql('DROP INDEX UNIQ_9CB46AD363E96BE3 ON ActionCategory');
        $this->addSql('ALTER TABLE ActionCategory DROP colorfield_id');
    }

    /**
     * Add color field for actions
     *
     * @param Schema $schema            
     */
    public function postUp(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SELECT * FROM ActionCategory");
        $categories = $stmt->fetchAll();
        foreach ($categories as $cat) {
            $this->connection->insert('customfields', array(
                'category_id' => $cat['id'],
                'type_id' => 1,
                'name' => "Couleur",
                'place' => 3,
                'defaultvalue' => "",
                'multiple' => 0,
                'tooltip' => ""
            ));
            $fieldid = $this->connection->lastInsertId();
            $this->connection->update('ActionCategory', array(
                'colorfield_id' => $fieldid
            ), array(
                'id' => $cat['id']
            ));
        }
    }
}
