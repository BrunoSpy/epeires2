<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Core\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class UserMenu extends AbstractHelper implements ServiceManagerAwareInterface {
	
	private $auth;
	
	private $servicemanager;
	
	public function __invoke(){

		$urlHelper = $this->view->plugin('url');
		
		$html = '<li class="dropdown">';
		$html .= '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
		$html .= '<i class="icon-user"></i> ';
		if($this->auth->getIdentity() != null) {
			$html .= $this->auth->getIdentity()->getUserName();
		} else {
			$html .= "Non connecté";
		}
		$html .= '<b class="caret"></b>';
		$html .= '</a>';
		$html .= '<ul class="dropdown-menu">';
		if($this->auth->getIdentity() != null) {
			$router = $this->servicemanager->get('router');
			$request = $this->servicemanager->get('request');
                        if($router->match($request) && $router->match($request)->getMatchedRouteName() == 'ipo'){
                            $html .= "<li><a href=\"".$urlHelper('application')."\">Interface OPE</a></li>";
                        } else {
                            if($this->auth->isGranted('ipo.read')){
                                $html .= "<li><a href=\"".$urlHelper('ipo')."\">Interface IPO</a></li>";
                            }
                        }
			if($router->match($request) && $router->match($request)->getMatchedRouteName() == 'administration'){
				$html .= "<li><a href=\"".$urlHelper('application')."\">Interface OPE</a></li>";
			} else {
				if($this->auth->getIdentity()->hasRole('admin')){
					$html .= "<li><a href=\"".$urlHelper('administration', array('controller'=>'home', 'action'=>'index'))."\">Interface administration</a></li>";
				}
			}
		    $html .= "<li><a href=\"".$urlHelper('zfcuser/logout')."?redirect=".$urlHelper('application')."\">Se déconnecter</a></li>";
		} else {
		    $html .= "<li><a href=\"#loginwindow\" data-toggle=\"modal\" >Se connecter</a></li>";
		}
		$html .= '</ul>';
		$html .= '</li>';

		return $html;
		
	}
	
	public function setAuthService(\ZfcRbac\Service\AuthorizationService $auth){
		$this->auth = $auth;
	}

	public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator){
		$this->servicemanager = $serviceLocator;
	}
	
}