<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="municipalities", options={"comment":"Contiene los Municipios de Colombia"})
 */
class Municipality  
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="municipalities")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id")
     */
    private $departmentId;

    /**
     * @ORM\Column(type="string", length=60, name="name", options={"comment":"Nombre del Municipios"})
     */
    private $name;

    /**
    * @ORM\Column(type="string", length=10, unique=true, name="code", options={"comment":"Codigo del Municipio"})
    */
    private $code;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="category", options={"comment":"Categoria de contaduria nacional"})
     */
    private $category;

    /**
    * @ORM\Column(type="string", length=10, nullable=true, options={"comment":"Zona del Municipio"})
    */
    private $zone;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Municipality
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Municipality
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return Municipality
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set zone
     *
     * @param string $zone
     *
     * @return Municipality
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set departmentId
     *
     * @param \AppBundle\Entity\Department $departmentId
     *
     * @return Municipality
     */
    public function setDepartmentId(\AppBundle\Entity\Department $departmentId = null)
    {
        $this->departmentId = $departmentId;

        return $this;
    }

    /**
     * Get departmentId
     *
     * @return \AppBundle\Entity\Department
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }
}
