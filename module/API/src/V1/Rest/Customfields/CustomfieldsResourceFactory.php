<?php
namespace API\V1\Rest\Customfields;

class CustomfieldsResourceFactory
{
    public function __invoke($services)
    {
        return new CustomfieldsResource(
            $services->get('Doctrine\ORM\EntityManager'),
            $services->get('eventservice')
        );
    }
}
