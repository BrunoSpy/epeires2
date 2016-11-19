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

use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DateTime;

use Application\Entity\FlightPlan;
use Application\Form\FlightPlanForm;

use Application\Entity\Event;
use Application\Entity\Organisation;
use Application\Entity\CustomFieldValue;
use Application\Entity\Status;
use Application\Entity\Impact;

/**
 *
 * @author Loïc Perrin
 */
class FlightPlansSarController extends FlightPlansController
{
    public function triggerAction(){
        /* TODO
         *   DROIT d'écriture sur FP, test s'il existe des evenements associes
         *   Pour l'instant utilisation de la categorie d'evenemenet generique => créer une catégorie SAR
         *   gérer organisation
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $fp = $this->em->getRepository(FlightPlan::class)->find($post['fpid']);
            // l'evenement sera confirmé dès sa création
            $status = $this->em->getRepository(Status::class)->find('2');
            // l'evenement sera d'impact mineur
            $impact = $this->em->getRepository(Impact::class)->find('3');
            // pour l'instant crna-x
            $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['id' => 1]);
            // catégorie en fonction du type d'alerte
            $categories = $this->em->getRepository('Application\Entity\Category')->findByName($post['type']);
            $cat = $categories[0];

            $e = new Event();
            $e->setPunctual(false);
            $e->setStartdate((new \DateTime('NOW'))->setTimezone(new \DateTimeZone("UTC")));
            $e->setStatus($status);
            $e->setImpact($impact);
            $e->setOrganisation($organisation);
            $e->setCategory($cat);
            $e->setAuthor($this->zfcUserAuthentication()->getIdentity());

            // Champ qui contient l'aircraft ID à afficher dans la timeline sur l'évènement
            $chpAirId = new CustomFieldValue();
            $chpAirId->setCustomField($cat->getFieldName());
            $chpAirId->setValue($fp->getAircraftid());
            $chpAirId->setEvent($e);

            $e->addCustomFieldValue($chpAirId);

            $this->em->persist($chpAirId);
            $this->em->persist($e);
            $this->em->flush();
        }
        return new JsonModel();
    }

}