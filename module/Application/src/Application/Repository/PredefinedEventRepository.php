<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class PredefinedEventRepository extends EntityRepository {
	
	/**
	 * @return array
	 */
	public function getEventsWithCategoryAsArray($id){
		$criteria = Criteria::create()->where(Criteria::expr()->eq('category', $id));
		$criteria->andWhere(Criteria::expr()->isNull('parent'));
		$list = parent::matching($criteria);
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
	
	
}