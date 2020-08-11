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

use Application\Entity\OpSupType;
use Application\Entity\ShiftHour;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\Common\Collections\Criteria;
use Application\Controller\FormController;
use Application\Entity\OperationalSupervisor;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class OpSupsController extends FormController
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

    public function indexAction()
    {
        $this->layout()->title = "Utilisateurs > Op Sups";
        
        $objectManager = $this->getEntityManager();
        
        $opsups = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->findBy(array("archived" => false));

        $opsupsArchived = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->findBy(array('archived' => true));

        $types = $objectManager->getRepository('Application\Entity\OpSupType')->findAll();

        $shifthours = $objectManager->getRepository('Application\Entity\ShiftHour')->findAll();

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
            'opsups' => $opsups,
            'opsupsArchived' => $opsupsArchived,
            'types' => $types,
            'shifthours' => $shifthours
        ));
        
        return $viewmodel;
    }

    public function saveopsupAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id);
            $form = $datas['form'];
            $form->setPreferFormInputFilter(true);
            $form->setData($post);
            $opsup = $datas['opsup'];
            
            if ($form->isValid()) {
                $objectManager->persist($opsup);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Op Sup enregistré.');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function deleteopsupAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $opsup = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->find($id);
        if ($opsup) {
            $objectManager->remove($opsup);
            try {
                $objectManager->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }

    public function archiveopsupAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $opsup = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->find($id);
        if ($opsup) {
            $opsup->setArchived(true);
            try {
                $objectManager->persist($opsup);
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
        
        $opsupid = $this->params()->fromQuery('opsupid', null);
        
        $getform = $this->getForm($opsupid);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'opsupid' => $opsupid
        ));
        return $viewmodel;
    }

    public function getqualifzoneAction()
    {
        $objectManager = $this->getEntityManager();
        $orgid = $this->params()->fromQuery('id', null);
        $json = array();
        if ($orgid) {
            $organisation = $objectManager->getRepository('Application\Entity\Organisation')->find($orgid);
            if ($organisation) {
                $criteria = Criteria::create()->where(Criteria::expr()->eq('organisation', $organisation));
                foreach ($objectManager->getRepository('Application\Entity\QualificationZone')->matching($criteria) as $zone) {
                    $json[$zone->getId()] = $zone->getName();
                }
            }
        }
        return new JsonModel($json);
    }

    private function getForm($opsupid = null)
    {
        $objectManager = $this->getEntityManager();
        $opsup = new OperationalSupervisor();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($opsup);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($opsup);
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());

        $form->get('type')->setValueOptions($objectManager->getRepository('Application\Entity\OpSupType')->getAllAsArray());

        if ($opsupid) {
            $opsup = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->find($opsupid);
            if ($opsup) {
                $form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->getAllAsArray($opsup->getOrganisation()));
                $form->bind($opsup);
                $form->setData($opsup->getArrayCopy());
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
            'opsup' => $opsup
        );
    }

    public function formtypeAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());

        $opsuptypeid = $this->params()->fromQuery('opsuptypeid', null);

        $getform = $this->getFormType($opsuptypeid);

        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'opsuptypeid' => $opsuptypeid
        ));
        return $viewmodel;
    }

    private function getFormType($opsuptypeid = null)
    {
        $objectManager = $this->getEntityManager();
        $opsuptype = new OpSupType();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($opsuptype);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($opsuptype);

        $form->get('roles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());

        if ($opsuptypeid) {
            $opsuptype = $objectManager->getRepository('Application\Entity\OpSupType')->find($opsuptypeid);
            if ($opsuptype) {
                $form->bind($opsuptype);
                $form->setData($opsuptype->getArrayCopy());
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
            'opsuptype' => $opsuptype
        );
    }

    public function saveopsuptypeAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getFormType($id);
            $form = $datas['form'];
            $form->setPreferFormInputFilter(true);
            $form->setData($post);
            $opsuptype = $datas['opsuptype'];

            if ($form->isValid()) {
                $objectManager->persist($opsuptype);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Type Op Sup enregistré.');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function deleteopsuptypeAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $opsuptype = $objectManager->getRepository('Application\Entity\OpSupType')->find($id);
        if ($opsuptype) {
            $objectManager->remove($opsuptype);
            try {
                $objectManager->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }

    public function formshifthourAction () {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());

        $shifthourid = $this->params()->fromQuery('shifthourid', null);

        $getform = $this->getFormShifthour($shifthourid);

        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'shifthourid' => $shifthourid
        ));
        return $viewmodel;
    }

    private function getFormShifthour($id = null) {
        $objectManager = $this->getEntityManager();
        $shifthour = new ShiftHour();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($shifthour);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($shifthour);

        $form->get('opsuptype')->setValueOptions($objectManager->getRepository('Application\Entity\OpSupType')->getAllAsArray());
        $form->get('qualificationzone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray());

        if ($id) {
            $shifthour = $objectManager->getRepository('Application\Entity\ShiftHour')->find($id);
            if ($shifthour) {
                $form->bind($shifthour);
                $form->setData($shifthour->getArrayCopy());
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
            'shifthour' => $shifthour
        );
    }

    public function saveshifthourAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getFormShifthour($id);
            $form = $datas['form'];
            $form->setPreferFormInputFilter(true);
            $form->setData($post);
            $shifthour = $datas['shifthour'];

            if ($form->isValid()) {
                $objectManager->persist($shifthour);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Heure de relève enregistrée.');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function deleteshifthourAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $shifthour = $objectManager->getRepository('Application\Entity\ShiftHour')->find($id);
        if ($shifthour) {
            $objectManager->remove($shifthour);
            try {
                $objectManager->flush();
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        return new JsonModel();
    }
}
