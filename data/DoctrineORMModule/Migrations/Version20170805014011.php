<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170805014011 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tabs ADD isDefault TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE tabs DROP isDefault');
    }
    
    public function postUp(Schema $schema)
    {
        //create the tab corresponding to the default timeline
        $this->connection->insert('tabs', array('name' => 'Timeline principale', 'shortname' => "Timeline", 'place' => '0', 'onlyroot' => 1, 'isDefault' => 1));
        $tabid = $this->connection->lastInsertId();
        $stmt = $this->connection->executeQuery("SELECT * FROM categories WHERE `timeline` = 1");
        $roles = array();
        //add categories to the tab
        foreach ($stmt->fetchAll() as $cat){
            $catid = $cat["id"];
            $this->connection->insert('tab_category', array(
               'tab_id' => $tabid,
               'category_id' => $catid
            ));
            //add the roles from each category _only once_
            $stmt2 = $this->connection->executeQuery("SELECT * FROM roles_categories_read WHERE `category_id` = $catid");
            foreach ($stmt2->fetchAll() as $role) {
                $roleid = $role["role_id"];
                if(!in_array($roleid, $roles)) {
                    $this->connection->insert('roles_tabs_read', array(
                        'tab_id' => $tabid,
                        'role_id' => $roleid
                    ));
                    $roles[] = $roleid;
                }
            }
        }
    }
}
