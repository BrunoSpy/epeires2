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

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\Form;
use Laminas\Form\Element\Select;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class IPOHelper extends AbstractHelper
{

    private $sm;

    public function __invoke($iponumber = null)
    {
        $html = "";
        
        $auth = $this->sm->get('ZfcRbac\Service\AuthorizationService');
        
        $zfcuserauth = $this->sm->get('zfcuser_auth_service');
        
        $objectmanager = $this->sm->get('Doctrine\ORM\EntityManager');
        
        if ($zfcuserauth->hasIdentity()) {
            
            $ipos = $objectmanager->getRepository('Application\Entity\IPO')->findBy(array(
                'organisation' => $zfcuserauth->getIdentity()
                    ->getOrganisation()
                    ->getId()
            ), array(
                'name' => 'asc'
            ));
            
            $currentipo = $objectmanager->getRepository('Application\Entity\IPO')->findOneBy(array(
                'organisation' => $zfcuserauth->getIdentity()
                    ->getOrganisation()
                    ->getId(),
                'current' => true
            ));
            if ($auth->isGranted('events.mod-ipo')) {
                
                $form = new Form('ipo');
                $selectIPO = new Select('nameipo');
                $ipoArray = array();
                $ipoArray['-1'] = "Choisir ".$this->view->translate('IPO');
                foreach ($ipos as $ipo) {
                    $ipoArray[$ipo->getId()] = $ipo->getName();
                }
                
                $selectIPO->setValueOptions($ipoArray);
                if ($currentipo) {
                    $selectIPO->setAttribute('value', $currentipo->getId());
                }
                
                $form->add($selectIPO);
                
                $formView = $this->view->form();
                
                $form->setAttributes(array('class' => 'navbar-form navbar-left'));
                
                $html .= $formView->openTag($form);
                $html .= '<div class="form-group">';
                $html .= '<label>' . '<span class="glyphicon glyphicon-warning-sign"></span><b> '.$this->view->translate('IPO').' ' . ($iponumber !== null ? $iponumber : '') . ' : </b>';
                $html .= $this->view->formSelect($form->get('nameipo')->setAttribute('class', 'form-control'));
                $html .= '</div>';
                $html .= $formView->closeTag();
                
            } else {
                if ($currentipo) {
                    $html .= '<p class="navbar-text navbar-left"><span class="glyphicon glyphicon-warning-sign"></span><b> '.$this->view->translate('IPO').' ' . ($iponumber !== null ? $iponumber : '') . ' : </b><span id="iponame">' . $currentipo->getName() . '</span></p>';
                } else { 
                    $html .= '<p class="navbar-text navbar-left"><span class="glyphicon glyphicon-warning-sign"></span><b> '.$this->view->translate('IPO').' ' . ($iponumber !== null ? $iponumber : '') . ' : </b><em>Aucun '.$this->view->translate('IPO').' configuré</em></p>';
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