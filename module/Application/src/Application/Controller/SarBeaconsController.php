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

use Application\Entity\InterrogationPlan;
use Application\Entity\Field;
use Application\Form\SarBeaconsForm;

/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractEntityManagerAwareController
{
    private $em, $form;
    public static $class = InterrogationPlan::class;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->form = (new AnnotationBuilder())->createForm(InterrogationPlan::class);
    }

    public function getEntityManager() {
        return $this->em;
    }

    public function getForm() {
        return $this->form;   
    }

    public function formAction() 
    {
        // TODO if (!$this->authSarBeacons('write')) return new JsonModel();
        $post = $this->getRequest()->getPost();
        $id = intval($post['id']);

        $intPlan = $this->sgbd()->get($id);
        $intPlan->setLatitude($post['lat']);
        $intPlan->setLongitude($post['lon']);     
        $this->form->bind($intPlan);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
            ]);
    }

    // TODO BOF BOF
    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        $pdatas = $post['datas'];
        $ppio = $post['pio'];

        $datasIntPlan = []; 
        parse_str($pdatas, $datasIntPlan);

        $fields = [];
        foreach ($ppio as $i => $field) 
        {
            $f = new Field($field);
            if($f->isValid()) $fields[] = $f;
        }
        $datasIntPlan['fields'] = $fields;

        $result = $this->sgbd()->save($datasIntPlan);  

        return new JsonModel($result);
    }

    public function listAction() 
    {
        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => $this->sgbd()
                    ->getBy([
                        'where' => [],
                        'order' => [
                            'startTime' => 'DESC'
                        ],
                        'limit' => 10
                    ])
            ]);
    }

    public function getAction() 
    {
        $post = $this->getRequest()->getPost();
        $intPlan = $this->sbSGBD($this->getEntityManager())->get($post['id']);
        
        return new JsonModel($intPlan->getArrayCopy());
    }

}