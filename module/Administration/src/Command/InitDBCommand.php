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

namespace Administration\Command;

use Application\Entity\Category;
use Application\Entity\Tab;
use Core\Entity\Permission;
use Core\Entity\Role;
use Core\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitDBCommand
 * @package Administration\Command
 */
class InitDBCommand extends Command {

    protected static $defaultName = 'epeires2:initdb';

    private EntityManager $entitymanager;
    private $config;

    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entitymanager = $entityManager;
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Fresh install database initialisation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = $this->entitymanager;

        //vérification du stade initial de la base de données
        //méthode empirique : uniquement 3 catégories, 1 seul utilisateur, rôle affecté au tab par défaut
        $numberCat = count($objectManager->getRepository(Category::class)->findAll());
        $numberUser = count($objectManager->getRepository(User::class)->findAll());
        $tab = $objectManager->getRepository(Tab::class)->find(1);
        $numberRoles = count($tab->getReadRoles());

        if($numberCat !== 3 || $numberUser != 1 || $numberRoles !== 0) {
            $output->writeln('Impossible d\'initialiser la base de données : des modifications ont déjà été apportées.');
            return Command::FAILURE;
        }

        //ajout du rôle admin dans les rôles autorisés à voir l'onglet principal
        $roleAdmin = $objectManager->getRepository(Role::class)->find(1);
        $rolesCollection = new ArrayCollection();
        $rolesCollection->add($roleAdmin);
        $tab->addReadRoles($rolesCollection);

        //ajout des droits au rôle admin
        $permissions = $this->config['permissions']['Administration'];
        $permissionCollection = new ArrayCollection();
        try {
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

            $objectManager->flush();
            $output->writeln("Base de données correctement initialisée");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}