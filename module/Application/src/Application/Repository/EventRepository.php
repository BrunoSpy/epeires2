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
    public function getEvents($userauth, $day = null, $lastmodified = null, $orderbycat = false){
            	
        $parameters = array();
        
    	$qb = $this->getEntityManager()->createQueryBuilder();
    	$qb->select(array('e', 'f'))
    	->from('Application\Entity\Event', 'e')
    	->leftJoin('e.zonefilters', 'f')
    	->andWhere($qb->expr()->isNull('e.parent'));//display only root events
    	
        //restriction à tous les evts modifiés depuis $lastmodified
    	if($lastmodified){
                $lastmodified = new \DateTime($lastmodified);
    		$qb->andWhere($qb->expr()->gte('e.last_modified_on', '?3'));
                $parameters[3] = $lastmodified->format("Y-m-d H:i:s");
    	}
        
        if($day) {
    		$daystart = new \DateTime($day);
    		$daystart->setTime(0, 0, 0);
    		$dayend = new \DateTime($day);
    		$dayend->setTime(23, 59, 59);
    		$daystart = $daystart->format("Y-m-d H:i:s");
    		$dayend = $dayend->format("Y-m-d H:i:s");
    		//tous les évènements ayant une intersection non nulle avec $day
    		$qb->andWhere($qb->expr()->orX(
                        //evt dont la date de début est le bon jour : inclus les ponctuels
                        $qb->expr()->andX(
                                $qb->expr()->gte('e.startdate', '?1'),
                                $qb->expr()->lte('e.startdate', '?2')
                                ),
                        //evt dont la date de début est passée : forcément non ponctuels
                        $qb->expr()->andX(
                                $qb->expr()->eq('e.punctual', 'false'),
                                $qb->expr()->lt('e.startdate', '?1'),
                                $qb->expr()->orX(
                                        $qb->expr()->isNull('e.enddate'),
    					$qb->expr()->gte('e.enddate', '?1')
    					)
                                )	
                    )
                );
                $parameters[1] = $daystart;
                $parameters[2] = $dayend;
    		$qb->setParameters($parameters);    		
    	} else {
    		//every open events and all events of the last 3 days
    		$now = new \DateTime('NOW');
    		$qb->andWhere($qb->expr()->orX(
    				$qb->expr()->gte('e.startdate', '?1'),
    				$qb->expr()->gte('e.enddate', '?1'),
    				$qb->expr()->in('e.status', '?2')
    		));
                $parameters[1] = $now->sub(new \DateInterval('P3D'))->format('Y-m-d H:i:s');
                $parameters[2] = array(1,2);
    		$qb->setParameters($parameters);
    	}
    
    	//filtre par zone
    	$session = new Container('zone');
    	$zonesession = $session->zoneshortname;
    	if($userauth && $userauth->hasIdentity()){
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

        if($orderbycat){
            $qb->addOrderBy('e.category')
               ->addOrderBy('e.startdate');
        }
        
    	$events = $qb->getQuery()->getResult();
    	
    	$readableEvents = array();
    	
    	if($userauth != null && $userauth->hasIdentity()){
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
    	} else if($userauth != null) {
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
    	} else {
            $readableEvents = $events;
        }
        
        return $readableEvents;
    }
    
}
