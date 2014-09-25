<?php

namespace Core\Guard;

use \Zend\Http\Request as HttpRequest;
use \Zend\Mvc\MvcEvent;
use \ZfcRbac\Guard\AbstractGuard;

/**
 * AutoConnect users based on IP
 */
class AutoConnectGuard extends AbstractGuard {
    
    const EVENT_PRIORITY = 100;

    /**
     * List of users to autoconnect
     */
    protected $users = [];

    protected $auth;


    /**
     * @param array $ipAddresses
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    public function setAuthService(\Zend\Authentication\AuthenticationService $auth){
        $this->auth = $auth;
    }
    
    /**
     * @param  MvcEvent $event
     * @return bool
     */
    public function isGranted(MvcEvent $event)
    {
        $request = $event->getRequest();
        
        if (!$request instanceof HttpRequest) {
            return true;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }
        
        if(array_key_exists($clientIp, $this->users)){
            $user = $this->users[$clientIp];
            
            if($this->auth->hasIdentity()){
                //do nothing
            } else {
                $adapter = $this->auth->getAdapter();
                $request = new HttpRequest();
                $request->setMethod(HttpRequest::METHOD_POST);
                $request->getPost()->identity = $user['user'];
                $request->getPost()->credential = $user['password'];
                $request->setContent($request->getPost()->toString());
                                
                $result = $adapter->prepareForAuthentication($request);

                $authenticate = $this->auth->authenticate($adapter);
                
                if(!$authenticate->isValid()){
                    error_log('FAIL');
                }
                
            }
            
        }
        
        return true;
    }
    
}
