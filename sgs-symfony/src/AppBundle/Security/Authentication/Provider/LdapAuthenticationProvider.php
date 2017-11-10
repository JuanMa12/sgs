<?php

namespace AppBundle\Security\Authentication\Provider;

use AppBundle\Security\Authentication\Token\LdapUserToken;

use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;

use AppBundle\Entity\User;

class LdapAuthenticationProvider implements AuthenticationProviderInterface {

    private $userProvider;
    private $providerKey;

    public function __construct(UserProviderInterface $userProvider, $providerKey,$authHelper,$em,$encoder) {
        $this->userProvider = $userProvider;
        $this->providerKey = $providerKey;
        $this->authHelper = $authHelper;
        $this->em = $em;
        $this->encoderService = $encoder;
    }

    public function authenticate(TokenInterface $token) {
            
        if (!$this->supports($token)) {
            return null;
        }

        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $password = $token->getCredentials();

        if ($password) {
            if($user->getType() == User::USER_TYPE_LDAP){
                if (!$this->authHelper->checkUserAuth($user->getMeta(), $password)) {
                    throw new AccountExpiredException('Credenciales no válidas.');
                }
            }else{
                if ($user->getStatus() == User::USER_STATUS_INACTIVE) {
                   
                   throw new DisabledException('Este usuario ha sido inhabilitado.');
                
                }elseif ($user->getStatus() == User::USER_STATUS_LOCKED){
                    throw new LockedException('El usuario se encuentra bloqueado.');
                }

                $encoder = $this->encoderService->getEncoder($user);
                if(!$encoder->isPasswordValid($user->getMeta(), $password, $user->getSalt())){
                    throw new AuthenticationException('Credenciales no válidas.');
                }
            }
        }

        $authenticatedToken = new LdapUserToken($user, $password, $this->providerKey, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token) {
        return $token instanceof LdapUserToken;
    }

}
