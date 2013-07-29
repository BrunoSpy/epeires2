<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class ExtendedRepository extends EntityRepository {
	
	/**
	 * @return array
	 */
	public function getAllAsArray(){
		$list = parent::findAll();
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}

	
}