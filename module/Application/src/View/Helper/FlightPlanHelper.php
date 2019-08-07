<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class FlightPlanHelper extends AbstractHelper
{
    public function renderFlightPlan($flightplan, $fields)
    {
        return $this->getView()->render(
            'flight-plans/helpers/flight-plan', [
                'flightplan' => $flightplan,
                'fields' => $fields
            ]);
    }

    public function renderAlert($flightplan)
    {
        return $this->getView()->render(
            'flight-plans/helpers/alert', [
                'flightplan' => $flightplan
            ]);
    }
}
