<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Entity\InterrogationPlan;

class SarBeaconsHelper extends AbstractHelper
{
    
    public function renderIp($ipArray)
    {
        return $this->getView()->render('sar-beacons/helper/ip', [
            'ip' => $ipArray,
        ]);
    }
    
}
