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


class HealthController extends Controller
{

    /**
    * @Route("/health/home/report/" ,name="health_home_report")
    */
    public function reportHomeAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();      
        $processOrderTypes = $em->getRepository('AppBundle:ProcessOrderType')
                                ->findBy(array('module'=>'health','type'=>1));

        return $this->render('health/report.html.twig',array(
            'processOrderTypes'=>$processOrderTypes
            ));
    }

    /**
     * @Route("/health/process/home/{processId}", defaults={"processId":""}, name="health_process_home")
     */
    public function processHomeAction(Request $request,$processId)
    {  
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($user->getRole() != 1 && !$user->getProfile() ) {
             $this->addFlash(
                    'notice',
                    'Lo sentimos no tiene un perfil asignado, comuniquese con el administrador.'
                );            
                
            return $this->redirectToRoute('homepage');
        }

        $processOrderTypes = $em->getRepository('AppBundle:ProcessOrderType')
                                ->findBy(array('module'=>'health','type'=>0));

        
        //llamado a funcion para optener los años y eps con procesos en la base de datos
        $objYearEps = $this->getYearAndEps(14); 

        //cargamos filtro por defecto
        $fieldName = "";
        if($processId != ""){
            $processOrder = $em->getRepository('AppBundle:ProcessOrder')->find($processId);

            if($processOrder){
                $fieldName = $processOrder->getName();
            }
        }

        return $this->render('health/process.html.twig',array('processOrderTypes'=>$processOrderTypes,'epss'=>$objYearEps,'defaultSearch'=>$fieldName));
    }

    /**
     * @Route("/health/order/save", name="health_order_save")
     */
    public function saveAction(Request $request)
    {
        try{
            $params = $request->request->all();            
            $em = $this->getDoctrine()->getManager();

            $name = '';
            if ($params['_type'] != 31) {
                foreach($request->files as $uploadedFile) {
                                        
                    if ($uploadedFile->getError() != 0) {
                        throw new Exception("Ha ocurrido un error durante el proceso", 1);
                    }
                    
                    $validFileTypes = array('text/csv','text/plain'); 

                    if (!in_array($uploadedFile->getClientMimeType(),$validFileTypes)) {
                        throw new Exception("El tipo de archivo no es compatible", 1);
                    }

                    $fileParts = pathinfo($uploadedFile->getClientOriginalName());
                    $validExtentions = array('csv','txt');// File extensions
                    if (!in_array(strtolower($fileParts['extension']),$validExtentions)) {
                        throw new Exception("El tipo de archivo no es compatible", 1);
                    }

                  
                    $baseDirectory = $this->container->getParameter('files_path');
                    $name = 'health'.'_'.time().'_'.hash('md5',time().'llaverara').'.'.$fileParts['extension'];

                    //se mueve el archivo al diectorio                
                    $uploadedFile->move($baseDirectory, $name);     

                }
            }
            $user= $this->getUser();

            //se crea el registro en la tabla de procesos                
            $processOrderType= $this->getDoctrine()->getRepository('AppBundle:ProcessOrderType')
                        ->find($params['_type']);
            
            $processOrder = new ProcessOrder();
            $em->persist($processOrder);

            $processOrder->setProcessOrderType($processOrderType);
            $processOrder->setName($params['_name']);
            $processOrder->setDate(time());
            $processOrder->setUser($user);

            $arrInfo = array();
            $arrAddParams = array();

            $arrInfo['source'] ='suf';
            $arrInfo['year'] = $params['_year']; 
            $arrAddParams['Año'] = $params['_year'];
            
            if ($name != '') {                    
                $arrInfo['path'] = $baseDirectory.$name;
                    
                $arrAddParams['filePath'] = $fileParts['basename'];
                                   
                switch($params['_type']){
                    case 24:
                        $arrInfo['source'] = 'pip';
                        break;
                    case 25:
                        $arrInfo['source'] = 'mpp';
                        break;
                }

            }else{
               $arrInfo['eps'] = $params['_eps'];        
               $arrAddParams['EPS'] = $params['_eps'];
            }

            if($params['_type']==31){
                $arrInfo['source'] = $params['_qsource'];
            }
            
            $processOrder->setMetaData($arrInfo);
            $processOrder->setParams($arrAddParams);
            
            $em->flush();

            $message = '';

            try{
                //lanzamos el proceso en node
                $restClient = $this->get('circle.restclient');
                $restResult = $restClient->put($this->container->getParameter('engine_path').'/porder/'.$processOrder->getId(),'');


            } catch (Exception $ex){
                $message = 'La conexión con el motor de procesamiento no fue efectiva:'. $ex->getMessage();
                $processOrder->setStatus(ProcessOrder::PROCESS_ORDER_STATUS_FAILED);
                $em->flush();
            }

            $path = $this->get('router')->generate('process_error_detail', array('id' =>$processOrder->getId()));
            
            $pathDelete = $this->get('router')->generate('process_status_save');

            $actions= false;
            if ($user->getRole() == 1 or $processOrder->getUser() == $user) {
                $actions=true;
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
                'type' => $processOrderType->getId(),
                'params' => $processOrder->getParams(),
                'autor' => $processOrder->getUser()->getName(),
                'status' => $processOrder->getStatus() 
                );            
        

            $this->get('log_activity_manager')->registerActivity('Se crea orden de proceso para el modulo de financiera con el nombre '.$processOrder->getName().' y el ID '.$processOrder->getId());
           
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

     /**
     * @Route("/health/report/save", name="health_report_save")
     */
    public function reportSaveAction(Request $request)
    {
        try{

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();            
            $user = $this->getUser();

            if ($user->getRole() != 1) {
                if ($user->getProfile()) {
                    if ($user->getProfile()->getCreateReport() != 1) {
                        throw new Exception("El perfil no tiene permisos para crear reportes.", 1);
                    }
                }else{
                    throw new Exception("no tiene perfil asignado, por favor comuniquese con el administrador.", 1);
                }
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

            switch ($params['type_source']) {
                case 'pip':
                    $nameSource= 'Pilotos';
                    break;
                case 'mpp':
                    $nameSource= 'Caprecom';
                    break;            
                default:
                    $nameSource= 'Estudio de Suficiencia UPC';
                    break;
            } 

            $arrPeriosDate = array();
            foreach ($params['periods'] as $value) {
               $arrPeriosDate[] = date('Y-m',$value);
            }

            $arrInfoParams = array(
                "Periodos" => $arrPeriosDate,
                "Fuente" => $nameSource
               );

            $extension = ".csv";

            if(array_search($processOrderType->getId(), [28,29]) >-1){
                $extension = ".zip";
            }

            $fileName = $processOrder->getId().'_'.hash('md5',time().'llaverara').$extension;
            
            $arrEngineParams = array(                    
                    'endPoint' => "/health/".$fileName,
                    'periods'=> $params['periods'],
                    'collection'=> $params['type_source']
                    );
 
            //Validacion por el parametro de parametro de calidad
            if(array_search($processOrderType->getId(),[21,22,28,29,30,38,45,46]) > -1){
                $arrEngineParams['qualityNumbers'] = [4,5,6,7,8,9];
            }
            
            $arrEps = array();
            $arrEpsNames = array();
            
            switch($params['type_select']){
                case 'all':
                    $arrInfoParams['EPSs'] = 'Todas';
                    break;
                case 'guild':
                    $qb = $em->createQueryBuilder();
                    $queryBuilder = $this->getDoctrine()->getRepository('AppBundle:HealthPromotionEntity')
                        ->createQueryBuilder('h')
                        ->where($qb->expr()->in('h.guild',$params['guildIds']));
                    
                    $arrObjEpss = $queryBuilder->getQuery()->getResult();

                    foreach ($arrObjEpss as $objEps) {
                        $arrEps[] = $objEps->getCode();
                        $arrEpsNames[] = $objEps->getName();
                    }

                    $arrInfoParams['EPSs'] = $arrEpsNames;
                    $arrEngineParams['epss'] = $arrEps;
                    
                    break;
                case 'epss':
                    foreach ($params['_in'] as $id) {
                            
                        $healthPromotionEntity = $this->getDoctrine()->getRepository('AppBundle:HealthPromotionEntity')
                                        ->find($id);

                        $arrEpsNames[] = $healthPromotionEntity->getName();
                        $arrEps[] = $healthPromotionEntity->getCode();
                    }   
                    
                    $arrInfoParams['EPSs'] = $arrEpsNames;
                    $arrEngineParams['epss'] = $arrEps;
                    break;
            }

            //diagnosticos
            if(array_search($processOrderType->getId(), [28]) >-1){
                switch($params['type_diagnostic']){
                    case 'all':
                        $arrInfoParams['Diagnosticos'] = 'Todos';
                        break;
                    case 'diagnostics':                        
                        foreach ($params['diagnostics'] as $diagnosticId) {
                            $diagnostic = $this->getDoctrine()->getRepository('AppBundle:Diagnostic')
                                            ->find($diagnosticId);

                            if(!$diagnostic){
                                throw new Exception("diagnostico no encontrado.", 1);
                            }
                            
                            $arrDiagnostics[] = $diagnostic->getCode();
                            $arrDiagnosticNames[] = $diagnostic->getDescription();
                        }

                        $arrInfoParams['Diagnosticos'] = $arrDiagnosticNames;
                        $arrEngineParams['diagnostics'] = $arrDiagnostics; 
                        break;
                }
            }

            //procedimientos
            if(array_search($processOrderType->getId(), [29]) >-1){
                switch($params['type_procedure']){
                    case 'all':
                        $arrInfoParams['Procedimientos'] = 'Todos';
                        break;
                    case 'procedures':
                        foreach ($params['code_procedure'] as $procedureId) {
                        
                            $procedure = $this->getDoctrine()->getRepository('AppBundle:HealthProcess')
                                            ->find($procedureId);

                            if(!$procedure){
                                throw new Exception("procedimiento no encontrado.", 1);
                            }
                            
                            $arrProcedures[] = $procedure->getCode();
                            $arrProceduresNames[] = $procedure->getDescription();
                        }
                        $arrInfoParams['Procedimientos'] = $arrProceduresNames;
                        $arrEngineParams['procedures'] = $arrProcedures; 
                        break;
                }
            }

            //localizacion
            if(array_search($processOrderType->getId(), [38,45,46]) > -1){
                switch($params['type_select_location']){ 
                    case 'all':
                        $arrInfoParams['Municipios'] = 'Todos';
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

                        $arrInfoParams['Municipios'] = $arrNameLocation;
                        $arrEngineParams['divipola'] = $arrCodeLocation;  
                        break;
                }
            }    

             //grupos quinquenales
            if(array_search($processOrderType->getId(), [38]) > -1){
                switch($params['type_select_qg']){
                    case 'all':
                        $arrInfoParams['Grupos_Quinquenales'] = 'Todos';
                        break;
                    case 'q_group':   
                        $arrCodeQGroup= array();
                        $arrNameQGroup= array();
               
                        foreach ($params['quinquennial_groups'] as $id) {
                            
                            $quinquennialGroup = $this->getDoctrine()->getRepository('AppBundle:QuinquennialGroup')
                                            ->find($id);

                            if(!$quinquennialGroup){
                                throw new Exception("Debe elegir un grupo quinquenal valido.", 1);
                            }
                            
                            $arrCodeQGroup[] = $quinquennialGroup->getId();
                            $arrNameQGroup[] = $quinquennialGroup->getName();
                        } 

                        $arrInfoParams['Grupos_Quinquenales'] = $arrNameQGroup;
                        $arrEngineParams['qGroup'] = $arrCodeQGroup;                          
                        break;
                }
            }    

            $processOrder->setParams($arrInfoParams);
            $processOrder->setMetaData($arrEngineParams);

            $em->flush();

            $message = '';

            $path = $this->get('router')->generate('process_error_detail', array('id' =>$processOrder->getId()));

            $pathDelete = $this->get('router')->generate('process_status_save');
            $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $processOrder->getId()));
            
            if ($homologation != '') {
                $pathHomeReport = $this->get('router')->generate('report_order_home',array('id'=> $homologation));
            }

            $pathGetVisibility = $this->get('router')->generate('process_visibility_detail',array('id'=> $processOrder->getId()));            

            $pathSalientSave = $this->get('router')->generate('process_salient_save');

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
            
            $actions= false;
            if ($user->getRole() == 1 or $processOrder->getUser() == $user) {
                $actions=true;
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
                'pathSalientSave' => $pathSalientSave,
                'salient' => false,
                'type' => $processOrderType->getId(),
                'typeName' => $processOrderType->getName(),
                'params' => $processOrder->getParams(),
                'status' => $processOrder->getStatus(), 
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

    /**
    * @Route("/health/validate/file/name",name="health_validate_file_name")
    */
    public function validateFileNameAction(Request $request)
    {   
        try {

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();


            $processOrders = $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                          ->findAll();

            $arrFileName = explode("\\", $params['fileName']);
            $lastPosition= count($arrFileName)-1;
            $strFileName = $arrFileName[$lastPosition];
            $name= explode('.',$strFileName);
            $strName= $name[0];

            foreach ($processOrders as $processOrder) {
                if ($processOrder->getStatus() != 4 and $processOrder->getProcessOrderType()->getType() == 0) {
                    if(isset($processOrder->getParams()['filePath'])){
                        if ($processOrder->getParams()['filePath'] == $strFileName) {
                            throw new Exception("El nombre del archivo ya existe", 1);  
                        }
                    }  
                }
            }
           
            if (strstr($strName,'ESUF')) {
                if (strlen($strName) != 35) {
                    throw new Exception("El tamaño del nombre del archivo no corresponde.", 1);  
                }

                $year = substr($strName,10,-21);
                $month= $strName[14].$strName[15];
                $day= $strName[16].$strName[17];
                $consecutive = $strName[33].$strName[34];
                $nit = ltrim(substr($strName,20,-3),'0');
  
                $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                            ->findOneBy(array('nit' => $nit));
                if (!$healthPromotionEntity) {
                    throw new Exception("Nit no encontrado por favor verificar.", 1);  
                }

                if ($user->getRole() != 1) {
                    $access = $this->getUserAccessProfile($user,$healthPromotionEntity); 
                    if (!$access) {
                        throw new Exception("El perfil del usuario no tiene acceso a la EPS.", 1); 
                    } 
                }
                $period= $day.'/'.$month.'/'.$year;
                $objResult = array(
                    'Nombre' => 'SUF'.' '.$period.' - '.$healthPromotionEntity->getCode().' '.$consecutive,
                    'Periodo'=> $period,
                    'Eps' => $healthPromotionEntity->getName(),
                    'Proceso' =>'Estudios de suficiencia',                    
                    'Consecutivo' =>$consecutive,
                    'type' => 14,                
                    'year' => $year,                
                    );
               

            }elseif(strstr($strName,'LIQUIDACION')){
                
                if (strlen($strName) != 25) {
                    throw new Exception("El tamaño del nombre del archivo no corresponde.", 1);  
                }
                           
                $codeEpss = substr($strName,0,-19);

                $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                            ->findOneBy(array('code' => $codeEpss));
                
                if (!$healthPromotionEntity) {
                    throw new Exception("Codigo no encontrado por favor verificar.", 1);  
                }
                if ($user->getRole() != 1) {
                    $access = $this->getUserAccessProfile($user,$healthPromotionEntity); 
                    if (!$access) {
                        throw new Exception("El perfil del usuario no tiene acceso a la EPS.", 1); 
                    } 
                }
                
                $day = substr($strName,17,-6);
                $month = substr($strName,19,-4);
                $year = substr($strName,21,25);
                $period = $day.'/'.$month.'/'.$year; 
                $objResult = array(
                    'Nombre' => 'LMA'.' '.$period.' - '.$healthPromotionEntity->getCode(),
                    'Periodo'=> $period,
                    'Eps' => $healthPromotionEntity->getName(),
                    'Proceso' =>'Liquidacion mensual de afiliados.',
                    'type'=> 13,   
                    'year' => $year,                                
                    );
                
            }elseif(strstr($strName,'QUALITY')){
                if (strlen($strName) != 46) {
                    throw new Exception("El tamaño del nombre del archivo no corresponde.", 1);  
                }

            }elseif(strstr($strName,'PIP')){
                if (strlen($strName) != 35) {
                    throw new Exception("El tamaño del nombre del archivo no corresponde.", 1);  
                }

                $nit = ltrim(substr($strName,20,-3),'0');
                $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                            ->findOneBy(array('nit' => $nit));
                
                if (!$healthPromotionEntity) {
                    throw new Exception("Nit no encontrado por favor verificar.", 1);  
                }
                if ($user->getRole() != 1) {
                    $access = $this->getUserAccessProfile($user,$healthPromotionEntity); 
                    if (!$access) {
                        throw new Exception("El perfil del usuario no tiene acceso a la EPS.", 1); 
                    } 
                }
                
                $day = substr($strName,16,-17);
                $month = substr($strName,14,-19);
                $year = substr($strName,10,-21);
                $consecutive= substr($strName,33,34);
                $period = $day.'/'.$month.'/'.$year; 
                $objResult = array(
                    'Nombre' => 'EPIP'.' '.$period.' - '.$healthPromotionEntity->getCode().' '.$consecutive,
                    'Periodo'=> $period,
                    'Eps' => $healthPromotionEntity->getName(),
                    'Proceso' =>'Pilotos de prima pura.',
                    'type'=> 24,    
                    'year' => $year,                               
                    );
            
            }elseif(strstr($strName,'MPP')){
                if (strlen($strName) != 35) {
                    throw new Exception("El tamaño del nombre del archivo no corresponde.", 1);  
                }

                $nit = ltrim(substr($strName,20,-3),'0');
                $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                            ->findOneBy(array('nit' => $nit));
                
                if (!$healthPromotionEntity) {
                    throw new Exception("Nit no encontrado por favor verificar.", 1);  
                }
                if ($user->getRole() != 1) {
                    $access = $this->getUserAccessProfile($user,$healthPromotionEntity); 
                    if (!$access) {
                        throw new Exception("El perfil del usuario no tiene acceso a la EPS.", 1); 
                    } 
                }
                
                $day = substr($strName,16,-17);
                $month = substr($strName,14,-19);
                $year = substr($strName,10,-21);
                $consecutive= substr($strName,33,34);
                $period = $day.'/'.$month.'/'.$year; 
                $objResult = array(
                    'Nombre' => 'MPP'.' '.$period.' - '.$healthPromotionEntity->getCode().' '.$consecutive,
                    'Periodo'=> $period,
                    'Eps' => $healthPromotionEntity->getName(),
                    'Proceso' =>'Caprecom.',
                    'type'=> 25, 
                    'year' => $year,                                  
                    );
            }else{
                throw new Exception("Archivo no valido.", 1);  
            }

            $output = array('success'=>true,'description' => $objResult);
        
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

    private function getUserAccessProfile($user,$healthPromotionEntity){
       
        if ($user->getProfile()) {
           foreach ($healthPromotionEntity->getProfilesHealthPromotion() as $healthProfile) {
                if($healthProfile->getProfile() == $user->getProfile()){
                    return true;
                }
            } 

            return false;
        }else{

            return false;
        }
    } 

    /**
    * @Route("/health/validate/quality/process",name="health_validate_quality_process")
    */
    public function validateQualityProcessAction(Request $request)
    {   
        try {

            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();
            $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')->findOneBy(array('code' => $params['eps']));

            if (!$healthPromotionEntity) {
                throw new Exception("Codigo de eps no encontrado por favor verificar.", 1);  
            }

            if ($user->getRole() != 1) {
                $access = $this->getUserAccessProfile($user,$healthPromotionEntity); 
                if (!$access) {
                    throw new Exception("El perfil del usuario no tiene acceso a la EPS.", 1); 
                } 
            }

            //dump($params);die();
            $name = "Calidad {$params['year']} - {$params['eps']} - {$params['_qsource']}";
            
            $processOrders = $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                          ->findBy(array('name'=>$name,'status'=>2));

            foreach ($processOrders as $processOrder) {
                if ($processOrder) {

                    $url = $this->get('router')->generate('health_process_home');

                    throw new Exception("El proceso de calidad ya existe para reiniciarlo por favor de clic en el siguiente enlace: <a href='".$url."/".$processOrder->getId()."'>".$url.'</a>', 1);
                }
            }

            $objResult = array(
                    'Nombre' => $name,
                    'Año'=> $params['year'],
                    'Codigo EPS' => $params['eps'],
                    'Proceso' =>'Calidad',
                    'type'=> 31, 
                    'year' => $params['year'],                                  
                    );

            $output = array('success'=>true,'description' => $objResult);
        
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

    private function getYearAndEps($processOrderTypeId){
        
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('AppBundle:ProcessOrder')
                            ->createQueryBuilder('p') 
                            ->innerJoin('p.processOrderType','pot','pot.id = p.process_order_type_id')              
                            ->where("pot.id = :type and p.status = :status")
                            ->setParameter('type',$processOrderTypeId)
                            ->setParameter('status',ProcessOrder::PROCESS_ORDER_STATUS_ENDED)
                            ->getQuery();

        $processOrders = $query->getResult();
        $arrEpss = array();

        foreach ($processOrders as $processOrder) {
            $strName = explode(' ', $processOrder->getName());
            $date = explode('/',$strName[1]);
            $year = $date[2];
            $eps = $strName[3];
            
            if (!array_key_exists($eps, $arrEpss)) {
                $arrEpss[$eps] = array();

                if (!in_array($year, $arrEpss[$eps])) { 
                    array_push($arrEpss[$eps], $year);
                } 
            }else{
                if (!in_array($year, $arrEpss[$eps])) { 
                    array_push($arrEpss[$eps], $year);
                } 
            }
        }
        
        return $arrEpss;
    } 

}

