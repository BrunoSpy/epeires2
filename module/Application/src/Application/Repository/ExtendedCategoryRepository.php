<?php
namespace Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class ExtendedCategoryRepository extends CategoryRepository {
	
	/**
	 * Tous les évènements antenne dont à la fois :
	 * la date début et antèrieure à maintenant
	 * la date de fin est nulle ou postèrieure à maintenant
	 * le status est soit confirmé soit terminé
	 */
	public function getCurrentEvents($category){
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		$qbEvents = $this->getEntityManager()->createQueryBuilder();
		$qbEvents->select(array('e', 'cat'))
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF '.$category)
		->andWhere($qbEvents->expr()->lte('e.startdate', '?1'))
		->andWhere($qbEvents->expr()->orX(
				$qbEvents->expr()->isNull('e.enddate'),
				$qbEvents->expr()->gte('e.enddate', '?2')))
		->andWhere($qbEvents->expr()->in('e.status', array(2,3)))
		->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
						2 => $now->format('Y-m-d H:i:s')));
					
		$query = $qbEvents->getQuery();
					
		return $query->getResult();
	}
	
}