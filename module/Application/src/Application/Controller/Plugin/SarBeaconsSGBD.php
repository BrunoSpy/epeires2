<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\InterrogationPlan;
class SarBeaconsSGBD extends AbstractPlugin
{
    protected $em;

    public function __invoke($em)
    {
        if(null === $this->em) $this->em = $em;
        return $this;
    }

    // public function getAll(array $params = [])
    // {
    //     $allAfis = [];
    //     foreach ($this->em->getRepository(Afis::class)->findBy($params) as $afis)
    //     {
    //         $allAfis[] = $afis;
    //     }
    //     return $allAfis;
    }

    public function get($id = null)
    {
        if (!$id) return new InterrogationPlan();
        $intPlan = $this->em->getRepository(InterrogationPlan::class)->find($id);   
        return ($intPlan == null or !$intPlan->isValid()) ? null : $intPlan;
    }

    public function save($p)
    {
        $c = $this->getController();
        $pm = $c->SarBeaconsMessages();

        $form = $c->getForm();
        $form->setData($p);

        $return = null;

        if($form->isValid()){

            $intPlan = (new DoctrineHydrator($this->em))
                            ->hydrate($form->getData(), $this->get($p['id']));

            $this->em->persist($intPlan);
            $this->em->flush();
            $return = $intPlan->getId();

        } else {
            $pm->add('form', 'error', [$form->getMessages()]);
            print_r($form->getMessages());
        }

        return $return;

        // if ($afisForm->getForm()->isValid()) {
        //     try {
        //         $afis = (new DoctrineHydrator($this->em))->hydrate($afisForm->getForm()->getData(), $afis);

        //         $this->em->persist($afis);
        //         $this->em->flush();

        //         if ($id) $pluginMessages->add('edit', 'success', [$afis->getName()]);
        //         else $pluginMessages->add('add', 'success', [$afis->getName()]);
        //     } catch (\Exception $ex) {
        //         if ($id) $pluginMessages->add('edit', 'error', [$ex]);
        //         else $pluginMessages->add('add', 'error', [$ex]);
        //     }
        // } else {
        //     $pluginMessages->add('form', 'error', [$afisForm->showErrors()]);
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