<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="process_orders", options={"comment":"Contiene las ordenes de procesos"})
 */
class ProcessOrder 
{
   
    const PROCESS_ORDER_STATUS_INIT = 0;
    const PROCESS_ORDER_STATUS_INPROGRESS = 1;
    const PROCESS_ORDER_STATUS_ENDED = 2;
    const PROCESS_ORDER_STATUS_FAILED = 3;
    const PROCESS_ORDER_STATUS_DELETED = 4;
    const PROCESS_ORDER_STATUS_DELETING = 5;


    /**
     * @ORM\Column(type="integer", options={"comment":"Id Autonumerico de la orden de proceso"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100,name="name" , options={"comment":"Nombre de la orden de proceso"})
     */
    private $name;

    /**
    * @ORM\Column(type="string", length=60,name="status" , options={"comment":"estado de la orden de proceso"})
    */
    private $status = self::PROCESS_ORDER_STATUS_INIT;

    /**
    * @ORM\Column(type="text", nullable=true,name="meta_data", options={"comment":"meta data de la orden de proceso"})
    */
    private $metaData;

    /**
    * @ORM\Column(type="string", length=60,name="date" , options={"comment":"fecha de de creacion de la orden de proceso"})
    */
    private $date;  

    /**
    * @ORM\ManyToOne(targetEntity="User", inversedBy="processesOrder")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
    */
    private $user;

    /**
    * @ORM\OneToMany(targetEntity="Process", mappedBy="processOrder")
    */
    private $processes;
   
    /**
    * @ORM\ManyToOne(targetEntity="ProcessOrderType", inversedBy="processesOrder")
    * @ORM\JoinColumn(name="process_order_type_id", referencedColumnName="id")
    */
    private $processOrderType;
    
    /**
    * @ORM\Column(type="integer", nullable=true,name="homologation", options={"comment":"contiene un id de un proceso con los mismos parametros de este registro"})
    */
    private $homologation;
 
    /**
    * @ORM\OneToMany(targetEntity="ProfileProcessOrder", mappedBy="processOrder")
    */
    private $profileProcessesOrder;

    /**
    * @ORM\Column(type="text", nullable=true,name="params", options={"comment":"contiene los parametros de la orden de proceso"})
    */
    private $params;

    /**
    * @ORM\OneToMany(targetEntity="UserProcessOrder", mappedBy="processOrder")
    */
    private $userProcessesOrder;

    /**
    * @ORM\Column(type="string",name="create_token" , nullable=true, options={"comment":"token de creacion de la orden de proceso"})
    */
    private $createToken; 



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
     * @return ProcessOrder
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
     * Set status
     *
     * @param string $status
     *
     * @return ProcessOrder
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
     * Set metaData
     *
     * @param string $metaData
     *
     * @return ProcessOrder
     */
    public function setMetaData($metaData)
    {
        $this->metaData = json_encode($metaData);

        return $this;
    }

    /**
     * Get metaData
     *
     * @return string
     */
    public function getMetaData()
    {
        return json_decode($this->metaData);
    }

    /**
     * Set date
     *
     * @param string $date
     *
     * @return ProcessOrder
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
     * Set params
     *
     * @param string $params
     *
     * @return ProcessOrder
     */
    public function setParams($params)
    {
        $this->params = json_encode($params);

        return $this;
    }

    /**
     * Get params
     *
     * @return string
     */
    public function getParams()
    {
        return json_decode($this->params,true);
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profileProcessesOrder = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userProcessesOrder = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set homologation
     *
     * @param integer $homologation
     *
     * @return ProcessOrder
     */
    public function setHomologation($homologation)
    {
        $this->homologation = $homologation;

        return $this;
    }

    /**
     * Get homologation
     *
     * @return integer
     */
    public function getHomologation()
    {
        return $this->homologation;
    }

    /**
     * Set createToken
     *
     * @param string $createToken
     *
     * @return ProcessOrder
     */
    public function setCreateToken($createToken)
    {
        $this->createToken = $createToken;

        return $this;
    }

    /**
     * Get createToken
     *
     * @return string
     */
    public function getCreateToken()
    {
        return $this->createToken;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return ProcessOrder
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add process
     *
     * @param \AppBundle\Entity\Process $process
     *
     * @return ProcessOrder
     */
    public function addProcess(\AppBundle\Entity\Process $process)
    {
        $this->processes[] = $process;

        return $this;
    }

    /**
     * Remove process
     *
     * @param \AppBundle\Entity\Process $process
     */
    public function removeProcess(\AppBundle\Entity\Process $process)
    {
        $this->processes->removeElement($process);
    }

    /**
     * Get processes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * Set processOrderType
     *
     * @param \AppBundle\Entity\ProcessOrderType $processOrderType
     *
     * @return ProcessOrder
     */
    public function setProcessOrderType(\AppBundle\Entity\ProcessOrderType $processOrderType = null)
    {
        $this->processOrderType = $processOrderType;

        return $this;
    }

    /**
     * Get processOrderType
     *
     * @return \AppBundle\Entity\ProcessOrderType
     */
    public function getProcessOrderType()
    {
        return $this->processOrderType;
    }

    /**
     * Add profileProcessesOrder
     *
     * @param \AppBundle\Entity\ProfileProcessOrder $profileProcessesOrder
     *
     * @return ProcessOrder
     */
    public function addProfileProcessesOrder(\AppBundle\Entity\ProfileProcessOrder $profileProcessesOrder)
    {
        $this->profileProcessesOrder[] = $profileProcessesOrder;

        return $this;
    }

    /**
     * Remove profileProcessesOrder
     *
     * @param \AppBundle\Entity\ProfileProcessOrder $profileProcessesOrder
     */
    public function removeProfileProcessesOrder(\AppBundle\Entity\ProfileProcessOrder $profileProcessesOrder)
    {
        $this->profileProcessesOrder->removeElement($profileProcessesOrder);
    }

    /**
     * Get profileProcessesOrder
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfileProcessesOrder()
    {
        return $this->profileProcessesOrder;
    }

    /**
     * Add userProcessesOrder
     *
     * @param \AppBundle\Entity\UserProcessOrder $userProcessesOrder
     *
     * @return ProcessOrder
     */
    public function addUserProcessesOrder(\AppBundle\Entity\UserProcessOrder $userProcessesOrder)
    {
        $this->userProcessesOrder[] = $userProcessesOrder;

        return $this;
    }

    /**
     * Remove userProcessesOrder
     *
     * @param \AppBundle\Entity\UserProcessOrder $userProcessesOrder
     */
    public function removeUserProcessesOrder(\AppBundle\Entity\UserProcessOrder $userProcessesOrder)
    {
        $this->userProcessesOrder->removeElement($userProcessesOrder);
    }

    /**
     * Get userProcessesOrder
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserProcessesOrder()
    {
        return $this->userProcessesOrder;
    }
}
