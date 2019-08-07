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
namespace Core\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Bootstrap control group helper
 * 
 * @author Bruno Spyckerelle
 *        
 */
class ControlGroup extends AbstractHelper
{

    public function __invoke($element, $options = array())
    {
        $view = $this->getView();

        $control_id = (isset($options['control_id'])) ? "id=\"" . $options['control_id'] . "\"" : "";

        $group_id = (isset($options['group_id'])) ? "id=\"" . $options['group_id'] . "\"" : "";

        $class = (isset($options['class'])) ? " " . $options['class'] : "";

        $labelclass = (isset($options['labelclass'])) ? " " . $options['labelclass'] : "";

        $large = (isset($options['large'])) ? $options['large'] : false ;
        
        $element->setLabelAttributes(array('class' => 'control-label '.($large ? 'col-sm-2' : 'col-sm-3').$labelclass));
        $elementClass = $element->getAttribute('class');
        $element->setAttributes(
            array_merge(
                $element->getAttributes(),
                array('class' => 'form-control '.$elementClass)));

        if ($element instanceof \Zend\Form\Element\Select) {
            $controlForm = $view->formSelect($element);
        } elseif ($element instanceof \Zend\Form\Element\Checkbox) {
            $element->setUseHiddenElement(true);
            $controlForm = '<div class="checkbox"><label>'.$view->formCheckbox($element).'</label></div>';
            $controlForm .= '<p class="help-block">'.$element->getAttribute('title').'</p>';
        } elseif ($element instanceof \Zend\Form\Element\Text) {
            $controlForm = $view->formText($element);
        } elseif ($element instanceof \Zend\Form\Element\Textarea) {
           $controlForm = $view->formTextarea($element);
        } elseif ($element instanceof \Zend\Form\Element\File) {
            $controlForm = $view->formFile($element)
                               . $view->formElementErrors($element)
                               . '<div id="file-errors" class="help-block"></div>'
                               . '<div id="progress" class="help-block">'
                               . '<div class="progress">'
                               . '<div class="progress-bar progress-bar-info progress-bar-striped active"></div>'
                               . '</div><p></p></div>';
        } else {
            $controlForm = $view->formInput($element);
        }
        $result = "<div class=\"form-group " . $class . "\" " . $group_id . ">";
        if($element->getLabel() !== null) {
            $result .= $view->formLabel($element);
        }
        $result .= "<div class=\"".($large ? "col-sm-10 col-lg-8" : "col-sm-9 col-lg-8")."\" " . $control_id . ">";
        $result .= $controlForm;
        $result .= "</div></div>";
        
        return $result;
    }
}