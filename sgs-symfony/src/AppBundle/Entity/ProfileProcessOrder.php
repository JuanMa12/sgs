<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="profile_process_orders", options={"comment":"Contiene los perfiles de acceso a las ordenes de procesos"})
 */
class ProfileProcessOrder 
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
    * @ORM\ManyToOne(targetEntity="Profile", inversedBy="profileProcessesOrder")
    * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
    */
    private $profile;

    /**
    * @ORM\ManyToOne(targetEntity="ProcessOrder", inversedBy="profileProcessesOrder")
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
     * Set profile
     *
     * @param \AppBundle\Entity\Profile $profile
     *
     * @return ProfileProcessOrder
     */
    public function setProfile(\AppBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \AppBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set processOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processOrder
     *
     * @return ProfileProcessOrder
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
