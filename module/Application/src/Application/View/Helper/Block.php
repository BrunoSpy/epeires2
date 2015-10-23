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
 * Bootstrap block helper
 * 
 * @author Bruno Spyckerelle
 *        
 */
class Block extends AbstractHelper
{

    public function __invoke($title, $body)
    {
        $html = '<div class="block">';
        $html .= '<div class="navbar navbar-inner block-header">';
        $html .= '<div class="muted pull-left">' . $title . '</div>';
        $html .= '</div>';
        $html .= '<div class="block-content collapse in">';
        $html .= '<div class="span12">';
        $html .= $body;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}