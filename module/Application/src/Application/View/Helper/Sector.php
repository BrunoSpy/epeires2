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

		$popoverfreq = "<ul id='actions-freq-'".$frequency->getId().">";
	//	$popoverfreq .= "<li><a href='#'>Brouillage</a></li>";
	//	$popoverfreq .= "<li><a href='#'>Perte totale</a></li>";
		$popoverfreq .= "<li><a class='switch-coverture' data-cov='1' data-freqid='".$frequency->getId()."' href='#'>Passage sur secours</a></li>";
		$popoverfreq .= "</ul>";
		
		$popovermain = "<ul class='actions-antenna-".$frequency->getMainAntenna()->getId()."'>";
		$popovermain .= "<li><a class='switch-coverture' data-cov='1' data-freqid='".$frequency->getId()."' href='#'>Passage sur secours</a></li>";
		$popovermain .= "<li><a class='switch-antenna' data-antenna='".$frequency->getMainAntenna()->getId()."' href='#'>Antenne HS</a></li>";
		$popovermain .= "</ul>";
		
		$popoverbackup = "<ul class='actions-antenna-".$frequency->getBackupAntenna()->getId()."'>";
		$popoverbackup .= "<li><a class='switch-coverture' data-cov='0' data-freqid='".$frequency->getId()."' href='#'>Retour sur normal</a></li>";
		$popoverbackup .= "<li><a class='switch-antenna' data-antenna='".$frequency->getBackupAntenna()->getId()."' href='#'>Antenne HS</a></li>";
		$popoverbackup .= "</ul>";
		
		$html = "<ul class=\"sector dropdown-menu\">";
		$html .= "<div class=\"sector-color frequency-".$frequency->getId()."\">";
		$html .= "<li class=\"sector-name\">".$name."</li>";
		$html .= "<li class=\"sector-freq\"><a href=\"#\" data-container=\"body\" data-content=\"".$popoverfreq."\" data-toggle=\"popover\" data-html=\"true\">".$frequency->getValue()."</a></li>";
		$html .= "</div>";
		$html .= "<li class=\"divider\"></li>";
		$html .= "<ul class=\"antennas\">";
		$html .= "<div class=\"mainantenna-color antenna-color antenna-".$frequency->getMainAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" data-container=\"body\" data-content=\"".$popovermain."\" data-toggle=\"popover\" data-html=\"true\">".$frequency->getMainAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "<div class=\"backupantenna-color antenna-color antenna-".$frequency->getBackupAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" data-container=\"body\" data-content=\"".$popoverbackup."\" data-toggle=\"popover\" data-html=\"true\">".$frequency->getBackupAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "</ul>";
		
		if($frequency->getMainantennaclimax() || $frequency->getBackupantennaclimax()){
			$html .= "<ul class=\"antennas\">";
			$html .= "<div class=\"mainantenna-color antenna-color antenna-climax-color antenna-".($frequency->getMainantennaclimax() ? $frequency->getMainantennaclimax()->getId() : "")."\">";
			$html .= "<li>".($frequency->getMainantennaclimax() ? "<a href=\"#\">".$frequency->getMainantennaclimax()->getShortname()."</a>" : "")."</li>";
			$html .= "</div>";
			$html .= "<div class=\"backupantenna-color antenna-color antenna-climax-color antenna-".($frequency->getBackupantennaclimax() ? $frequency->getBackupantennaclimax()->getId() : "")."\">";
			$html .= "<li>".($frequency->getBackupantennaclimax() ? "<a href=\"#\">".$frequency->getBackupantennaclimax()->getShortname()."</a>" : "")."</li>";
			$html .= "</div>";
			$html .= "</ul>";
		}
		
		$html .= '</ul>';
		
		return $html;
	}
	
}