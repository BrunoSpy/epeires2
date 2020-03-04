<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Administration\Controller;

use Application\Entity\Category;
use Application\Entity\Tab;
use Application\Entity\AfisCategory;
use Application\Entity\AlertCategory;
use Application\Entity\FlightPlanCategory;
use Application\Entity\FieldCategory;
use Application\Entity\InterrogationPlanCategory;
use Core\Controller\AbstractEntityManagerAwareController;
use Core\Entity\Permission;
use Core\Entity\Role;
use Core\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Laminas\Console\Request as ConsoleRequest;

/**
 *
 * @author Bruno Spyckerelle
 */
class MaintenanceController extends AbstractEntityManagerAwareController
{
    private $config;

    private $categoryfactory;

    public function __construct(EntityManager $entityManager, $categoryfactory, $config)
    {
        parent::__construct($entityManager);
        $this->categoryfactory = $categoryfactory;
        $this->config = $config;
    }

    public function deleteEventsAction()
    {
        $request = $this->getRequest();
        
        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectManager = $this->getEntityManager();
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));
        
        if (! $organisation) {
            throw new \RuntimeException('Unable to find organisation.');
        }
        
        $batchSize = 20;
        $i = 0;
        $q = $objectManager->createQuery('select e from Application\Entity\Event e where e.organisation = ?1');
        $q->setParameter(1, $organisation->getId());
        
        $iterable = $q->iterate();
        while (($row = $iterable->next()) !== false) {
            $objectManager->remove($row[0]);
            if (($i % $batchSize) === 0) {
                $objectManager->flush();
                $objectManager->clear();
            }
            ++ $i;
        }
        try {
            $objectManager->flush();
            return "Suppression des évènements réussie.";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }
    
    /**
     * Supprime des logs les références aux éléments supprimés.
     * Particulièrement utile après un nettoyage de la base de données
     */
    public function cleanLogsAction()
    {
        $request = $this->getRequest();
        
        if(! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }
        
        $objectmanager = $this->getEntityManager();
        
        //get the number of rows to delete
        $dql = $objectmanager->createQueryBuilder();
        $dql->select('count(log.id)')
            ->from('Application\Entity\Log', 'log')
            ->where($dql->expr()->eq('log.action', '?1'))
            ->setParameter(1, "remove");
        try {
            $removeRowsCount = $dql->getQuery()
                ->getSingleScalarResult();
        } catch(\Exception $ex) {
            error_log($ex->getMessage());
        }
        
        $q = $objectmanager->createQuery('select l from Application\Entity\Log l where l.action = ?1');
        $q->setParameter(1, "remove");
        $iterable = $q->iterate();
        $i = 0;
        $batchSize = 50;
        print("Nettoyage des logs en cours... Cette opération peut prendre plusieurs minutes selon la taille de la base de données.\n");
        while(($row = $iterable->next()) !== false) {
            $object = $row[0];
            $q2 = $objectmanager->createQuery('select l from Application\Entity\Log l where l.objectId = ?1 and l.objectClass = ?2');
            $q2->setParameters(array(1 => $object->getObjectId(), 2 => $object->getObjectClass()));
            $iterable2 = $q2->iterate();
            while(($row2 = $iterable2->next()) !== false) {
                $objectmanager->remove($row2[0]);
            }
            $objectmanager->remove($row[0]);
            if(($i % $batchSize) === 0) {
                printf( "%.1f %% effectué... \n",(($i / $removeRowsCount)*100) );
                $objectmanager->flush();
                $objectmanager->clear();
            }
            $i++;
        }
        
        try {
            $objectmanager->flush();
            return "Nettoyage des logs effectué.";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }

    /**
     * Initialise la base de données avec des valeurs par défaut permettant l'utilisation de l'application
     */
    public function initdbAction() {
        $request = $this->getRequest();

        if(! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $objectManager = $this->getEntityManager();

        //vérification du stade initial de la base de données
        //méthode empirique : uniquement 3 catégories, 1 seul utilisateur, rôle affecté au tab par défaut
        $numberCat = count($objectManager->getRepository(Category::class)->findAll());
        $numberUser = count($objectManager->getRepository(User::class)->findAll());
        $tab = $objectManager->getRepository(Tab::class)->find(1);
        $numberRoles = count($tab->getReadRoles());

        if($numberCat !== 3 || $numberUser != 1 || $numberRoles !== 0) {
            return 'Impossible d\'initialiser la base de données : des modifications ont déjà été apportées.'."\n";
        }

        //ajout du rôle admin dans les rôles autorisés à voir l'onglet principal
        $roleAdmin = $objectManager->getRepository(Role::class)->find(1);
        $rolesCollection = new ArrayCollection();
        $rolesCollection->add($roleAdmin);
        $tab->addReadRoles($rolesCollection);

        //ajout des droits au rôle admin
        $permissions = $this->config['permissions']['Administration'];
        $permissionCollection = new ArrayCollection();
        foreach ($permissions as $permission => $description) {
            $perm = $objectManager->getRepository(Permission::class)->findOneBy(array(
                'name' => $permission
            ));
            if (! $perm) {
                // create new permission
                $perm = new Permission();
                $perm->setName($permission);
                $objectManager->persist($perm);
                $permissionCollection->add($perm);
            }
        }
        $roleAdmin->addPermissions($permissionCollection);
        $objectManager->persist($roleAdmin);

        try {
            $objectManager->flush();
            return "Base de données correctement initialisée";
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Initialise la base de données avec des valeurs par défaut permettant l'utilisation de l'application pour les BTIV
     */
    public function initbtivdbAction() {
        $request = $this->getRequest();

        if(! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $objectManager = $this->getEntityManager();

        //vérification du stade initial de la base de données
        //pas de categories BTIV (afis/pln/alertes/pio/terrains)
        $nAfisCat = count($objectManager->getRepository(AfisCategory::class)->findAll());
        $nPlnCat = count($objectManager->getRepository(FlightPlanCategory::class)->findAll());
        $nAltCat = count($objectManager->getRepository(AlertCategory::class)->findAll());
        $nPiCat = count($objectManager->getRepository(InterrogationPlanCategory::class)->findAll());
        $nFieldCat = count($objectManager->getRepository(FieldCategory::class)->findAll());

        if (array_sum([$nAfisCat, $nPlnCat, $nAltCat, $nPiCat, $nFieldCat]) > 0) {
            return 'Impossible d\'initialiser les catégories btiv dans la base de données : des modifications ont déjà été apportées.'."\n";
        }

        //ajout de la catégorie d'événement AFIS
        $afisCat = $this->categoryfactory->createAfisCategory();
        $afisCat->setName("AFIS");
        $afisCat->setShortName("AF");
        $afisCat->setCompactMode(0);
        $afisCat->setTimelineConfirmed(0);       
        $afisCat->setColor("#008000");

        $objectManager->persist($afisCat);

        //ajout de la catégorie d'événement PLN
        $plnCat = $this->categoryfactory->createFlightPlanCategory();
        $plnCat->setName("GESTION PLN");
        $plnCat->setShortName("PLN");
        $plnCat->setCompactMode(0);
        $plnCat->setTimelineConfirmed(0);       
        $plnCat->setColor("#0000FF");

        $objectManager->persist($plnCat);

        //ajout de la catégorie d'événement Alerte
        $AltCat = $this->categoryfactory->createAlertCategory();
        $AltCat->setName("ALERTES");
        $AltCat->setShortName("ALT");
        $AltCat->setCompactMode(0);
        $AltCat->setTimelineConfirmed(0);       
        $AltCat->setColor("#FF0000");

        $objectManager->persist($AltCat);

        //ajout de la catégorie d'événement PIO
        $ipCat = $this->categoryfactory->createInterrogationPlanCategory();
        $ipCat->setName("PIO/PIA");
        $ipCat->setShortName("PI");
        $ipCat->setCompactMode(0);
        $ipCat->setTimelineConfirmed(0);       
        $ipCat->setColor("#FF6600");

        $objectManager->persist($ipCat);

        //ajout de la catégorie d'événement terrains interrogés pour les PIO
        $fCat = $this->categoryfactory->createFieldCategory();
        $fCat->setName("TERRAINS");
        $fCat->setShortName("TER");
        $fCat->setCompactMode(0);
        $fCat->setTimelineConfirmed(0);       
        $fCat->setColor("#800080");
        $fCat->setParent($ipCat);

        $objectManager->persist($fCat);

        try {
            $objectManager->flush();
            return "Données BTIV correctement initialisées";
        } catch (\Exception $e) {
            // echo $e->getMessage();
            error_log($e->getMessage());
        }
    }
}