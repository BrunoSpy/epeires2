<?php
namespace Afis\View\Helper;

use Afis\Entity\Afis;
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
        return $this->getView()->render('afis/helper/admin', [
            'afis' => $afis
        ]);
        
    }
    
    public function renderSideNav(array $allAfis)
    {
        return $this->getView()->render('afis/helper/sidenav', [
            'allAfis' => $allAfis
        ]);
    }
    
}
