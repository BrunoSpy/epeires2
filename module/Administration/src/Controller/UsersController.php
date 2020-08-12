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
use Core\Entity\User;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Laminas\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\Criteria;
use Administration\Form\ChangePassword;
use Administration\Form\ChangePasswordFilter;
use Application\Controller\FormController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class UsersController extends FormController
{

    private $entityManager;
    private $zfcUserModuleOptions;
    private $config;

    public function __construct(EntityManager $entityManager, $userModuleOptions, $config)
    {
        $this->entityManager = $entityManager;
        $this->zfcUserModuleOptions = $userModuleOptions;
        $this->config = $config;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function indexAction()
    {
        $this->layout()->title = "Utilisateurs > Administration";
        
        $objectManager = $this->getEntityManager();
        
        $users = $objectManager->getRepository('Core\Entity\User')->findAll();
        
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
            'users' => $users
        ));
        
        return $viewmodel;
    }

    public function saveuserAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $id = $post['id'];
            $datas = $this->getForm($id);
            $form = $datas['form'];
            $form->setData($post);
            $form->setPreferFormInputFilter(true);
            $user = $datas['user'];
            if ($form->isValid()) {
                if (isset($post['password'])) {
                    $bcrypt = new Bcrypt();
                    $bcrypt->setCost($this->zfcUserModuleOptions
                        ->getPasswordCost());
                    $user->setPassword($bcrypt->create($user->getPassword()));
                }
                if(isset($post['mattermostPassword'])) {
                    $user->setMattermostPassword(base64_encode(openssl_encrypt(
                        $user->getMattermostPassword(),
                        "AES-256-CBC",
                        $this->config['secret_key'],
                        0,
                        $this->config['secret_init']
                    )));
                }
                try {
                    $objectManager->persist($user);
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Utilisateur enregistré.');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function deactivateuserAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $user = $objectManager->getRepository('Core\Entity\User')->find($id);
        if ($user) {
            $user->setState(0);
            $objectManager->persist($user);
            $objectManager->flush();
        }
        return new JsonModel();
    }

    public function reactivateuserAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $user = $objectManager->getRepository('Core\Entity\User')->find($id);
        if ($user) {
            $user->setState(1);
            $objectManager->persist($user);
            $objectManager->flush();
        }
        return new JsonModel();
    }

    public function deleteuserAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getEntityManager();
        $user = $objectManager->getRepository('Core\Entity\User')->find($id);
        if ($user) {
            $objectManager->remove($user);
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
        
        $userid = $this->params()->fromQuery('userid', null);
        
        $getform = $this->getForm($userid);
        
        $viewmodel->setVariables(array(
            'form' => $getform['form'],
            'userid' => $userid
        ));
        return $viewmodel;
    }

    public function changepasswordAction()
    {
        $objectManager = $this->getEntityManager();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            
            $form = new ChangePassword('changepassword');
            $form->setInputFilter(new ChangePasswordFilter());
            // $form->setPreferFormInputFilter(true);
            $form->setData($post);
            
            if ($form->isValid()) {
                $user = $objectManager->getRepository('Core\Entity\User')->find($post['id']);
                if ($user && isset($post['newCredential'])) {
                    $bcrypt = new Bcrypt();
                    $bcrypt->setCost($this->zfcUserModuleOptions
                        ->getPasswordCost());
                    $user->setPassword($bcrypt->create($post['newCredential']));
                }
                $objectManager->persist($user);
                try {
                    $objectManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Mot de passe correctement modifié');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            } else {
                $this->processFormMessages($form->getMessages());
            }
        }
        return new JsonModel();
    }

    public function changepasswordformAction()
    {
        $request = $this->getRequest();
        $viewmodel = new ViewModel();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $userid = $this->params()->fromQuery('id', null);
        
        $form = new ChangePassword('changepassword');
        $form->setInputFilter(new ChangePasswordFilter());
        
        $form->get('id')->setValue($userid);
        
        $viewmodel->setVariables(array(
            'form' => $form
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

    private function getForm($userid = null)
    {
        $objectManager = $this->getEntityManager();
        $user = new User();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($user);
        $form->setHydrator(new DoctrineObject($objectManager))->setObject($user);
        
        $form->get('userroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')
            ->getAllAsArray());
        
        $form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        if ($userid) {
            $user = $objectManager->getRepository('Core\Entity\User')->find($userid);
            if ($user) {
                $form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')
                    ->getAllAsArray($user->getOrganisation()));
                
                $form->remove('password');
                $form->getInputFilter()->remove('password');
                $form->bind($user);
                $form->setData($user->getArrayCopy());
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
            'user' => $user
        );
    }
}
