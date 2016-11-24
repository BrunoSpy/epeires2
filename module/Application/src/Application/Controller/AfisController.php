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
use Zend\Form\Annotation\AnnotationBuilder;

use Application\Entity\Organisation;
use Application\Entity\Afis;

/**
 *
 * @author Loïc Perrin
 */
class AfisController extends AbstractEntityManagerAwareController
{
    private $em, $form;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();
        $this->form = (new AnnotationBuilder())->createForm($this::getEntity());
        $organisations = $this->em->getRepository(Organisation::class);
        $this->form->get('organisation')->setValueOptions($organisations->getAllAsArray());
    }

    public static function getEntity() {
        return Afis::class;
    }

    public function getForm() {
        return $this->form;   
    }

    public function indexAction()
    {
        if (!$this->authAfis('read')) return new JsonModel();

        return (new ViewModel())
            ->setVariables([
                'messages'  => $this->msg()->get(),
                'allAfis'   => $this->sgbd()->getBy(['decommissionned' => 0])
            ]);
    }

    public function formAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        
        $afis = $this->sgbd()->get($id);
        $this->form->bind($afis);

        return 
            (new ViewModel())
                ->setTerminal($this->getRequest()->isXmlHttpRequest())
                ->setVariables([
                    'form' => $this->form
                ]);
    }

    public function switchafisAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = intval($post['id']);

        $afis = $this->sgbd()->get($id);
        $afis->setState((boolean) $post['state']);

        $result = $this->sgbd()->save($afis);
        $msg = ($result['type'] == 'success') ? [$result['msg']->getName(), $result['msg']->getStrState()] : [$result['msg']];
        $this->msg()->add('afis','switch', $result['type'], $msg);

        return new JsonModel();
    }

    public function saveAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();

        $result = $this->sgbd()->save($post);
        $msg = [ ($result['type'] == 'success') ? $result['msg']->getName() : $result['msg'] ];
        $this->msg()->add('afis','edit', $result['type'], $msg);

        return new JsonModel();
    }

    public function deleteAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);

        $result = $this->sgbd()->del($id);
        $msg = [ ($result['type'] == 'success') ? $result['msg']->getName() : $result['msg'] ];
        $this->msg()->add('afis','del', $result['type'], $msg);

        return new JsonModel();
    }

    public function getAllAction($params = []) 
    {
        if (!$this->authAfis('write')) return new JsonModel();

        return $this->sgbd()->getBy($params);
    }

    private function authAfis($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.'.$action)) ? false : true;
    }
}