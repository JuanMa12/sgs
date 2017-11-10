<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="profiles", options={"comment":"Contiene los perfiles"})
 */
class Profile 
{

    const PROFILE_CREATE_REPORT_INACTIVE = 0;
    const PROFILE_CREATE_REPORT_ACTIVE = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="name", options={"comment":"Nombre del perfil"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer", name="create_report", options={"comment":"Define si el perfil puede crear o no reportes"})
    */
    private $createReport;

    /**
    * @ORM\OneToMany(targetEntity="ProfileHealthPromotion", mappedBy="profile")
    */
    private $profilesHealthPromotion;

    /**
    * @ORM\OneToMany(targetEntity="ProfileProcessOrder", mappedBy="profile")
    */
    private $profileProcessesOrder;

    /**
    * @ORM\OneToMany(targetEntity="User", mappedBy="profile")
    */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profilesHealthPromotion = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profileProcessesOrder = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Profile
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
     * Set createReport
     *
     * @param integer $createReport
     *
     * @return Profile
     */
    public function setCreateReport($createReport)
    {
        $this->createReport = $createReport;

        return $this;
    }

    /**
     * Get createReport
     *
     * @return integer
     */
    public function getCreateReport()
    {
        return $this->createReport;
    }

    /**
     * Add profilesHealthPromotion
     *
     * @param \AppBundle\Entity\ProfileHealthPromotion $profilesHealthPromotion
     *
     * @return Profile
     */
    public function addProfilesHealthPromotion(\AppBundle\Entity\ProfileHealthPromotion $profilesHealthPromotion)
    {
        $this->profilesHealthPromotion[] = $profilesHealthPromotion;

        return $this;
    }

    /**
     * Remove profilesHealthPromotion
     *
     * @param \AppBundle\Entity\ProfileHealthPromotion $profilesHealthPromotion
     */
    public function removeProfilesHealthPromotion(\AppBundle\Entity\ProfileHealthPromotion $profilesHealthPromotion)
    {
        $this->profilesHealthPromotion->removeElement($profilesHealthPromotion);
    }

    /**
     * Get profilesHealthPromotion
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfilesHealthPromotion()
    {
        return $this->profilesHealthPromotion;
    }

    /**
     * Add profileProcessesOrder
     *
     * @param \AppBundle\Entity\ProfileProcessOrder $profileProcessesOrder
     *
     * @return Profile
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
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Profile
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
