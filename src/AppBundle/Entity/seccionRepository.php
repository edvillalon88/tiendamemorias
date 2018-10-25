<?php
/**
 * Created by PhpStorm.
 * User: carlos.rodriguez
 * Date: 24/03/2018
 * Time: 09:12 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;



class seccionRepository extends EntityRepository
{
    //Retorna el orden asignado a una sección o categoría nueva
    //de acuerdo con el mayor orden alamcenado
    public function getOrdenSeccion($principal)
    {
        $em = $this->getEntityManager();
        $seccions = $em->getRepository('AppBundle:seccion')->findAll();

        if(empty($seccions)) return 1;

        if(empty($principal)) {
            $result = $query = $em->createQuery('SELECT MAX(s.orden) FROM AppBundle:seccion s WHERE s.principal IS NULL')
                ->getSingleScalarResult();
        }
        else {
            $result = $query = $em->createQuery('SELECT MAX(s.orden) FROM AppBundle:seccion s WHERE s.principal ='.$principal)
                ->getSingleScalarResult();
        }

        $orden = (empty($result) ? 0 : $result) + 1;

        return $orden;
    }
}
