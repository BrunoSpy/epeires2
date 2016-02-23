<?php

namespace Bookcase\Form;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface as Locator;
use Bookcase\Form\BookFormAwareInterface;

class BookFormAwareInitializer implements InitializerInterface
{
    public function initialize($instance, Locator $locator)
    {
        if ($instance instanceof BookFormAwareInterface) {
            $form = $locator->getServiceLocator()->get('FormElementManager')->get('BookForm');
            $instance->setForm($form);
        }
    }
}
