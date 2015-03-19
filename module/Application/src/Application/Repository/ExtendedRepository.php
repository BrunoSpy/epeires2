<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class ExtendedRepository extends EntityRepository {
	
	/**
	 * @return array
	 */
	public function getAllAsArray($criteria = null){
		$list = array();
		if($criteria === null) {
			$list = parent::findAll();
		} else {
			$list = parent::findBy($criteria);
		}
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
}