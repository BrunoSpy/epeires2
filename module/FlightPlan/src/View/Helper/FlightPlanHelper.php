<?php
namespace Flightplan\View\Helper;

use Zend\View\Helper\AbstractHelper;
use FlightPlan\Entity\FlightPlan;

class FlightPlanHelper extends AbstractHelper
{
    
    public function renderRow(FlightPlan $fp)
    {
        return $this->getView()->render('flight-plan/helper/flight-plan', [
            'fp' => $fp
        ]);
    }
    
}
