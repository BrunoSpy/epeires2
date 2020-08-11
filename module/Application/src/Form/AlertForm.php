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

use Laminas\InputFilter;
use Laminas\Form\Form;
use Laminas\Form\Element;

/**
 * Description of AlertForm
 *
 * @author Loïc Perrin
 */
class AlertForm extends Form
{

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $altType = new Element\Select('alt-type');
        $altType
            ->setLabel("Type d'alerte ")
            ->setAttributes([
                'id' => 'alt-type',
                'multiple' => false,
            ]);

        $alt_array = [];
        foreach ($options['alt-type'] as $alt_name => $alt_classe) {
            $alt_array[$alt_name] = $alt_name;
        }
        $altType->setValueOptions($alt_array);

        $altCause = new Element\Textarea('alt-cause');
        $altCause->setLabel('Cause');
        $altCause->setAttribute('placeholder', "Raisons du déclenchement");
        $altCause->setAttribute('rows', 4);

        $altNote = new Element\Textarea('alt-note');
        $altNote->setLabel('Note');
        $altNote->setAttribute('placeholder', "Commentaires");
        $altNote->setAttribute('rows', 4);

        $this->add($altType);
        $this->add($altCause);
        $this->add($altNote);
    }
}
