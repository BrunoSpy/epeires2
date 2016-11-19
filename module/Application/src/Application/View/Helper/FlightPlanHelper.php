<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Entity\FlightPlan;

class FlightPlanHelper extends AbstractHelper
{
    
    public function renderRow(FlightPlan $fp)
    {
        return $this->getView()->render('fp/helper/fp', [
            'fp' => $fp,
        ]);
    }
    
}
