<?php

/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Controller;

use Core\Controller\AbstractEntityManagerAwareController;
use Doctrine\ORM\EntityManager;
use DOMPDFModule\View\Model\PdfModel;
use Doctrine\Common\Collections\Criteria;

/**
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class ReportController extends AbstractEntityManagerAwareController
{

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    public function dailyAction()
    {
        $day = $this->params()->fromQuery('day', null);
        
        if ($day) {
            $daystart = new \DateTime($day);
            $offset = $daystart->getTimezone()->getOffset($daystart);
            $daystart->setTimezone(new \DateTimeZone('UTC'));
            $daystart->add(new \DateInterval("PT" . $offset . "S"));
            $daystart->setTime(0, 0, 0);

            $dayend = new \DateTime($day);
            $dayend->setTimezone(new \DateTimeZone('UTC'));
            $dayend->add(new \DateInterval("PT" . $offset . "S"));
            $dayend->setTime(23, 59, 59);

            $criteria = Criteria::create()->where(Criteria::expr()->isNull('parent'))
                ->andWhere(Criteria::expr()->eq('system', false))
                ->orderBy(array(
                'place' => Criteria::ASC
            ));
            
            $cats = $this->getEntityManager()->getRepository('Application\Entity\Category')->matching($criteria);
            
            $eventsbycats = array();
            
            foreach ($cats as $cat) {
                $category = array();
                $category['name'] = $cat->getName();

                //évènements lisibles par l'utilisateur, du jour spécifié, de la catégorie et non supprimés
                $category['events'] = $this->getEntityManager()
                    ->getRepository('Application\Entity\Event')
                    ->getEvents($this->lmcUserAuthentication(), $day, null, null, true, array($cat->getId()), array(1,2,3,4));
                $category['childs'] = array();
                foreach ($cat->getChildren() as $subcat) {
                    $subcategory = array();
                    $subcategory['events'] = $this->getEntityManager()
                        ->getRepository('Application\Entity\Event')
                        ->getEvents($this->lmcUserAuthentication(), $day, null, null, true, array($subcat->getId()), array(1,2,3,4));
                    $subcategory['name'] = $subcat->getName();
                    $category['childs'][] = $subcategory;
                }
                $eventsbycats[] = $category;
            }
            
            $pdf = new PdfModel();
            $pdf->setVariables(array(
                'events' => $eventsbycats,
                'day' => $day,
                'logs' => $this->getEntityManager()->getRepository('Application\Entity\Log'),
                'opsups' => $this->getEntityManager()->getRepository('Application\Entity\Log')->getOpSupsChanges($daystart, $dayend, false, 'ASC')
            ));
            $pdf->setOption('paperSize', 'a4');
            
            $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');
            $pdf->setOption('filename', 'rapport_du_' . $formatter->format(new \DateTime($day)));

            return $pdf;
        } else {
            // erreur
        }
    }

}
