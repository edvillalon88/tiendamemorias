<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Utils;
use AppBundle\Entity\usuario;
use Doctrine\Common\Collections\Criteria;

/**
 * Usuario controller.
 *
 */
class UsuarioController extends Controller
{
    public function loginAction(Request $request)
    {
        $helper = $this->get("security.authentication_utils"); 

        return $this->render('AppBundle:Usuario:login.html.twig', array(
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError()
        ));

    }

    public function logoutAction()
    {
        $this->get('security.token_storage')->setToken(null);

        return new RedirectResponse($this->generateUrl("homepage"));
    }

    public function errorAction(Request $request)
    {
        return $this->render("AppBundle:Usuario:error.html.twig", array(
            "title" => $request->get("title"),
            "subTitle" => $request->get("subTitle"),
            "text" => $request->get("text")
        ));
    }

    public function error_expiradoAction()
    {
        return $this->render("AppBundle:Usuario:error_expirado.html.twig");
    }

    public function indexAction()
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return $this->render("AppBundle:Usuario:error_expirado.html.twig");

        return new RedirectResponse($this->generateUrl("homepage"));
    }

    public function perfilAction()
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return $this->render("AppBundle:Usuario:error_expirado.html.twig");

        return $this->render('AppBundle:Usuario:perfil.html.twig', array(
            "user" => $logueado
        ));
    }

    public function updateUserAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $user = strtolower($request->get("editUsuario"));
        $em = $this->getDoctrine()->getManager();

        $logueado->setNombre($request->get("editNombre"));
        $logueado->setApellidos($request->get("editApellidos"));
        $logueado->setUser($user);

        $curr = $request->get("editCurrentPass");
        $newer = $request->get("editNewPass");
        $conf = $request->get("editConfPass");

        print_r($curr);

        if(!empty($curr) && !empty($newer) && !empty($conf))
        {
            if($this->get('security.password_encoder')->encodePassword($logueado, $curr) !=
                $this->getUser()->getPassword())
                return new Response(json_encode(null), 402);

            if($newer == $conf)
            {
                $password = $this->get('security.password_encoder')->encodePassword($logueado, $newer);
                $logueado->setPassword($password);
            }
        }

        $em->flush();

        return new Response(json_encode($logueado), 200);

    }

}
