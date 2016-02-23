<?php

namespace Bookcase\Form;

use Zend\Form\FormInterface;

interface FormAwareInterface
{
    public function setForm(FormInterface $form);
}
