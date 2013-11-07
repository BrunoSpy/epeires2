<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractActionController
{
    public function indexAction()
    {
   	
    	$config = $this->getServiceLocator()->get('Config');
    	
    	error_log(print_r($config['permissions'], true));
    	
        return array();
    }
    
    
}
