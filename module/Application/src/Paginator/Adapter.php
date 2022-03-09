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

namespace Application\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;

class Adapter implements AdapterInterface {

    protected $repository;
    
    public function __construct($repository)
    {
        $this->repository = $repository;
    }
    
    public function count()
    {
        return $this->repository->count(array());
    }
    
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->repository->getItems($offset, $itemCountPerPage);
    }
    
}