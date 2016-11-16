<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;

class AfisMessages extends AbstractPlugin
{
    CONST MSG = [
        'switch' => [
            'success'    => 'Nouvel état de l\'AFIS "%s" : %s.',
            'error'      => 'Impossible de modifier l\'état de l\'AFIS. %s'
        ],
        'form' => [
            'error'      => 'Formulaire invalide : %s'
        ],
        'add' => [
            'success'    => 'L\'AFIS "%s" a bien été ajouté',
            'error'      => 'Impossible d\'ajouter l\'AFIS. %s',
        ],
        'edit' => [
            'success'    => 'L\'AFIS "%s" a bien été modifié',
            'error'      => 'Impossible de modifier l\'AFIS. %s',
        ],
        'del' => [
            'success'    => 'L\'AFIS "%s" a bien été supprimé',
            'error'      => 'Impossible de supprimer l\'AFIS. %s'
        ],
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

    public function add($action, $etat, array $values = []){
        if(array_key_exists($action, $this::MSG) and array_key_exists($etat, $this::MSG[$action])) {
            $fm = $this->getController()->flashMessenger();
            $method = 'add'.ucfirst($etat).'Message';
            $fm->$method(vsprintf($this::MSG[$action][$etat], $values));
        }
    }
}