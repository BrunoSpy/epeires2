<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Application\Entity\Organisation;

class QualificationZoneRepository extends EntityRepository {
	
	/**
	 * @return array
	 */
	public function getAllAsArray(Organisation $organisation = null){
		$list = array();
		if($organisation){
			$criteria = Criteria::create()->where(Criteria::expr()->eq('organisation', $organisation));
			$list = parent::matching($criteria);
		} else {
			$list = parent::findAll();
		}
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}

	
}