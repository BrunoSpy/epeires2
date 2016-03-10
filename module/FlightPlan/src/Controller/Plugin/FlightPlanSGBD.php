<?php

namespace FlightPlan\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use FlightPlan\Entity\FlightPlan;
use FlightPlan\Form\FlightPlanForm;

class FlightPlanSGBD extends AbstractPlugin
{
    protected $em;

    public function __invoke($em)
    {
        if(null === $this->em) $this->em = $em;
        return $this;
    }
    /*
     * Retourne toutes les entitées FlightPlan suivant un tableau de paramètres
     */
    public function getAll(array $params = [])
    {
        $allFp = [];
        foreach ($this->em->getRepository(FlightPlan::class)->findBy($params) as $fp)
        {
            $allFp[] = $fp;
        }
        return $allFp;
    }
    /*
     * Retourne une entitée FlightPlan suivant un ID, Si pas d'ID on retourne un new FlightPlan.
     */
    public function get($id = null)
    {
        if ($id) {
            $fp = $this->em->getRepository(FlightPlan::class)->find($id);
            if ($fp == null or !$fp->isValid()) return null;
        } else {
            $fp = new FlightPlan();
        }
        return $fp;
    }

    public function save(Parameters $p)
    {
        $id = $p['id'];
        $fp   = $this->get($id);
        $fpForm = new FlightPlanForm($this->em);
        $fpForm->getForm()->setData($p);
        $pluginMessages = $this->getController()->fpMessages();
        if ($fpForm->getForm()->isValid()) {
            try {
                $fp = (new DoctrineHydrator($this->em))->hydrate($fpForm->getForm()->getData(), $fp);

                $this->em->persist($fp);
                $this->em->flush();

                if ($id) $pluginMessages->add('edit', 'success', [$fp->getAircrafid()]);
                else $pluginMessages->add('add', 'success', [$fp->getAircraftid()]);
            } catch (\Exception $ex) {
                if ($id) $pluginMessages->add('edit', 'error', [$ex]);
                else $pluginMessages->add('add', 'error', [$ex]);
            }
        } else {
            $pluginMessages->add('form', 'error', [$fpForm->showErrors()]);
        }
    }

    public function del($id)
    {
        $fp = $this->get($id);
        if (is_a($fp, FlightPlan::class)) {
            $pluginMessages = $this->getController()->fpMessages();
            try {
                $this->em->remove($fp);
                $this->em->flush();

                $pluginMessages->add('del', 'success', [$fp->getAircraftid()]);
            } catch (\Exception $ex) {
                $pluginMessages->add('del', 'error', [$ex]);
            }
        }
    }
}