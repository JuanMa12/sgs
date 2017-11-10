<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="process_errors", options={"comment":"Contiene los errorres de los procesos"})
 */
class ProcessError 
{

    /**
     * @ORM\Column(type="integer", options={"comment":"Id Autonumerico de los errores de los procesos"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\Column(type="string", options={"comment":"descripcion del error del proceso"})
    */
    private $description;

    /**
    * @ORM\Column(type="string", options={"comment":"fecha del error del proceso"})
    */
    private $date;

    /**
    * @ORM\Column(type="string", length=60, options={"comment":"gravedad del error del proceso"})
    */
    private $severity;

    /**
    * @ORM\ManyToOne(targetEntity="Process", inversedBy="processesError")
    * @ORM\JoinColumn(name="process_id", referencedColumnName="id")
    */
    private $process;
   



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
     * Set description
     *
     * @param string $description
     *
     * @return ProcessError
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
     * Set date
     *
     * @param string $date
     *
     * @return ProcessError
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set severity
     *
     * @param string $severity
     *
     * @return ProcessError
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * Get severity
     *
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Set process
     *
     * @param \AppBundle\Entity\Process $process
     *
     * @return ProcessError
     */
    public function setProcess(\AppBundle\Entity\Process $process = null)
    {
        $this->process = $process;

        return $this;
    }

    /**
     * Get process
     *
     * @return \AppBundle\Entity\Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
