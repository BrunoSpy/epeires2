<?php
namespace API\V1\Rest\File;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\Stdlib\Parameters;
use Application\Entity\File;
use Laminas\InputFilter\FileInput;
use Laminas\Validator\File\UploadFile;
use Application\Entity\Event;
use Doctrine\ORM\EntityManager;

class FileResource extends AbstractResourceListener
{
    protected $em;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function fileToJson($file, $event_id){
        $json['id'] = $file->getId();
        $json['name'] = $file->getName();
        $json['filename'] = $file->getFilename();
        $json['reference'] = $file->getReference();
        $json['mimetype'] = $file->getMimetype();
        $json['size'] = $file->getSize();
        $json['path'] = $file->getPath();
        $json['events_id'] = $event_id;
        return $json;
    }

    public function uploadFile($data) 
    {
        $fileInput = new FileInput('file');
        $fileInput->getValidatorChain()->attach(new UploadFile());
        $fileData = $data;

        $fileInput->setValue($fileData);

        if (!$fileInput->isValid()) {
            $messages = $fileInput->getMessages();
        }
        $file = $fileInput->getValue();

        $uploadDir = 'public/files/';

        $originalNameWithoutExtension = pathinfo($fileData['name'], PATHINFO_FILENAME);
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $shortenedName = substr($originalNameWithoutExtension, 0, 15);
        $uniqueFilename = $shortenedName . '_' . uniqid() . '.' . $extension;

        if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $uniqueFilename)) {
            $file = new File();
            $file->setName($fileData['name']);
            $file->setMimetype($fileData['type']);
            $file->setSize($fileData['size']);
            $file->setPath('/files/' . $uniqueFilename);
            $file->setFilename($uniqueFilename);
            $this->em->persist($file);
            return $file;
        } else {
            throw new \RuntimeException('Failed to move uploaded file');
        }
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $file = $data->file;
        $event_id = $data->event_id;

        $event = $this->em->getRepository('Application\Entity\Event')->find($event_id);
        $file = $this->uploadFile($data->file);

        // Si un fichier avec le même nom existe il est délié de l'évènement
        $fileAlreadyExist = FALSE;
        foreach ($event->getFiles() as $eventFiles) {
            if ($file->getName() == $eventFiles->getName()){
                $fileAlreadyExist = TRUE;
                $event->removeFile($eventFiles);
            }
        }
        // Si $file existe, on l'ajoute à l'évènement
        if (isset($file)) {
            $event->addFile($file);
        } else {
            throw new \RuntimeException('Failed to upload file');
        }

        $this->em->flush();
        return array($this->fileToJson($file, $event_id));
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $file = $this->em->getRepository('Application\Entity\File')->find($id);
        return array($file);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array|Parameters $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
