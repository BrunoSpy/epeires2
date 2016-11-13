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


use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Entity\InterrogationPlan;
use Application\Entity\Field;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractActionController
{
    public function indexAction()
    {
        parent::indexAction();  
        return (new ViewModel())
            ->setVariables([
                'messages' => $this->SarBeaconsMessages()->get()
            ]);
    }

    public function formAction() 
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $intPlan = $this->sarBeaconsSGBD($em)->get($request->getPost()['id']);
            $form->setData($intPlan->getArrayCopy());
        }
        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $form
            ]);
    }

    public function getForm() 
    {
        return (new AnnotationBuilder())->createForm(InterrogationPlan::class);
    }

    public function sauverAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();

        $id = null;

        if ($request->isPost()) {
            $pdatas = $request->getPost('datas');
            $ppio = $request->getPost('pio');

            $datasIntPlan = []; 
            parse_str($pdatas, $datasIntPlan);

            $fields = [];
            if(is_array($ppio)){
                foreach ($ppio as $i => $field) {
                    if($field != NULL) {
                        $f = new Field();
                        $f->setName($field['name']);
                        $f->setComment($field['comment']);
                        $f->setIntTime(new \DateTime());
                        $fields[] = $f;
                    }
                }
            }
            $datasIntPlan['fields'] = $fields;
            $id = $this->SarBeaconsSGBD($em)->save($datasIntPlan);
        }
        return new JsonModel([
            'id' => $id
        ]);
    }
}