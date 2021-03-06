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
namespace Core\Controller;

use Core\Entity\LoginAttempt;
use Laminas\Stdlib\ResponseInterface as Response;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class UserController extends \LmcUser\Controller\UserController
{

    protected $failedLoginMessage = 'Connexion impossible. Merci de réessayer';
    
    public function loginAction()
    {
        if ($this->lmcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()
                ->getLoginRedirectRoute());
        }
        $request = $this->getRequest();
        $form = $this->getLoginForm();
        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }
        if (! $request->isPost()) {
            return array(
                'loginForm' => $form,
                'redirect' => $redirect,
                'enableRegistration' => $this->getOptions()->getEnableRegistration()
            );
        }
        $form->setData($request->getPost());
        
        if (! $form->isValid()) {
            $this->flashMessenger()
                ->addErrorMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute($redirect));
        }
        
        // clear adapters
        $this->lmcUserAuthentication()
            ->getAuthAdapter()
            ->resetAdapters();
        $this->lmcUserAuthentication()
            ->getAuthService()
            ->clearIdentity();
        
        return $this->forward()->dispatch('coreuser', array(
            'action' => 'authenticate'
        ));
    }
    
    public function authenticateAction()
    {
        if ($this->lmcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
    
        $adapter = $this->lmcUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));
    
        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }
    
        $auth = $this->lmcUserAuthentication()->getAuthService()->authenticate($adapter);

        $om = $this->serviceLocator->get('Doctrine\ORM\EntityManager');
        $attempt = new LoginAttempt();

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }

        $attempt->setIpAdress($clientIp);

        if (!$auth->isValid()) {
            $this->flashMessenger()->addErrorMessage($this->failedLoginMessage);
            $attempt->setUsername($this->lmcUserAuthentication()->getAuthAdapter()->getEvent()->getRequest()->getPost()->identity);
            $adapter->resetAdapters();
            $om->persist($attempt);
            $om->flush();
            return $this->redirect()->toUrl(
                $this->url()->fromRoute($redirect));
        } else {
            $attempt->setUser($this->lmcUserAuthentication()->getIdentity());
            $om->persist($attempt);
            $om->flush();
        }
    
        return $this->redirect()->toUrl($this->url()->fromRoute($redirect));
    }
}