<?php 
namespace AppBundle\Security\User;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Doctrine\ORM\EntityManager;
use Exception;

class LdapUserProvider implements UserProviderInterface
{

    public function __construct(EntityManager $em,$authHelper)
    {
        $this->em = $em;
        $this->authHelper= $authHelper;
    }


    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository('AppBundle:User')
            ->findOneBy(array(
                "username"=>$username
                ));

        if(!$user || $user->getType() == User::USER_TYPE_LDAP){

            $objResult = $this->authHelper->getUserInfo($username);
            
            if($objResult->success){
                $userInfo = $objResult->description;

                if(!$user){
                    $user = new User();
                    $user->setType(User::USER_TYPE_LDAP);
                    $this->em->persist($user);
                    $user->setUsername($username);
                }

                $user->setMeta($userInfo->dn);
                $user->setMail($userInfo->email);
                $user->setName($userInfo->name);

                $this->em->flush();
               
            }
        }
        
        if($user){
            return $user;
        }

        throw new AuthenticationException(
            sprintf('Credenciales no válidas.')
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Entity\User';
    }
}

 ?>