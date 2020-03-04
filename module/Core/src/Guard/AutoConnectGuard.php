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
namespace Core\Guard;

use \Laminas\Http\Request as HttpRequest;
use \Laminas\Mvc\MvcEvent;
use \ZfcRbac\Guard\AbstractGuard;

/**
 * AutoConnect users based on IP
 */
class AutoConnectGuard extends AbstractGuard
{

    const EVENT_PRIORITY = 100;

    /**
     * List of users to autoconnect
     */
    protected $users = [];

    protected $auth;

    /**
     *
     * @param array $ipAddresses            
     */
    public function __construct(?array $users)
    {
        $this->users = $users;
    }

    public function setAuthService(\Laminas\Authentication\AuthenticationService $auth)
    {
        $this->auth = $auth;
    }

    /**
     *
     * @param MvcEvent $event            
     * @return bool
     */
    public function isGranted(MvcEvent $event)
    {
        $request = $event->getRequest();
        
        if (! $request instanceof HttpRequest) {
            return true;
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }
        
        if ($this->users && array_key_exists($clientIp, $this->users)) {
            $user = $this->users[$clientIp];
            
            if ($this->auth->hasIdentity()) {
                // do nothing
            } else {
                $adapter = $this->auth->getAdapter();
                $request = new HttpRequest();
                $request->setMethod(HttpRequest::METHOD_POST);
                $request->getPost()->identity = $user['user'];
                $request->getPost()->credential = $user['password'];
                $request->setContent($request->getPost()
                    ->toString());
                
                $result = $adapter->prepareForAuthentication($request);
                
                $authenticate = $this->auth->authenticate($adapter);
                
                if (! $authenticate->isValid()) {
                    error_log('FAIL');
                }
            }
        }
        
        return true;
    }
}
