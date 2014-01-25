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
		if($this->auth->hasIdentity()) {
			$html .= $this->auth->getIdentity()->getUserName();
		} else {
			$html .= "Non connecté";
		}
		$html .= '<b class="caret"></b>';
		$html .= '</a>';
		$html .= '<ul class="dropdown-menu">';
		if($this->auth->hasIdentity()) {
			$router = $this->servicemanager->get('router');
			$request = $this->servicemanager->get('request');
			if($router->match($request) && $router->match($request)->getMatchedRouteName() == 'administration'){
				$html .= "<li><a href=\"".$urlHelper('application')."\">Retour application</a></li>";
			} else {
				if($this->auth->getIdentity()->hasRole('admin')){
					$html .= "<li><a href=\"".$urlHelper('administration', array('controller'=>'home', 'action'=>'index'))."\">Administration</a></li>";
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
	
	public function setAuthService($auth){
		$this->auth = $auth;
	}

	public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator){
		$this->servicemanager = $serviceLocator;
	}
	
}