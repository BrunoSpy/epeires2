<?php
namespace API\V1\Rest\Sector;

class SectorResourceFactory
{
    public function __invoke($services)
    {
        return new SectorResource($services->get('Doctrine\ORM\EntityManager'));
    }
}
