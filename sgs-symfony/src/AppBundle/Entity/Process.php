<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="processes", options={"comment":"Contiene las ordenes de procesos"})
 */
class Process
{
   
    const PROCESS_STATUS_INIT = 0;
    const PROCESS_STATUS_INPROGRESS = 1;
    const PROCESS_STATUS_ENDED = 2;
    const PROCESS_STATUS_FAILED = 3;
    const PROCESS_STATUS_INVALID = 4;

    /**
     * @ORM\Column(type="integer", options={"comment":"Id Autonumerico de los procesos"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\Column(type="string", length=10,name="status" , options={"comment":"estado del proceso"})
    */
    private $status = self::PROCESS_STATUS_INIT;

    /**
    * @ORM\Column(type="integer", options={"comment":"progreso del proceso"})
    */
    private $progress;

    /**
    * @ORM\ManyToOne(targetEntity="ProcessOrder", inversedBy="processes")
    * @ORM\JoinColumn(name="process_order_id", referencedColumnName="id")
    */
    private $processOrder;

    /**
    * @ORM\OneToMany(targetEntity="ProcessError", mappedBy="process")
    */
    private $processesError;

   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processesError = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set status
     *
     * @param string $status
     *
     * @return Process
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set progress
     *
     * @param integer $progress
     *
     * @return Process
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return integer
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set processOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processOrder
     *
     * @return Process
     */
    public function setProcessOrder(\AppBundle\Entity\ProcessOrder $processOrder = null)
    {
        $this->processOrder = $processOrder;

        return $this;
    }

    /**
     * Get processOrder
     *
     * @return \AppBundle\Entity\ProcessOrder
     */
    public function getProcessOrder()
    {
        return $this->processOrder;
    }

    /**
     * Add processesError
     *
     * @param \AppBundle\Entity\ProcessError $processesError
     *
     * @return Process
     */
    public function addProcessesError(\AppBundle\Entity\ProcessError $processesError)
    {
        $this->processesError[] = $processesError;

        return $this;
    }

    /**
     * Remove processesError
     *
     * @param \AppBundle\Entity\ProcessError $processesError
     */
    public function removeProcessesError(\AppBundle\Entity\ProcessError $processesError)
    {
        $this->processesError->removeElement($processesError);
    }

    /**
     * Get processesError
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProcessesError()
    {
        return $this->processesError;
    }
}
