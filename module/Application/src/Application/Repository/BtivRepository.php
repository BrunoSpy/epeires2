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
namespace Application\Repository;

// use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Stdlib\Parameters;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

class BtivRepository extends EntityRepository
{
    public function getBy(array $params = [])
    {        
        $allObj = [];

        $where = (array_key_exists('where', $params) && is_array($params['where'])) ? $params['where'] : null;
        $order = (array_key_exists('order', $params) && is_array($params['order'])) ? $params['order'] : null; 
        $limit = (array_key_exists('limit', $params) && is_int($params['limit'])) ? $params['limit'] : 0;

        if ($where | $order | $limit) {
            foreach ($this->findBy($where, $order, $limit) as $obj)
            {
                $allObj[] = $obj;
            }
        }
        else
        {
            foreach ($this->findBy($params) as $obj) $allObj[] = $obj;
        }
        return $allObj;
    }

    // public function get($id)
    // {
    //     $obj = $this->find($id);
    //     $obj = ($obj == null or !$obj->isValid()) ? new $this->entity : $obj;
    //     return $obj;
    // }

    public function getByCriteria(Criteria $crit) 
    {
        return $this->matching($crit);
    }

    public function hydrate($datas, $obj) {
        if(!$obj) return false;

        $hyd = new DoctrineHydrator($this->getEntityManager());
        return $hyd->hydrate($datas, $obj);
    }

    public function save($obj)
    {
        $em = $this->getEntityManager();
        try 
        {
            $em->persist($obj);
            $em->flush();
            $msg = [
                'type' => 'success', 
                'msg' => 'Opération effectuée avec succès.'
            ];            
        } 
        catch (\Exception $ex) 
        {
            $msg = [
                'type' => 'error', 
                'msg' => $ex->getMessage()
            ]; 
        }
        return $msg;
    }

    public function del($obj)
    {
        $em = $this->getEntityManager();
        try 
        {
            $em->remove($obj);
            $em->flush();

            $msg = [
                'type' => 'success', 
                'msg' => 'Opération effectuée avec succès.'
            ];  
        } catch (\Exception $ex) {
            $msg = [
                'type' => 'success', 
                'msg' => $ex->getMessage()
            ]; 
        }
        return $msg;
    }

    private function showErrors($form){
        $str = '';
        foreach ($form->getMessages() as $field => $messages)
            foreach ($messages as $typeErr => $message)
                $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}