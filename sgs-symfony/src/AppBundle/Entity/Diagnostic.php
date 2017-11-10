<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="diagnostics", options={"comment":"Contiene los diagnosticos"})
 */
class Diagnostic 
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
     * @ORM\ManyToOne(targetEntity="DiagnosticGroup", inversedBy="diagnostics")
     * @ORM\JoinColumn(name="diagnostic_group_id", referencedColumnName="id")
     */
    private $diagnosticGroup;

    /**
     * @ORM\Column(type="string", options={"comment":"Codigo del diagnostico"})
     */
    private $code;

    /**
     * @ORM\Column(type="text", options={"comment":"descripcino del diagnostico"})
     */
    private $description;  

    /**
     * @ORM\Column(type="string", length=5, options={"comment":"Generos o genero a los que pertenece el diagnostico"})
    */
    private $genre;

    /**
     * @ORM\Column(type="string", length=10, options={"comment":"Edad minima del diagnostico"})
    */
    private $minAge;

    /**
     * @ORM\Column(type="string", length=10, options={"comment":"Edad maxima del diagnostico"})
    */
    private $maxAge;
  

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
     * @return Diagnostic
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
     * Set description
     *
     * @param string $description
     *
     * @return Diagnostic
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
     * Set genre
     *
     * @param string $genre
     *
     * @return Diagnostic
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
     * Set minAge
     *
     * @param string $minAge
     *
     * @return Diagnostic
     */
    public function setMinAge($minAge)
    {
        $this->minAge = $minAge;

        return $this;
    }

    /**
     * Get minAge
     *
     * @return string
     */
    public function getMinAge()
    {
        return $this->minAge;
    }

    /**
     * Set maxAge
     *
     * @param string $maxAge
     *
     * @return Diagnostic
     */
    public function setMaxAge($maxAge)
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Get maxAge
     *
     * @return string
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Set diagnosticGroup
     *
     * @param \AppBundle\Entity\DiagnosticGroup $diagnosticGroup
     *
     * @return Diagnostic
     */
    public function setDiagnosticGroup(\AppBundle\Entity\DiagnosticGroup $diagnosticGroup = null)
    {
        $this->diagnosticGroup = $diagnosticGroup;

        return $this;
    }

    /**
     * Get diagnosticGroup
     *
     * @return \AppBundle\Entity\DiagnosticGroup
     */
    public function getDiagnosticGroup()
    {
        return $this->diagnosticGroup;
    }
}
