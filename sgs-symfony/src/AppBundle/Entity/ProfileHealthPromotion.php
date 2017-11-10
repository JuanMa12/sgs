<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="profile_health_promotion", options={"comment":"Contiene los perfiles por EPS"})
 */
class ProfileHealthPromotion 
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
    * @ORM\ManyToOne(targetEntity="Profile", inversedBy="profilesHealthPromotion")
    * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
    */
    private $profile;


    /**
    * @ORM\ManyToOne(targetEntity="HealthPromotionEntity", inversedBy="profilesHealthPromotion")
    * @ORM\JoinColumn(name="health_promotion_entity_id", referencedColumnName="id")
    */
    private $healthPromotionEntity;
    

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
     * @return ProfileHealthPromotion
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
     * Set healthPromotionEntity
     *
     * @param \AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity
     *
     * @return ProfileHealthPromotion
     */
    public function setHealthPromotionEntity(\AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity = null)
    {
        $this->healthPromotionEntity = $healthPromotionEntity;

        return $this;
    }

    /**
     * Get healthPromotionEntity
     *
     * @return \AppBundle\Entity\HealthPromotionEntity
     */
    public function getHealthPromotionEntity()
    {
        return $this->healthPromotionEntity;
    }
}
