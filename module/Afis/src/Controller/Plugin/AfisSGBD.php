<?php

namespace Afis\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Afis\Entity\Afis;
use Afis\Form\AfisForm;


class AfisSGBD extends AbstractPlugin
{
    protected $em;

    public function __invoke($em)
    {
        if(null === $this->em) $this->em = $em;
        return $this;
    }
    /*
     * Retourne toutes les entitées AFIS suivant un tableau de paramètres
     */
    public function getAll(array $params = [])
    {
        $allAfis = [];
        foreach ($this->em->getRepository(Afis::class)->findBy($params) as $afis)
        {
            $allAfis[] = $afis;
        }
        return $allAfis;
    }

    /*
     * Retourne une entité Afis suivant un ID, Si pas d'ID on retourne un new Afis.
     */
    public function get($id = null)
    {
        if ($id) {
            $afis = $this->em->getRepository(Afis::class)->find($id);
            if ($afis == null or !$afis->isValid()) return null;
        } else {
            $afis = new Afis();
        }
        return $afis;
    }

    public function save(Parameters $p)
    {
        $id = $p['id'];
        $afis   = $this->get($id);
        $afisForm = new AfisForm($this->em);
        $afisForm->getForm()->setData($p);
        $pluginMessages = $this->getController()->afisMessages();
        if ($afisForm->getForm()->isValid()) {
            try {
                $afis = (new DoctrineHydrator($this->em))->hydrate($afisForm->getForm()->getData(), $afis);

                $this->em->persist($afis);
                $this->em->flush();

                if ($id) $pluginMessages->add('edit', 'success', [$afis->getName()]);
                else $pluginMessages->add('add', 'success', [$afis->getName()]);
            } catch (\Exception $ex) {
                if ($id) $pluginMessages->add('edit', 'error', [$ex]);
                else $pluginMessages->add('add', 'error', [$ex]);
            }
        } else {
            $pluginMessages->add('form', 'error', [$afisForm->showErrors()]);
        }
    }

    public function switchState(Parameters $p)
    {
        $afis   = $this->get($p['afisid']);
        if(is_a($afis,Afis::class)) {
            $pluginMessages = $this->getController()->afisMessages();
            try {
                $afis->setState((boolean) $p['state']);

                $this->em->persist($afis);
                $this->em->flush();

                $pluginMessages->add('switch', 'success', [$afis->getName(), $afis->getStrState()]);
            } catch (\Exception $ex) {
                $pluginMessages->add('switch', 'error', [$ex]);
            }
        }
    }

    public function del($id)
    {
        $afis = $this->get($id);
        if (is_a($afis, Afis::class)) {
            $pluginMessages = $this->getController()->afisMessages();
            try {
                $this->em->remove($afis);
                $this->em->flush();

                $pluginMessages->add('del', 'success', [$afis->getName()]);
            } catch (\Exception $ex) {
                $pluginMessages->add('del', 'error', [$ex]);
            }
        }
    }
}