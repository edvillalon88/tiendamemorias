<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;

class DefaultController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $tienda = $em->find('AppBundle:tienda',1);
        return $this->render('default/indexnotuser.html.twig', array(
            'path'=>'homepage',
            'tienda'=>$tienda

        ));

    }

    public function detailAction($id){

        $em = $this->getDoctrine()->getManager();
        $seccion = $em->getRepository('AppBundle:seccion')->find($id);
        $cat = $em->getRepository('AppBundle:categoria')->findOneBy(array('seccion'=>$seccion));

        
        return $this->render('AppBundle:Seccion:categori_detail.html.twig', array(
            'entity'=>$seccion,
            'cat'=>$cat
        ));
    }

    public function abautAction()
    {
        $em = $this->getDoctrine()->getManager();
        $tienda = $em->find('AppBundle:tienda',1);
        return $this->render('default/abaut.html.twig', array(
            'tienda'=>$tienda));
    }

    public function contactAction()
    {
        return  $this->render('default/contact.html.twig', array(
            'path'=>'contact',
        ));
    }

    public function sendEmailAction(Request $request)
    {
        $contact = $request->get("Username");
        $email = $request->get("Email");
        $body = $request->get("Body");

        try{
            $messMemory = (new \Swift_Message('Contactando desde Memorias'))
                ->setFrom($email)
                ->setTo("memoriashabana@gmail.com")
                ->setBody("Contacto: ".$contact. "<br/> Cuerpo: ".$body);

            $messContact = (new \Swift_Message($this->get('translator')->trans('menu.Title')))
                ->setFrom("memoriashabana@gmail.com")
                ->setTo($email)
                ->setBody($this->get('translator')->trans('MensajeEmail'));

            $this->get('mailer')->send($messMemory);
            $this->get('mailer')->send($messContact);

            return new Response("ok", 200);
        }
        catch(Exception $e){
            return new Response("Error send email", 500);
        }

    }

    public function ofertasAction()
    {
        $em = $this->getDoctrine()->getManager();
        $secciones = $em->createQuery('SELECT sec FROM AppBundle:seccion sec WHERE sec.principal IS NULL ORDER BY sec.orden ASC')->getResult();
        $count = count($secciones);
        $result = array();
        $tienda = $em->find('AppBundle:tienda',1);
        for($i = 0; $i < $count; $i++ ){
            $seccion = $secciones[$i];
            $categorias = $em->createQuery('SELECT cat FROM AppBundle:seccion cat JOIN cat.principal sec Where sec.id = :id ORDER BY cat.orden ASC')
                ->setParameter('id',$secciones[$i]->getId())
                ->getResult();

            $c = array('seccion'=>$seccion, 'categorias'=>$categorias, 'cantCat' => count($categorias));
            array_push($result, $c);
        }
        return  $this->render('default/ofertas.html.twig', array(
            'path'=>'ofertas',
            'model'=>$result,
            'tienda'=>$tienda
        ));
    }
}
