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
 * @ORM\Table(name="tienda")
 */

class tienda
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    protected $descripcion;


    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    protected $nosotros;

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
    public function getNosotros()
    {
        return $this->nosotros;
    }

    /**
     * @param mixed $nosotros
     */
    public function setNosotros($nosotros)
    {
        $this->nosotros = $nosotros;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }


    function __toString()
    {
        return $this->id;
    }


}
