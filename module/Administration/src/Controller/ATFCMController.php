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

use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class ATFCMController extends AdminTabController
{

    public function configAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Onglets > Régulations";
        
        $objectManager = $this->getEntityManager();

        $this->layout()->lang = $this->getAppConfig()['lang'];

        $viewmodel->setVariables(array(
            'cats' => $objectManager->getRepository('Application\Entity\ATFCMCategory')
                ->findBy(array(), array(
                'name' => 'ASC'
            ))
        ));
        
        // gestion des erreurs lors d'un reload
        $return = array();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $viewmodel->setVariables(array(
            'messages' => $return
        ));
        return $viewmodel;
    }

    public function saveAction()
    {
        $objectManager = $this->getEntityManager();
        $messages = array();
        $json = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            
            $datas = $this->getForm($id);
            $form = $datas['form'];
            
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $atfcmcat = $datas['atfcmcat'];
            
            if ($form->isValid()) {
                if (! (strpos($atfcmcat->getColor(), "#") === 0)) {
                    $atfcmcat->setColor("#" . $atfcmcat->getColor());
                }
                $objectManager->persist($atfcmcat);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage("Catégorie modifée.");
                    $atfcm = array(
                        'id' => $atfcmcat->getId(),
                        'name' => $atfcmcat->getName(),
                        'tvs' => $atfcmcat->getTvs()
                    );
                    $json['atfcmcat'] = $atfcm;
                    $json['success'] = true;
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $this->processFormMessages($form->getMessages());
                $this->flashMessenger()->addErrorMessage("Impossible de modifier la catégorie.");
            }
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $getform = $this->getForm($id);
        $form = $getform['form'];
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            )
        ));
        
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    private function getForm($id)
    {
        $objectManager = $this->getEntityManager();
        $atfcmcat = new \Application\Entity\ATFCMCategory();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($atfcmcat);
        
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($atfcmcat);
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')
            ->getAllAsArray());
        
        $form->get('color')->setAttribute('class', 'pick-a-color');
        
        if ($id) {
            $atfcmcat = $objectManager->getRepository('Application\Entity\ATFCMCategory')->find($id);
            if ($atfcmcat) {
                $form->bind($atfcmcat);
                $form->setData($atfcmcat->getArrayCopy());
            }
        }
        
        return array(
            'form' => $form,
            'atfcmcat' => $atfcmcat
        );
    }
}
