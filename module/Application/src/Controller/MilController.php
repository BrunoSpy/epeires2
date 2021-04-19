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
namespace Application\Controller;

use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\MilCategory;
use Application\Entity\MilCategoryLastUpdate;
use Application\Entity\Organisation;
use Application\Entity\Status;
use Core\Controller\AbstractEntityManagerAwareController;
use Core\Entity\User;
use Core\Service\MAPDService;
use Core\Service\NMB2BService;
use Doctrine\ORM\EntityManager;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Db\Sql\Ddl\Column\Datetime;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilController extends AbstractEntityManagerAwareController
{

    private $nmb2b;
    private $mapd;

    public function __construct(EntityManager $entityManager, NMB2BService $nmb2b, MAPDService $mapd)
    {
        parent::__construct($entityManager);
        $this->nmb2b = $nmb2b;
        $this->mapd = $mapd;
    }

    public function importAction()
    {
        $request = $this->getRequest();

        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $j = $request->getParam('delta');

        $org = $request->getParam('orgshortname');
        $organisation = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));

        $email = $request->getParam('email');

        $verbose = $request->getParam('verbose');

        $username = $request->getParam('username');

        $user = $this->getEntityManager()->getRepository('Core\Entity\User')->findOneBy(array(
            'username' => $username
        ));

        $service = $request->getParam('service');
        if(strcmp($service, 'nmb2b') == 0) {
            $this->importNMB2B($j, $organisation, $user, $email, $verbose);
        } elseif (strcmp($service, 'mapd') == 0) {
            $this->importMAPD($j, $organisation, $user, $email);
        } else {
            throw new \RuntimeException('Service '.$service.' unknown.');
        }
    }



}
