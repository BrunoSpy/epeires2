<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Services;

use Application\Entity\AntennaCategory;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Bruno Spyckerelle
 */
class CustomFieldService
{

    /**
     * Entity Manager
     */
    private $em;

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get the name of a customfield, depends on the customfield type
     * 
     * @param $customfield
     * @return string
     */
    public function getFormattedValue(\Application\Entity\CustomField $customfield, $fieldvalue)
    {
        $name = null;
        switch ($customfield->getType()->getType()) {
            case 'string':
                $name = $fieldvalue;
                break;
            case 'text':
                $name = $fieldvalue;
                break;
            case 'sector':
                if ($customfield->isMultiple()) {
                    $sectors = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($sectors as $s) {
                        $sector = $this->em->getRepository('Application\Entity\Sector')->find($s);
                        if ($sector) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $sector->getName() . " ";
                        }
                    }
                } else {
                    $sector = $this->em->getRepository('Application\Entity\Sector')->find($fieldvalue);
                    if ($sector) {
                        $name = $sector->getName();
                    }
                }
                break;
            case 'antenna':
                if ($customfield->isMultiple()) {
                    $antennas = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($antennas as $a) {
                        $antenna = $this->em->getRepository('Application\Entity\Antenna')->find($a);
                        if ($antenna) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $antenna->getName() . " ";
                        }
                    }
                } else {
                    $antenna = $this->em->getRepository('Application\Entity\Antenna')->find($fieldvalue);
                    if ($antenna) {
                        $name = $antenna->getName();
                    }
                }
                break;
            case 'frequency':
                if ($customfield->isMultiple()) {
                    $frequencies = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($frequencies as $freq) {
                        $frequency = $this->em->getRepository('Application\Entity\Frequency')->find($freq);
                        if ($frequency) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $frequency->getName() . " ";
                        }
                    }
                } else {
                    $frequency = $this->em->getRepository('Application\Entity\Frequency')->find($fieldvalue);
                    if ($frequency) {
                        $name = $frequency->getName() . ' ' . $frequency->getValue();
                    }
                }
                break;
            case 'radar':
                if ($customfield->isMultiple()) {
                    $radars = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($radars as $r) {
                        $radar = $this->em->getRepository('Application\Entity\Radar')->find($r);
                        if ($radar) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $radar->getName() . " ";
                        }
                    }
                } else {
                    $radar = $this->em->getRepository('Application\Entity\Radar')->find($fieldvalue);
                    if ($radar) {
                        $name = $radar->getName();
                    }
                }
                break;
            case 'select':
                $defaultvalue = preg_replace('~\r[\n]?~', "\n", $customfield->getDefaultValue());
                if ($defaultvalue && $fieldvalue != null) {
                    $values = explode("\n", $defaultvalue);
                    if($customfield->isMultiple()) {
                        $ids = explode("\r", $fieldvalue);
                        $name = "";
                        foreach ($ids as $id) {
                            if(array_key_exists($id, $values)) {
                                $name .= $values[$id] . " ";
                            }
                        }
                    } else {
                        if (array_key_exists($fieldvalue, $values)) {
                            $name = $values[$fieldvalue];
                        }
                    }
                    $name = trim($name);
                }
                break;
            case 'stack':
                if ($customfield->isMultiple()) {
                    $stacks = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($stacks as $s) {
                        $stack = $this->em->getRepository('Application\Entity\Stack')->find($s);
                        if ($stack) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $stack->getName() . " ";
                        }
                    }
                } else {
                    $stack = $this->em->getRepository('Application\Entity\Stack')->find($fieldvalue);
                    if ($stack) {
                        $name = $stack->getName();
                    }
                }
                break;
            case 'afis':
                if ($customfield->isMultiple()) {
                    $afis = explode("\r", $fieldvalue);
                    $name = "";
                    foreach ($afis as $a) {
                        $af = $this->em->getRepository('Application\Entity\Afis')->find($a);
                        if ($af) {
                            if (strlen($name) > 0) {
                                $name .= "+ ";
                            }
                            $name .= $af->getName() . " ";
                        }
                    }
                } else {
                    $af = $this->em->getRepository('Application\Entity\Afis')->find($fieldvalue);
                    if ($af) {
                        $name = $af->getName();
                    }
                }
                break;
            case 'boolean':
                $name = ($fieldvalue ? "Oui" : "Non");
                break;
            default:
                ;
                break;
        }
      
        return $name;
    }

    /**
     *
     * @param \Application\Entity\CustomFieldType $customfieldtype
     * @return array
     */
    public function getFormAttributes(\Application\Entity\CustomField $customfield)
    {
        $customfieldtype = $customfield->getType();
        $attributes = [];
        switch ($customfieldtype->getType()) {
            case 'string':
            case 'text':
            case 'boolean':
                break;
            case 'frequency':
            case 'sector':
            case 'antenna':
            case 'select':
            case 'stack':
            case 'radar':
            case 'afis':
                if ($customfield->isMultiple()) {
                    $attributes['multiple'] = 'multiple';
                }
                break;
            default:
                break;
        }
        if($customfield->getCategory() instanceof AntennaCategory) {
            if($customfield->getType()->getType() == 'antenna') {
                $attributes['data-trigger-refresh-to'] = $customfield->getCategory()->getFrequenciesField()->getId();
            }
        }
        return $attributes;
    }

    /**
     * Returns the corresponding Zend Form Type
     * 
     * @param \Application\Entity\CustomFieldType $customfieldtype            
     * @return Ambigous <NULL, string>
     */
    public function getZendType(\Application\Entity\CustomFieldType $customfieldtype)
    {
        $type = null;
        switch ($customfieldtype->getType()) 
        {
            case 'string':
                $type = 'Zend\Form\Element\Text';
                break;
            case 'text':
                $type = 'Zend\Form\Element\Textarea';
                break;
            case 'frequency':
            case 'sector':
            case 'antenna':
            case 'select':
            case 'stack':
            case 'radar':
            case 'afis':
                $type = 'Zend\Form\Element\Select';
                break;
            case 'boolean':
                $type = 'Zend\Form\Element\Checkbox';
                break;
            default:
                ;
                break;
        }
        return $type;
    }

    public function isMultipleAllowed(\Application\Entity\CustomField $customfield)
    {
        $multiple = true;
        if ($customfield->getType()) {
            switch ($customfield->getType()->getType()) {
                case 'string':
                case 'text':
                case 'boolean':
                    $multiple = false;
                    break;
                case 'sector':
                case 'antenna':
                case 'frequency':
                case 'radar':
                case 'select':
                case 'stack':
                case 'afis':
                    $multiple = true;
                    break;
                default:
                    break;
            }
        }
        return $multiple;
    }

    /**
     *
     * @param \Application\Entity\CustomFieldType $customfieldtype            
     * @return array
     */
    public function getFormValueOptions(\Application\Entity\CustomField $customfield)
    {
        $value_options = null;
        $om = $this->em;
        switch ($customfield->getType()->getType()) {
            case 'string':
                break;
            case 'text':
                break;
            case 'sector':
                $value_options = $om->getRepository('Application\Entity\Sector')->getAllAsArray(array(
                    'decommissionned' => false
                ));
                break;
            case 'antenna':
                $value_options = $om->getRepository('Application\Entity\Antenna')->getAllAsArray(array(
                    'decommissionned' => false
                ));
                break;
            case 'frequency':
                $qb = $om->createQueryBuilder();
                $qb->select(array(
                    'f'
                ))
                    ->from('Application\Entity\Frequency', 'f')
                    ->leftJoin('f.defaultsector', 's')
                    ->leftJoin('s.zone', 'z')
                    ->where($qb->expr()
                    ->eq('f.decommissionned', 'false'))
                    ->addOrderBy('z.name', 'DESC')
                    ->addOrderBy('s.name', 'ASC');
                $result = array();
                foreach ($qb->getQuery()->getResult() as $frequency) {
                    $result[$frequency->getId()] = $frequency->getName();
                }
                $value_options = $result;
                break;
            case 'radar':
                $value_options = $om->getRepository('Application\Entity\Radar')->getAllAsArray(array(
                    'decommissionned' => false
                ));
                break;
            case 'select':
                $input = preg_replace('~\r[\n]?~', "\n", $customfield->getDefaultValue());
                $value_options = explode("\n", $input);
                break;
            case 'stack':
                $qb = $om->createQueryBuilder();
                $qb->select(array(
                    's'
                ))
                    ->from('Application\Entity\Stack', 's')
                    ->where($qb->expr()
                    ->eq('s.decommissionned', 'false'))
                    ->addOrderBy('s.name', 'ASC');
                $results = array();
                foreach ($qb->getQuery()->getResult() as $stack) {
                    $results[$stack->getId()] = $stack->getName();
                }
                $value_options = $results;
                break;
            case 'afis':
                $value_options = $om->getRepository('Application\Entity\Afis')->getAllAsArray(array(
                    'decommissionned' => false
                ));
                break;
            case 'boolean':
                break;
            default:
                break;
        }
        return $value_options;
    }

    public function getEmptyOption(\Application\Entity\CustomField $customfield)
    {
        $empty_option = null;
        switch ($customfield->getType()->getType()) {
            case 'string':
                break;
            case 'text':
                break;
            case 'sector':
                if ($customfield->isMultiple()) {
                    $empty_option = "Tous les secteurs.";
                } else {
                    $empty_option = "Choisissez le secteur.";
                }
                break;
            case 'antenna':
                if ($customfield->isMultiple()) {
                    $empty_option = "Toutes les antennes.";
                } else {
                    $empty_option = "Choisissez l'antenne.";
                }
                break;
            case 'frequency':
                if ($customfield->isMultiple()) {
                    $empty_option = "Toutes les fréquences.";
                } else {
                    $empty_option = "Choisissez la fréquence.";
                }
                break;
            case 'radar':
                if ($customfield->isMultiple()) {
                    $empty_option = "Tous les radars";
                } else {
                    $empty_option = "Choisissez le radar.";
                }
                break;
            case 'select':
                break;
            case 'stack':
                if ($customfield->isMultiple()) {
                    $empty_option = "Toutes les attentes.";
                } else {
                    $empty_option = "Choisissez l'attente.";
                }
                break;
            case 'afis':
                if ($customfield->isMultiple()) {
                    $empty_option = "Tous les Afis.";
                } else {
                    $empty_option = "Choisissez le terrain Afis.";
                }
                break;
            case 'boolean':
                break;
            default:
                break;
        }
        return $empty_option;
    }
}