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
    private $em, $viewpdfrenderer, $config, $form;

    public function __construct(EntityManager $em, $viewpdfrenderer, $config)
    {
        parent::__construct($em);
        $this->em = $this->getEntityManager();
        $this->viewpdfrenderer = $viewpdfrenderer;
        $this->config = $config;
        $this->form = (new AnnotationBuilder())->createForm($this::getEntity());
    }

    public static function getEntity() {
        return InterrogationPlan::class;
    }

    public function getForm() {
        return $this->form;   
    }

    public function formAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $id = intval($post['id']);

        $iP = $this->sgbd()->get($id);
        $iP->setLatitude($post['lat']);
        $iP->setLongitude($post['lon']);     
        $this->form->bind($iP);

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'form' => $this->form
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

        $result = $this->sgbd()->save($datasIntPlan);
        $id = ($result['type'] == 'success') ? $result['msg']->getId() : 0;
        if($result['type'] == 'success') {
            $this->saveToPdf($result['msg']);
        }
        return new JsonModel(['id' => $id, 'type' => $result['type'], 'msg' => $result['msg']]);
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

    public function listAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        return (new ViewModel())
            ->setTerminal($this->getRequest()->isXmlHttpRequest())
            ->setVariables([
                'intPlans' => $this->sgbd()
                    ->getBy([
                        'where' => [],
                        'order' => [
                            'startTime' => 'DESC'
                        ],
                        'limit' => 5
                    ])
            ]);
    }

    public function getAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $iP = $this->sgbd()->get($post['id']);
        
        return new JsonModel($iP->getArrayCopy());
    }

    public function printAction() 
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        $iP = $this->sgbd()->get($this->params()->fromRoute('id'));

        $pdf = (new PdfModel())             
            ->setOption('paperSize', 'a4')
            ->setOption('filename', $iP->getPdfFileName())
            ->setVariables([
                'iP' => $iP
            ]);

        return $pdf;
    }

    public function mailAction()
    {
        if (!$this->authSarBeacons('read')) return new JsonModel();

        if (!array_key_exists('emailfrom', $this->config) | !array_key_exists('smtp', $this->config)) return new JsonModel();

        $post = $this->getRequest()->getPost();
        $iP = $this->sgbd()->get($post['id']);

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

        // echo $message->toString();
        // echo $message->isValid();
        if($message->isValid()) 
        {
            $transportOptions = new SmtpOptions($this->config['smtp']);
            $transport = (new Smtp())
                ->setOptions($transportOptions)
                ->send($message);
        }
        return new JsonModel();
    }

    private function authSarBeacons($action) {
        return (!$this->zfcUserAuthentication()->hasIdentity() or !$this->isGranted('sarbeacons.'.$action)) ? false : true;
    }

}