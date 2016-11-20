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
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Common\Collections\Criteria;

class SGBDPlugin extends AbstractPlugin
{
    protected $ctrller, $em, $entity, $repository; 
    //TODO  public function __invoke(SGBDAwareInterface $ctrller)
    public function __invoke()
    {   
        $this->ctrller = $this->getController();
        $this->em = $this->ctrller->getEntityManager();
        $this->entity = $this->ctrller::$class;
        $this->repository = $this->em->getRepository($this->entity);
        return $this;
    }

    public function getBy(array $params = [])
    {        
        $allObj = [];

        $where = (array_key_exists('where', $params) && is_array($params['where'])) ? $params['where'] : null;
        $order = (array_key_exists('order', $params) && is_array($params['order'])) ? $params['order'] : null; 
        $limit = (array_key_exists('limit', $params) && is_array($params['limit'])) ? $params['limit'] : null; 

        if ($where | $order | $limit) { 
            foreach ($this->repository->findBy($where, $order, $limit) as $obj)
            {
                $allObj[] = $obj;
            }
        } 
        else 
        {
            foreach ($this->repository->findBy($params) as $obj) $allObj[] = $obj;
        }
        return $allObj;
    }

    public function get(Int $id)
    {
        $obj = $this->repository->find($id);
        $obj = ($obj == null or !$obj->isValid()) ? new $this->entity : $obj;
        return $obj;
    }

    public function getByCriteria(Criteria $crit) 
    {
        return $this->repository->matching($crit);
    }

    public function save($p)
    {
        if (is_a($p, Parameters::class) || is_array($p))
        { 
            $obj = $this->get(intval($p['id']));
            $form = $this->ctrller->getForm()->setData($p);
            if (!$form->isValid()) return ['type' => 'error', 'msg' => $this->showErrors($form)];
            $obj = (new DoctrineHydrator($this->em))->hydrate($form->getData(), $obj);
        } 
        elseif (is_a($p, $this->entity)) 
        {
            $obj = $p;
        } 
        else return ['type' => 'error', 'msg' => 'Paramètres invalides'];

        try 
        {
            $this->em->persist($obj);
            $this->em->flush();

            return ['type' => 'success', 'msg' => $obj];
        } 
        catch (\Exception $ex) 
        {
            return ['type' => 'error', 'msg' => $ex];
        }
    }

    public function del(Int $id)
    {
        $obj = $this->get($id);
        if (!is_a($obj, $this->entity)) return ['type' => 'error', 'msg' => 'Objet Invalide'];

        try 
        {
            $this->em->remove($obj);
            $this->em->flush();

            return ['type' => 'success', 'msg' => $obj];
        } catch (\Exception $ex) {
            return ['type' => 'error', 'msg' => $ex];
        }
    }

    private function showErrors($form){
        $str = '';
        foreach ($form->getMessages() as $field => $messages)
            foreach ($messages as $typeErr => $message)
                $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}