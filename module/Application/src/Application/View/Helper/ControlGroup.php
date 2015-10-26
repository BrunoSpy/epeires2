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

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Bootstrap control group helper
 * 
 * @author Bruno Spyckerelle
 *        
 */
class ControlGroup extends AbstractHelper
{

    public function __invoke($label, $control, $options = array())
    {
        $control_id = (isset($options['control_id'])) ? "id=\"" . $options['control_id'] . "\"" : "";
        
        $group_id = (isset($options['group_id'])) ? "id=\"" . $options['group_id'] . "\"" : "";
        
        $class = (isset($options['class'])) ? " " . $options['class'] : "";
        
        $result = "<div class=\"form-group" . $class . "\" " . $group_id . ">";
        $result .= $label;
        $result .= "<div class=\"col-sm-8\" " . $control_id . ">";
        $result .= $control;
        $result .= "</div></div>";
        
        return $result;
    }
}