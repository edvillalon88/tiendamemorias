<?php
/**
 * Created by PhpStorm.
 * User: carlos.rodriguez
 * Date: 20/03/2018
 * Time: 14:01
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\Entity\Translation;
/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\seccionRepository")
 * @ORM\Table(name="seccion")
 */

class seccion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=100)
     * */
    protected $nombre;

    protected  $nombreENG;

    /** @ORM\Column(type="integer") */
    protected $orden;

    /** @ORM\Column(type="boolean") */
    protected $visible;

    /** @ORM\Column(type="integer") */
    protected $img_row;

    /**
     * Many Secciones has One Seccion.
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\seccion", inversedBy="categorias")
     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id")
     */
    protected $principal;

    /**
     * One Seccion has Many Secciones.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\seccion", mappedBy="principal")
     */
    protected $categorias;

    /**
     * One Seccion have One Categoria.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\categoria", mappedBy="seccion")
     */
    protected $info_categ;

    /**
     * One Seccion has Many Imagenes.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\imagen", mappedBy="seccion")
     */
    protected $imagenes;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    public function __construct() {
        $this->categorias = new ArrayCollection();
        $this->info_categ = new ArrayCollection();
        $this->imagenes = new ArrayCollection();
        $this->img_row = 0;
    }

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
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * @param mixed $orden
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    }

    /**
     * @return mixed
     */
    public function getImgRow()
    {
        return $this->img_row;
    }

    /**
     * @param mixed $img_row
     */
    public function setImgRow($img_row)
    {
        $this->img_row = $img_row;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param mixed $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }

    /**
     * @return mixed
     */
    public function getCategorias()
    {
        return $this->categorias;
    }

    /**
     * @param mixed $categorias
     */
    public function setCategorias($categorias)
    {
        $this->categorias = $categorias;
    }

    /**
     * @return mixed
     */
    public function getInfoCategoria()
    {
        if($this->info_categ == null ||
            $this->info_categ->count() == 0)
            return null;

        return $this->info_categ->first();
    }

    /**
     * @return mixed
     */
    public function getImagenes()
    {
        return $this->imagenes;
    }

    /**
     * @param mixed $imagenes
     */
    public function setImagenes($imagenes)
    {
        $this->imagenes = $imagenes;
    }

    /**
     * @return mixed
     */
    public function getNombreENG()
    {
        return $this->nombreENG;
    }

    /**
     * @param mixed $nombreENG
     */
    public function setNombreENG($nombreENG)
    {
        $this->nombreENG = $nombreENG;
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
