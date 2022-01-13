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

use Laminas\View\Helper\AbstractHelper;

/**
 *
 * @author Bruno Spyckerelle
 *
 */
class ViewSelector extends AbstractHelper
{
    public function __invoke(int $viewduration)
    {
        $html = '<form class="navbar-form navbar-right" role="search" id="search">';
        $html .= '<div class="form-group form-group-material-' . $color . '-500 has-feedback">';
        $html .= '<input type="text" class="form-control" placeholder="Chercher" name="search">';
        $html .= '<span class="glyphicon glyphicon-search form-control-feedback"></span>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '<div id="changeview" class="navbar-right" style="margin-top: 5px">Vue : <div class="btn-group" data-toggle="buttons">';
        $html .= '<label class="btn btn-xs btn-info active">';
        $html .= '<input name="viewOptions" id="viewsix" type="radio" autocomplete="off" value="six" checked><strong>'.$viewduration.' h</strong>';
        $html .= '</label>';
        $html .= '<label class="btn btn-xs btn-info ">';
        $html .= '<input name="viewOptions" id="viewday" type="radio" autocomplete="off" value="day"><strong>24 h</strong>';
        $html .= '</label>';
        $html .= '<label class="btn btn-xs btn-info ">';
        $html .= '<input name="viewOptions" id="viewmonth" type="radio" autocomplete="off" value="month"><strong>7 j/+</strong>';
        $html .= '</label>';
        $html .= '</div>';

        return $html;
    }
}