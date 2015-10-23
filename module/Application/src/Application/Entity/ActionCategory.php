<?php
/** 
 * Epeires 2
 *
 * Catégorie d'évènements.
 * Peut avoir une catégorie parente.
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 */
class ActionCategory extends Category
{

    /**
     * Ref to the field used to store the state of a radar
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $namefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $textfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $colorfield;

    public function getNamefield()
    {
        return $this->namefield;
    }

    public function setNamefield($namefield)
    {
        $this->namefield = $namefield;
    }

    public function getTextfield()
    {
        return $this->textfield;
    }

    public function setTextfield($textfield)
    {
        $this->textfield = $textfield;
    }

    public function setColorField($color)
    {
        $this->colorfield = $color;
    }

    public function getColorField()
    {
        return $this->colorfield;
    }
}