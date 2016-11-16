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
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Entity\InterrogationPlan;
use Application\Entity\Field;
use Application\Form\SarBeaconsForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractEntityManagerAwareController
{
    // public function indexAction()
    // {
    //     parent::indexAction();  
    //     // return (new ViewModel())
    //     //     ->setVariables([
    //     //         'messages' => $this->SarBeaconsMessages()->get()
    //     //     ]);
    // }

    public function formAction() 
    {
        $post = $this->getRequest()->getPost();
        $intPlan = $this->sbSGBD($this->getEntityManager())->get($post['id']);
        $intPlan->setLatitude($post['lat']);
        $intPlan->setLongitude($post['lon']);     

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => (new SarBeaconsForm($this->getEntityManager()))->getForm()->setData($intPlan->getArrayCopy())
            ]);
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $pdatas = $request->getPost('datas');
        $ppio = $request->getPost('pio');

        $datasIntPlan = []; 
        parse_str($pdatas, $datasIntPlan);

        $fields = [];
        // if (is_array($ppio)) {
            foreach ($ppio as $i => $field) 
            {
                $f = new Field($field);
                if($f->isValid()) $fields[] = $f;
            }
        // }
        $datasIntPlan['fields'] = $fields;

        return new JsonModel($this->sbSGBD($this->getEntityManager())->save($datasIntPlan));
    }

    public function listAction() {

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => $this->sbSGBD($this->getEntityManager())
                    ->getAll([
                        'where' => '',
                        'order' => [
                            'startTime' => 'DESC'
                        ]
                    ])
            ]);
    }

    public function getAction() {

        $post = $this->getRequest()->getPost();
        $intPlan = $this->sbSGBD($this->getEntityManager())->get($post['id']);
        return new JsonModel($intPlan->getArrayCopy());
    }

}