<?php 

namespace AppBundle\Services;

use AppBundle\Entity\LogActivity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



/**
* Servicio para registro de enventos del log
*/
class LogActivityManager
{  

	protected $em;

	public function __construct($entityManager,TokenStorageInterface $token_storage)
	{
		$this->token_storage = $token_storage;
		$this->em = $entityManager;
	}
	
	public function registerActivity($message)
	{
		$user = $this->token_storage->getToken()->getUser();
		
		//se crea el registro del log en la entidad
		
		$logActivity = new LogActivity();
        $this->em->persist($logActivity);

        $logActivity->setDescription($message);
        $logActivity->setDate(time());
        $logActivity->setUser($user);

        $this->em->flush();
	}
}