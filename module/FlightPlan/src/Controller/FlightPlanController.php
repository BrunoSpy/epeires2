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
namespace FlightPlan\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;
use DateTime;

use FlightPlan\Entity\FlightPlan;
use FlightPlan\Form\FlightPlanForm;

use Application\Entity\Event;
use Application\Entity\Organisation;
use Application\Entity\CustomFieldValue;
use Application\Entity\Status;
use Application\Entity\Impact;

/**
 *
 * @author Loïc Perrin
 */
class FlightPlanController extends AbstractActionController
{
    const TYPES_ALERTE = [
        'INCERFA' => [
            'btnType' => 'info'
        ],
        'ALERFA' => [
            'btnType' => 'warning',
        ],
        'DETRESFA' => [
            'btnType' => 'danger'
        ]
    ];
    /*
     * Entity Manager
     */
    protected $em;

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function indexAction()
    {
        /* TODO
        DROIT de lecture sur FP
        */
        $this->layout()->setTemplate('fp/layout');

        $q = $this->params()->fromQuery();
        if(array_key_exists('date',$q)) {
            $d = explode(',',$q['date']);
            $dateTime = new DateTime($d[1].'/'.$d[0].'/'.$d[2]);
        }
        else
            $dateTime = new DateTime();

        return (new ViewModel())
            ->setTemplate('fp/index')
            ->setVariables(
            [
                'messages'  => $this->fpMessages()->get(),
                'allFp' => $this->fpSGBD($this->em)->getByDate($dateTime),
                'typesAlerte' => self::TYPES_ALERTE,
            ]);
    }
    
    public function formAction()
    {
        /* TODO
       DROIT d'écriture sur FP
        */
        $form = (new FlightPlanForm($this->em))->getForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $fp = $this->fpSGBD($this->em)->get($request->getPost()['fpid']);
            $form->setData($fp->getArrayCopy());
        }
        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setTemplate('fp/form')
                ->setVariables([
                    'form' => $form
        ]);
    }
    
    public function saveAction()
    {
        /* TODO
           DROIT d'écriture sur FP
        */
        if (!$this->zfcUserAuthentication()->hasIdentity())
            return new JsonModel();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $this->fpSGBD($this->em)->save($post);
        }
        return new JsonModel();
    }
    /*
     * Suppression d'une entité
     */
    public function deleteAction()
    {
        /* TODO
         *   DROIT d'écriture sur FP, test s'il existe des evenements associes
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->getRequest()->getPost()['fpid'];
            if ($id) {
                $this->fpSGBD($this->em)->del($id);
            }
        }
        return new JsonModel();
    }

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