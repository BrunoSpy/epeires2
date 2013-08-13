<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfigController extends AbstractActionController
{
    public function indexAction()
    {
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Paramètres";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$viewmodel->setVariables(array('status' => $objectManager->getRepository('Application\Entity\Status')->findAll(),
    									'impacts' => $objectManager->getRepository('Application\Entity\Impact')->findAll(),
    									'fields' => $objectManager->getRepository('Application\Entity\CustomFieldType')->findAll(),
    							));
    	
        return $viewmodel;
    }
    
    
}
