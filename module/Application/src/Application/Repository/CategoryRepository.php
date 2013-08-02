<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class CategoryRepository extends EntityRepository {
	
	/**
	 * @return array
	 */
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
	
	public function getChildsAsArray($parentId){
		$criteria = Criteria::create()->where(Criteria::expr()->eq('parent', $parentId));
		$list = parent::matching($criteria);
		$res = array();
		foreach ($list as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
}