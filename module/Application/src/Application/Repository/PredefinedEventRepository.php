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
		$criteria->andWhere(Criteria::expr()->eq('listable', true));
		$list = parent::matching($criteria);
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
	public function getChildsAsArray($parentid){
		$criteria = Criteria::create()->where(Criteria::expr()->eq('parent', $id));
		$criteria->andWhere(Criteria::expr()->eq('listable', true));
		$criteria->orderBy('order');
		$list = parent::matching($criteria);
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
	public function getRootsAsArray($id = null){
		$criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
		if($id){
			$criteria->andWhere(Criteria::expr()->neq('id', $id));
		}
		$list = parent::matching($criteria);
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
}