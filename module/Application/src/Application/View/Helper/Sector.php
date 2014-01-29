<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class Sector extends AbstractHelper {
	
	public function __invoke(\Application\Entity\Sector $sector){

		$html = "<ul class=\"sector dropdown-menu\">";
		$html .= "<div class=\"sector-color\">";
		$html .= "<li class=\"sector-name\">".$sector->getName()."</li>";
		$html .= "<li class=\"sector-freq\">".$sector->getFrequency()->getValue()."</li>";
		$html .= "</div>";
		$html .= "<li class=\"divider\"></li>";
		$html .= "<ul class=\"antennas\">";
		$html .= "<li><a href=\"#\">".$sector->getFrequency()->getMainAntenna()->getShortname()."</a></li>";
		$html .= "<li><a href=\"#\">".$sector->getFrequency()->getBackupAntenna()->getShortname()."</a></li>";
		$html .= "</ul>";
		$html .= '</ul>';
		
		return $html;
	}
	
}