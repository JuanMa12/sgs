<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="health_procesedures", options={"comment":"Contiene los codigos de procedimientos cups"})
 */
class HealthProcess 
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
     * @ORM\ManyToOne(targetEntity="HealthProcess",inversedBy="childs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
    **/
    private $parent;
    /**
     * @ORM\Column(type="string", options={"comment":"Codigo del procedimiento"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", options={"comment":"tipo de procedimiento"})
    */
    private $type;

    /**
     * @ORM\Column(type="text", options={"comment":"descripcino del procedimiento"})
     */
    private $description;  
    
    /**
     * @ORM\Column(type="text", options={"comment":"seccion a la que pertenece el procedimiento"})
    */
    private $section;  

    /**
     * @ORM\Column(type="text", options={"comment":"capitulo a la que pertenece el procedimiento"})
    */
    private $chapter; 

    /**
     * @ORM\Column(type="string", options={"comment":"Covertura del procedimiento"})
     */
    private $coverage;

    /**
    * @ORM\OneToMany(targetEntity="HealthProcess", mappedBy="parent")
    */
    private $childs;
    
    /**
     * @ORM\Column(type="string", length=5, options={"comment":"Generos o genero a los que pertenece el procedimiento"})
    */
    private $genre;

    /**
     * @ORM\Column(type="string", length=10, options={"comment":"Ambito original del procedimiento"})
    */
    private $ambit;

    /**
     * @ORM\Column(type="string", length=10, options={"comment":"Estancia original del procedimiento"})
    */
    private $stay;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->childs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return HealthProcess
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
     * Set type
     *
     * @param string $type
     *
     * @return HealthProcess
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return HealthProcess
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set section
     *
     * @param string $section
     *
     * @return HealthProcess
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set chapter
     *
     * @param string $chapter
     *
     * @return HealthProcess
     */
    public function setChapter($chapter)
    {
        $this->chapter = $chapter;

        return $this;
    }

    /**
     * Get chapter
     *
     * @return string
     */
    public function getChapter()
    {
        return $this->chapter;
    }

    /**
     * Set coverage
     *
     * @param string $coverage
     *
     * @return HealthProcess
     */
    public function setCoverage($coverage)
    {
        $this->coverage = $coverage;

        return $this;
    }

    /**
     * Get coverage
     *
     * @return string
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Set genre
     *
     * @param string $genre
     *
     * @return HealthProcess
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set ambit
     *
     * @param string $ambit
     *
     * @return HealthProcess
     */
    public function setAmbit($ambit)
    {
        $this->ambit = $ambit;

        return $this;
    }

    /**
     * Get ambit
     *
     * @return string
     */
    public function getAmbit()
    {
        return $this->ambit;
    }

    /**
     * Set stay
     *
     * @param string $stay
     *
     * @return HealthProcess
     */
    public function setStay($stay)
    {
        $this->stay = $stay;

        return $this;
    }

    /**
     * Get stay
     *
     * @return string
     */
    public function getStay()
    {
        return $this->stay;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\HealthProcess $parent
     *
     * @return HealthProcess
     */
    public function setParent(\AppBundle\Entity\HealthProcess $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\HealthProcess
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\HealthProcess $child
     *
     * @return HealthProcess
     */
    public function addChild(\AppBundle\Entity\HealthProcess $child)
    {
        $this->childs[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\HealthProcess $child
     */
    public function removeChild(\AppBundle\Entity\HealthProcess $child)
    {
        $this->childs->removeElement($child);
    }

    /**
     * Get childs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChilds()
    {
        return $this->childs;
    }
}
