<?php
namespace Application\Services;

class EventService{
	/**
	 * Service Manager
	 */
	protected $sm;
	
	/**
	 * Entity Manager
	 */
	private $em;
	
	public function setEntityManager(\Doctrine\ORM\EntityManager $em){
		$this->em = $em;
	}
	
	/**
	 * Get the name of an event depending on the title field of the category.
	 * If no title field is set, returns the event's id
	 * @param $event
	 */
	public function getName($event){	

		if($event instanceof \Application\Entity\PredefinedEvent){
			if($event->getParent() == null && $event->getName()){
				return $event->getName();
			}
		}
		
		$name = $event->getId();
		
		$category = $event->getCategory();
		
		$titlefield = $category->getFieldname();
		if($titlefield){
			foreach($event->getCustomFieldsValues() as $fieldvalue){
				if($fieldvalue->getCustomField()->getId() == $titlefield->getId()){
					
					switch ($fieldvalue->getCustomField()->getType()->getType()) {
						case 'string':
							$name = $fieldvalue->getValue();
							break;
						case 'text':
							//forbidden
							break;
						case 'sector':
							$sector = $this->em->getRepository('Application\Entity\Sector')->find($fieldvalue->getValue());
							if($sector){
								$name = $sector->getName(); 
							}
							break;
						case 'antenna':
							$antenna = $this->em->getRepository('Application\Entity\Antenna')->find($fieldvalue->getValue());
							if($antenna){
								$name = $antenna->getName();
							}
							break;
						case 'select':
							$defaultvalue = $fieldvalue->getCustomField()->getDefaultValue();
							if($defaultvalue && $fieldvalue->getValue()) {
								$values = explode(PHP_EOL, $defaultvalue);
								if(count($values) >= $fieldvalue->getValue()){
									$name = $values[$fieldvalue->getValue()];
								}
							}
							break;
						case 'stack':
							$stack = $this->em->getRepository('Application\Entity\Stack')->find($fieldvalue->getValue());
							if($stack){
								$name = $stack->getName();
							}
							break;
						case 'boolean':
							//forbidden
							break;
						default:
							;
							break;
					}
				}
			}
		}
		return $name;
	}
		
	/**
	 * Returns an array :
	 * datetime => array(array ('fieldname', 'new value', 'user'))
	 * @param Application\Entity\Event $event
	 */
	public function getHistory($event){
		$history = array();

		$repo = $this->em->getRepository('Application\Entity\Log');
		$logentries = $repo->getLogEntries($event);
				
		if(count($logentries) > 1 && $logentries[count($logentries)-1]->getAction() == "create" ){
			foreach (array_reverse($logentries) as $logentry){
				foreach($logentry as $key => $value){
					
				}
			}
		}
		
	}
	
}