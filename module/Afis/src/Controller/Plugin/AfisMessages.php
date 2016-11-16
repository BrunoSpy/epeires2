<?php
namespace Afis\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;

class AfisMessages extends AbstractPlugin
{
    public function get()
    {
        $messages = [];
        $fm = $this->getController()->flashMessenger();
        if ($fm->hasSuccessMessages()) {
            $messages['success'] = $fm->getSuccessMessages();
        }

        if ($fm->hasErrorMessages()) {
            $messages['error'] = $fm->getErrorMessages();
        }
        return $messages;
    }

    public function add($action, $etat, array $values = []){
        $userMessages = $this->getController()->getServiceLocator()->get('Config')['user_messages'];
        if(array_key_exists($action, $userMessages) and array_key_exists($etat, $userMessages[$action])) {
            $fm = $this->getController()->flashMessenger();
            $method = 'add'.ucfirst($etat).'Message';
            $fm->$method(vsprintf($userMessages[$action][$etat], $values));
        }
    }
}