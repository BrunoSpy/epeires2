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
namespace Administration\Controller;

use Application\Controller\FormController;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Loïc Perrin
 *        
 */
class AfisController extends FormController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $this->layout()->title = "Afis";

        $allAfis = $this->forward()->dispatch('Application\Controller\Afis', [
                'action'     => 'getAll',
        ]);

        $this->layout('layout/adminlayout'); 
        return [
            'messages'  => $this->msg()->get(),
            'allAfis'   => $allAfis,
        ];
    }
}