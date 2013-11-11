<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RolesController extends AbstractActionController
{
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > Roles";
    	 
    	$config = $this->serviceLocator->get('config');
    	
        return array('config'=>$config['permissions']);
    }
    
    
}
