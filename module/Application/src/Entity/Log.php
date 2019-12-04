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
namespace Application\Entity;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="Application\Repository\LogRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @author Bruno Spyckerelle
 */
class Log extends AbstractLogEntry
{

    /**
     * UTC
     */
    public function setLoggedAt()
    {
        $this->loggedAt = new \DateTime();
        $this->loggedAt->setTimezone(new \DateTimeZone("UTC"));
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        if ($this->loggedAt) {
            $offset = $this->loggedAt->getTimezone()->getOffset($this->loggedAt);
            $this->loggedAt->setTimezone(new \DateTimeZone("UTC"));
            $this->loggedAt->add(new \DateInterval("PT" . $offset . "S"));
        }
    }
}