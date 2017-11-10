<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="user_process_orders", options={"comment":"Contiene las ordenes de procesos destacadas por usuario"})
 */
class UserProcessOrder 
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
    * @ORM\ManyToOne(targetEntity="User", inversedBy="userProcessesOrder")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="ProcessOrder", inversedBy="userProcessesOrder")
    * @ORM\JoinColumn(name="process_order_id", referencedColumnName="id")
    */
    private $processOrder;


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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserProcessOrder
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
     * Set processOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processOrder
     *
     * @return UserProcessOrder
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
}
