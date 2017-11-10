<?php

namespace AppBundle\Security\Firewall;

use AppBundle\Security\Authentication\Token\LdapUserToken;

use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Security;

class LdapListener extends AbstractAuthenticationListener
{
    private $csrfProvider;
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(
            TokenStorageInterface $tokenStorage, 
            AuthenticationManagerInterface $authenticationManager, 
            SessionAuthenticationStrategyInterface $sessionStrategy, 
            HttpUtils $httpUtils, 
            $providerKey, 
            AuthenticationSuccessHandlerInterface $successHandler, 
            AuthenticationFailureHandlerInterface $failureHandler, 
            array $options = array(), 
            LoggerInterface $logger = null, 
            EventDispatcherInterface $dispatcher = null, 
            CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($tokenStorage, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, array_merge(array(
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'csrf_parameter'     => '_csrf_token',
            'intention'          => 'authenticate',
            'post_only'          => true,
        ), $options), $logger, $dispatcher);

        $this->csrfProvider = $csrfProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        if ($this->options['post_only'] && !$request->isMethod('POST')) {
            return false;
        }

        return parent::requiresAuthentication($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        if (null !== $this->csrfProvider) {
            $csrfToken = $request->get($this->options['csrf_parameter'], null, true);

            if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'], null, true));
            $password = $request->request->get($this->options['password_parameter'], null, true);
        } else {
            $username = trim($request->get($this->options['username_parameter'], null, true));
            $password = $request->get($this->options['password_parameter'], null, true);
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate(new LdapUserToken($username, $password, $this->providerKey));
    }
}
