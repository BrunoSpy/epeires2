<?php

namespace Bookcase\Form;

use Zend\Form\FormInterface;

trait FormAwareTrait
{
    protected $form;
    
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        
        return $this;
    }
    
    public function getForm()
    {
        return $this->form;
    }
}
