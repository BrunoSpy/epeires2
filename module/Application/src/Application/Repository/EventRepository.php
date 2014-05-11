<?php
namespace Application\Repository;

use Zend\Session\Container;


/**
 * Description of EventRepository
 *
 * @author soularch
 */
class EventRepository extends ExtendedRepository {
    
    /**
     * 
     * @param type $day
     * @param type $lastmodified
     * @return type
     */
    public function getEvents($userauth, $day = null, $lastmodified = null){
            	
    	$qb = $this->getEntityManager()->createQueryBuilder();
    	$qb->select(array('e', 'f'))
    	->from('Application\Entity\Event', 'e')
    	->leftJoin('e.zonefilters', 'f')
    	->andWhere($qb->expr()->isNull('e.parent'));//display only root events
    	
    	if($lastmodified){
    		$qb->andWhere($qb->expr()->gte('last_modified_on', $lastmodified));
    	} else if($day) {
    		$daystart = new \DateTime($day);
    		$daystart->setTime(0, 0, 0);
    		$dayend = new \DateTime($day);
    		$dayend->setTime(23, 59, 59);
    		$daystart = $daystart->format("Y-m-d H:i:s");
    		$dayend = $dayend->format("Y-m-d H:i:s");
    		//tous les évènements ayant une intersection non nulle avec $day
    		$qb->andWhere($qb->expr()->orX(
    				$qb->expr()->andX(
    					$qb->expr()->lt('e.startdate', '?1'),
    					$qb->expr()->orX(
    						$qb->expr()->isNull('e.enddate'),
    						$qb->expr()->gte('e.enddate', '?1')
    					)
    				),
    				$qb->expr()->andX(
    					$qb->expr()->gte('e.startdate', '?1'),
    					$qb->expr()->lte('e.startdate', '?2')
    				)
    		));
    		$qb->setParameters(array(1 => $daystart, 2 => $dayend));    		
    	} else {
    		//every open events and all events of the last 3 days
    		$now = new \DateTime('NOW');
    		$qb->andWhere($qb->expr()->orX(
    				$qb->expr()->gte('e.startdate', '?1'),
    				$qb->expr()->gte('e.enddate', '?1'),
    				$qb->expr()->in('e.status', '?2')
    		));
    		$qb->setParameters(array(1 => $now->sub(new \DateInterval('P3D'))->format('Y-m-d H:i:s'),
    				2 => array(1,2)));
    	}

    
    	//filtre par zone
    	$session = new Container('zone');
    	$zonesession = $session->zoneshortname;
    	if($userauth->hasIdentity()){
    		//on filtre soit par la valeur en session soit par l'organisation de l'utilisateur
    		//TODO gérer les evts partagés
    		if($zonesession != null){ //application d'un filtre géographique
    			if($zonesession != '0'){
    				//la variable de session peut contenir soit une orga soit une zone
    				$orga = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->findOneBy(array('shortname'=>$zonesession));
    				if($orga){
    					$qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
    				} else {
    					$zone = $this->getEntityManager()->getRepository('Application\Entity\QualificationZone')->findOneBy(array('shortname'=>$zonesession));
    					if($zone){
    						$qb->andWhere($qb->expr()->andX(
    							$qb->expr()->eq('e.organisation', $zone->getOrganisation()->getId()),
                                                        $qb->expr()->orX(
                                                            $qb->expr()->eq('f', $zone->getId()),
                                                            $qb->expr()->isNull('f.id'))
                                                        )
    						);
    					} else {
    						//throw error
    					}
    				}
    			} else {
    				//tous les evts de l'org de l'utilisateur connecté
    				$orga = $userauth->getIdentity()->getOrganisation();
    				$qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
    			}
    		} else {
    			//tous les evts de l'org de l'utilisateur connecté
    			$orga = $userauth->getIdentity()->getOrganisation();
    			$qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
    		}
    	} else {
    		//aucun filtre autre que les rôles
    	}

    	$events = $qb->getQuery()->getResult();
    	
    	$readableEvents = array();
    	
    	if($userauth->hasIdentity()){
    		$roles = $userauth->getIdentity()->getRoles();
    		foreach ($events as $event){
    			$eventroles = $event->getCategory()->getReadroles();
    			foreach ($roles as $role){
    				if($eventroles->contains($role)){
    					$readableEvents[] = $event;
    					break;
    				}
    			}
    		}
    	} else {
    		//$role = $this->getServiceLocator()->get('ZfcRbac\Options\ModuleOptions')->getGuestRole();
    		$roleentity = $this->getEntityManager()->getRepository('Core\Entity\Role')->findOneBy(array('name'=>'guest'));
    		if($roleentity){
    			foreach ($events as $event){
    				$eventroles = $event->getCategory()->getReadroles();
    				if($eventroles->contains($roleentity)){
    					$readableEvents[] = $event;
    				}
    			}
    		}
    	}
        
        return $readableEvents;
    }
    
}
