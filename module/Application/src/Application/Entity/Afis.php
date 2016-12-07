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
use Zend\Form\Annotation;
use Application\Entity\TemporaryResource;
use Application\Entity\Organisation;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\BtivRepository")
 * @ORM\Table(name="afis")
 *
 * @author Loïc Perrin
 *        
 */
class Afis extends TemporaryResource
{
    const DEFAULT_STATE = 0;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required(True)
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required(True)
     * @Annotation\Options({"label":"Nom abrégé :"})
     */
    protected $shortname;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Organisation")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(True)
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;
    
    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $state = self::DEFAULT_STATE;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getShortname()
    {
        return $this->shortname;
    }

    public function setShortname($name)
    {
        $this->shortname = $name;
    }
    
    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }
    
    public function getState()
    {
        return $this->state;
    }

    public function getStrState()
    {
        return ($this->getState() == true) ? 'actif' : 'inactif';
    }

    public function setState($state)
    {
        $s = self::DEFAULT_STATE;
        if(is_bool($state)) $s = $state;
        $this->state = $s;
    }
    
    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['organisation'] = ($this->organisation) ? $this->organisation->getId() : null; 
        return $object_vars;
    }
    
    public function isValid(){
        $r = false;
        if (    is_int($this->id) and
                is_string($this->name) and
                is_string($this->shortname) and
                is_a($this->organisation, Organisation::class) and
                is_bool($this->state)) $r = true;
        return $r;
    }

}