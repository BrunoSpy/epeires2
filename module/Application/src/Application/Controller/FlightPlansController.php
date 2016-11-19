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
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DateTime;
use Zend\Form\Annotation\AnnotationBuilder;

use Application\Entity\FlightPlan;



/**
 *
 * @author Loïc Perrin
 */
class FlightPlansController extends AbstractEntityManagerAwareController
{
    private $em, $form;
    public static $class = FlightPlan::class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->form = (new AnnotationBuilder())->createForm(FlightPlan::class);
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

        $q = $this->params()->fromQuery();
        if(array_key_exists('date',$q)) {
            $d = explode(',',$q['date']);
            $dateTime = new DateTime($d[1].'/'.$d[0].'/'.$d[2]);
        }
        else
            $dateTime = new DateTime();

        return (new ViewModel())
            ->setVariables(
            [
                'messages'  => $this->msg()->get(),
                'allFp' => $this->fpSGBD($this->em)->getByDate($dateTime),
            ]);
    }
    
    public function listAction() {
        $post = $this->getRequest()->getPost();
        $dateTime = new DateTime($post['date']);
        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'flightplans' => $this->fpSGBD($this->em)->getByDate($dateTime)
            ]);
    }

    public function formAction()
    {
        if (!$this->authFlightPlans('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $this->form->bind($this->fpSGBD()->get($id));

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
        $result = ($this->fpSGBD($this->em)->save($post));
        $txt = ($result['type'] == 'success') ? $result['msg']->getAircraftid() : $result['msg'];
        $this->msg()->add('fp','edit', $result['type'], [$txt]);
        return new JsonModel();
    }

    public function deleteAction()
    {
        if (!$this->authFlightPlans('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $result = $this->fpSGBD($this->em)->del($id);
        $txt = ($result['type'] == 'success') ? $result['msg']->getAircraftid() : $result['msg'];
        $this->msg()->add('fp','del', $result['type'], [$txt]);
        return new JsonModel();
    }

    private function authFlightPlans($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('flightplans.'.$action)) ? false : true;
    }
}