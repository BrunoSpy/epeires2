<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\InterrogationPlan;
use Application\Form\SarBeaconsForm;

class SarBeaconsSGBD extends AbstractPlugin
{
    protected $em;

    public function __invoke($em)
    {
        if(null === $this->em) $this->em = $em;
        return $this;
    }

    public function get($id = null)
    {
        if (!$id) return new InterrogationPlan();
        $intPlan = $this->em->getRepository(InterrogationPlan::class)->find($id);   
        return ($intPlan == null or !$intPlan->isValid()) ? null : $intPlan;
    }


    public function getAll($params = [])
    {        
        $intPlans = [];
        foreach ($this->em->getRepository(InterrogationPlan::class)->findBy($params) as $intPlan)
        {
            $intPlans[] = $intPlan;
        }
        return $intPlans;
    }

    public function save($p)
    {
        $c = $this->getController();
        // $pm = $c->SarBeaconsMessages();

        $sbForm = new SarBeaconsForm($this->em);
        $form = $sbForm->getForm();
        $form->setData($p);

        if($form->isValid()) {
            $intPlan = (new DoctrineHydrator($this->em))
                            ->hydrate($form->getData(), $this->get($p['id']));
                            
            $this->em->persist($intPlan);
            $this->em->flush();

            $return = [
                'id' => $intPlan->getId(), 
                'type' => 'success',
                'message' => 'Plan d\'interrogation sauvegardé.'
                ];

        } else { 
            $return = [
                'id' => '', 
                'type' => 'error',
                'message' => 'Données invalides : '.$sbForm->printErrors()
                ];
        }
        return $return;
        // }
    }

    // public function del($id)
    // {
    //     $afis = $this->get($id);
    //     if (is_a($afis, Afis::class)) {
    //         $pluginMessages = $this->getController()->afisMessages();
    //         try {
    //             $this->em->remove($afis);
    //             $this->em->flush();

    //             $pluginMessages->add('del', 'success', [$afis->getName()]);
    //         } catch (\Exception $ex) {
    //             $pluginMessages->add('del', 'error', [$ex]);
    //         }
    //     }
    // }
}