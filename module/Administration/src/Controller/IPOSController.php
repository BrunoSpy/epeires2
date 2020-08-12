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
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Application\Entity\IPO;

class IPOSController extends AdminTabController
{

    public function indexAction()
    {
        parent::indexAction();
        $this->layout()->title = "Utilisateurs > " . $this->translate('IPO');
        
        $objectManager = $this->getEntityManager();
        
        $ipos = $objectManager->getRepository('Application\Entity\IPO')->findAll();
        
        $return = array();
        
        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['error'] = $this->flashMessenger()->getErrorMessages();
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['success'] = $this->flashMessenger()->getSuccessMessages();
        }
        
        $this->flashMessenger()->clearMessages();
        
        $viewmodel = new ViewModel();
        
        $viewmodel->setVariables(array(
            'messages' => $return,
            'ipos' => $ipos
        ));
        
        return $viewmodel;
    }

    public function saveipoAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id);
            $form = $datas['form'];
            $form->setData($post);
            $ipo = $datas['ipo'];
            
            if ($form->isValid()) {
                $objectManager->persist($ipo);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage($this->translate('IPO').' enregistré.');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function deleteipoAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $ipo = $objectManager->getRepository('Application\Entity\IPO')->find($id);
        if ($ipo) {
            $objectManager->remove($ipo);
            try {
                $objectManager->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }

    public function formAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $ipoid = $this->params()->fromQuery('ipoid', null);
        
        $getform = $this->getForm($ipoid);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'ipoid' => $ipoid
        ));
        return $viewmodel;
    }

    private function getForm($ipoid = null)
    {
        $objectManager = $this->getEntityManager();
        $ipo = new IPO();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($ipo);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($ipo);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($ipoid) {
            $ipo = $objectManager->getRepository('Application\Entity\IPO')->find($ipoid);
            if ($ipo) {
                $form->bind($ipo);
                $form->setData($ipo->getArrayCopy());
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
            'ipo' => $ipo
        );
    }
}
