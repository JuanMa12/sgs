<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="diagnostic_groups", options={"comment":"Contiene los grupos de diagnosticos"})
 */
class DiagnosticGroup 
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
     * @ORM\Column(type="string", options={"comment":"Nomnre del Grupo"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=60, options={"comment":"Codigo del grupo de diagnostico"})
     */
    private $code;

    /**
    * @ORM\OneToMany(targetEntity="Diagnostic", mappedBy="diagnosticGroup")
    */
    private $diagnostics;
    
    /**
     * @ORM\ManyToOne(targetEntity="DiagnosticGroup",inversedBy="childs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
    **/
    private $parent;

    /**
    * @ORM\OneToMany(targetEntity="DiagnosticGroup", mappedBy="parent")
    */
    private $childs;
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->diagnostics = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return DiagnosticGroup
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
     * @return DiagnosticGroup
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
     * Add diagnostic
     *
     * @param \AppBundle\Entity\Diagnostic $diagnostic
     *
     * @return DiagnosticGroup
     */
    public function addDiagnostic(\AppBundle\Entity\Diagnostic $diagnostic)
    {
        $this->diagnostics[] = $diagnostic;

        return $this;
    }

    /**
     * Remove diagnostic
     *
     * @param \AppBundle\Entity\Diagnostic $diagnostic
     */
    public function removeDiagnostic(\AppBundle\Entity\Diagnostic $diagnostic)
    {
        $this->diagnostics->removeElement($diagnostic);
    }

    /**
     * Get diagnostics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDiagnostics()
    {
        return $this->diagnostics;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\DiagnosticGroup $parent
     *
     * @return DiagnosticGroup
     */
    public function setParent(\AppBundle\Entity\DiagnosticGroup $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\DiagnosticGroup
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\DiagnosticGroup $child
     *
     * @return DiagnosticGroup
     */
    public function addChild(\AppBundle\Entity\DiagnosticGroup $child)
    {
        $this->childs[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\DiagnosticGroup $child
     */
    public function removeChild(\AppBundle\Entity\DiagnosticGroup $child)
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
