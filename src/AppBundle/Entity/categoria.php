<?php
/**
 * Created by PhpStorm.
 * User: carlos.rodriguez
 * Date: 20/03/2018
 * Time: 14:01
 */

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\Entity\Translation;
/**
 * @ORM\Entity
 * @ORM\Table(name="categoria")
 */

class categoria
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
    protected $titulo;

    protected $tituloEng;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    protected $descripcion;

    protected $descripcionEng;

    /**
     * One Seccion have One Categoria.
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\seccion", inversedBy="info_categ")
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
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * @param mixed $titulo
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
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
    public function getTituloEng()
    {
        return $this->tituloEng;
    }

    /**
     * @param mixed $tituloEng
     */
    public function setTituloEng($tituloEng)
    {
        $this->tituloEng = $tituloEng;
    }

    /**
     * @return mixed
     */
    public function getDescripcionEng()
    {
        return $this->descripcionEng;
    }

    /**
     * @param mixed $descripcionEng
     */
    public function setDescripcionEng($descripcionEng)
    {
        $this->descripcionEng = $descripcionEng;
    }




    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    function __toString()
    {
        return $this->getTitulo();
    }


}
