<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="departments", options={"comment":"Contiene los departamentos de Colombia"})
 */
class Department 
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
     * @ORM\Column(type="string", length=60, name="name", options={"comment":"Nombre del Departamento"})
     */
    private $name;

    /**
    * @ORM\Column(type="string", length=10, unique=true, name="code", options={"comment":"Codigo del Departamento"})
    */
    private $code;

    /**
    * @ORM\OneToMany(targetEntity="Municipality", mappedBy="departmentId")
    */
    private $municipalities;
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->municipalities = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return Department
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
     * @return Department
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
     * Add municipality
     *
     * @param \AppBundle\Entity\Municipality $municipality
     *
     * @return Department
     */
    public function addMunicipality(\AppBundle\Entity\Municipality $municipality)
    {
        $this->municipalities[] = $municipality;

        return $this;
    }

    /**
     * Remove municipality
     *
     * @param \AppBundle\Entity\Municipality $municipality
     */
    public function removeMunicipality(\AppBundle\Entity\Municipality $municipality)
    {
        $this->municipalities->removeElement($municipality);
    }

    /**
     * Get municipalities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMunicipalities()
    {
        return $this->municipalities;
    }
}
