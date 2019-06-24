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
use Zend\Stdlib\Parameters;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Entity\Afis;
use Application\Entity\Organisation;

use Application\Entity\AfisCategory;

/**
 *
 * @author Loïc Perrin
 *        
 */
class AfisController extends AbstractEntityManagerAwareController
{
    private $em, $repo, $form;

    public function __construct($em)
    {
        $this->em = $em;
        $this->repo = $em->getRepository(Afis::class);

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
        $this->layout()->title = "Centres > AFIS";
    
        $return = [];
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
            'afis' => $this->repo->findAll()
        ));
        return $viewmodel;
    }

    public function formAction()
    {
        $id = intval($this->getRequest()->getPost()['id']);
        $afis = ($id) ? $this->repo->find($id) : new Afis();
        $this->form->bind($afis);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
            ]);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        $afis = $this->validateAfis($post);

        if(is_a($afis, Afis::class)) 
        {     
            $this->em->persist($afis);
            try 
            {
                $this->em->flush();
                $this->flashMessenger()->addSuccessMessage("Modifications effectuées avec succès.");
            } 
            catch (\Exception $e) 
            {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        } 
        else 
        {
            $this->processFormMessages($form->getMessages());
        }

        return new JsonModel(); 
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
            $hydrator = new DoctrineHydrator($this->em);
            $ret = $hydrator->hydrate($this->form->getData(), $afis);
        }
        return $ret;
    }

    public function deleteAction()
    {
        $id = intval($this->getRequest()->getPost()['id']);

        $afis = $this->repo->find($id);
        if (!is_a($afis, Afis::class))
        {
            $this->flashMessenger()->addErrorMessage("Impossible de trouver l'AFIS à supprimer.");
            return new JsonModel();
        } 

        $this->em->remove($afis);    
        try 
        {
            $this->em->flush();
            $this->flashMessenger()->addSuccessMessage("Suppression effectuée avec succès.");
        } 
        catch (\Exception $e) 
        {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }

        return new JsonModel(); 
    }

}
