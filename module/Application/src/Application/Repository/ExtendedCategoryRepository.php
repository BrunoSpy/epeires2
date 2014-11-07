<?php
namespace Application\Repository;

class ExtendedCategoryRepository extends CategoryRepository {
	
	/**
	 * Tous les évènements dont à la fois :
	 * la date début est antèrieure à maintenant
         * et la date de fin est nulle ou postèrieure à maintenant
	 */
	public function getCurrentEvents($category){
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		$qbEvents = $this->getEntityManager()->createQueryBuilder();
		$qbEvents->select(array('e', 'cat'))
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF '.$category)
                ->andWhere($qbEvents->expr()->eq('e.punctual', 'false'))
		->andWhere($qbEvents->expr()->lte('e.startdate', '?1'))
		->andWhere($qbEvents->expr()->orX(
				$qbEvents->expr()->isNull('e.enddate'),
				$qbEvents->expr()->gte('e.enddate', '?2')))
		->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
                                      2 => $now->format('Y-m-d H:i:s')));
					
		$query = $qbEvents->getQuery();
					
		return $query->getResult();
	}
	
	/**
	 * Tous les éléments prévus :
	 * - Date de début dans les 12h
	 */
	public function getPlannedEvents($category){
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		$qbEvents = $this->getEntityManager()->createQueryBuilder();
		$qbEvents->select(array('e', 'cat'))
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF '.$category)
		->andWhere($qbEvents->expr()->andX(
                                            $qbEvents->expr()->gte('e.startdate', '?1'),
                                            $qbEvents->expr()->lte('e.startdate', '?2')))
		->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
					2 => $now->add(new \DateInterval('PT12H'))->format('Y-m-d H:i:s')));
					
		$query = $qbEvents->getQuery();
					
		return $query->getResult();
	}
}