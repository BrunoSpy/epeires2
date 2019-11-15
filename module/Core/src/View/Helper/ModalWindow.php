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
 *
 * @author Bruno Spyckerelle
 *        
 */
class ModalWindow extends AbstractHelper
{

    /**
     *
     * @param
     *            $id
     * @param
     *            $header
     * @param
     *            $headerstyle
     * @param $content content
     *            div, if not null $body and $footer will be ignored
     * @param
     *            $body
     * @param
     *            $footer
     * @param
     *            $size
     * @return string
     */
    public function __invoke($id, $header, $headerstyle, $content, $body = null, $footer = null, $size = '')
    {
        $html = '<div class="modal fade" id="' . $id . '" ' . $headerstyle . '>';
        $html .= '<div class="modal-dialog '.$size.'">';
        $html .= '<div class="modal-content">';
        if($header !== null) {
            $html .= '<div class="modal-header">';
            $html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span aria-hidden="true">&times;</span></button>';
            $html .= $header;
            $html .= '</div>';
        }
        if ($content) {
            $html .= $content;
        } else {
            $html .= '<div class="modal-body">';
            $html .= $body;
            $html .= '</div>';
            $html .= '<div class="modal-footer">';
            $html .= $footer;
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}