<?php
namespace Application\View\Helper;

use Application\Entity\Afis;
use Zend\View\Helper\AbstractHelper;

class AfisHelper extends AbstractHelper
{
    
    public function renderRow(Afis $afis)
    {
        return $this->getView()->render('afis/helper/afis', [
            'afis' => $afis
        ]);
    }
    
    public function renderAdminRow(Afis $afis)
    {
        return $this->getView()->render('afis/helper/afadmin', [
            'afis' => $afis
        ]);
    }
    
}
