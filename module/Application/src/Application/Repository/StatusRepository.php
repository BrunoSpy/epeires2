<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository {
	
	public function getAllAsArray(){
		$list = parent::findAll();
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
}