<?php
namespace API\V1\Rest\File;

class FileResourceFactory
{
    public function __invoke($services)
    {
        return new FileResource(
            $services->get('Doctrine\ORM\EntityManager')
        );
    }
}
