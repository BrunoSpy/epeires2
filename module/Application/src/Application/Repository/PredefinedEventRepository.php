<?php
namespace Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

class PredefinedEventRepository extends SortableRepository {
	
	/**
	 * @return array
	 */
	public function getEventsWithCategoryAsArray($category){	
		$criteria = Criteria::create()->where(Criteria::expr()->eq('category', $category));
		$criteria->andWhere(Criteria::expr()->isNull('parent'));
		$criteria->andWhere(Criteria::expr()->eq('listable', true));
		$criteria->orderBy(array('place' => 'ASC'));
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