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

use Zend\Form\Annotation\AnnotationBuilder;
use Application\Entity\Organisation;
use Application\Entity\Afis;
class AfisForm
{
    const DEFAULT_METHOD = 'post';

    protected $form;

    public function __construct($em)
    {
        $organisations = $em->getRepository(Organisation::class);

        $this->form = (new AnnotationBuilder())->createForm(Afis::class);
        $this->form
            ->setAttributes([
                'method'    => self::DEFAULT_METHOD,
                'class'     => 'form-horizontal'
            ])
            ->add([
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'class' => 'btn btn-primary btn-small'
                ]
            ])
            ->get('organisation')
            ->setValueOptions($organisations->getAllAsArray())
        ;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function showErrors(){
        $str = '';
        foreach ($this->form->getMessages() as $field => $messages)
            foreach ($messages as $typeErr => $message)
                $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}