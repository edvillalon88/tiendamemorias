<?php

namespace AppBundle\Controller;

use AppBundle\Entity\imagen;
use Doctrine\ORM\EntityNotFoundException;
use Monolog\Handler\Curl\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Utils;
use AppBundle\Entity\seccion;
use AppBundle\Entity\categoria;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Seccion controller.
 */
class SeccionController extends Controller
{
    public function indexAction()
    {
        //return new RedirectResponse($this->generateUrl("seccions"));
        return new RedirectResponse($this->generateUrl("homepage"));
    }

    public function seccionsAction()
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return $this->render("AppBundle:Usuario:error_expirado.html.twig");

        return $this->render('AppBundle:Seccion:seccions.html.twig');
    }

    public function _seccionsAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $search = $request->get('search')["value"];

        if($search != "")
        {
            //OR s.img_row LIKE :text OR s.visible LIKE :text
            $query = $em->createQuery('SELECT COUNT(s.id) FROM AppBundle:seccion s WHERE s.nombre LIKE :text AND s.principal IS NULL')
                ->setParameter('text', '%'.$search.'%');

            $entities = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.nombre LIKE :text AND s.principal IS NULL ORDER BY s.orden')
                ->setParameter('text', '%'.$search.'%')
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }
        else
        {
            $query = $em->createQuery('SELECT COUNT(s.id) FROM AppBundle:seccion s WHERE s.principal IS NULL');

            $entities = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.principal IS NULL ORDER BY s.orden')
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }

        $count = $query->getSingleScalarResult();
        $p = new Utils();
        $cant = count($entities);
        for($i = 0; $i < $cant; $i++){
            $tempEnt = $em->find("AppBundle:seccion", $entities[$i]["id"]);
            $tempEnt->setTranslatableLocale('en');
            $em->refresh($tempEnt);
            $entities[$i]["NombreENG"]= $tempEnt->getNombre();
        }

        return new Response(json_encode($p->GetPagination($request, $entities, $count )), 200);
    }

    public function saveSeccionAction(Request $request)
    {
        $util = new Utils();
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");

        //Insertar
        if($id == null) {
            // Crear sección con atributos: nobmre, visible,
            // orden (obtenido acorde a los datos almacenados) y principal en null
            //
            $seccion = new seccion();

            $seccion->setNombre($request->get("SecName"));
            $seccion->setOrden($em->getRepository('AppBundle:seccion')->getOrdenSeccion(null));
            $seccion->setVisible(false);
            $seccion->setPrincipal(null);
            $em->persist($seccion);
            $em->flush();

            // Traducir los contenidos de la oferta al inglés
            $id = $seccion->getId();
            $seccion_en = $em->getRepository('AppBundle:seccion')->find($id);
            $seccion_en->setNombre($request->get("SecNameTsl"));
            $seccion_en->setTranslatableLocale('en');
            $em->persist($seccion_en);
            $em->flush();

            // Obtener variable para determinar si se crea
            // solo la sección o si además se inserta una
            // categoría

            $dual = $request->get("Dual");

            if($dual == "true")
            {
                // Crear la seccion de tipo categoría con los atributos
                // nobmre, visible,orden (obtenido acorde a los datos almacenados) y principal
                // Crear los datos adicionales de categoría


                $newSecCateg = new seccion();
                $newSecCateg->setNombre($request->get("CatName"));
                $newSecCateg->setOrden($em->getRepository('AppBundle:seccion')->getOrdenSeccion($seccion->getId()));
                $newSecCateg->setVisible(false);
                $newSecCateg->setImgRow($request->get("ImgRow"));
                $newSecCateg->setPrincipal($seccion);
                $em->persist($newSecCateg);


                $newCateg = new categoria();
                $newCateg->setTitulo($request->get("Title"));
                $newCateg->setDescripcion($request->get("Desc"));
                $newCateg->setSeccion($newSecCateg);
                $em->persist($newCateg);
                $em->flush();

                $archivo = $request->files->get("Images");

                if(!empty($archivo))
                {
                    $ext = $archivo->guessExtension();
                    $name = 'images.'.$ext;

                    if($ext == 'zip'){
                        $uri = $util->getUploadRootDir().$newSecCateg->getId()."/";

                        $dirComplete = $uri.$name;
                        $archivo->move($uri, $name);

                        $zipObject = new \ZipArchive();
                        $zipObject->open($dirComplete);
                        $zip= zip_open($dirComplete);
                        if($zip){
                            $i = 1;
                            while($fichero = zip_read($zip)){
                                $name = zip_entry_name($fichero);
                                if(strpos($name, ".") == 0) continue;

                                $images = $em->getRepository('AppBundle:imagen')->findBy(array(
                                    'nombre'=>$name,
                                    'seccion'=>$newSecCateg
                                ));

                                if(empty($images)){

                                    //extraigo el zip
                                    $zipObject->extractTo($uri, array($name));
                                    $image = new imagen();
                                    $image->setNombre($name);
                                    $image->setTexto('');
                                    $image->setNumeracion($i);
                                    $image->setSeccion($newSecCateg);
                                    $em->persist($image);
                                    $i++;

                                }
                            }
                        }
                        $zipObject->close();
                        zip_close($zip);
                        //borro el rar
                        unlink($dirComplete);
                    }else{
                        return new Response(json_encode(array('error'=>true, 'msg'=>'Tipo de fichero incorrecto')), 500);
                    }

                }

                $em->flush();

                // Traducir los contenidos de la oferta al inglés
                $id = $newSecCateg->getId();
                $newSecCateg_en = $em->getRepository('AppBundle:seccion')->find($id);
                $newSecCateg_en->setNombre($request->get("CatNameTsl"));
                $newSecCateg_en->setTranslatableLocale('en');
                $em->persist($newSecCateg_en);

                $idCateg = $newCateg->getId();
                $newSecCateg_en = $em->getRepository('AppBundle:categoria')->find($idCateg);
                $newSecCateg_en->setTitulo($request->get("TitleTsl"));
                $newSecCateg_en->setDescripcion($request->get("DescTsl"));
                $newSecCateg_en->setTranslatableLocale('en');
                $em->persist($newSecCateg_en);
                $em->flush();


            }
        }
        //Modificar
        else{
            $seccion = $em->getRepository('AppBundle:seccion')->find($id);
            $seccion->setNombre($request->get("EdSecName"));

            $publish = $request->get("CheckPubish");

            $seccion->setVisible($publish);

            $categorias = $seccion->getCategorias();

            if(!empty($categorias))
            {
                foreach($categorias as $cat)
                    $cat->setVisible($publish);
            }

            $em->flush();

            // Traducir los contenidos de la oferta al inglés
            $id = $seccion->getId();
            $seccion_en = $em->getRepository('AppBundle:seccion')->find($id);
            $seccion_en->setNombre($request->get("EdSecNameTsl"));
            $seccion_en->setTranslatableLocale('en');
            $em->persist($seccion_en);
            $em->flush();
        }



        return new Response(json_encode($seccion), 200);
    }

    public function deleteSeccionAction(Request $request)
    {
        $util = new Utils();
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();

        $seccion = $em->getRepository('AppBundle:seccion')->find($id);

        if(empty($seccion))
            return new Response(json_encode(null), 200);

        $orden = $seccion->getOrden();

        $categorias = $seccion->getCategorias();

        if(!empty($categorias))
        {
            foreach($categorias as $cat)
            {
                $subfolder = "";
                $imgs = $cat->getImagenes();
                if(!empty($imgs)){
                    foreach($imgs as $img)
                    {
                        try {
                            $file = explode("/", $img->getNombre());

                            if(count($file) == 1)
                            {
                                if(strpos($img->getNombre(), ".") == 0) continue;

                                if(is_file($util->getUploadRootDir().$cat->getId()."/".$img->getNombre()))
                                    unlink($util->getUploadRootDir().$cat->getId()."/".$img->getNombre());
                            }
                            else{
                                if($subfolder == "")
                                    $subfolder = $file[0];

                                if(is_file($util->getUploadRootDir().$cat->getId()."/".$file[0]."/".$file[1]))
                                    unlink($util->getUploadRootDir().$cat->getId()."/".$file[0]."/".$file[1]);
                            }
                        } catch (Exception $e) { }

                        $em->remove($img);
                    }
                }

                if($subfolder != "" && is_dir($util->getUploadRootDir().$cat->getId()."/".$subfolder))
                    rmdir($util->getUploadRootDir().$cat->getId()."/".$subfolder);

                if(is_dir($util->getUploadRootDir().$cat->getId()))
                    rmdir($util->getUploadRootDir().$cat->getId());

                $em->remove($cat->getInfoCategoria());
                $em->remove($cat);
            }
        }

        $em->remove($seccion);

        $seciones = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden > :orden AND s.principal IS NULL ORDER BY s.orden')
            ->setParameter('orden', $orden)
            ->getResult();

        foreach($seciones as $secc)
        {
            $secc->setOrden($orden);
            $orden++;
        }

        $em->flush();

        return new Response(json_encode($seccion), 200);
    }


    public function categoriesAction()
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return $this->render("AppBundle:Usuario:error_expirado.html.twig");

        $em = $this->getDoctrine()->getManager();

        $seccions = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.principal IS NULL ORDER BY s.orden')
            ->getResult();


        return $this->render('AppBundle:Seccion:categories.html.twig', array(
            "seccions" => $seccions
        ));
    }

    public function _categoriesAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $search = $request->get('search')["value"];
        $columns = $request->get('columns');
        $seccionId = $columns[1]["search"]["value"];


        if($search != "")
        {
            $query = $em->createQuery('SELECT COUNT(c.id) FROM AppBundle:categoria c JOIN c.seccion s JOIN s.principal p WHERE (c.titulo LIKE :text AND s.visible LIKE :text) AND p.id = :seccionId')
                ->setParameter('seccionId', $seccionId )
                ->setParameter('text', '%'.$search.'%');

            $entities = $em->createQuery('SELECT c,s,p FROM AppBundle:categoria c JOIN c.seccion s JOIN s.principal p WHERE (c.titulo LIKE :text AND s.visible LIKE :text) AND p.id = :seccionId ORDER BY s.orden')
                ->setParameter('seccionId', $seccionId )
                ->setParameter('text', '%'.$search.'%')
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }
        else
        {
            $query = $em->createQuery('SELECT COUNT(c.id) FROM AppBundle:categoria c JOIN c.seccion s JOIN s.principal p WHERE p.id = :seccionId')
                ->setParameter('seccionId', $seccionId );

            $entities = $em->createQuery('SELECT c,s,p FROM AppBundle:categoria c JOIN c.seccion s JOIN s.principal p WHERE p.id = :seccionId ORDER BY s.orden')
                ->setParameter('seccionId', $seccionId )
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }

        $count = $query->getSingleScalarResult();
        $p = new Utils();

        $cant = count($entities);
        for($i = 0; $i < $cant; $i++){
            $tempEnt = $em->find("AppBundle:categoria", $entities[$i]["id"]);
            $tempEnt->setTranslatableLocale('en');
            $em->refresh($tempEnt);
            $entities[$i]["TituloEng"]= $tempEnt->getTitulo();
            $entities[$i]["DescripcionEng"]= $tempEnt->getDescripcion();
        }

        return new Response(json_encode($p->GetPagination($request, $entities, $count )), 200);
    }

    public function getCategoryAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");

        $seccion = $em->getRepository('AppBundle:seccion')->find($id);

        $seccionEng = $em->getRepository('AppBundle:seccion')->find($id);
        if(empty($seccion))
            return new Response(json_encode(null), 200);

        $seccion->setTranslatableLocale('es');
        $em->refresh($seccion);

        $nombre = $seccion->getNombre();
        $titulo = $seccion->getInfoCategoria() == null ? "" : $seccion->getInfoCategoria()->getTitulo();
        $desc = $seccion->getInfoCategoria() == null ? "" : $seccion->getInfoCategoria()->getDescripcion();

        $obj = array(
            "seccionId" => $seccion->getId(),
            "principalId" => $seccion->getPrincipal() == null ? 0 : $seccion->getPrincipal()->getId(),
            "nombre" => $nombre,
            "nombreEng" => $seccionEng->getNombre(),
            "orden" => $seccion->getOrden(),
            "visible" => $seccion->getVisible(),
            "img_row" => $seccion->getImgRow(),
            "titulo" => $titulo,
            "tituloEng" => $titulo,
            "desc" => $desc,
            "descEng" => $desc
        );



        $seccionEng->setTranslatableLocale('en');
        $tempCat = $seccionEng->getInfoCategoria();
        $tempCat->setTranslatableLocale('en');
        $em->refresh($seccionEng);
        $em->refresh($tempCat);

        $obj["nombreEng"] = $seccionEng->getNombre();
        $obj["tituloEng"] = $seccion->getInfoCategoria() == null ? "" : $tempCat->getTitulo();
        $obj["descEng"] = $seccion->getInfoCategoria() == null ? "" : $tempCat->getDescripcion();

        return new Response(json_encode($obj), 200);
    }

    public function saveCategoryAction(Request $request)
    {
        $util = new Utils();
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $seccion = $em->getRepository('AppBundle:seccion')->find($request->get("SeccionsIns"));

        $id = $request->get("Id");

        //Insertar
        if($id == "") {
            // Crear la seccion de tipo categoría con los atributos
            // nobmre, visible,orden (obtenido acorde a los datos almacenados) y principal
            // Crear los datos adicionales de categoría

            $orden = $em->getRepository('AppBundle:seccion')->getOrdenSeccion($seccion->getId());
            $secCat = new seccion();
            $secCat->setNombre($request->get("CatName"));
            $secCat->setOrden($orden);
            $secCat->setVisible(false);
            $secCat->setPrincipal($seccion);
            $secCat->setImgRow($request->get("ImgRow"));
            $em->persist($secCat);

            $newCateg = new categoria();
            $newCateg->setTitulo($request->get("Title"));
            $newCateg->setDescripcion($request->get("Desc"));
            $newCateg->setSeccion($secCat);
            $em->persist($newCateg);
            $em->flush();

            $archivo = $request->files->get("Images");

            if(!empty($archivo))
            {
                $ext = $archivo->guessExtension();
                if($ext == 'zip'){
                    $name = 'images.'.$ext;

                    $uri = $util->getUploadRootDir().$secCat->getId()."/";

                    $dirComplete = $uri.$name;

                    $archivo->move($uri, $name);

                    $zipObject = new \ZipArchive();
                    $zipObject->open($dirComplete);


                    $zip= zip_open($dirComplete);
                    if($zip){
                        $i = 1;
                        while($fichero = zip_read($zip)){
                            $name = zip_entry_name($fichero);
                            if(strpos($name, ".") == 0) continue;

                            $images = $em->getRepository('AppBundle:imagen')->findBy(array(
                                'nombre'=>$name,
                                'seccion'=>$secCat
                            ));

                            if(empty($images)){

                                //extraigo el zip
                                $zipObject->extractTo($uri, array($name));
                                $image = new imagen();
                                $image->setNombre($name);
                                $image->setTexto('');
                                $image->setNumeracion($i);
                                $image->setSeccion($secCat);
                                $em->persist($image);
                                $i++;

                            }
                        }
                    }
                    $zipObject->close();
                    zip_close($zip);
                    //borro el rar
                    unlink($dirComplete);
                }else{
                    return new Response(json_encode(array('error'=>true, 'msg'=>'Tipo de fichero incorrecto')), 500);
                }

            }

            $em->flush();

            // Traducir los contenidos de la oferta al inglés
            $id = $secCat->getId();
            $secCat_eng = $em->getRepository('AppBundle:seccion')->find($id);
            $secCat_eng->setNombre($request->get("CatNameTsl"));
            $secCat_eng->setTranslatableLocale('en');
            $em->persist($secCat_eng);

            // Traducir los contenidos de la oferta al inglés
            $idcat = $newCateg->getId();
            $newCateg_eng = $em->getRepository('AppBundle:categoria')->find($idcat);
            $newCateg_eng->setTitulo($request->get("TitleTsl"));
            $newCateg_eng->setDescripcion($request->get("DescTsl"));
            $newCateg_eng->setTranslatableLocale('en');
            $em->persist($newCateg_eng);

            $em->flush();
        }
        //Modificar
        else{
            $secCat = $em->getRepository('AppBundle:seccion')->find($id);
            $onlyImg = $request->get("OnlyImg");

            //Si no viene esta variable se modifica
            //Si viene indica que solo se cambiarán las
            //Imágenes, dejando así de ejecutar el código
            // de modificación
            if(empty($onlyImg))
            {
                $cat = $em->getRepository('AppBundle:categoria')->findOneBy(array(
                    'seccion' => $secCat
                ));

                $secCat->setNombre($request->get("CatName"));
                $secCat->setImgRow($request->get("ImgRow"));
                $secCat->setVisible($request->get("CheckPubish"));
                $secCat->setPrincipal($seccion);

                $cat->setTitulo($request->get("Title"));
                $cat->setDescripcion($request->get("Desc"));
                $em->flush();

                //falta esta traduccion

                // Traducir los contenidos de la oferta al inglés
                $id = $secCat->getId();
                $secCat_eng = $em->getRepository('AppBundle:seccion')->find($id);
                $secCat_eng->setNombre($request->get("CatNameTsl"));
                $secCat_eng->setTranslatableLocale('en');
                $em->persist($secCat_eng);

                $idCat = $cat->getId();
                $Cat_eng = $em->getRepository('AppBundle:categoria')->find($idCat);
                $Cat_eng->setTitulo($request->get("TitleTsl"));
                $Cat_eng->setDescripcion($request->get("DescTsl"));
                $Cat_eng->setTranslatableLocale('en');
                $em->persist($Cat_eng);

                $em->flush();
            }

            $archivo = $request->files->get("Images");
            if(!empty($archivo)) {

                $ext = $archivo->guessExtension();
                //Gestiona las imagenes al final para que sirva
                //El mismo codigo a la hora de cambiar las imágenes
                //solamente

                if($ext == "zip") {

                    //Se eliminan las imagenes qeu existen
                    $subfolder = "";
                    $imgs = $secCat->getImagenes();
                    if(!empty($imgs)){
                        foreach($imgs as $img)
                        {
                            try {
                                $file = explode("/", $img->getNombre());

                                if(count($file) == 1)
                                {
                                    if(strpos($img->getNombre(), ".") == 0) continue;

                                    if(is_file($util->getUploadRootDir().$secCat->getId()."/".$img->getNombre()))
                                        unlink($util->getUploadRootDir().$secCat->getId()."/".$img->getNombre());
                                }
                                else{
                                    if($subfolder == "")
                                        $subfolder = $file[0];

                                    if(is_file($util->getUploadRootDir().$secCat->getId()."/".$file[0]."/".$file[1]))
                                        unlink($util->getUploadRootDir().$secCat->getId()."/".$file[0]."/".$file[1]);
                                }
                            } catch (Exception $e) { }

                            $em->remove($img);
                        }

                        $em->flush();
                    }

                    if($subfolder != "" && is_dir($util->getUploadRootDir().$secCat->getId()."/".$subfolder))
                        rmdir($util->getUploadRootDir().$secCat->getId()."/".$subfolder);

                    //Se procede a guardar las nuevas imagenes

                    $name = 'images.' . $ext;

                    //return new Response(array('error'=>true, 'msg'=>'Tipo de fichero incorrecto'), 500);

                    $uri = $util->getUploadRootDir() . $secCat->getId() . "/";

                    $dirComplete = $uri . $name;
                    $archivo->move($uri, $name);

                    $zipObject = new \ZipArchive();
                    $zipObject->open($dirComplete);
                    $zip= zip_open($dirComplete);
                    if($zip){
                        $i = 1;
                        while($fichero = zip_read($zip)){
                            $name = zip_entry_name($fichero);
                            if(strpos($name, ".") == 0) continue;

                            $images = $em->getRepository('AppBundle:imagen')->findBy(array(
                                'nombre'=>$name,
                                'seccion'=>$secCat
                            ));

                            if(empty($images)){

                                //extraigo el zip
                                $zipObject->extractTo($uri, array($name));
                                $image = new imagen();
                                $image->setNombre($name);
                                $image->setTexto('');
                                $image->setNumeracion($i);
                                $image->setSeccion($secCat);
                                $em->persist($image);
                                $i++;

                            }
                        }
                    }
                    $zipObject->close();
                    zip_close($zip);
                    //borro el rar
                    unlink($dirComplete);
//
//                //abro el rar
//                $rar_file = rar_open($dirComplete);
//                //obtengo la lista
//                $list = rar_list($rar_file);
//
//
//                $i = 1;
//                //recorro cada fichero
//                foreach ($list as $file) {
//
//                    $name = $file->getName();
//
//                    if(strpos($name, ".") == 0) continue;
//
//                    $entry = rar_entry_get($rar_file, $name);
//
//                    $images = $em->getRepository('AppBundle:imagen')->findBy(array(
//                        'nombre' => $name,
//                        'seccion' => $secCat
//                    ));
//
//                    if (empty($images)) {
//
//                        $entry->extract($uri);
//
//                        $image = new imagen();
//                        $image->setNombre($file->getName());
//                        $image->setTexto('');
//                        $image->setNumeracion($i);
//                        $image->setSeccion($secCat);
//                        $em->persist($image);
//                        $i++;
//
//                    }
//                }
//
//                rar_close($rar_file);
//                //borro el rar
//                unlink($dirComplete);

                    $em->flush();
                }
                else{
                    return new Response(json_encode(array('error'=>true, 'msg'=>'Tipo de fichero incorrecto')), 500);
                }
            }
        }

        return new Response(json_encode($secCat), 200);
    }

    public function deleteCategoryAction(Request $request)
    {
        $util = new Utils();
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $id = $request->get('id');;
        $em = $this->getDoctrine()->getManager();

        $seccion = $em->getRepository('AppBundle:seccion')->find($id);

        if(empty($seccion))
            return new Response(json_encode(null), 200);

        $orden = $seccion->getOrden();
        $father = $seccion->getPrincipal()->getId();

        $subfolder = "";
        $imgs = $seccion->getImagenes();

        if(!empty($imgs)){

            foreach($imgs as $img)
            {
                try {
                    $file = explode("/", $img->getNombre());

                    if(count($file) == 1)
                    {
                        if(strpos($img->getNombre(), ".") == 0) continue;

                        if(is_file($util->getUploadRootDir().$seccion->getId()."/".$img->getNombre()))
                            unlink($util->getUploadRootDir().$seccion->getId()."/".$img->getNombre());
                    }
                    else{
                        if($subfolder == "")
                            $subfolder = $file[0];

                        if(is_file($util->getUploadRootDir().$seccion->getId()."/".$file[0]."/".$file[1]))
                            unlink($util->getUploadRootDir().$seccion->getId()."/".$file[0]."/".$file[1]);
                    }
                } catch (Exception $e) { }
                $em->remove($img);
            }

            if($subfolder != "" && is_dir($util->getUploadRootDir().$seccion->getId()."/".$subfolder))
                rmdir($util->getUploadRootDir().$seccion->getId()."/".$subfolder);

            if(is_dir($util->getUploadRootDir().$seccion->getId()))
                rmdir($util->getUploadRootDir().$seccion->getId());
        }

        $em->remove($seccion->getInfoCategoria());
        $em->remove($seccion);

        $seciones = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden > :orden AND s.principal = :father ORDER BY s.orden')
            ->setParameter('orden', $orden)
            ->setParameter('father', $father)
            ->getResult();

        foreach($seciones as $secc)
        {
            $secc->setOrden($orden);
            $orden++;
        }

        $em->flush();

        return new Response(json_encode($seccion), 200);
    }


    public function imagesAction()
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return $this->render("AppBundle:Usuario:error_expirado.html.twig");

        $em = $this->getDoctrine()->getManager();

        $seccions = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.principal IS NULL ORDER BY s.orden')
            ->getResult();

        $first = null;

        if(!empty($seccions))
            $first = $seccions[0];

        $categories = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.principal = :seccion ORDER BY s.orden')
            ->setParameter('seccion', $first)
            ->getResult();

        return $this->render('AppBundle:Seccion:images.html.twig', array(
            "seccions" => $seccions,
            "categories" => $categories
        ));
    }

    public function _imagesAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $search = $request->get('search')["value"];
        $columns = $request->get('columns');
        $seccionId = $columns[1]["search"]["value"];


        if($search != "")
        {
            $query = $em->createQuery('SELECT COUNT(i.id) FROM AppBundle:imagen i JOIN i.seccion s WHERE (i.nombre LIKE :text ) AND s.id = :seccionId')
                ->setParameter('seccionId', $seccionId )
                ->setParameter('text', '%'.$search.'%');

            $entities = $em->createQuery('SELECT i,s FROM AppBundle:imagen i JOIN i.seccion s WHERE (i.nombre LIKE :text ) AND s.id = :seccionId ORDER BY i.numeracion')
                ->setParameter('seccionId', $seccionId )
                ->setParameter('text', '%'.$search.'%')
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }
        else
        {
            $query = $em->createQuery('SELECT COUNT(i.id) FROM AppBundle:imagen i JOIN i.seccion s WHERE s.id = :seccionId')
                ->setParameter('seccionId', $seccionId );

            $entities = $em->createQuery('SELECT i,s FROM AppBundle:imagen i JOIN i.seccion s WHERE s.id = :seccionId ORDER BY i.numeracion')
                ->setParameter('seccionId', $seccionId )
                ->setFirstResult($request->get('start'))
                ->setMaxResults($request->get('length'))->getArrayResult();
        }

        $count = $query->getSingleScalarResult();
        $p = new Utils();

        $cant = count($entities);
        for($i = 0; $i < $cant; $i++){
            $tempEnt = $em->find("AppBundle:imagen", $entities[$i]["id"]);
            $tempEnt->setTranslatableLocale('en');
            $em->refresh($tempEnt);
            $entities[$i]["TextoENG"]= $tempEnt->getTexto();
        }

        return new Response(json_encode($p->GetPagination($request, $entities, $count )), 200);
    }

    public function saveImageAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");

        $image = $em->getRepository('AppBundle:imagen')->find($id);

        $image->setTexto($request->get("Text"));
        $em->persist($image);
        $em->flush();

        //print_r($image->getTexto());

        // Traducir los contenidos al inglés
        $image_eng = $em->getRepository('AppBundle:imagen')->find($id);
        $image_eng->setTexto($request->get("TextTsl"));
        $image_eng->setTranslatableLocale('en');
        $em->persist($image_eng);

        $em->flush();

        return new Response(json_encode($image), 200);
    }

    public function getCategoriesBySeccionAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");

        $seccion = $em->getRepository('AppBundle:seccion')->find($id);


        $categories = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.principal = :seccion ORDER BY s.orden')
            ->setParameter('seccion', $seccion)
            ->getArrayResult();

        return new Response(json_encode($categories), 200);
    }


    /**
     * @param Request $request
     * @return Response
     */

    public function publishSeccionAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");
        $seccion = $em->getRepository('AppBundle:seccion')->find($id);

        if(empty($seccion))
            return new Response(json_encode(null), 200);

        $publish = $request->get("Publish");
        $seccion->setVisible($publish);

        $categorias = $seccion->getCategorias();

        if(!empty($categorias))
        {
            foreach($categorias as $cat)
                $cat->setVisible($publish);
        }

        $em->flush();

        return new Response(json_encode($seccion), 200);
    }

    public function changeOrderAction(Request $request)
    {
        $logueado = $this->getUser();

        if ($logueado == null)
            return new Response(json_encode(null), 401);

        $em = $this->getDoctrine()->getManager();

        $id = $request->get("Id");
        $father = $request->get("Father");
        $mode = $request->get("Mode");

        //Representa Seccion o Categoria en caso de Padre distinto de NULL
        $seccion = $em->getRepository('AppBundle:seccion')->find($id);

        if(empty($seccion))
            return new Response(json_encode(null), 200);

        $orden = $seccion->getOrden();

        if($orden == 1 && $mode == "up")
            return new Response(json_encode(null), 200);

        //Ordenar Seccion
        if(empty($father))
        {
            if($mode == "up")
            {
                $before = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden < :orden AND s.principal IS NULL ORDER BY s.orden DESC')
                    ->setParameter('orden', $orden)
                    ->setMaxResults(1)
                    ->getResult();

                if(empty($before))
                    return new Response(json_encode(null), 200);

                $before[0]->setOrden($orden);
                $orden--;
                $seccion->setOrden($orden);

            }
            else
            {
                $after = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden > :orden AND s.principal IS NULL ORDER BY s.orden')
                    ->setParameter('orden', $orden)
                    ->setMaxResults(1)
                    ->getResult();

                if(empty($after))
                    return new Response(json_encode(null), 200);

                $after[0]->setOrden($orden);
                $orden++;
                $seccion->setOrden($orden);
            }
        }
        //Ordenar Categoria
        else
        {
            if($mode == "up")
            {
                $before = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden < :orden AND s.principal =:father ORDER BY s.orden DESC')
                    ->setParameter('orden', $orden)
                    ->setParameter('father', $father)
                    ->setMaxResults(1)
                    ->getResult();

                if(empty($before))
                    return new Response(json_encode(null), 200);

                $before[0]->setOrden($orden);
                $orden--;
                $seccion->setOrden($orden);

            }
            else
            {
                $after = $em->createQuery('SELECT s FROM AppBundle:seccion s WHERE s.orden > :orden AND s.principal =:father ORDER BY s.orden')
                    ->setParameter('orden', $orden)
                    ->setParameter('father', $father)
                    ->setMaxResults(1)
                    ->getResult();

                if(empty($after))
                    return new Response(json_encode(null), 200);

                $after[0]->setOrden($orden);
                $orden++;
                $seccion->setOrden($orden);
            }
        }

        $em->flush();

        return new Response(json_encode($seccion), 200);
    }

}
