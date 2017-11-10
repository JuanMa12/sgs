<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="guilds", options={"comment":"Contiene los gremios"})
 */
class Guild 
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
     * @ORM\Column(type="string", name="name", options={"comment":"Nombre del gremio"})
     */
    private $name;  

    /**
    * @ORM\OneToMany(targetEntity="HealthPromotionEntity", mappedBy="guild")
    */
    private $healthPromotionEntities; 

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->healthPromotionEntities = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Guild
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
     * Add healthPromotionEntity
     *
     * @param \AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity
     *
     * @return Guild
     */
    public function addHealthPromotionEntity(\AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity)
    {
        $this->healthPromotionEntities[] = $healthPromotionEntity;

        return $this;
    }

    /**
     * Remove healthPromotionEntity
     *
     * @param \AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity
     */
    public function removeHealthPromotionEntity(\AppBundle\Entity\HealthPromotionEntity $healthPromotionEntity)
    {
        $this->healthPromotionEntities->removeElement($healthPromotionEntity);
    }

    /**
     * Get healthPromotionEntities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHealthPromotionEntities()
    {
        return $this->healthPromotionEntities;
    }
}
