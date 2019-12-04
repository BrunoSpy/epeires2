<?php
namespace API\V1\Rest\Frequency;

class FrequencyResourceFactory
{
    public function __invoke($services)
    {
        return new FrequencyResource($services->get('Doctrine\ORM\EntityManager'));
    }
}
