<?php

namespace AppBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;


class LdapFactory extends AbstractFactory
{
    /*
    
    public function create(ContainerBuilder $container, $id, $config, $userProviderId, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.ldap.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('ldap.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProviderId))                
            ->replaceArgument(1, $id);
        
        $listenerId = 'security.authentication.listener.ldap.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('ldap.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'ldap';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
     */

    //////
    ///
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId) {
        return 'ldap.security.authentication.provider';
    }

    protected function getListenerId() {
        return 'ldap.security.authentication.listener';
    }

    public function getKey() {
        return 'ldap';
    }

    public function getPosition() {
        return 'pre_auth';
    }

}
