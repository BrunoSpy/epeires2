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

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;

use Doctrine\ORM\EntityManager;
use DOMPDFModule\View\Model\PdfModel;

use Core\Controller\AbstractEntityManagerAwareController;

use Application\Entity\InterrogationPlan;
use Application\Entity\Field;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
/**
 *
 * @author Loïc Perrin
 */
class SarBeaconsController extends AbstractEntityManagerAwareController
{
    private $em, $repo, $viewpdfrenderer, $config, $form;

    public function __construct(EntityManager $em, $viewpdfrenderer, $config)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();
        $this->repo = $this->em->getRepository(InterrogationPlan::class);

        $this->viewpdfrenderer = $viewpdfrenderer;
        $this->config = $config;
        $this->form = (new AnnotationBuilder())->createForm(InterrogationPlan::class);
    }


    public function indexAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();
    }

    public function formAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = intval($post['id']);

        $iP = ($id) ? $this->repo->find($id) : new InterrogationPlan();

        $iP->setLatitude($post['lat']);
        $iP->setLongitude($post['lon']);     
        $this->form->bind($iP);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
            ]);
    }

    public function listAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => $this->repo
                    ->getBy([
                        'where' => [],
                        'order' => [
                            'startTime' => 'DESC'
                        ],
                        'limit' => 5
                    ])
            ]);
    }
    // TODO BOF BOF
    public function saveAction()
    {
        if (!$this->authSarBeacons('write')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $pdatas = $post['datas'];
        $ppio = (is_array($post['iP'])) ? $post['iP'] : [];
        $datasIntPlan = [];
        parse_str($pdatas, $datasIntPlan);

        if (is_array($post['iP']) && count($post['iP']) > 0) {
            $fields = [];
            foreach ($ppio as $i => $field) {
                $f = new Field($field);
                if ($f->isValid()) $fields[] = $f;
            }
            $datasIntPlan['fields'] = $fields;
        }

        $id = intval($datasIntPlan['id']);
        $iP = ($id) ? $this->repo->find($id) : new InterrogationPlan();
        $this->form->setData($datasIntPlan);

        if (!$this->form->isValid()) $iP = false;
        else 
        { 
            $iP = $this->repo->hydrate($this->form->getData(), $iP);
        }
        if (is_a($iP, InterrogationPlan::class)) {
            $this->repo->save($iP);    
            $this->saveToPdf($iP);       
        }

        return new JsonModel([
            'id' => $iP->getId(),
            'type' => 'success',
            'msg' => 'Le plan d\'interrogation a bien été enregistré.'
        ]);
    }

    private function saveToPdf($iP) 
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd_LL_yyyy');

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4');                         

        $pdfView = (new ViewModel($pdf))
            ->setTerminal(true)
            ->setTemplate('application/sar-beacons/print')
            ->setVariables([
                'iP' => $iP
            ]);

        $html = $this->viewpdfrenderer->getHtmlRenderer()->render($pdfView);
        $engine = $this->viewpdfrenderer->getEngine();
        $engine->load_html($html);
        $engine->render();

        $dir = 'data/interrogation-plans';
        if(! is_dir($dir)) mkdir($dir);
        file_put_contents($iP->getPdfFilePath(), $engine->output());
    }

    public function getAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $iP = $this->repo->find($post['id']);
        
        return new JsonModel($iP->getArrayCopy());
    }

    public function validpdfAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $iP = $this->repo->find($this->params()->fromRoute('id'));
        if (!file_exists($iP->getPdfFilePath())) 
            return new JsonModel(['error', 'Problème lors du chargement du fichier pdf. Impossible de continuer.']); 
        else return new JsonModel();

    }

    public function printAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $iP = $this->repo->find($this->params()->fromRoute('id'));

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4')
            ->setOption('filename', $iP->getPdfFileName())
            ->setVariables([
                'iP' => $iP
            ]);

        return $pdf;
    }

    public function mailAction() {
        if (!array_key_exists('emailfrom', $this->config) | !array_key_exists('smtp', $this->config)) return new JsonModel(['error', 'Problème de configuration des adresses email.']);

        $post = $this->getRequest()->getPost();
        $iP = $this->repo->find($post['id']);

        $text = new MimePart('Veuillez trouver ci-joint un plan d\'interrogation.');
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        
        $fileContents = fopen($iP->getPdfFilePath(), 'r');
        $attachment = new MimePart($fileContents);
        $attachment->type = 'application/pdf';

        $attachment->filename = $iP->getPdfFileName();

        $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
        
        $body = new MimeMessage();
        $body
            ->setParts([
                $text,
                $attachment
            ]);

        $message = (new Message())
            ->setEncoding("UTF-8")
            ->addFrom($this->config['emailfrom'])
            ->addTo('loic.perrin@aviation-civile.gouv.fr')
            ->setSubject('Plan d\'interrogation')
            ->setBody($body);

        if($message->isValid()) 
        {
            $transportOptions = new SmtpOptions($this->config['smtp']);
            $transport = (new Smtp())
                ->setOptions($transportOptions)
                ->send($message);
        }
        return new JsonModel(['success', 'Courriel(s) envoyé(s) avec succès.']);
    }

    private function authSarBeacons($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('sarbeacons.'.$action)) ? false : true;
    }

}