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

use Core\Controller\AbstractEntityManagerAwareController;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DateTime;
use DateInterval;
use Zend\Form\Annotation\AnnotationBuilder;

use Application\Entity\FlightPlan;

/**
 *
 * @author Loïc Perrin
 */
class FlightPlansController extends AbstractEntityManagerAwareController
{
    protected $em, $form;
    public static $class = FlightPlan::class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->form = (new AnnotationBuilder())->createForm($this::$class);
    }

    public function getEntityManager() {
        return $this->em;
    }

    public function getForm() {
        return $this->form;   
    }

    public function indexAction()
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();

        $start = (new DateTime())->setTime(0,0,0);
        $end = (new DateTime())->setTime(0,0,0)->add(new DateInterval('P1D'));

        $crit = new Criteria();
        $expr = Criteria::expr();

        $crit->where(
            $expr->andX(
                $expr->eq('typealerte', 0),
                $expr->lt('estimatedtimeofarrival', $end),
                $expr->gt('estimatedtimeofarrival', $start)
            )
        );

        return (new ViewModel())
            ->setVariables(
            [
                'messages'  => $this->msg()->get(),
                'flightplans' => $this->sgbd()->getByCriteria($crit)
            ]);
    }
    
    public function sarAction()
    {
        if (!$this->authFlightPlans('read')) return new JsonModel();

        $start = (new DateTime())->setTime(0,0,0);
        $end = (new DateTime())->setTime(0,0,0)->add(new DateInterval('P1D'));

        $crit = new Criteria();
        $expr = Criteria::expr();

        $crit->where(
            $expr->andX(
                $expr->gt('typealerte', 0),
                $expr->lt('estimatedtimeofarrival', $end),
                $expr->gt('estimatedtimeofarrival', $start)
            )
        );

        return (new ViewModel())
            ->setTemplate('application/flight-plans/index')
            ->setVariables(
            [
                'messages'  => $this->msg()->get(),
                'flightplans' => $this->sgbd()->getByCriteria($crit)
            ]);
    }   

    public function listAction() {
        $post = $this->getRequest()->getPost();
        
        $start = new DateTime($post['date']);
        $end = (new DateTime($post['date']))->add(new DateInterval('P1D'));

        $crit = new Criteria();
        $expr = Criteria::expr();

        if($post['sar']) $crit->where($expr->gt('typealerte', 0));
        else $crit->where($expr->eq('typealerte', 0));

        $crit->andWhere(
            $expr->andX(
                $expr->lt('estimatedtimeofarrival', $end),
                $expr->gt('estimatedtimeofarrival', $start)
            )
        );

        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'flightplans' => $this->sgbd()->getByCriteria($crit)
            ]);
    }

    public function formAction()
    {
        if (!$this->authFlightPlans('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $this->form->bind($this->sgbd()->get($id));

        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'form' => $this->form
        ]);
    }
    
    public function saveAction()
    {
        if (!$this->authFlightPlans('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();

        $result = ($this->sgbd()->save($post));
        $txt = ($result['type'] == 'success') ? $result['msg']->getAircraftid() : $result['msg'];
        $this->msg()->add('fp','edit', $result['type'], [$txt]);

        return new JsonModel();
    }

    public function deleteAction()
    {
        if (!$this->authFlightPlans('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);

        $result = $this->sgbd()->del($id);
        $txt = ($result['type'] == 'success') ? $result['msg']->getAircraftid() : $result['msg'];
        $this->msg()->add('fp','del', $result['type'], [$txt]);

        return new JsonModel();
    }

    private function authFlightPlans($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('flightplans.'.$action)) ? false : true;
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