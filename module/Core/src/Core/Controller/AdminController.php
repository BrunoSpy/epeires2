<?php
namespace Core\Controller;

use Zend\Console\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Entity\Impact;
use Core\Entity\Permission;
use Core\Entity\Role;
use Core\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Application\Entity\Status;
use Application\Entity\CustomFieldType;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Organisation;

class AdminController extends AbstractActionController {
	
	//TODO Move data to a config file
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
			array('name' => 'Fréquence','type' => 'frequency'),
			array('name' => 'Radar','type' => 'radar'),
			array('name' => 'Liste','type' => 'select'),
			array('name' => 'Attente','type' => 'stack'),
			array('name' => 'Vrai/Faux','type' => 'boolean')
	);
	
	
	public function initdbAction(){
		$request = $this->getRequest();

		if(!$request instanceof Request){
			throw new \RuntimeException('Action disponible uniquement par la console.');
		}
		
		$verbose =  ($request->getParam('verbose') || $request->getParam('v'));
		
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		
		$result = "";
		
		foreach (self::$impacts as $impact){
			if($verbose){
				$result .= "Ajout impact ".$impact['name'].".\n";
			}
			$entity = new Impact();
			$entity->setName($impact['name']);
			$entity->setStyle($impact['style']);
			$entity->setValue($impact['value']);
			$em->persist($entity);
		}
		
		foreach(self::$statuses as $s){
			if($verbose){
				$result .= "Ajout statut ".$s['name'].".\n";
			}
    		$status = new Status();
    		$status->setName($s['name']);
    		$status->setOpen($s['open']);
    		$status->setDisplay($s['display']);
    		$status->setDefault($s['defaut']);
    		$em->persist($status);
    	}
    	
    	foreach (self::$customfieldtypes as $c){
    		if($verbose){
    			$result .= "Ajout champ spécifique ".$c['name'].".\n";
    		}
    		$fieldtype = new CustomFieldType();
    		$fieldtype->setName($c['name']);
    		$fieldtype->setType($c['type']);
    		$em->persist($fieldtype);
    	}
    	
    	$admin = new Role();
    	$admin->setName("admin");
    	
    	foreach($this->serviceLocator->get('config')['permissions'] as $module){
    		foreach ($module as $p => $name){
    			if($verbose){
	    			$result .= "Ajout permission ".$p.".\n";
    			}
    			$permission = new Permission();
    			$permission->setName($p);
    			$admin->addPermission($permission);
    			$em->persist($permission);
    		}
    	}
    	
    	if($verbose){
   			$result .= "Ajout roles admin et anonymous.\n";
   		}

   		
   		$anon = new Role();
   		$anon->setName("guest");
   		$anon->setParent($admin);
   		$em->persist($admin);
   		$em->persist($anon);    		

   		if($verbose){
   			$result .= "Ajout utilisateur admin.\n";
   		}
   		
   		$organisation = new Organisation();
   		$organisation->setName("CRNA-X");
   		$organisation->setShortname("LFXX");
   		$organisation->setAddress("");
   		$em->persist($organisation);
   		
   		$adminuser = new User();
    	$adminuser->setUsername("Admin");
    	$adminuser->setDisplayName("Admin");
    	$adminuser->setOrganisation($organisation);
    	$adminuser->addRole($admin);
    	$adminuser->setEmail("change@email");
    	$bcrypt = new Bcrypt();
    	$bcrypt->setCost($this->getServiceLocator()->get('zfcuser_module_options')->getPasswordCost());
    	$adminuser->setPassword($bcrypt->create('adminadmin'));
		$em->persist($adminuser);
    	
    	
		$em->flush();
		
		return $result."Import des données terminé.\n";
		
	}
	
}