<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\ProcessOrder;
use AppBundle\Entity\ProcessOrderType;
use AppBundle\Entity\ProfileProcessOrder;

use Exception;
use stdClass;


class FinantialController extends Controller
{    
    /**
    * @Route("/finantial/home/report", name="finantial_home_report")
    */
    public function reporthomeAction(Request $request)
    {   
        //tipos de ordenes de proceso para los filtros
        $em = $this->getDoctrine()->getManager();
        $processOrderTypes = $em->getRepository('AppBundle:ProcessOrderType')
            ->findBy(array('module'=>'finantial'));

        return $this->render('finantial/report.html.twig',array('processOrderTypes'=>$processOrderTypes));
    }


     /**
     * @Route("/finantial/report/save", name="finantial_report_save")
     */
    public function reportSaveAction(Request $request)
    {
        try{

            $params = $request->request->all();            
            $em = $this->getDoctrine()->getManager();
            $user= $this->getUser();

            if ($user->getRole() != 1) {
                if ($user->getProfile()) {
                    if ($user->getProfile()->getCreateReport() != 1) {
                        throw new Exception("El perfil no tiene permisos para crear reportes.", 1);
                    }
                }else{
                    throw new Exception("no tiene perfil asignado, por favor comuniquese con el administrador.", 1);
                }
            }
            
            if(!isset($params['type_id'])){
                throw new Exception("Seleccione un tipo de reporte", 1);
            }

            $processOrderType = $this->getDoctrine()->getRepository('AppBundle:ProcessOrderType')
                    ->find($params['type_id']);

            if (!$processOrderType) {
                throw new Exception("tipo de orden de proceso no encontrado", 1); 
            } 

            $objValidate = $this->get('report_validator')->getValidateReport($processOrderType,$params);

            $homologation = $objValidate['homologation'];
            $processOrder = new ProcessOrder();
            $em->persist($processOrder);

            $processOrder->setProcessOrderType($processOrderType);
            $processOrder->setName($objValidate['name']);
            $processOrder->setDate(time()); 
            $processOrder->setUser($user);

            $messageHomologation = '';
            if ($homologation !=  '') {
                $messageHomologation = "Reporte Homologado."; 

                $processOrder->setHomologation($homologation);                
            }else{
                $processOrder->setCreateToken($objValidate['hash']);
            }

            $name = $processOrder->getId().'_'.hash('md5',time().'llaverara').'.csv'; 
            $arrAddInfo = array(                    
                    'endPoint' => "/finantial/".$name
            );

            $arrAddParams = array();

            //Validacion por el parametro de parametro de calidad
            if(array_search($processOrderType->getId(),[18]) > -1){
               $arrAddInfo['qualityNumbers'] = [4,5,6,7,8,9];
            }

            //almacena los codigos de las eps
            if(array_search($processOrderType->getId(), [18,20]) > -1){
                switch($params['type_select']){
                    case 'all':
                        $arrAddParams['epss'] = 'Todas';
                        break;
                     case 'guild':
                        $arrEps = [];
                        $arrEpsNames = [];
                
                        $qb = $em->createQueryBuilder();
                        $queryBuilder = $this->getDoctrine()->getRepository('AppBundle:HealthPromotionEntity')
                            ->createQueryBuilder('h')
                            ->where($qb->expr()->in('h.guild',$params['guildIds']));
                        
                        $arrObjEpss = $queryBuilder->getQuery()->getResult();

                        foreach ($arrObjEpss as $objEps) {
                            $arrEps[] = $objEps->getCode();
                            $arrEpsNames[] = $objEps->getName();
                        }
                        
                        $arrAddInfo['epss'] = $arrEps;
                        $arrAddParams['epss'] = $arrEpsNames;
                        break;
                    case 'epss':
                        foreach ($params['_in'] as $id) {
                                
                            $healthPromotionEntity = $this->getDoctrine()->getRepository('AppBundle:HealthPromotionEntity')
                                            ->find($id);

                            $arrEps[] = $healthPromotionEntity->getCode();
                            $arrEpsNames[] = $healthPromotionEntity->getName();
                        }   
                        
                        $arrAddInfo['epss'] = $arrEps;
                        $arrAddParams['epss'] = $arrEpsNames;
                        break;
                }
 
            }

            if(array_search($processOrderType->getId(), [18,20]) > -1){
                $arrAddInfo['periods'] = array();
                $arrPeriodsDate = array();  

                foreach ($params['periods'] as $value) {
                    $periods = explode(',', $value);
                    if (count($periods) > 0) {
                        foreach ($periods as $item) {                        
                            array_push($arrAddInfo['periods'], $item);
                            $arrPeriodsDate[] = date('Y-m',$item);
                        }
                    }else{
                        $arrPeriodsDate[] = date('Y-m',$value);
                        array_push($arrAddInfo['periods'], $value);
                    }
                }

                $arrAddParams['Periodos'] = $arrPeriodsDate;
            }
            
            if(array_search($processOrderType->getId(), [20]) > -1){
                switch($params['type_select_location']){
                    case 'all':
                        $arrAddParams['Municipios'] = 'Todos';
                        break;
                    case 'specific':
                        $arrCodeLocation= array();
                        $arrNameLocation= array();
               
                        foreach ($params['location'] as $id) {
                            
                            $municipality = $this->getDoctrine()->getRepository('AppBundle:Municipality')
                                            ->find($id);

                            if(!$municipality){
                                throw new Exception("Debe elegir un departamento valido", 1);
                            }
                            
                            $arrCodeLocation[] = $municipality->getCode();
                            $arrNameLocation[] = $municipality->getName();
                        } 

                        $arrAddParams['Municipios'] = $arrNameLocation;
                        $arrAddInfo['divipolas'] = $arrCodeLocation;  

                        break;
                }
            }                
            if(array_search($processOrderType->getId(), [18,20]) > -1){
                switch ($params['type_source']) {
                    case 'pip':
                        $nameSource= 'Pilotos';
                        break;
                    case 'mpp':
                        $nameSource= 'Caprecom';
                        break;        
                    case 'msps':
                        $nameSource= 'MSPS';
                        break;        
                    case 'eps_union':
                        $nameSource= 'EPS AGREMIADAS';
                        break;            
                    default:
                        $nameSource= 'Estudio de Suficiencia UPC';
                        break;
                } 
                $arrAddInfo['collection'] = $params['type_source'];
                $arrAddParams['Fuente'] = $nameSource;
            }
                      
            $processOrder->setParams($arrAddParams);
            $processOrder->setMetaData($arrAddInfo);
            $em->flush();

            //se asignan los permisos al reporte para el usuario que lo creo
            if ($user->getRole() != 1 && $user->getProfile()) {
                
                $profileProcessOrder = new ProfileProcessOrder();
                $em->persist($profileProcessOrder);

                $profileProcessOrder->setProcessOrder($processOrder);
                $profileProcessOrder->setProfile($user->getProfile());
                $em->flush();

            }elseif($user->getRole() != 1 && !$user->getProfile()){
                throw new Exception("El usuario no tiene asignado un perfil.", 1);

            } 
             
            $em->flush();

            $message = '';

            $path = $this->get('router')->generate('process_error_detail', array('id' =>$processOrder->getId()));

            $pathDelete = $this->get('router')->generate('process_status_save');
            
            $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $processOrder->getId()));

            $pathGetVisibility = $this->get('router')->generate('process_visibility_detail',array('id'=> $processOrder->getId()));            

            $pathSalientSave = $this->get('router')->generate('process_salient_save',array('id'=> $processOrder->getId()));

            $actions= false;
            if ($user->getRole() == 1 or $processOrder->getUser() == $user) {
                $actions=true;
            }

            if ($homologation != '') {
                $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $homologation));
            }

            $arrRender = array(
                'id' => $processOrder->getId(),
                'name' => $processOrder->getName(),
                'path' => $path,
                'date' =>  date('Y-m-h',$processOrder->getDate()),
                'progress' => 0,
                'errors' => 0,
                'message' => $message,
                'actions' => $actions,
                'pathDelete' => $pathDelete,
                'pathHomeReport' => $pathHomeReport,                
                'pathGetVisibility' => $pathGetVisibility,
                'salient' => false,
                'type' => $processOrderType->getId(),
                'typeName' => $processOrderType->getName(),
                'params' => $processOrder->getParams(),
                'status' => $processOrder->getStatus() ,
                'messageHomologation' => $messageHomologation, 
                );

            if ($homologation == '') {                           
                //se hace llamado al motor para correr el proceso
                try{

                    $restClient = $this->get('circle.restclient');
                    $restResult = $restClient->put($this->container->getParameter('engine_path').'/porder/'.$processOrder->getId(),'');    

                }catch(Exception $e){
                    $processOrder->setStatus(ProcessOrder::PROCESS_ORDER_STATUS_FAILED);
                    $em->flush();
                    $message = 'La conexión con el motor de procesamiento no fue efectiva:'. $e->getMessage();
                } 

                $this->get('log_activity_manager')->registerActivity('Se crea el reporte del modulo de demografia con el nombre '.$processOrder->getName().' y el ID '.$processOrder->getId());
            }else{
                $processOrder->setStatus(ProcessOrder::PROCESS_ORDER_STATUS_ENDED);
                $arrRender['status'] = 2;
                $arrRender['progress'] = 100;
                $em->flush();
            }
           
            $output = array('success'=>true,'description' => $arrRender);
       
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

