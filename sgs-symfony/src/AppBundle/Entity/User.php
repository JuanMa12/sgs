<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;    
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\Criteria;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="users", options={"comment":"Contiene el complemento de referencia a usuarios"})
 */
class User implements AdvancedUserInterface
{

    const USER_STATUS_INACTIVE = 0;
    const USER_STATUS_ACTIVE = 1;
    const USER_STATUS_PENDING = 2;
    const USER_STATUS_LOCKED = 3;

    const USER_TYPE_LOCAL = 1;
    const USER_TYPE_LDAP = 2;

    private $salt;
    private $password;


    /**
     * @ORM\Column(type="integer", options={"comment":"Id Autonumerico de las ciudades"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", options={"comment":"Nombre de usuario"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"Nombre"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"Email de usuario"})
     */
    private $mail;

    /**
     * @ORM\Column(type="text", nullable=true, options={"comment":"parametros de autenticacion"})
     */
    private $meta;

    /**
     * @ORM\Column(type="integer", options={"comment":"tipo de authenticacion"})
     */
    private $type;

    /**
     * @ORM\Column(type="integer", options={"comment":"Rol del Usuario"})
     */
    private $role = 2;

    /**
    * @ORM\ManyToOne(targetEntity="Profile", inversedBy="users")
    * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
    */
    private $profile;

    /**
     * @ORM\Column(type="integer", options={"comment":"Estado de usuario Usuario"})
     */
    private $status = 1;

    /**
     * @ORM\Column(type="text", name="security_token", nullable=true, options={"comment":"Token de seguridad"})
     */
    private $securityToken;

    /**
    * @ORM\OneToMany(targetEntity="ProcessOrder", mappedBy="user")
    */
    private $processesOrder;

    /**
    * @ORM\OneToMany(targetEntity="UserProcessOrder", mappedBy="user")
    */
    private $userProcessesOrder;

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
     * Set role
     *
     * @param integer $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return integer 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getRoles()
    {

        $arrRoles = array();
        switch ($this->role) {
            case 1:
                $arrRoles[] = 'ROLE_ADMIN';
                break;
            case 2:
                $arrRoles[] = 'ROLE_USER';
                break;
        }

        return $arrRoles;
    }

    public function getPassword()
    {
        return $this->meta;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(User $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        if($this->status == self::USER_STATUS_LOCKED){
            return false;
        }else{
            return true;
        }
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        if($this->status == self::USER_STATUS_ACTIVE){
            return true;
        }else{
            return false;
        }
    }
    
    
    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set mail
     *
     * @param string $mail
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return User
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
     * Set meta
     *
     * @param string $meta
     *
     * @return User
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta
     *
     * @return string
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set securityToken
     *
     * @param string $securityToken
     *
     * @return User
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;

        return $this;
    }

    /**
     * Get securityToken
     *
     * @return string
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->processesOrder = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userProcessesOrder = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set profile
     *
     * @param \AppBundle\Entity\Profile $profile
     *
     * @return User
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
     * Add processesOrder
     *
     * @param \AppBundle\Entity\ProcessOrder $processesOrder
     *
     * @return User
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

    /**
     * Add userProcessesOrder
     *
     * @param \AppBundle\Entity\UserProcessOrder $userProcessesOrder
     *
     * @return User
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
