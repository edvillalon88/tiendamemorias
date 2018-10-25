<?php
/**
 * Created by PhpStorm.
 * User: eduardo.valdes
 * Date: 21/09/2017
 * Time: 9:11
 */

namespace AppBundle\Entity;


class Utils
{
    //request es el objeto request
    //entities es el resultado de la consulta
    //count es el total de registros de la tabla
    public function GetPagination($request, $entities, $count )
    {
        $draw = $request->get('draw');
        $recordsFiltered = count($entities);
        $recordsTotal = $count;

        $result = array ("data"=>$entities, "draw"=> $draw, 'recordsFiltered' => $recordsTotal,'recordsTotal'=>$recordsTotal);
        return $result;
    }

    public function SortArrayByKey(&$array, $key, $string=false, $asc=true)
    {
        if($string)
        {
            usort($array, function($a, $b) use(&$key, &$asc)
            {
                if($asc) return strcmp(strtolower($a{$key}), strtolower($b{$key}));
                else return strcmp(strtolower($b{$key}), strtolower($a{$key}));
            });
        }
        else
        {
            usort($array, function($a, $b) use(&$key, &$asc)
            {
                if($asc) return ($a{$key}) < ($b{$key}) ? -1 : 1;
                else return ($a{$key}) > ($b{$key}) ? -1 : 1;
            });
        }
    }

    public function fileUpload($ruta,$archivo,$tipo,$temp)
    {

        //define ("MAX_SIZE","100");
        $prefijo = substr(md5(uniqid(rand())),0,6);
        $fyle=explode("/",$tipo);
        $tipo=$fyle[0];

        // guardamos el archivo a la carpeta files
        $destino =  $ruta.$prefijo."_".substr($archivo,-4);
        if (copy($temp,$destino)) {
           return true;
        } else{
            return false;
        }

    }

    public function getUploadRootDir()
    {
        // la ruta absoluta del directorio donde se deben
        // guardar los archivos cargados
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getName(){
        $prefijo = substr(md5(uniqid(rand())),0,6);
        return $prefijo;
    }

    protected function getUploadDir()
    {
        // se deshace del __DIR__ para no meter la pata
        // al mostrar el documento/imagen cargada en la vista.
        return 'uploads/';
    }

    protected function getUploadDirImages()
    {
        // se deshace del __DIR__ para no meter la pata
        // al mostrar el documento/imagen cargada en la vista.
        return 'images_category/';
    }
}