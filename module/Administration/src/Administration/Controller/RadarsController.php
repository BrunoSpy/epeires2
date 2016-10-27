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

use Core\Controller\AbstractEntityManagerAwareController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Entity\Radar;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class RadarsController extends AbstractEntityManagerAwareController
{

    public function indexAction()
    {
        $this->layout()->title = "Centres > Radars";
        
        $objectManager = $this->getEntityManager();
        
        $radars = $objectManager->getRepository('Application\Entity\Radar')->findAll();
        
        return array(
            'radars' => $radars
        );
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id);
            $form = $datas['form'];
            $form->setData($post);
            $radar = $datas['radar'];
            
            if ($form->isValid()) {
                
                $objectManager->persist($radar);
                $objectManager->flush();
                
                if ($radar->isDecommissionned()) {
                    // sets all related events read-only
                    $objectManager->getRepository('Application\Entity\Event')->setReadOnly($radar);
                }
            }
        }
        return new JsonModel();
    }

    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $radar = $objectManager->getRepository('Application\Entity\Radar')->find($id);
        if ($radar) {
            $objectManager->remove($radar);
            $objectManager->flush();
        }
        return new JsonModel();
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getForm($id);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'id' => $id
        ));
        return $viewmodel;
    }

    private function getForm($id = null)
    {
        $objectManager = $this->getEntityManager();
        $radar = new Radar();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($radar);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($radar);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($id) {
            $radar = $objectManager->getRepository('Application\Entity\Radar')->find($id);
            if ($radar) {
                $form->bind($radar);
                $form->setData($radar->getArrayCopy());
            }
        }
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary btn-small'
            )
        ));
        
        return array(
            'form' => $form,
            'radar' => $radar
        );
    }
}
