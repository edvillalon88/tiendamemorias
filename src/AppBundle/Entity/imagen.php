<?php
/**
 * Created by PhpStorm.
 * User: carlos.rodriguez
 * Date: 20/03/2018
 * Time: 14:01
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\Entity\Translation;
/**
 * @ORM\Entity
 * @ORM\Table(name="imagen")
 */

class imagen
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=250)
     */
    protected $nombre;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    protected $texto;

    protected  $textoENG;

    /** @ORM\Column(type="string", length=250) */
    protected $numeracion;

    /**
     * Many Imagenes have One Seccion.
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\seccion", inversedBy="imagenes")
     * @ORM\JoinColumn(name="seccion_id", referencedColumnName="id")
     */
    protected $seccion;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
 * @return mixed
 */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * @param mixed $texto
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;
    }

    /**
     * @return mixed
     */
    public function getNumeracion()
    {
        return $this->numeracion;
    }

    /**
     * @param mixed $numeracion
     */
    public function setNumeracion($numeracion)
    {
        $this->numeracion = $numeracion;
    }

    /**
     * @return mixed
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * @param mixed $seccion
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;
    }

    /**
     * @return mixed
     */
    public function getTextoENG()
    {
        return $this->textoENG;
    }

    /**
     * @param mixed $textoENG
     */
    public function setTextoENG($textoENG)
    {
        $this->textoENG = $textoENG;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }


    function __toString()
    {
        return $this->getNombre();
    }


}
