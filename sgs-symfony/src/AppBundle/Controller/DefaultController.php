<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\Security\Csrf\CsrfToken;

use Exception;

use AppBundle\Entity\User;
use AppBundle\Entity\ProcessOrder;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $qb = $em->getRepository('AppBundle:ProcessOrder')
            ->createQueryBuilder('po')
            ->innerJoin('po.processOrderType','pot','poy.id = po.process_order_type_id')
            ->andwhere("po.status != :status")
            ->andWhere("pot.type = 1")//reporte
            ->setParameter('status',ProcessOrder::PROCESS_ORDER_STATUS_DELETED)//activo
            ->setMaxResults(5)
            ;

        if ($user->getUserProcessesOrder()) {
            $qb ->innerJoin('po.userProcessesOrder','upo','upo.process_order_id = po.id')
                ->andWhere("upo.user = :user")//reporte
                ->setParameter('user',$user)
                ->orderBy('upo.id', 'DESC');
        }

        $processeOrders = $qb->getQuery()->getResult();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            "processOrders"=>$processeOrders
            ));
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'default/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }

    /**
     * @Route("/profile", name="profile_form")
     */
    public function profileAction()
    {
        return $this->render('default/profile.html.twig');
    }

    /**
     * @Route("/profile/save", name="my_profile_save")
     * @Method({"POST"})
     */
    public function saveProfileAction(Request $request)
    {
        try {

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();
            
            $user = $this->getUser();

            if($user->getType() == User::USER_TYPE_LDAP){
                throw new Exception("El tipo de usuario no permite hacer modificaciones.", 1);
            }

            $user->setName($params['_name']);
            $user->setMail($params['_email']);

            $em->flush();

            $this->get('log_activity_manager')->registerActivity('se editan los datos del usuario '.$user->getName().' con el nombre de usuario: '.$user->getUsername());

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
     * @Route("/auth/recover_password/form", name="recover_password_form")
     */
    public function recoverPasswordFormAction()
    {
        $csrf = $this->get('security.csrf.token_manager');
        $csrf->refreshToken('change_password');

        return $this->render('default/password_recover.html.twig');
    }

    /**
     * @Route("/auth/recover_password/action", name="recover_password_action")
     * @Method({"POST"})
     */
    public function recoverPasswordAction(Request $request)
    {
        try {               
            $params = $request->request->all();

            $csrf = $this->get('security.csrf.token_manager');
            $csrf_token = new CsrfToken('change_password', $params['_csrf_token']);
            if(!$csrf->isTokenValid($csrf_token)){
                //validacion de csrf
                throw new Exception("Lo sentimos. Por razones de seguridad, por favor recargue la pagina.", 1);
            }

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')
                ->findOneBy(array('mail'=>$params['email']));

            if(!$user) {
                throw new Exception("El correo no se encuentra registrado.", 1);
            }

            if($user->getType() == User::USER_TYPE_LDAP){
                throw new Exception("Los usuarios del directorio activo deben consultar con su administrador para cambio de contraseña.", 1);
            }

            $status = $user->getStatus();

            if($status != User::USER_STATUS_ACTIVE) {
                //el usuario no se encuentra activo
                throw new Exception("El Usuario no se encuentra activo", 1);
            }

            //se genera el token
            $token = hash_hmac("md5", $user->getUsername().time(), $this->container->getParameter('secret'));
            $user->setSecurityToken($token);
            $em->flush();

            $urltoken = urlencode($token);
            $link = $request->getScheme() . '://' . $request->getHttpHost() . $this->get('router')->generate('update_password_form', array('token' => $urltoken));

            //envio de correo de bienvenida
            $mailer = $this->container->get('mailer');
            $message = $mailer->createMessage()
                    ->setCharset('UTF-8')
                    ->setSubject('Recuperacion de contraseña')
                    ->setTo($user->getMail())
                    ->setFrom($this->container->getParameter('mail_sender'))
                    ->setBody(
                        $this->renderView(
                            'emails/recoverPassword.html.twig',
                            array(
                                'user' => $user,
                                'confirmlink' => $link
                                ) 
                        ), 
                        'text/html'
                    );

            $mailer->send($message);


            $output = array('success'=>true,'description' => 'Se envio el correo');
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
     * @Route("/auth/update_password/form/{token}", name="update_password_form")
     */
    public function updatePasswordFormAction(Request $request ,$token)
    {
        $token = urldecode($token);

        if($this->getUser()){
            $this->addFlash(
                'notice',
                'Hay un usuario con una sesion valida. Por favor cerrar session e intentar de nuevo'
            );

            return $this->redirectToRoute('homepage');
        }


        if ($token == '') {
            $this->addFlash(
                'notice',
                'Token invalido.'
            );

            return $this->redirectToRoute('login');
        }
        
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(array('securityToken' =>$token));
        
        if (!$user) {
            $this->addFlash(
                'notice',
                'Token asociado a usuario invalido.'
            );

            return $this->redirectToRoute('login');
        }


        $session = $request->getSession();
        $session->set('recover_userid',$user->getId());

        return $this->render('default/password_update.html.twig',array('user'=>$user));
    }

    /**
     * @Route("/auth/update_password", name="update_password")
     */
    public function updatePasswordAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                
                $session = $request->getSession();
                $userId = $session->get('recover_userid');

                $user = $this->getDoctrine()
                    ->getRepository('AppBundle:User')
                    ->find($userId);

                if (!$user) {
                    throw new Exception("Usuario incorrecto", 1);
                }

            }else{
                $user = $this->getUser();
            }

            $password= $params['_password'];

            if (!preg_match("/^.*(?=.{6,})(?=.*\d)(?=.*[a-z]).*$/", $password)) { 
                
                throw new Exception("Contraseña No valida", 1); 
            }

            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $pass = $encoder->encodePassword($params['_password'],$user->getSalt());
            
            $user->setMeta($pass);
            $user->setSecurityToken(null);
            $user->setStatus(User::USER_STATUS_ACTIVE);
            //$user->setAttempts(0);

            $em->flush();

            if (!$this->getUser()) {
                //logeo al usuario
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);

            }
            
            $output = array('success'=>true,'description' => $this->get('router')->generate('homepage'));

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
