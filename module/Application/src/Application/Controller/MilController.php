<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;


class MilController extends TabController {

    public function indexAction() {

        parent::indexAction();

        $viewmodel = new ViewModel();

        $return = array();

        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['errorMessages'] = $this->flashMessenger()->getErrorMessages();
        }

        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['successMessages'] = $this->flashMessenger()->getSuccessMessages();
        }

        $this->flashMessenger()->clearMessages();

        $viewmodel->setVariables(array('messages' => $return));

        return $viewmodel;
    }

    public function importNMB2BAction(){
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $j = $request->getParam('delta');
        
        $org = $request->getParam('orgshortname');
        
        $organisation = $objectManager->getRepository('Application\Entity\Organisation')->findOneBy(array('shortname' => $org));
        
        if(!$organisation){
            throw new \RuntimeException('Unable to find organisation.');
        } 
        
        $username = $request->getParam('username');
        
        $user = $objectManager->getRepository('Core\Entity\User')->findOneBy(array('username' => $username));
        
        if(!$user){
            throw new \RuntimeException('Unable to find user.');
        }
        
        $day = new \DateTime('now');
        if($j){
            if($j > 0){
                $day->add(new \DateInterval('P'.$j.'D'));
            } else {
                $j = -$j;
                $interval = new \DateInterval('P'.$j.'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        
        $nmservice = $this->serviceLocator->get('nmb2b');
        
        $eaupchain = new \Core\NMB2B\EAUPChain($nmservice->getEAUPCHain($day));
        $lastSequence = $eaupchain->getLastSequenceNumber();
        $milcats = $objectManager->getRepository('Application\Entity\MilCategory')->findBy(array('nmB2B' => true));
        for ($i = 1; $i <= $lastSequence; $i++) {
            $eauprsas = new \Core\NMB2B\EAUPRSAs($nmservice->getEAUPRSA(NULL, $day, $i));
            foreach ($milcats as $cat) {
                $objectManager->getRepository('Application\Entity\Event')->addZoneMilEvents($eauprsas, $cat, $organisation, $user);
            }
        }
    }
	
}