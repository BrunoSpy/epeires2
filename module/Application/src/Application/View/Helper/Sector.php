<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Application\Entity\Frequency;

class Sector extends AbstractHelper {
	
	public function __invoke(Frequency $frequency, $name){
		
		$html = "<ul class=\"sector dropdown-menu\">";
		$html .= "<div class=\"sector-color frequency-".$frequency->getId()."\">";
		$html .= "<li class=\"sector-name\">".$name."</li>";
		$html .= "<li class=\"sector-freq\"><a href=\"#\" id=\"actions-freq-".$frequency->getId()."\" class=\"actions-freq\" data-freq=\"".$frequency->getId()."\">".$frequency->getValue()."</a></li>";
		$html .= "</div>";
		$html .= "<li class=\"divider\"></li>";
		$html .= "<ul class=\"antennas\">";
		$html .= "<div data-antennaid=\"".$frequency->getMainantenna()->getId()."\" class=\"mainantenna-color antenna-color antenna-".$frequency->getMainAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" class=\"actions-antenna\" data-id=\"".$frequency->getMainantenna()->getId()."\">".$frequency->getMainAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "<div data-antennaid=\"".$frequency->getBackupantenna()->getId()."\" class=\"backupantenna-color antenna-color antenna-".$frequency->getBackupAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" class=\"actions-antenna\" data-id=\"".$frequency->getBackupantenna()->getId()."\">".$frequency->getBackupAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "</ul>";
		
		if($frequency->getMainantennaclimax() || $frequency->getBackupantennaclimax()){
			$html .= "<ul class=\"antennas\">";
			$html .= "<div data-antennaid=\"".($frequency->getMainantennaclimax() ? $frequency->getMainantennaclimax()->getId() : "")."\" class=\"mainantenna-color antenna-color antenna-climax-color antenna-".($frequency->getMainantennaclimax() ? $frequency->getMainantennaclimax()->getId() : "")."\">";
			$html .= "<li>".($frequency->getMainantennaclimax() ? "<a href=\"#\" class=\"actions-antenna\" data-id=\"".$frequency->getMainantennaclimax()->getId()."\">".$frequency->getMainantennaclimax()->getShortname()."</a>" : "")."</li>";
			$html .= "</div>";
			$html .= "<div data-antennaid=\"".($frequency->getBackupantennaclimax() ? $frequency->getBackupantennaclimax()->getId() : "")."\" class=\"backupantenna-color antenna-color antenna-climax-color antenna-".($frequency->getBackupantennaclimax() ? $frequency->getBackupantennaclimax()->getId() : "")."\">";
			$html .= "<li>".($frequency->getBackupantennaclimax() ? "<a href=\"#\" class=\"actions-antenna\" data-id=\"".$frequency->getBackupantennaclimax()->getId()."\">".$frequency->getBackupantennaclimax()->getShortname()."</a>" : "")."</li>";
			$html .= "</div>";
			$html .= "</ul>";
		}
		
		$html .= '</ul>';
		
		return $html;
	}
	
}