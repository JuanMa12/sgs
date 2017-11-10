<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="process_order_types", options={"comment":"Contiene las tipos de ordenes de procesos"})
 */
class ProcessOrderType 
{


    const PROCESS_ORDER_TYPE_PROCESS = 0;
    const PROCESS_ORDER_TYPE_REPORT = 1;

    /**
     * @ORM\Column(type="integer", options={"comment":"Id Autonumerico del tipo de orden de proceso"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100,name="name" , options={"comment":"Nombre del tipo de orden de proceso"})
     */
    private $name;

    /**
    * @ORM\Column(type="string", length=100, options={"comment":"modulo del tipo de orden de proceso"})
    */
    private $module;
   
    /**
    * @ORM\Column(type="string", length=60, options={"comment":"categoria de la orden de proceso"})
    */
    private $type = self::PROCESS_ORDER_TYPE_PROCESS; 

    /**
    * @ORM\OneToMany(targetEntity="ProcessOrder", mappedBy="processOrderType")
    */
    private $processesOrder;

    /**
    * @ORM\Column(type="text", name="description" , options={"comment":"Descripcion del tipo de orden de proceso"})
    */
    private $description;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processesOrder = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ProcessOrderType
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
     * Set module
     *
     * @param string $module
     *
     * @return ProcessOrderType
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ProcessOrderType
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
     * @return ProcessOrderType
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
     * Add processesOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processesOrder
     *
     * @return ProcessOrderType
     */
    public function addProcessesOrder(\AppBundle\Entity\ProcessOrder $processesOrder)
    {
        $this->processesOrder[] = $processesOrder;

        return $this;
    }

    /**
     * Remove processesOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processesOrder
     */
    public function removeProcessesOrder(\AppBundle\Entity\ProcessOrder $processesOrder)
    {
        $this->processesOrder->removeElement($processesOrder);
    }

    /**
     * Get processesOrder
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProcessesOrder()
    {
        return $this->processesOrder;
    }
}
