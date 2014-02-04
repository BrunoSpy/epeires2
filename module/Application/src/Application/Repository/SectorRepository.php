<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class SectorRepository extends ExtendedRepository {
	
	/**
	 * @return array
	 */
	public function getUnsetSectorsAsArray(){
		$list = parent::findAll();
		$res = array();
		foreach ($list as $element) {
			if($element->getFrequency() == null){
				$res[$element->getId()]= $element->getName();
			}
		}
		return $res;
	}

	
}