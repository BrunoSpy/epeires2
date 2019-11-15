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
namespace Core\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class LoginAttempt
 * @ORM\Entity
 * @ORM\Table(name="loginattempts")
 * @ORM\HasLifecycleCallbacks
 */
class LoginAttempt
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="loginattempts")
     */
    protected $user = null;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $attempt;

    /**
     * @ORM\Column(type="string")
     * @var string $ipAdress
     */
    protected $ipAdress;

    /**
     * Log username used if login failed
     * @ORM\Column(type="string", nullable=true)
     * @var string $username
     */
    protected $username;

    /**
     * @ORM\PrePersist
     */
    public function setAttempt()
    {
        $this->attempt = new \DateTime('NOW');
        $this->attempt->setTimeZone(new \DateTimeZone("UTC"));
    }

    public function getAttempt() : \DateTime
    {
        return $this->attempt;
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        // les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
        // mais à la création php les crée en temps local, il faut donc les corriger
        if ($this->attempt) {
            $offset = $this->attempt->getTimezone()->getOffset($this->attempt);
            $this->attempt->setTimezone(new \DateTimeZone("UTC"));
            $this->attempt->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    /**
     * @return mixed
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getIpAdress()
    {
        return $this->ipAdress;
    }

    /**
     * @param string $ipAdress
     */
    public function setIpAdress(string $ipAdress): void
    {
        $this->ipAdress = $ipAdress;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }
}
