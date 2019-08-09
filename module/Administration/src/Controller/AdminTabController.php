<?php


namespace Administration\Controller;


use Application\Controller\FormController;
use Doctrine\ORM\EntityManager;

class AdminTabController extends FormController
{

    private $entityManager;
    private $config;
    private $translator;

    public function __construct(EntityManager $entityManager, $config, $translator)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->translator = $translator;
    }

    public function indexAction()
    {
        $this->layout()->lang = $this->config['lang'];
    }

    public function getAppConfig()
    {
        return $this->config;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function translate($string)
    {
        return $this->translator->translate($string);
    }
}