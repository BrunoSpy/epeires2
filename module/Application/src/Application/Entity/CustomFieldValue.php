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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="customfieldvalues")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 *
 * @author Bruno Spyckerelle
 */
class CustomFieldValue
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AbstractEvent", inversedBy="custom_fields_values")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $event;

    /**
     * @ORM\ManyToOne(targetEntity="CustomField", inversedBy="values")
     */
    protected $customfield;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Versioned
     */
    protected $value;

    public function getId()
    {
        return $this->id;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getCustomField()
    {
        return $this->customfield;
    }

    public function setCustomField($customfield)
    {
        $this->customfield = $customfield;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}