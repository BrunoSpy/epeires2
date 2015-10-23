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
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class CustomFieldGroup extends AbstractHelper
{

    public function __invoke($element)
    {
        $view = $this->getView();
        
        if ($element instanceof \Zend\Form\Element\Select) {
            $html = $view->controlGroup($view->formLabel($element->setLabelAttributes(array(
                'class' => 'control-label'
            ))), $view->formSelect($element));
        } elseif ($element instanceof \Zend\Form\Element\Checkbox) {
            $element->setUseHiddenElement(true);
            $html = $view->controlGroup($view->formLabel($element->setLabelAttributes(array(
                'class' => 'control-label'
            ))), $view->formCheckbox($element));
        } elseif ($element instanceof \Zend\Form\Element\Text) {
            $html = $view->controlGroup($view->formLabel($element->setLabelAttributes(array(
                'class' => 'control-label'
            ))), $view->formText($element));
        } elseif ($element instanceof \Zend\Form\Element\Textarea) {
            $html = $view->controlGroup($view->formLabel($element->setLabelAttributes(array(
                'class' => 'control-label'
            ))), $view->formTextarea($element));
        } else {
            $html = $view->formRow($element);
        }
        
        return $html;
    }
}