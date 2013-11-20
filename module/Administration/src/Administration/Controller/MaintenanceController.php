<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\Status;
use Application\Entity\Impact;
use Application\Entity\CustomFieldType;
use Core\Entity\Role;

class MaintenanceController extends AbstractActionController
{
	
	private static $impacts = array(
		array('value' => '100','name' => 'Majeur','style' => 'important'),
		array('value' => '80','name' => 'Significatif','style' => 'warning'),
		array('value' => '60','name' => 'Mineur','style' => 'info'),
		array('value' => '40','name' => 'Sans impact','style' => 'success'),
		array('value' => '10','name' => 'Information','style' => 'default')
	);
	
	private static $statuses = array(
		array('open' => '1','display' => '1','name' => 'Nouveau','defaut' => '1'),
		array('open' => '1','display' => '1','name' => 'Confirmé','defaut' => '0'),
		array('open' => '0','display' => '1','name' => 'Terminé','defaut' => '1'),
		array('open' => '0','display' => '1','name' => 'Annulé','defaut' => '0'),
		array('open' => '0','display' => '0','name' => 'Archivé','defaut' => '0')
	);
	
	private static $customfieldtypes = array(
		array('name' => 'Texte','type' => 'string'),
		array('name' => 'Texte long','type' => 'text'),
		array('name' => 'Secteur','type' => 'sector'),
		array('name' => 'Antenne','type' => 'antenna'),
		array('name' => 'Liste','type' => 'select'),
		array('name' => 'Attente','type' => 'stack'),
		array('name' => 'Vrai/Faux','type' => 'boolean')
	);
	
    public function indexAction()
    {
   	
        return array();
    }
    
    
    public function initdbAction(){
    	
    	
    	//status
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	//TODO load an external file
    	foreach(self::$statuses as $s){
    		$status = new Status();
    		$status->setName($s['name']);
    		$status->setOpen($s['open']);
    		$status->setDisplay($s['display']);
    		$status->setDefault($s['defaut']);
    		$objectManager->persist($status);
    		
    	}
    	foreach (self::$impacts as $i){
    		$impact = new Impact();
    		$impact->setValue($i['value']);
    		$impact->setName($i['name']);
    		$impact->setStyle($i['style']);
    		$objectManager->persist($impact);
    	}
    	
    	foreach (self::$customfieldtypes as $c){
    		$fieldtype = new CustomFieldType();
    		$fieldtype->setName($c['name']);
    		$fieldtype->setType($c['type']);
    		$objectManager->persist($fieldtype);
    	}
    	
    	$objectManager->flush();
    	
    	return new JsonModel(array());
    	
    }
}