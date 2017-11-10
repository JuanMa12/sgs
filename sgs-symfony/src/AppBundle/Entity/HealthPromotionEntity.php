<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="health_promotion_entities", options={"comment":"Contiene las EPS"})
 */
class HealthPromotionEntity 
{


    const HEALTH_PROMOTION_STATUS_INACTIVE = 0;
    const HEALTH_PROMOTION_STATUS_ACTIVE = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="name", options={"comment":"Nombre de la EPS "})
     */
    private $name;

    /**
    * @ORM\Column(type="string", options={"comment":"Alias de la EPS "})
    */
    private $alias;

    /**
    * @ORM\Column(type="string", length=60, name="code", options={"comment":"Codigo de la eps "})
    */
    private $code;

    /**
    * @ORM\Column(type="integer", options={"comment":"estado de la eps "})
    */
    private $status = self::HEALTH_PROMOTION_STATUS_ACTIVE;

    /**
    * @ORM\Column(type="string", options={"comment":"juridica de la EPS"})
    */
    private $legal;

    /**
    * @ORM\OneToMany(targetEntity="ProfileHealthPromotion", mappedBy="healthPromotionEntity")
    */
    private $profilesHealthPromotion;

    /**
    * @ORM\ManyToOne(targetEntity="Guild", inversedBy="healthPromotionEntities")
    * @ORM\JoinColumn(name="guild_id", referencedColumnName="id")
    */
    private $guild; 

    /**
    * @ORM\Column(type="string", nullable=true, length=60, name="code_mobility", options={"comment":"Codigo de movilidad de la eps "})
    */
    private $codeMobility;

    /**
    * @ORM\Column(type="string",length=20,  options={"comment":"codigo nit de la eps"})
    */
    private $nit;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profilesHealthPromotion = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return HealthPromotionEntity
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
     * Set alias
     *
     * @param string $alias
     *
     * @return HealthPromotionEntity
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return HealthPromotionEntity
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
     * Set status
     *
     * @param integer $status
     *
     * @return HealthPromotionEntity
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set legal
     *
     * @param string $legal
     *
     * @return HealthPromotionEntity
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;

        return $this;
    }

    /**
     * Get legal
     *
     * @return string
     */
    public function getLegal()
    {
        return $this->legal;
    }

    /**
     * Set codeMobility
     *
     * @param string $codeMobility
     *
     * @return HealthPromotionEntity
     */
    public function setCodeMobility($codeMobility)
    {
        $this->codeMobility = $codeMobility;

        return $this;
    }

    /**
     * Get codeMobility
     *
     * @return string
     */
    public function getCodeMobility()
    {
        return $this->codeMobility;
    }

    /**
     * Set nit
     *
     * @param string $nit
     *
     * @return HealthPromotionEntity
     */
    public function setNit($nit)
    {
        $this->nit = $nit;

        return $this;
    }

    /**
     * Get nit
     *
     * @return string
     */
    public function getNit()
    {
        return $this->nit;
    }

    /**
     * Add profilesHealthPromotion
     *
     * @param \AppBundle\Entity\ProfileHealthPromotion $profilesHealthPromotion
     *
     * @return HealthPromotionEntity
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
     * Set guild
     *
     * @param \AppBundle\Entity\Guild $guild
     *
     * @return HealthPromotionEntity
     */
    public function setGuild(\AppBundle\Entity\Guild $guild = null)
    {
        $this->guild = $guild;

        return $this;
    }

    /**
     * Get guild
     *
     * @return \AppBundle\Entity\Guild
     */
    public function getGuild()
    {
        return $this->guild;
    }
}
