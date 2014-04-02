<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class CategoryRepository extends ExtendedRepository {
	
	/**
	 * @return array
	 */
	public function getRootsAsArray($id = null, $timeline = null){		
		$res = array();
		foreach ($this->getRoots($id, $timeline) as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
	public function getRoots($id = null, $timeline = null){
		$criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'));
		if($timeline){
			$criteria->andWhere(Criteria::expr()->eq('timeline', true));
		}
		if($id){
			$criteria->andWhere(Criteria::expr()->neq('id', $id));
		}
                $criteria->orderBy(array('place' => Criteria::ASC));
		$list = parent::matching($criteria);
		return $list;
	}
	
	public function getChilds($parentId = null){
		if($parentId){
			$criteria = Criteria::create()->where(Criteria::expr()->eq('parent', parent::find($parentId)));
		} else {
			$criteria = Criteria::create()->where(Criteria::expr()->neq('parent', null));
		}
		$criteria->orderBy(array('place' => Criteria::ASC));
		$list = parent::matching($criteria);
		
		return $list;
	}
	
	public function getChildsAsArray($parentId = null){
		$res = array();
		foreach ($this->getChilds($parentId) as $element) {
			$res[$element->getId()]= $element->getName();
		}
		return $res;
	}
	
}