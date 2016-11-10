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


use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Zend\Form\Annotation\AnnotationBuilder;
use Application\Form\CustomFieldset;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Entity\InterrogationPlan;
use Application\Entity\Field;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractActionController
{
    // const DEFAULT_METHOD = "post";
    // protected $em;

    // public function __invoke($em)
    // {
    //     if(null === $this->em) $this->em = $em;
    //     return $this;
    // }

    public function indexAction()
    {
        parent::indexAction();
        
        $viewmodel = new ViewModel();
        
        $return = array();
        
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['errorMessages'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['successMessages'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $viewmodel->setVariables(array(
            'messages' => $return,
            //'form' => $this->getRadarForm()
        ));
        
        // $viewmodel->setVariable('radars', $this->getRadars());
        
        return $viewmodel;
    }

    private function get($id = null)
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $intPlan = $em->getRepository(InterrogationPlan::class)->find($id);
            if ($intPlan == null or !$intPlan->isValid()) return null;
        } else {
            $intPlan = new InterrogationPlan();
        }
        return $intPlan;
    }

    public function sauverAction()
    {
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $post   = $request->getPost();
            print_r($post);
            $f = [];
            $f1 = new Field();
            $f1->setName("test");
            $f1->setComment("test baodfjed");
            $f1->setIntTime(new \DateTime());

            $f[] = $f1;
            $data = [];
            foreach ($post as $key => $value) {
                $data[$key] = $value;
            }
            $data['fields'] = $f;

            // print_r($data);
            $form = $this->getForm();
            $form->setData($data);
            if($form->isValid()){
                $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                // print_r($form->getData());
                $intPlan = (new DoctrineHydrator($em))->hydrate($form->getData(), $this->get());
                // print_r($intPlan);
                $em->persist($intPlan);
                $em->flush();
            } else {
                // print_r($form->getData());
            }
            // print_r($form);
            // print_r($post);
        }
        return new JsonModel();
    }

    public function formAction() {
        // print_r($this->getForm());

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->getForm()
            ])
        ;
    }

    private function getForm() {
        $form = (new AnnotationBuilder())->createForm(InterrogationPlan::class);
            // ->get('organisation')
            // ->setValueOptions($organisations->getAllAsArray())
        ;

        $form->add([
            'type' => \Zend\Form\Element\Collection::class,
            'options' => [
                'label' => 'Terrains',
                'count' => 2,
                'should_create_template' => true,
                'target_element' => new \Zend\Form\Element\Color()
            ],
        ]);
        return $form;
    }

}