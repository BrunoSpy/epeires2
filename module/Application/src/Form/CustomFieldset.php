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
namespace Application\Form;

use Application\Entity\MilCategory;
use Application\Services\CustomFieldService;
use Doctrine\ORM\EntityManager;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * Fieldset for custom fields
 * 
 * @author Bruno Spyckerelle
 *        
 */
class CustomFieldset extends Fieldset implements InputFilterProviderInterface
{

    private $names;

    public function __construct(EntityManager $entityManager, CustomFieldService $customFieldService, $categoryid, $model = false)
    {
        parent::__construct('custom_fields');

        $this->names = array();
        
        $category = $entityManager->getRepository('Application\Entity\Category')->find($categoryid);
        $customfields = $entityManager
            ->getRepository('Application\Entity\CustomField')
            ->matching(Criteria::create()
                ->where(Criteria::expr()->eq('category', $category))
                ->orderBy(array("place" => Criteria::ASC))
            );
        
        // add category id to regenerate fieldset during creation process
        $this->add(array(
            'name' => 'category_id',
            'type' => '\Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => $categoryid
            )
        ));
        
        foreach ($customfields as $customfield) {
            if($customfield->isHidden())
                continue;
            
            $definition = array();
            $definition['name'] = $customfield->getId();
            $this->names[] = $customfield->getId();
            $options = array(
                'label' => $customfield->getName() . " :"
            );

            $value_options = $customFieldService->getFormValueOptions($customfield);
            if ($value_options) {
                $options['value_options'] = $value_options;
            }
            $empty_option = $customFieldService->getEmptyOption($customfield);
            if ($empty_option) {
                $options['empty_option'] = $empty_option;
            }
            
            $definition['type'] = $customFieldService->getZendType($customfield->getType());
            
            foreach ($customFieldService->getFormAttributes($customfield) as $key => $attribute) {
                $definition['attributes'][$key] = $attribute;
            }
            
            $definition['options'] = $options;
            
            if (! $model && $customfield->getId() == $category->getFieldname()->getId()) {
                $definition['attributes']['required'] = 'required';
                if($category instanceof MilCategory) {
                    if(strlen($category->getZonesRegex()) > 0) {
                        $regex = substr($category->getZonesRegex(), 1);
                        $regex = substr($regex, 0, -1);
                        $definition['attributes']['pattern'] = $regex.'.*';
                        $definition['attributes']['title'] = $category->getZonesRegex();
                        $definition['attributes']['placeholder'] = $category->getZonesRegex();
                    }
                }
            }

            if($category instanceof MilCategory &&
                ($customfield->getId() == $category->getUpperLevelField()->getId() || $customfield->getId() == $category->getLowerLevelField()->getId())) {
                $definition['attributes']['data-rule-number'] = true;
                $definition['attributes']['data-rule-min'] = 0;
                $definition['attributes']['data-rule-max'] = 999;
            }

            //disable required fields if model
            if( $model ){
                $definition['attributes']['required'] = '';
            }

            if(strcmp($customfield->getType()->getType(), 'string') == 0) {
                $definition['attributes']['maxlength'] = '48';
            }

            if(strlen($customfield->getTooltip()) > 0 && !array_key_exists('placeholder',$definition['attributes'])) {
                $definition['attributes']['title'] = $customfield->getTooltip();
                $definition['attributes']['placeholder'] = $customfield->getTooltip();
            }
            $this->add($definition);
        }
    }

    public function getInputFilterSpecification()
    {
        $specifications = array();
        foreach ($this->names as $name) {
            $specifications[$name] = array(
                'required' => false
            );
        }
        return $specifications;
    }
}