<?php 

namespace AppBundle\Security\Authentication\Node;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Exception;

/**
* 
*/
class AuthHelper
{
	
	function __construct($restClient,$helperHost)
	{
		$this->restClient = $restClient;
		$this->helperHost = $helperHost;
	}

	public function getUserInfo($username)
	{
		try{
			$response = $this->restClient->post($this->helperHost.'/info',json_encode(array('username'=>$username)));
			return json_decode($response->getContent());
		}catch(Exception $e){
			throw new AuthenticationException("No es posible conectar con el servidor de authenticacion", 1);
		}
	}

	public function checkUserAuth($dn,$password)
	{
		try{
			$iv = "0000000000000000";
			$cypherPassword = openssl_encrypt($password,'aes-128-cbc','1234567812345678',null,$iv);

			$result = $this->restClient->post($this->helperHost.'/auth',json_encode(array(
				'dn' => $dn,
				'password' => $cypherPassword,
				'iv'=> $iv
				)));

			$content = json_decode($result->getContent());

			if(isset($content->success)){
				return $content->success;
			}else{
				return false;
			}
		}catch(Exception $e){
			var_dump($e->getMessage());
			die();
			throw new AuthenticationException("No es posible conectar con el servidor de authenticacion", 1);
		}

	}
}

 ?>