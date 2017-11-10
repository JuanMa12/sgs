<?php 
// src/AppBundle/Security/Authentication/Token/WsseUserToken.php
namespace AppBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class LdapUserToken extends AbstractToken
{
    private $credentials;

    public function __construct($user,$credentials,$providerKey, array $roles = array())
    {
        parent::__construct($roles);

         if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->setUser($user);
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}

 ?>