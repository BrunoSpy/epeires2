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

use Application\Controller\FormController;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Loïc Perrin
 *        
 */
class AfisController extends FormController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $this->layout()->title = "Afis";

        $allAfis = $this->forward()->dispatch('Application\Controller\Afis', [
                'action'     => 'getAll',
        ]);

        $this->layout('layout/adminlayout'); 
        return [
            'messages'  => $this->afMessages()->get(),
            'allAfis'   => $allAfis,
        ];
    }
    // public function saveAction()
    // {
    //     $save = $this->forward()->dispatch('Application\Controller\Afis', [
    //             'action'     => 'save',
    //     ]);
    //     $this->layout('layout/adminlayout');
    //     return new JsonModel();
    // }
/*
    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $radar = $this->entityManager->getRepository('Application\Entity\Radar')->find($id);
        if ($radar) {
            $this->entityManager->remove($radar);
            $this->entityManager->flush();
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
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
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
*/
}
