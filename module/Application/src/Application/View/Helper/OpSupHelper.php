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
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\Form;
use Zend\Form\Element\Select;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class OpSupHelper extends AbstractHelper
{

    private $sm;

    public function __invoke($id)
    {
        $html = "";
        
        $auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');
        
        $zfcuserauth = $this->sm->get('zfcuser_auth_service');
        
        $objectmanager = $this->sm->get('Doctrine\ORM\EntityManager');

        $type = $objectmanager->getRepository('Application\Entity\OpSupType')->find($id);

        if ($zfcuserauth->hasIdentity()) {
            
            $criteria = array();
            $criteria['organisation'] = $zfcuserauth->getIdentity()
                ->getOrganisation()
                ->getId();

            $criteria['type'] = $id;

            $query = $objectmanager->createQueryBuilder();
            $query->select('o')
                ->from('Application\Entity\OperationalSupervisor', 'o')
                ->where('o.type = ?1')
                ->groupBy('o.zone')
                ->setParameter(1, $id);


            if ($zfcuserauth->getIdentity()->getZone()) {
                $query->andWhere($query->expr()->eq('o.zone', '?2'))
                    ->setParameter(2, $zfcuserauth->getIdentity()->getZone()->getId());
            }

            $zones = $query->getQuery()->getResult();

            foreach ($zones as $result) {

                $criteria['zone'] = $result->getZone()->getId();

                $opsups = $objectmanager->getRepository('Application\Entity\OperationalSupervisor')->findBy($criteria, array(
                    'name' => 'asc'
                ));

                $currentopsup = $objectmanager->getRepository('Application\Entity\OperationalSupervisor')->findOneBy(array(
                    'organisation' => $zfcuserauth->getIdentity()
                        ->getOrganisation()
                        ->getId(),
                    'zone' => $result->getZone()->getId(),
                    'current' => true
                ));

                if ($auth->isGranted('events.mod-opsup')) {

                    $form = new Form();
                    $selectOpSup = new Select('nameopsup');
                    $opsupArray = array();
                    $opsupArray['-1'] = "Choisir Op Sup";
                    foreach ($opsups as $opsup) {
                        $opsupArray[$opsup->getId()] = $opsup->getName();
                    }

                    $selectOpSup->setValueOptions($opsupArray);


                    if ($currentopsup) {
                        $selectOpSup->setAttribute('value', $currentopsup->getId());
                    }

                    $form->add($selectOpSup);

                    $formView = $this->view->form();

                    $form->setAttributes(array('class' => 'navbar-form navbar-left opsup-form'));

                    $html .= $formView->openTag($form);
                    $html .= '<div class="form-group">';
                    $html .= '<label for="nameopsup">';
                    $html .= ' <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <b>'
                        . $type->getName() . (count($zones) > 1 ? ' ('.$result->getZone()->getShortname().')' : '')
                        .' : </b>';

                    $html .= '<b class="caret"></b></label>';
                    $html .= $this->view->formSelect($form->get('nameopsup')->setAttribute('class', 'form-control'));
                    $html .= '</div>';
                    $html .= $formView->closeTag();
                } else {
                    if ($currentopsup) {
                        $html .= '<p class="navbar-text navbar-left" style="margin-left: 0px">'
                            . '<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <b>'
                            . $type->getName() . (count($zones) > 1 ? ' ('.$result->getZone()->getShortname().')' : '')
                            .' : </b>'
                            . $currentopsup->getName() . '</p>';
                    } else {
                        $html .= '<p class="navbar-text navbar-left" style="margin-left: 0px"><em>Aucun Op Sup configuré</em></p>';
                    }
                }
            }

        } else {
            $html .= '<p class="navbar-text navbar-left"><em>Connexion nécessaire</em></p>';
        }
        return $html;
    }

    public function setServiceManager($servicemanager)
    {
        $this->sm = $servicemanager;
    }
}