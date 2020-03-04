<?php
namespace Application\View\Helper;

use Application\Entity\Afis;
use Laminas\View\Helper\AbstractHelper;

class AfisHelper extends AbstractHelper
{
    
    public function renderRow($afis)
    {
        return $this->getView()->render('afis/helper/afis', [
            'afis' => $afis,
        ]);
    }
    
    public function renderAdminRow($afis)
    {
        return $this->getView()->render('afis/helper/afadmin', [
            'afis' => $afis
        ]);
    }
    
}
