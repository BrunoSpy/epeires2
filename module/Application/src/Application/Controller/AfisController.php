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
use Zend\Stdlib\Parameters;

use Application\Entity\Organisation;
use Application\Entity\Afis;
/**
 *
 * @author Loïc Perrin
 */
class AfisController extends AbstractEntityManagerAwareController
{
    private $em, $repo, $form;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();
        $this->repo = $this->em->getRepository(Afis::class);

        $this->form = (new AnnotationBuilder())->createForm(Afis::class);    
        $this->form
            ->get('organisation')
            ->setValueOptions(
                $this->em
                    ->getRepository(Organisation::class)
                    ->getAllAsArray()
                );
    }

    public function indexAction()
    {
        if (!$this->authAfis('read')) return new JsonModel();

        return (new ViewModel())
            ->setVariables([
                'allAfis'   => $this->repo->findBy(['decommissionned' => 0])
            ]);
    }

    public function getAction() 
    {
        if (!$this->authAfis('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $decom = (boolean) intval($post['decomissionned']);
        $admin = (boolean) intval($post['admin']);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'admin'    => $admin,
                'afises'   => $this->repo->findBy(['decommissionned' => $decom])
            ]);     
    }

    public function formAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->bind($afis);

        return (new ViewModel())
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

        $afis = $this->repo->find($id);

        if(is_a($afis, Afis::class)) {
            $afis->setState((boolean) $post['state']);
            return new JsonModel($this->repo->save($afis));
        } else {
            return new JsonModel([
                'type' => 'error', 
                'msg' => 'Afis non existant'
            ]);
        }
    }

    public function saveAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $afis = $this->validateAfis($post);

        if(is_a($afis, Afis::class)) {
            return new JsonModel($this->repo->save($afis));
        } else {
            return new JsonModel([
                'type' => 'error', 
                'msg' => $this->form->getMessages()
            ]);
        }
    }

    public function deleteAction()
    {
        if (!$this->authAfis('write')) return new JsonModel();

        $id = intval($this->getRequest()->getPost()['id']);

        $afis = $this->repo->find($id);

        if(is_a($afis, Afis::class)) {
            return new JsonModel($this->repo->del($afis));
        } else {
            return new JsonModel([
                'type' => 'error', 
                'msg' => 'Afis non existant'
            ]);
        }
    }

    private function authAfis($action) 
    {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('afis.'.$action)) ? false : true;
    }

    private function validateAfis($params) 
    {
        if (!is_a($params, Parameters::class) && !is_array($params)) return false;

        $id = intval($params['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->setData($params);

        if (!$this->form->isValid()) $ret = false;
        else 
        { 
            $ret = $this->repo->hydrate($this->form->getData(), $afis);
        }
        return $ret;
    }
}