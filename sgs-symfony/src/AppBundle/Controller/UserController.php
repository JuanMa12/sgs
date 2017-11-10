<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;

use Exception;
use stdClass;


class UserController extends Controller
{
    /**
     * @Route("/admin/user/list", name="user_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $profiles = $em->getRepository('AppBundle:Profile')
            ->findAll();

        return $this->render('user/list.html.twig',array('profiles'=> $profiles));
    }

    /**
     * @Route("/admin/user/grid", name="user_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'name',
            'role',
            'type',
            'status',
            'id',
            );
        
        $subquery = 'SELECT u.* FROM users as u ';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('user/itemActions.html.twig',array('item' => $data));

            switch ($data['role']) {
                case 1:
                    $roleName = 'Administrador';
                    break;
                default:
                    $roleName = 'Usuario';
            }

            switch ($data['type']) {
                case 2:
                    $typeName = 'Directorio Activo';
                    break;
                default:
                    $typeName = 'Local';
            }

            switch ($data['status']) {
                case 0:
                    $status = 'Inactivo';
                    break;
                case 1:
                    $status = 'Activo';
                    break;
                case 3:
                    $status = 'Bloqueado';
                    break;
                case 2:
                    $status = 'Pendiente';
                    break;
            }
          

            $result['output']['aaData'][] = array(
                $data['name'],
                $roleName,
                $typeName,
                $status,
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/user/form/save", name="user_save")
    */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();
            

            if ($params['_id'] != '') {
                $user = $em->getRepository('AppBundle:User')
                   ->find($params['_id']);

                $logMessage= 'Se edita la información del usuario con nombre '.$user->getName().' y nombre de usuario '.$user->getUsername();

            }else{
                $user = new User();
                $em->persist($user);

                $user->setType(User::USER_TYPE_LOCAL);
                $user->setStatus(User::USER_STATUS_PENDING);

                //verificacioni de username
                $validation = $this->getDoctrine()
                        ->getRepository('AppBundle:User')
                        ->findOneBy(array('username' =>$params['_username']));
            
                if($validation){
                    throw new Exception("El nombre de usuario ya existe", 1);       
                }

                //verificacion de correo
                $validation = $this->getDoctrine()
                            ->getRepository('AppBundle:User')
                            ->findOneBy(array('mail' =>$params['_email']));

                if($validation){
                    throw new Exception("Este correo ya está registrado", 1);       
                }

                $logMessage= 'Se crea un usuario con nombre '.$params['_name'].' y nombre de usuario '.$params['_username'];

            }

            $user->setName($params['_name']);
            $user->setUsername($params['_username']);
            $user->setMail($params['_email']);
            $user->setRole($params['_role']);

            if($params['_profile'] != ''){
                $profile = $em->getRepository('AppBundle:Profile')
                    ->find($params['_profile']);
                $user->setProfile($profile);
            }

            if($params['_id'] == ''){
                $token = hash_hmac("md5", $user->getUsername().time(), $this->container->getParameter('secret'));
                $user->setSecurityToken($token);

                $urltoken = urlencode($token);
                $link = $request->getScheme().'://'.$request->getHttpHost().$this->get('router')->generate('update_password_form', array('token' => $urltoken));

                $mailer = $this->container->get('mailer'); 
                $message = $mailer->createMessage() 
                            ->setCharset('UTF-8') 
                            ->setSubject('Bienvenido al SIGG') 
                            ->setTo($user->getMail()) 
                            ->setFrom($this->container->getParameter('mail_sender')) 
                            ->setBody($this->renderView( 'emails/wellcome.html.twig', array( 'user' => $user, 'confirmlink' => $link ) ), 'text/html' ); 
                $mailer->send($message);
            }

            $em->flush();

            $this->get('log_activity_manager')->registerActivity($logMessage);

            $output = array('success'=>true,'description' => $user->getId());

        } catch (Exception $e) {
            $output = array(
                "success" => false,
                "description" => $e->getMessage()
                );
        }

        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    /**
     * @Route("/admin/user/info", name="user_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();
            
            $user = $em->getRepository('AppBundle:User')
                ->find($params['id']);

            if(!$user){
                throw new Exception("Aseguradora no encontrada", 1);
            }

            $objResponse = array(
                'name' => $user->getName(),
                'username' => $user->getUsername(),
                'email' => $user->getMail(),
                'role' => $user->getRole(),
                'profile' => ($user->getProfile())?$user->getProfile()->getId():''
                ); 

            $output = array('success'=>true,'description' => $objResponse);

        } catch (Exception $e) {
            $output = array(
                "success" => false,
                "description" => $e->getMessage()
                );
        }

        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /**
    * @Route("/admin/user/changeStatus",name="user_changeStatus")
    */
    public function changeStatusAction(Request $request)
    {   
        try {

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $user = $this->getDoctrine()
                    ->getRepository('AppBundle:User')
                    ->find($params['id']);

            if(!$user){
                throw new Exception("El usuario no existe", 1);
            }

            $user->setStatus($params['status']);
            $em->flush();

            $this->get('log_activity_manager')->registerActivity('Se cambia estado de usuario con nombre de usuario: '.$user->getUsername(). ' a estado '.$params['status']);
        
            $output = array('success'=>true,'description' => $user->getId());
        
        } catch (Exception $e) {
            $output = array(
                "success" => false,
                "description" => $e->getMessage()
                );
        }

        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;   
    }

    
}

