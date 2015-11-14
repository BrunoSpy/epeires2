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

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class UserController extends \ZfcUser\Controller\UserController
{

    protected $failedLoginMessage = 'Connexion impossible. Merci de réessayer';
    
    public function loginAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
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
        $this->zfcUserAuthentication()
            ->getAuthAdapter()
            ->resetAdapters();
        $this->zfcUserAuthentication()
            ->getAuthService()
            ->clearIdentity();
        
        return $this->forward()->dispatch('coreuser', array(
            'action' => 'authenticate'
        ));
    }
    
    public function authenticateAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
    
        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));
    
        $result = $adapter->prepareForAuthentication($this->getRequest());
    
        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }
    
        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);
    
        if (!$auth->isValid()) {
            $this->flashMessenger()->addErrorMessage($this->failedLoginMessage);
            $adapter->resetAdapters();
            return $this->redirect()->toUrl(
                $this->url()->fromRoute($redirect));
        }
    
        return $this->redirect()->toUrl($this->url()->fromRoute($redirect));
    }
}