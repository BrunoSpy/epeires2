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
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilController extends \Application\Controller\FormController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function configAction()
    {
        $viewmodel = new ViewModel();
        $this->layout()->title = "Onglets > Zones militaires";
        
        $objectManager = $this->getEntityManager();
        
        $viewmodel->setVariables(array(
            'cats' => $objectManager->getRepository('Application\Entity\MilCategory')
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
            $milcat = $datas['milcat'];
            
            if ($form->isValid()) {
                if (! (strpos($milcat->getColor(), "#") === 0)) {
                    $milcat->setColor("#" . $milcat->getColor());
                }
                $objectManager->persist($milcat);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage("Catégorie modifée.");
                    $mil = array(
                        'id' => $milcat->getId(),
                        'name' => $milcat->getName(),
                        'regex' => $milcat->getZonesRegex()
                    );
                    $json['milcat'] = $mil;
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
        $milcat = new \Application\Entity\MilCategory();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($milcat);
        
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($milcat);
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')
            ->getAllAsArray());
        
        $form->get('color')->setAttribute('class', 'pick-a-color');
        
        if ($id) {
            $milcat = $objectManager->getRepository('Application\Entity\MilCategory')->find($id);
            if ($milcat) {
                $form->bind($milcat);
                $form->setData($milcat->getArrayCopy());
            }
        }
        
        return array(
            'form' => $form,
            'milcat' => $milcat
        );
    }
}
