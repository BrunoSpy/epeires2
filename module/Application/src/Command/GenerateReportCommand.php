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

namespace Application\Command;

use Application\Entity\Organisation;
use Core\Service\EmailService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use DOMPDFModule\View\Model\PdfModel;
use DOMPDFModule\View\Renderer\PdfRenderer;
use Laminas\Mime\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Laminas\View\Model\ViewModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateReportCommand
 * @package Application\Command
 */
class GenerateReportCommand extends Command
{
    protected static $defaultName = 'epeires2:generate-command';

    private EntityManager $entityManager;
    private PdfRenderer $viewpdfrenderer;
    private EmailService $emailService;

    public function __construct(EntityManager $entityManager, PdfRenderer $viewpdfrenderer, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->viewpdfrenderer = $viewpdfrenderer;
        $this->emailService = $emailService;
        parent::__construct();
    }

    /**
     * report [--email] [--delta=] <orgshortname>
     */
    protected function configure()
    {
        $this
            ->setDescription('Generate IPO report for the current (+-delta) day.')
            ->addOption('email', null, InputOption::VALUE_NONE, 'Sends the report to the IPO')
            ->addOption('delta', null, InputOption::VALUE_REQUIRED, 'Days to add to the current day. (Import yesterday : --delta=-1)', 0)
            ->addArgument('orgshortname', InputArgument::REQUIRED, 'Which organisation to use.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orgName = $input->getArgument('orgshortname');
        $organisation = $this->getEntityManager()->getRepository(Organisation::class)->findOneBy(array('shortname'=>$orgName));
        if($organisation == null) {
            $output->writeln('Impossible de trouver l\'organisation spécifiée.');
            return Command::FAILURE;
        }

        $j = $input->getOption('delta');

        $day = new \DateTime('now');
        if ($j) {
            if ($j > 0) {
                $day->add(new \DateInterval('P' . $j . 'D'));
            } else {
                $j = - $j;
                $interval = new \DateInterval('P' . $j . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }

        $day = $day->format(DATE_RFC2822);

        $daystart = new \DateTime($day);
        $offset = $daystart->getTimezone()->getOffset($daystart);
        $daystart->setTimezone(new \DateTimeZone('UTC'));
        $daystart->add(new \DateInterval("PT" . $offset . "S"));
        $daystart->setTime(0, 0, 0);

        $dayend = new \DateTime($day);
        $dayend->setTimezone(new \DateTimeZone('UTC'));
        $dayend->add(new \DateInterval("PT" . $offset . "S"));
        $dayend->setTime(23, 59, 59);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('parent'))
            ->andWhere(Criteria::expr()->eq('system', false))
            ->orderBy(array('place' => Criteria::ASC));

        $cats = $this->getEntityManager()->getRepository('Application\Entity\Category')->matching($criteria);

        $eventsByCats = array();
        foreach ($cats as $cat) {
            $category = array();
            $category['name'] = $cat->getName();
            $category['events'] = $this->getEntityManager()
                ->getRepository('Application\Entity\Event')
                ->getEvents(null, $day, null, null, true, array($cat->getId()), array(1,2,3,4));
            $category['childs'] = array();
            foreach ($cat->getChildren() as $subcat) {
                $subcategory = array();
                $subcategory['events'] = $this->getEntityManager()
                    ->getRepository('Application\Entity\Event')
                    ->getEvents(null, $day, null, null, true, array($subcat->getId()), array(1,2,3,4));
                $subcategory['name'] = $subcat->getName();
                $category['childs'][] = $subcategory;
            }
            $eventsByCats[] = $category;
        }

        $pdf = new PdfModel();
        $pdf->setOption('paperSize', 'a4');

        $formatter = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'dd_LL_yyyy');

        $pdf->setOption('filename', 'rapport_du_' . $formatter->format(new \DateTime($day)));

        $pdfView = new ViewModel($pdf);
        $pdfView->setTerminal(true)
            ->setTemplate('application/report/daily')
            ->setVariables(array(
                'events' => $eventsByCats,
                'day' => $day,
                'logs' => $this->getEntityManager()->getRepository('Application\Entity\Log'),
                'opsups' => $this->getEntityManager()->getRepository('Application\Entity\Log')->getOpSupsChanges($daystart, $dayend, false, 'ASC')));

        $html = $this->viewpdfrenderer->getHtmlRenderer()->render($pdfView);
        $engine = $this->viewpdfrenderer->getEngine();

        $engineOptions = $engine->getOptions();
        $engineOptions->setChroot(__DIR__.'/../../../../..');
        $engine->setOptions($engineOptions);
        $engine->loadHtml($html);
        $engine->render();

        // creating directory if it doesn't exists
        if (! is_dir('data/reports')) {
            mkdir('data/reports');
        }

        file_put_contents('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', $engine->output());

        if($input->getOption('email') == true) {
            // prepare body with file attachment
            $text = new Part('Veuillez trouver ci-joint le rapport automatique de la journée du ' . $formatter->format(new \DateTime($day)));
            $text->type = Mime::TYPE_TEXT;
            $text->charset = 'utf-8';

            $fileContents = fopen('data/reports/rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf', 'r');
            $attachment = new Part($fileContents);
            $attachment->type = 'application/pdf';
            $attachment->filename = 'rapport_du_' . $formatter->format(new \DateTime($day)) . '.pdf';
            $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = Mime::ENCODING_BASE64;

            $mimeMessage = new Message();
            $mimeMessage->setParts(array(
                $text,
                $attachment
            ));

            try {
                $this->emailService->sendEmailTo(
                    $organisation->getIpoEmail(),
                    'Rapport automatique du ' . $formatter->format(new \DateTime($day)),
                    $mimeMessage,
                    $organisation
                );
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    private function getEntityManager() : EntityManager
    {
        return $this->entityManager;
    }
}