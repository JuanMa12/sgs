<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\ProcessOrder;
use AppBundle\Entity\ProcessOrderType;
use AppBundle\Entity\Process;
use Exception;
use StdClass;


class ProcessOrderController extends Controller
{
    /**
    * @Route("/admin/process/list", name="process_list")
    */
    public function formAction(Request $request)
    {
        return $this->render('process/list.html.twig');
    }

    /**
    * @Route("/admin/process/grid", name="process_grid")
    */
    public function gridAction(Request $request)
    {
        $colums = array(
            'dateOrder',
            'name',
            'type',
            'total',
            'idOrder',
            );
        
        $subquery = 'SELECT po.id AS idOrder,po.date AS dateOrder,po.name,pot.name AS type,round(avg(p.progress),0) AS total 
                    FROM process_orders AS po 
                        LEFT JOIN processes AS p ON po.id= p.process_order_id 
                        LEFT JOIN process_order_types  AS pot ON po.process_order_type_id = pot.id
                            WHERE p.status <> 4 and p.status <> 3
                        GROUP BY po.id';



        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('process/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                date('Y-m-d h:i',$data['dateOrder']),
                $data['name'],
                $data['type'],
                ($data['total'] != null)?$data['total']:0,
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/process/error/detail/{id}", name="process_error_detail")
     */
    public function getProcessErrorInfoAction(ProcessOrder $processOrder)
    {
        try{

            $html = $this->renderView('process/error_details.html.twig',array('processOrder' => $processOrder));

            $pathDowland = $this->get('router')->generate('process_dowland_report', array('id' =>$processOrder->getId()));

            $output = array("success"=>true,"description"=> array('html' => $html,'pathDowland'=>$pathDowland ));

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
    * @Route("/process/order/info/{module}", name="process_order_info")
    */
    public function processOrderValueAction(Request $request,$module)
    {   
        try {
            
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $params = $request->request->all();
            
            if (isset($params['filters'])) {
                $whereTypeId = "pot.id IN (".implode(',',$params['filters']).")";
            }else{
               throw new Exception("No se encontraron datos relacionados.", 1);
            }
            
            $qb = $em->getRepository('AppBundle:ProcessOrder')
                ->createQueryBuilder('po')
                ->innerJoin('po.processOrderType','pot','pot.id = po.process_order_type_id')
                ->where($whereTypeId)
                ->andWhere("po.status != :status")
                ->andWhere("pot.type = 0")
                ->andWhere("pot.module = :module")
                ->setParameter('status',ProcessOrder::PROCESS_ORDER_STATUS_DELETED)
                ->setParameter('module',$module);

            //Busqueda por palabra
            if(isset($params['word']) && $params['word'] != ''){
                $qb->andWhere('po.name like :lname')
                    ->setParameter('lname',"%".$params['word']."%");
            }

            if ($user->getRole() != 1) {
                $qb ->andWhere("po.user =:user")
                    ->setParameter('user',$user);
            }
            
            $limit = 10;
            $offset = ($params['page'] - 1) * $limit;
            $qb->setMaxResults($limit)
               ->setFirstResult($offset)
               ->orderBy('po.id', 'DESC');

            $processeOrders = $qb->getQuery()->getResult();

            $arrValueProcess=array();
            $arrProcessInfo=array();
            $arrProcessError=array();
            $user= $this->getUser();

            foreach ($processeOrders as  $processOrder) {
                
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

                $actions = false;
                if ($user->getRole() == 1 or $processOrder->getUser() == $user) {
                    $actions=true;
                }

                $path = $this->get('router')->generate('process_error_detail', array('id' =>$processOrder->getId()));
                
                $objProcessInfo = new StdClass;
                $objProcessInfo->id = $processOrder->getId();
                $objProcessInfo->name = $processOrder->getName();
                $objProcessInfo->date = date('Y-m-d h:i',$processOrder->getDate());
                $objProcessInfo->status = $processOrder->getStatus();
                $objProcessInfo->path = $path;
                $objProcessInfo->params = $processOrder->getParams();
                $objProcessInfo->progress = $valueProgress;
                $objProcessInfo->errors = $errorProgress;
                $objProcessInfo->actions = $actions;
                $objProcessInfo->type = $processOrder->getProcessOrderType()->getId();
                $objProcessInfo->autor = $processOrder->getUser()->getName();
                $objProcessInfo->pathDelete = $this->get('router')->generate('process_status_save');


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
     * @Route("/process/error/dowland/report/{id}",defaults={"id" = 0}, name="process_dowland_report")
     */
    public function reportErrorAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $processOrder= $this->getDoctrine()
                    ->getRepository('AppBundle:ProcessOrder')
                    ->find($id);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=errores.xls');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        $response->setContent($this->renderView('demography/processError.html.twig',array('item'=>$processOrder)));

        return $response;

    }

    /**
     * Eliminacion o reiniciacion de procesos
     * @Route("/process/status/save", name="process_status_save")
     */
    public function changeStatusAction(Request $request) 
    {
        try {

            $params = $request->request->all();

            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();

            $processOrder = $this->getDoctrine()->getRepository('AppBundle:ProcessOrder')
                            ->find($params['id']);

            if(!$processOrder){
                throw new Exception("La orden de proceso no existe", 1);
            }
            
            if ($processOrder->getUser() != $user and $user->getRole() != 1) {
                throw new Exception("El usuario no esta habilitado para realizar el cambio de estado de este proceso.", 1);
            }
            
            $processType = $processOrder->getProcessOrderType()->getType();

            if($params['status'] == 4 and $processType == ProcessOrderType::PROCESS_ORDER_TYPE_REPORT){
                foreach ($processOrder->getProcesses() as $process) {
                    $process->setStatus(4);
                    $em->flush();
                }

                // se marca como eliminado
                $processOrder->setStatus(ProcessOrder::PROCESS_ORDER_STATUS_DELETED);
                $em->flush();

            }

            $logMessage = 'Se elimina la orden de proceso con nombre: '.$processOrder->getName().' y ID: '.$processOrder->getId();
            
            try{
                $restClient = $this->get('circle.restclient');

                if ($params['status'] == 0) {//reinicio de proceso
                    $restResult = $restClient->post($this->container->getParameter('engine_path').'/porder/'.$processOrder->getId(),"");
                    
                    $logMessage = 'Se reinicia la orden de proceso con el nombre: '.$processOrder->getName().' y el ID: '.$processOrder->getId();

                }else{//eliminacion de proceso
                    if($processType == ProcessOrderType::PROCESS_ORDER_TYPE_PROCESS){
                        $restResult = $restClient->delete($this->container->getParameter('engine_path').'/porder/'.$processOrder->getId());
                    }
                }
                
            }catch(Exception $ex){
                $message = 'La conexión con el motor de procesamiento no fue efectiva:'. $ex->getMessage();
                
            }  
    
            $this->get('log_activity_manager')->registerActivity($logMessage);            

            switch($processOrder->getProcessOrderType()->getModule()){
                case 'health':
                    $path = $this->get('router')->generate('health_home_report');
                    break;
                case 'demography':
                    $path = $this->get('router')->generate('demography_report_home');
                    break;
                case 'operative':
                    $path = $this->get('router')->generate('operative_home_report');
                    break; 
                case 'social':
                    $path = $this->get('router')->generate('social_home_report');
                    break;
                default:
                    $path = $this->get('router')->generate('finantial_home_report');
            }
    
            $output = array('success'=>true,'description' => $path);
        
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


