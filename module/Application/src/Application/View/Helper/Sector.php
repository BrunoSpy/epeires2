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

		$popoverfreq = "<ul id='actions-freq-'".$sector->getFrequency()->getId().">";
	//	$popoverfreq .= "<li><a href='#'>Brouillage</a></li>";
	//	$popoverfreq .= "<li><a href='#'>Perte totale</a></li>";
		$popoverfreq .= "<li><a class='switch-coverture' data-cov='1' data-freqid='".$sector->getFrequency()->getId()."' href='#'>Passage sur secours</a></li>";
		$popoverfreq .= "</ul>";
		
		$popovermain = "<ul class='actions-antenna-".$sector->getFrequency()->getMainAntenna()->getId()."'>";
		$popovermain .= "<li><a class='switch-coverture' data-cov='1' data-freqid='".$sector->getFrequency()->getId()."' href='#'>Passage sur secours</a></li>";
		$popovermain .= "<li><a class='switch-antenna' data-antenna='".$sector->getFrequency()->getMainAntenna()->getId()."' href='#'>Antenne HS</a></li>";
		$popovermain .= "</ul>";
		
		$popoverbackup = "<ul class='actions-antenna-".$sector->getFrequency()->getBackupAntenna()->getId()."'>";
		$popoverbackup .= "<li><a class='switch-coverture' data-cov='0' data-freqid='".$sector->getFrequency()->getId()."' href='#'>Retour sur normal</a></li>";
		$popoverbackup .= "<li><a class='switch-antenna' data-antenna='".$sector->getFrequency()->getBackupAntenna()->getId()."' href='#'>Antenne HS</a></li>";
		$popoverbackup .= "</ul>";
		
		$html = "<ul class=\"sector dropdown-menu\">";
		$html .= "<div class=\"sector-color frequency-".$sector->getFrequency()->getId()."\">";
		$html .= "<li class=\"sector-name\">".$sector->getName()."</li>";
		$html .= "<li class=\"sector-freq\"><a href=\"#\" data-container=\"body\" data-content=\"".$popoverfreq."\" data-toggle=\"popover\" data-html=\"true\">".$sector->getFrequency()->getValue()."</a></li>";
		$html .= "</div>";
		$html .= "<li class=\"divider\"></li>";
		$html .= "<ul class=\"antennas\">";
		$html .= "<div class=\"mainantenna-color antenna-color antenna-".$sector->getFrequency()->getMainAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" data-container=\"body\" data-content=\"".$popovermain."\" data-toggle=\"popover\" data-html=\"true\">".$sector->getFrequency()->getMainAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "<div class=\"backupantenna-color antenna-color antenna-".$sector->getFrequency()->getBackupAntenna()->getId()."\">";
		$html .= "<li><a href=\"#\" data-container=\"body\" data-content=\"".$popoverbackup."\" data-toggle=\"popover\" data-html=\"true\">".$sector->getFrequency()->getBackupAntenna()->getShortname()."</a></li>";
		$html .= "</div>";
		$html .= "</ul>";
		$html .= '</ul>';
		
		return $html;
	}
	
}