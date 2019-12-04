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


use Application\Entity\Briefing;
use Application\Entity\Event;
use Core\Controller\AbstractEntityManagerAwareController;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class BriefingController extends AbstractEntityManagerAwareController
{

    public function briefingAction() {
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $viewModel->setTerminal($request->isXmlHttpRequest());

        $briefing = null;
        $importantEvents = null;

        $userauth = $this->zfcUserAuthentication();
        if ($userauth != null && $userauth->hasIdentity()) {
            //get last briefing of the organisation
            $briefing = $this->getEntityManager()->getRepository(Briefing::class)->findOneBy(array('organisation' => $userauth->getIdentity()->getOrganisation()->getId()));

            $importantEvents = $this->getEntityManager()->getRepository(Event::class)->getCurrentImportantEvents($userauth);

            $regulations = $this->getEntityManager()->getRepository(Event::class)->getCurrentRegulations($userauth);

        }
        if($briefing !== null) {
            $viewModel->setVariable('informations', $briefing->getContent());
        } else {
            $viewModel->setVariable('informations', "");
        }

        $viewModel->setVariable('events', $importantEvents);
        $viewModel->setVariable('regulations', $regulations);

        return $viewModel;
    }

    public function saveAction() {
        $json = array();
        $messages = array();
        $userauth = $this->zfcUserAuthentication();
        if ($userauth != null && $userauth->hasIdentity()) {
            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                $briefing = $this->getEntityManager()->getRepository(Briefing::class)->findOneBy(array('organisation' => $userauth->getIdentity()->getOrganisation()->getId()));
                if($briefing == null) {
                    $briefing = new Briefing();
                    $briefing->setOrganisation($userauth->getIdentity()->getOrganisation());
                }
                $briefing->setContent($post['content']);
                try {
                    $this->getEntityManager()->persist($briefing);
                    $this->getEntityManager()->flush();
                    $messages['success'][] = "Informations correctement enregistrées.";
                } catch (\Exception $e) {
                    $messages['error'] = $e->getMessage();
                }
            }
        } else {
            $messages['error'][] = "Droits manquants pour modifier les informations.";
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    /**
     * Returns current briefing for the user's organisation
     */
    public function getBriefingAction() {
        $json = array();
        $json['briefing'] = "";

        $userauth = $this->zfcUserAuthentication();
        if ($userauth != null && $userauth->hasIdentity()) {
            $briefing = $this->getEntityManager()->getRepository(Briefing::class)->findOneBy(array('organisation' => $userauth->getIdentity()->getOrganisation()->getId()));
            if($briefing !== null) {
                $json['briefing'] = $briefing->getContent();
            }
        }

        return new JsonModel($json);
    }

}