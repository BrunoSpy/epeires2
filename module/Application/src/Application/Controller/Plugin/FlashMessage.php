<?php
namespace Application\Controller\Plugin;

use Zend\ServiceManager\ServiceLocatorInterface as Locator;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;

class FlashMessage extends AbstractPlugin
{
    CONST MSG = [
        'afis' => [
            'switch' => [
                'success'    => 'Nouvel état de l\'AFIS "%s" : %s.',
                'error'      => 'Impossible de modifier l\'état de l\'AFIS. %s'
            ],
            'edit' => [
                'success'    => 'L\'AFIS "%s" a bien été enregistré',
                'error'      => 'Impossible d\'enregistrer l\'AFIS. %s'
            ],
            'del' => [
                'success'    => 'L\'AFIS "%s" a bien été supprimé',
                'error'      => 'Impossible de supprimer l\'AFIS. %s'
            ],
        ],
        'fp' => [
            'edit' => [
                'success'    => 'Le plan de vol d\'indicatif "%s" a bien été enregistré',
                'error'      => 'Impossible d\'enregistrer le plan de vol. %s'
            ],
            'del' => [
                'success'    => 'Le plan de vol d\'indicatif "%s" a bien été supprimé',
                'error'      => 'Impossible de supprimer le plan de vol. %s'
            ],
        ]
    ];

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

    public function add($obj, $action, $etat, array $values = []){
        if(array_key_exists($obj, $this::MSG) && array_key_exists($action, $this::MSG[$obj]) and array_key_exists($etat, $this::MSG[$obj][$action])) 
        {
            $fm = $this->getController()->flashMessenger();
            $method = 'add'.ucfirst($etat).'Message';
            $fm->$method(vsprintf($this::MSG[$obj][$action][$etat], $values));
        }
    }
}