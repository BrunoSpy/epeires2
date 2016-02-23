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
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mvc\Controller\AbstractActionController;
use FlightPlan\Entity\FlightPlan;
use FlightPlan\Form\FlightPlanForm;
use Doctrine\ORM\EntityManager;
/**
 *
 * @author Loïc Perrin
 */
class FlightPlanController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout()->setTemplate('flight-plan/layout');
        return [
            'allFp' => $this->getAllFlightPlan(),
        ];
    }

    private function getAllFlightPlan(array $params = [])
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $allFp = [];
        foreach ($em->getRepository(FlightPlan::class)->findBy($params) as $fp) 
        {
            $allFp[] = $fp;
        }
        return $allFp;
    }
    
    private function getFlightPlan($id)
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        return $em->getRepository(FlightPlan::class)->find($id);
    }
    
    public function formAction()
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $form = FlightPlanForm::newInstance(new FlightPlan(), $em);
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $fp = $this->getFlightPlan($request->getPost()['fpid']);
            $form->setData($fp->getArrayCopy());
        }
        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setTemplate('flight-plan/form')
                ->setVariables([
                    'form' => $form
        ]);
    }
    
    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $em = $this->getServiceLocator()->get(EntityManager::class);
            $post = $request->getPost();
            if($post['id']){
                $fp = $em->getRepository(FlightPlan::class)->find($post['id']);
            } else {
                $fp = new FlightPlan();
            }
            
            $form = FlightPlanForm::newInstance($fp, $em);
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $em->persist((new DoctrineHydrator($em))->hydrate($form->getData(), $fp));
                $em->flush();    
            }
        }
        return new JsonModel();
    }
    /*
     * Suppression d'une entité
     */
    public function deleteAction()
    {
        $em = $this->getServiceLocator()->get(EntityManager::class);
        $id = $this->getRequest()->getPost()['fpid'];
        
        if ($id) {
            $fp = $em->getRepository(FlightPlan::class)->find($id);
            $em->remove($fp);
            $em->flush();
        }
        return new JsonModel();
    }
}