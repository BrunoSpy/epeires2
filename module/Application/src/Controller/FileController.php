<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container;
use Zend\ProgressBar;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Form\FileUpload;

/**
 *
 * @author Bruno Spyckerelle
 */
class FileController extends FormController
{

    /**
     *
     * @var Container
     */
    protected $sessionContainer;

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->sessionContainer = new Container('file_upload');
        $this->entityManager = $entityManager;
    }

    public function sessionprogressAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $progress = new ProgressBar\Upload\SessionProgress();
        
        $view = new JsonModel(array(
            'id' => $id,
            'status' => $progress->getProgress($id)
        ));
        return $view;
    }

    public function formAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $form = new FileUpload('file-form');
        if ($this->getRequest()->isPost()) {
            // Postback
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            
            $form->setData($data);
            $messages = array();
            if ($form->isValid()) {
                $file = new \Application\Entity\File();
                $status = true;
                $data = $form->getData();
                if (! isset($data['file']) && ! (isset($data['url']) && isset($data['name']))) {
                    $status = false;
                    $messages['error'][] = "Aucun fichier et aucun lien fourni.";
                } else {
                    if (isset($data['file']) && ! empty($data['file'])) {
                        $fileinfo = $data['file'];
                        $file->setMimetype($fileinfo['type']);
                        $file->setSize($fileinfo['size']);
                        $tmp_name = substr($fileinfo['tmp_name'], 15);
                        $file->setPath('/files/' . $tmp_name);
                        $file->setFilename($tmp_name);
                        if (isset($data['name'])) {
                            $file->setName($data['name']);
                        } else {
                            $file->setName($fileinfo['name']);
                        }
                    } elseif (isset($data['url'])) {
                        // unused : forbidden by browsers
                        // TODO find a way to allow links to local files...
                        $file->setFileName($data['name']);
                        $file->setPath('file:///' . $data['url']);
                    }
                    if (isset($data['reference'])) {
                        $file->setReference($data['reference']);
                    }
                    $this->entityManager->persist($file);
                    try {
                        $this->entityManager->flush();
                        $messages['success'][] = "Nouveau fichier ajouté";
                    } catch (\Exception $ex) {
                        $messages['error'][] = $ex->getMessage();
                    }
                }
                $formData = array();
                $formData['reference'] = $file->getReference();
                $formData['path'] = $file->getPath();
                $formData['name'] = ($file->getName() ? $file->getName() : $file->getFilename());
                
                return new JsonModel(array(
                    'status' => $status,
                    'formData' => $formData,
                    'formMessages' => $messages,
                    'fileId' => $file->getId()
                ));
            } else {
                $this->processFormMessages($form->getMessages(), $messages);
                return new JsonModel(array(
                    'status' => false,
                    'formData' => $form->getData(),
                    'formMessages' => $messages
                ));
            }
        }
        
        $viewmodel->setVariable('form', $form);
        
        return $viewmodel;
    }
}
