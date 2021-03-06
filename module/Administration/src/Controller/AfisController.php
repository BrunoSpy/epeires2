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

use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Entity\Afis;
use Application\Entity\Organisation;

/**
 *
 * @author Loïc Perrin
 *
 */
class AfisController extends AbstractEntityManagerAwareController
{
    private $em, $repo, $notamweb;

    public function __construct($em, $notamweb)
    {
        $this->em = $em;
        $this->repo = $em->getRepository(Afis::class);
        $this->notamweb = $notamweb;
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

        $form = $this->createForm();
        $form->bind($afis);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $form
            ]);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        $form = $this->createForm();
        $form->setData($post);
        if ($form->isValid())
        {
            $this->save($form, intval($post['id']));
        }
        else
        {
            $this->formFail($form);
            //$this->processFormMessages($form->getMessages());
        }
        return new JsonModel();
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

    private function createForm()
    {
        $form = (new AnnotationBuilder())->createForm(Afis::class);
        $form->get('organisation')->setValueOptions(
            $this->em->getRepository(Organisation::class)->getAllAsArray()
        );
        return $form;
    }

    private function save($form, $id)
    {
        $afis = ($id > 0) ? $this->repo->find($id) : new Afis();
        $afis = (new DoctrineHydrator($this->em))->hydrate($form->getData(), $afis);

        if ($afis->isValid())
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
        else {
            print_r("WRONG");
        }
    }

    private function formFail($form)
    {
        $this->flashMessenger()->addErrorMessage("Données du formulaire invalides.");
        $errorString = "";
        foreach ($form->getMessages() as $fieldName => $fieldErrors)
        {
            foreach ($fieldErrors as $type => $reason)
            {
                $errorString .= "<p>";
                $errorString .= "<strong>".$fieldName."[".$type."]</strong> : ";
                $errorString .= "<cite>".$reason."</cite>";
                $errorString .= "</p>";
            }
        }
        $this->flashMessenger()->addErrorMessage($errorString);
    }

    public function testNotamAccessAction()
    {
        return new JsonModel([
            'notamAccess' => $this->notamweb->testNOTAMWeb(),
            'notamUrl' => $this->notamweb->getNotamWebUrl(),
            'notamProxy' => $this->notamweb->getNotamWebProxy(),
            'notamTimeout' => $this->notamweb->getNotamWebCurlTimeout(),
        ]);
    }

    public function getAllNotamFromCodeaction()
    {
        $code = strtoupper($this->params()->fromQuery('code'));
        $content = $this->notamweb->getFromCode($code);
        return new JsonModel([
            'notams'   => $content,
            'notamTimeout' => $this->notamweb->getNotamWebCurlTimeout(),
            'notamPlafond' => $this->notamweb->getNotamWebAeroPlafond(),
            'notamRayon' => $this->notamweb->getNotamWebAeroRayon(),
        ]);
    }
}
