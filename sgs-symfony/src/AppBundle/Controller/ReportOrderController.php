<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\ProcessOrder;
use AppBundle\Entity\ProfileProcessOrder;
use AppBundle\Entity\UserProcessOrder;
use AppBundle\Entity\Process;
use Exception;
use StdClass;

class ReportOrderController extends Controller
{
    /**
    * @Route("/report/order/info/{module}", name="report_order_info")
    */
    public function reportOrderValueAction(Request $request,$module)
    {   
        try {
            $em = $this->getDoctrine()->getManager();
            $user= $this->getUser();

            $params = $request->request->all();
            
            if (isset($params['filters'])) {
                $whereTypeId = "pot.id IN (".implode(',',$params['filters']).")";
            }


            $qb = $em->getRepository('AppBundle:ProcessOrder')
                ->createQueryBuilder('po')
                ->innerJoin('po.processOrderType','pot','poy.id = po.process_order_type_id')
                ->where($whereTypeId)
                ->andWhere("po.status != :status")
                ->andWhere("pot.type = 1")
                ->andWhere("pot.module = :module")
                ->setParameter('status',ProcessOrder::PROCESS_ORDER_STATUS_DELETED)
                ->setParameter('module',$module)
                ;


            //Busqueda por palabra
            if(isset($params['word']) && $params['word'] != ''){
                $qb->andWhere('po.name like :lname')
                    ->setParameter('lname',"%".$params['word']."%");
            }

            if ($user->getRole() != 1 and $user->getProfile()) {

                $qb ->innerJoin('po.profileProcessesOrder','ppo')
                    ->andWhere("ppo.profile =:profile")
                    ->setParameter('profile',$user->getProfile());

            }
            if ($user->getUserProcessesOrder()) {
                $qb ->leftJoin('po.userProcessesOrder','upo')
                    ->orderBy('po.id', 'DESC');

                $arrUserProcessOrder= array();
                foreach ($user->getUserProcessesOrder() as $userProcessesOrder) {
                    $arrUserProcessOrder[]= $userProcessesOrder->getProcessOrder()->getId();
                }
            }


            $limit = 10;
            $offset = ($params['page'] - 1) * $limit;
            $qb->setMaxResults($limit)
               ->setFirstResult($offset);

            $processeOrders = $qb->getQuery()->getResult();
            
            if($user->getRole()== 2 and !$user->getProfile()){
                $processeOrders = array();
            }
            $pathDelete = $this->get('router')->generate('process_status_save');

            $arrValueProcess=array();
            $arrProcessInfo=array();
            $arrProcessError=array();
            $user = $this->getUser();

            foreach ($processeOrders as  $processOrder) {
               
                $salient = false;
                if ($arrUserProcessOrder) {
                   if (in_array($processOrder->getId(), $arrUserProcessOrder)) {
                       $salient= true;
                   }
                }
                
                $valueProgress= 0;
                $errorProgress= 0;
                $processNumber= 0;
                foreach ($processOrder->getProcesses() as $process) {
                    if($process->getStatus() != Process::PROCESS_STATUS_INVALID){
                        $valueProgress += $process->getProgress();
                        $processNumber++;
                        foreach ($process->getProcessesError() as $processError) {
                            $errorProgress ++;                    
                        }
                    }
                }

                if($processNumber > 0){
                    $valueProgress = $valueProgress/$processNumber;
                }

                $path = $this->get('router')->generate('process_error_detail', array('id' =>$processOrder->getId()));

                $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $processOrder->getId()));

                $actions= false;
                if ($user->getRole() == 1 or $processOrder->getUser() == $user) {
                    $actions=true;
                }
                $messageHomologation = '';
                if ($processOrder->getHomologation()) {
                    $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $processOrder->getHomologation()));
                    $messageHomologation = "Reporte Homologado.";
                }

                $pathGetVisibility = $this->get('router')->generate('process_visibility_detail',array('id'=> $processOrder->getId()));

                $pathSalientSave = $this->get('router')->generate('process_salient_save',array('id'=> $processOrder->getId()));

                $objProcessInfo = new StdClass;
                $objProcessInfo->id = $processOrder->getId();
                $objProcessInfo->name = $processOrder->getName();
                $objProcessInfo->date = date('Y-m-d h:i',$processOrder->getDate());
                $objProcessInfo->status = $processOrder->getStatus();
                $objProcessInfo->path = $path;
                $objProcessInfo->progress = $valueProgress;
                $objProcessInfo->errors = $errorProgress;
                $objProcessInfo->pathDelete = $pathDelete;
                $objProcessInfo->messageHomologation = $messageHomologation;
                $objProcessInfo->actions = $actions;
                $objProcessInfo->pathGetVisibility = $pathGetVisibility;
                $objProcessInfo->pathHomeReport = $pathHomeReport;
                $objProcessInfo->pathSalientSave = $pathSalientSave;
                $objProcessInfo->salient = $salient;
                $objProcessInfo->params = $processOrder->getParams();
                $objProcessInfo->type = $processOrder->getProcessOrderType()->getId();
                $objProcessInfo->typeName = $processOrder->getProcessOrderType()->getName();

                $arrProcessInfo = $objProcessInfo;

                $arrValueProcess[] = $arrProcessInfo;
            }  

           $output = array('success'=>true,'description' => $arrValueProcess);

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

    * @Route("/process/visibility/detail/{id}", name="process_visibility_detail")
    */
    public function getProcessVisibilityAction(ProcessOrder $processOrder)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $objProfiles = $em->getRepository('AppBundle:Profile')
                            ->findAll();
                
            $arrProfiles= array();
            foreach ($objProfiles as $profile) {
                $arrProfiles[$profile->getId()] = array(
                    'profileName'=>$profile->getName(),
                    'status'=>0,
                    'disabled'=>0,
                    );     
            }

            if ($processOrder->getProfileProcessesOrder()) {
                foreach ($processOrder->getProfileProcessesOrder() as $value) {
                    $active = 0;
                    if ($processOrder->getUser()->getProfile() && $value->getProfile()->getId() == $processOrder->getUser()->getProfile()->getId()) {
                       $active = 1;
                    }
                    $arrProfiles[$value->getProfile()->getId()]= array(
                        'profileName' => $value->getProfile()->getName(),
                        'status'=> 1,
                        'disabled'=> $active,
                        );   
                } 
            }
            $output = array("success"=>true,"description"=> $arrProfiles);

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
    * @Route("/process/visibility/save", name="process_visibility_save")
    */
    public function changeVisibilityAction(Request $request) 
    {
        try {

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();
            $user= $this->getUser();
            
            $processOrder = $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                            ->find($params['process_order_id']);

            if(!$processOrder){
                throw new Exception("La orden de proceso no existe", 1);
            }
            
            if ($processOrder->getUser() != $user and $user->getRole() != 1) {
                throw new Exception("El usuario no esta habilitado para asignar permisos a este reporte.", 1);
            }

            foreach ($processOrder->getProfileProcessesOrder() as $profileProcessOrder) {
                
                if ($user->getRole() != 1) {
                    if ($processOrder->getUser()->getProfile() != $profileProcessOrder->getProfile()) {
                        
                        $em->remove($profileProcessOrder);
                        $em->flush();
                    }
                }else{
                    $em->remove($profileProcessOrder);
                    $em->flush(); 
                }
            }

            if (isset($params['_profile'])) {
                foreach ($params['_profile'] as $profileId) {
                    
                    $profile = $this->getDoctrine()->getRepository('AppBundle:Profile')
                                   ->find($profileId);
                    
                    $profileProcessOrder = new ProfileProcessOrder();
                    $em->persist($profileProcessOrder);

                    $profileProcessOrder->setProcessOrder($processOrder);
                    $profileProcessOrder->setProfile($profile);

                    $em->flush();
                }
                
            }
           
            $logMessage = 'Se editar los acceos de la orden de proceso: '.$processOrder->getName().' y ID: '.$processOrder->getId();

            $this->get('log_activity_manager')->registerActivity($logMessage);
                            
            $output = array('success'=>true,'description' => $processOrder->getId());
        
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
    * @Route("/process/salient/save", name="process_salient_save")
    */
    public function getProcessSalientAction(Request $request)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $params = $request->request->all();
            $user= $this->getUser();

            $processOrder = $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                    ->find($params['_id']);

            $userProcessOrder = $em->getRepository('AppBundle:UserProcessOrder')
                                    ->findBy(array('user' => $user,'processOrder'=>$processOrder));
            if ($userProcessOrder) {
                foreach ($userProcessOrder as $value) {
                    $em->remove($value);
                    $em->flush();
                }

                $status= 1; 
            
            }else{  

                $count= 0;
                foreach ($user->getUserProcessesOrder() as $userProcessOrder) {
                    if ($userProcessOrder->getProcessOrder()->getStatus() != 4) {
                        $count++;
                    }
                }

                if ($count >= 5) {
                    throw new Exception("Lo sentimos el usuario a superado el limite de favoritos.", 1);
                }
                
                $userProcessOrder = new UserProcessOrder();
                $em->persist($userProcessOrder);

                $userProcessOrder->setProcessOrder($processOrder);
                $userProcessOrder->setUser($user);

                $em->flush();

                $status= 0; 
            }


	 		$output = array("success"=>true,"description"=> $status);

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
    * @Route("/report/order/home/{id}", name="report_order_home")
    */
    public function homeReportAction(Request $request,ProcessOrder $processOrder)
    {   
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();

        if ($user->getRole() != 1 and $user->getProfile()) {

            $active = false;
            foreach ($processOrder->getProfileProcessesOrder() as $profileProcessOrder) {
                if ($profileProcessOrder->getProfile() == $user->getProfile()) {
                    $active = true;
                }
            }

            $homologation = $em->getRepository('AppBundle:ProcessOrder')
                                    ->findBy(array('user' => $user,'homologation'=>$processOrder->getId()));
            if ($homologation) {
                $active = true;
            }
            if (!$active) {
                $this->addFlash(
                    'notice',
                    'Lo sentimos su perfil no tiene accesos a este reporte.'
                );            
                
                return $this->redirectToRoute('demography_report_home');
            }
        }elseif($user->getRole() != 1 and !$user->getProfile()){
            $this->addFlash(
                'notice',
                'Lo sentimos perfil asignado para acceder a este reporte.'
            );

            return $this->redirectToRoute('demography_report_home');
        }

        //seteo de color en la interfaz
        switch($processOrder->getProcessOrderType()->getModule()){
            case 'health':
                $color = 'blue';
                break;
            case 'demography':
                $color = 'pink';
                break;
            case 'finantial':
                $color = 'green';
                break;
            default:
                $color = 'grey';
        }

        return $this->render('general/homeReport.html.twig',array('processOrder'=>$processOrder,"color"=>$color));
    }


    /**
    * @Route("/report/order/data", name="report_order_data")
    */
    public function homeDataReportAction(Request $request)
    {   
        try{
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();    

            $processOrder= $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                          ->find($params['_id']);

            $style = '';
            if (isset($params['style'])) {
                $style = $params['style'];
            }

            $htmlRender = $this->getHtmlProcessOrder($processOrder,true,true,$style);            
         
            $output = array("success"=>true,"description"=> $htmlRender);

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

    public function dashDataReportAction($processOrder){
        return new Response($this->getHtmlProcessOrder($processOrder,false,false,''));
    }

    /**
     * Function que renderiza una orden de proceso
     * @param  [type] $processOrder [description]
     * @return [type]               [description]
     */
    private function getHtmlProcessOrder($processOrder,$resume,$params,$style){
        try{
            try{
                //lanzamos el proceso en node
                $restClient = $this->get('circle.restclient');
                $result = $restClient->get($this->container->getParameter('engine_path').'/report/'.$processOrder->getId());
                
                $data = json_decode($result->getContent(),true)['description'];

            } catch (Exception $ex){
                $message = 'La conexión con el motor de procesamiento no fue efectiva:'. $ex->getMessage();
            }
            
            switch ($processOrder->getProcessOrderType()->getId()) {
                case 2:
                    
                    $htmlRender = $this->renderView('demography/chartReportEps.html.twig',array(
                        'arrResult' => $data,
                        'periods' => $processOrder->getMetaData()->periods,
                        'processOrder' => $processOrder,
                        'params'=>$params
                    ));

                    break;
                 case 27:
                    $arrPeriods = array();        
                    $arrResult = array();  
                    $totalRegimenPeriod = 0; 
                    $arrPeriodos = array();
                    foreach ($data as $obj) {
                        if(!in_array(date('Y-m',$obj["periodo"]), $arrPeriodos)){
                            $arrPeriodos[] = date('Y-m',$obj["periodo"]);
                        }
                    }
                                       
                    foreach ($data as $obj) {
                        if(!isset($arrRegimens[$obj["regimen"]])){
                            $arrRegimens[$obj["regimen"]] = array();
                        }
                        $arrRegimens[$obj["regimen"]][] = $obj;
                    }

                    foreach ($arrRegimens as $key => $regimens) {
                        $arrPeriods= array();
                        foreach ($regimens as  $value) {
                            $regPeriod = date("Y-m",$value["periodo"]);
                            if(!isset($arrPeriods[$regPeriod])){

                                $arrPeriods[$regPeriod] =  $value["cantidad"];
                            }else{

                                $totalRegimenPeriod += $arrPeriods[$regPeriod] + $value["cantidad"];
                                $arrPeriods[$regPeriod] = $totalRegimenPeriod;
                            
                            }
                            $totalRegimenPeriod=0;

                        }

                        foreach ($arrPeriodos as $periodT) {

                            if (!isset($arrPeriods[$periodT])) {
                               $arrPeriods[$periodT]=0;
                            }
                        }

                        $arrResult[$key]= $arrPeriods;
                    }

                    $htmlRender = $this->renderView('demography/chartHome.html.twig',array(
                        'data' => $arrResult,
                        'periods' => $arrPeriodos,
                        'processOrder' => $processOrder,
                        'resume'=>$resume,
                        'params'=>$params,
                        ));

                    break;
                case 3:
                    $htmlRender = $this->renderView('charts/multiLevelChart.twig',array(
                        'processOrder' => $processOrder,
                        "fistLevelName"=>"Departamentos",
                        "data"=>$data,
                        'style' => $style,
                        'params'=>$params
                    ));
                    break;
                case 4:
                    $htmlRender = $this->renderView('charts/multiLevelChart.twig',array(
                        'processOrder' =>$processOrder,
                        "fistLevelName"=>"Gremios",
                        "data"=>$data,
                        'style' => $style,
                        'params'=>$params
                    ));
                    break;
                case 8:
                    foreach ($data as $key => $value) {
                        foreach ($value as $key=>$eps) {
                            $arrEpss[]= $key;
                        }
                    }

                    $htmlRender = $this->renderView('finantial/chartReportNoposNR.html.twig',array('arrPeriods'=>$data,
                        'arrEpss'=>$arrEpss,
                        'processOrder' =>$processOrder,
                        'params'=>$params
                        ));

                    break;

                case 12:

                    foreach ($data as $key => $value) {
                        foreach ($value as $key=>$eps) {
                            $arrEpss[]= $key;
                        }
                    }

                    $htmlRender = $this->renderView('finantial/chartReportNoposNR.html.twig',array(
                        'arrPeriods'=>$data,
                        'arrEpss'=>$arrEpss,
                        'processOrder' =>$processOrder,
                        'params'=>$params));

                    break;
                case 18:            
                    $htmlRender = $this->renderView('finantial/chartReportES.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 20:           
                    $htmlRender = $this->renderView('finantial/chartReportLMA.html.twig',array(
                        'processOrder'=>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 21:
                    $htmlRender = $this->renderView('health/chartReportFrec.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 22:
                    $htmlRender = $this->renderView('health/chartReportExt.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 23:
                    $htmlRender = $this->renderView('demography/chartReportPPy.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 26:
                    $htmlRender = $this->renderView('finantial/chartReportCR.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 28:
                    $htmlRender = $this->renderView('health/chartReportDiag.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 29:
                    $htmlRender = $this->renderView('health/chartReportProd.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 30:        
                    $htmlRender = $this->renderView('health/chartReportInt.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 32:        
                case 33:        
                case 34:
                case 36:
                case 39:
                case 41:
                case 42:
                case 44:
                case 48:
                case 50:
                    $htmlRender = $this->renderView('finantial/chartReportCircular.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'params'=>$params
                        ));
                    break;
                case 37:
                    $htmlRender = $this->renderView('operative/chartReportMobility.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'params'=>$params
                        ));
                    break;
                case 38:
                    $htmlRender = $this->renderView('health/chartReportEP.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                 case 45:
                    $htmlRender = $this->renderView('health/chartReportAmbit.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
                case 46:
                    $htmlRender = $this->renderView('health/chartReportModality.html.twig',array(
                        'processOrder' =>$processOrder,
                        'data'=>$data,
                        'resume'=>$resume,
                        'params'=>$params
                        ));
                    break;
            }
        }catch(Exception $e){ 
            $htmlRender= "<p>No se pudo renderizar orden {$processOrder->getId()}. Consulte al administrador del portal.</p>";
        }

        return $htmlRender;
    }


    /**
    * @Route("/report/order/params/html", name="report_order_params_html")
    */
    public function paramsHtmlReportAction(Request $request)
    {   
        try{
            
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 

            if ($params['_name'] == 'periods') {
                $arrPeriods = array();
                $anio2 = intval(date("Y"));
                $anio1 = date("Y")-4;

                for ($i = $anio2; $i > $anio1; $i+= -1) { 
                    $arrPeriods[$i] = array();
                    for ($j=1; $j <13 ; $j++) {
                        if ($j<10) {
                            $period = $i.'-0'.$j;
                        }else{
                            $period = $i.'-'.$j;                               
                        } 
                        array_push($arrPeriods[$i],strtotime($period));
                    }

                }

            }   
            $mode = null;
            if (isset($params['_mode'])) {
                $mode = $params['_mode'];
            }
            //seteo de color en la interfaz
            switch($params['_name']){
                case 'status':
                   $htmlRender = $this->renderView('wizard/status.html.twig');
                    break;
                case 'guilds':                    
                    $arrGuilds = $this->getDoctrine()->getRepository('AppBundle:Guild')
                                        ->findAll();

                    $htmlRender = $this->renderView('wizard/guilds.html.twig',array('guilds'=>$arrGuilds));
                    break;
                case 'regime':
                   $htmlRender = $this->renderView('wizard/regime.html.twig');
                    break;
                case 'group':
                   $htmlRender = $this->renderView('wizard/group.html.twig');
                    break;
                case 'payer':
                   $htmlRender = $this->renderView('wizard/payer.html.twig');
                    break;
                case 'eps':
                    $arrGuilds = $this->getDoctrine()->getRepository('AppBundle:Guild')
                                        ->findAll();
                    $htmlRender = $this->renderView('wizard/eps.html.twig',array('guilds'=>$arrGuilds));
                    break;
                case 'source':
                    $htmlRender = $this->renderView('wizard/source.html.twig',array('mode' =>$mode));
                    break;
                case 'georeferencing':
                    $htmlRender = $this->renderView('wizard/location.html.twig');
                    break;
                case 'genre':
                    $htmlRender = $this->renderView('wizard/genre.html.twig');
                    break;
                case 'periods':
                    $htmlRender = $this->renderView('wizard/periods.html.twig',array('periods'=>array_reverse($arrPeriods,true),'mode' =>$mode));
                    break;
                case 'quality':
                    $htmlRender = $this->renderView('wizard/quality.html.twig');
					break;
                case 'diagnostics':
                    $htmlRender = $this->renderView('wizard/diagnostic.html.twig');
                    break;
                case 'typeReport':
                    $htmlRender = $this->renderView('wizard/typeReport.html.twig');
                    break;
                case 'procedures':
                    $htmlRender = $this->renderView('wizard/procedures.html.twig');
                    break;
                case 'quinquennialGroup':
                    $arrQGroups = $this->getDoctrine()->getRepository('AppBundle:QuinquennialGroup')
                                        ->findAll();

                    $htmlRender = $this->renderView('wizard/quinquennialGroup.html.twig',array('quinquennialGroups' => $arrQGroups));
                    break;
                case 'typeGroup':
                    $htmlRender = $this->renderView('wizard/typeGroup.html.twig');
                    break;
                default:
                    throw new Exception("Tipo de parametro no encontrado.", 1);
                    
            }           

            $output = array("success"=>true,"description"=> $htmlRender);

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



