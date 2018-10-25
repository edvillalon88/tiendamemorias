<?php

namespace AppBundle\Controller;

use AppBundle\Entity\tienda;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TiendaController extends Controller
{
    public function tiendaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $t = $em->find('AppBundle:tienda', 1);

        $objTien = array(
            'descripcion'=>$t->getDescripcion(),
            'nosotros'=>$t->getNosotros(),
        );

        $tiendaEng = $em->find('AppBundle:tienda', 1);
        $tiendaEng->setTranslatableLocale('en');
        $em->refresh($tiendaEng);

        return $this->render('AppBundle:Tienda:tienda.html.twig', array(
                'tienda'=>$objTien,
                'tiendaEng'=>$tiendaEng
            ));
    }

    public function updateAction(Request $request)
    {
        $logueado = $this->getUser();
        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $desc = $request->get('Desc');
        $descTsl = $request->get('DescTsl');
        $Nosotros = $request->get('Nosotros');
        $NosotrosTsl = $request->get('NosotrosTsl');

        $em = $this->getDoctrine()->getManager();

        $tienda = $em->find('AppBundle:tienda', 1);
        $tienda->setDescripcion($desc);
        $tienda->setNosotros($Nosotros);
        $em->flush();


        $tienda_en = $em->getRepository('AppBundle:tienda')->find(1);
        $tienda_en->setDescripcion($descTsl);
        $tienda_en->setNosotros($NosotrosTsl);
        $tienda_en->setTranslatableLocale('en');
        $em->persist($tienda_en);
        $em->flush();

        return new Response(json_encode($tienda), 200);
    }

}
