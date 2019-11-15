<?php
namespace API\V1\Rest\Event;

class EventResourceFactory
{
    public function __invoke($services)
    {
        return new EventResource(
            $services->get('Doctrine\ORM\EntityManager'),
            $services->get('eventservice'));
    }
}
