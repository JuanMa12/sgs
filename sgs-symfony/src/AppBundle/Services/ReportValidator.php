<?php 

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\ProcessOrderType;
use AppBundle\Entity\ProcessOrder;

use Exception;

/**
* Servicio para registro de enventos del log
*/
class ReportValidator
{  
	protected $em;

	public function __construct($entityManager,ContainerInterface $container)
	{
		$this->em = $entityManager;
		$this->container = $container;
	}
	
	public function getValidateReport($processOrderType,$params)
	{
		$em = $this->em;
		
        $location = '';
        $status = '';
        $regimes = '';
        $epss = '';
        $genre = '';
        $guilds = '';
        $periods = '';
        $source = '';
        $payers = '';
        $group = '';
        $diagnostics = '';
        $typeReport = '';
        $procedures = '';
        $typeGroup = '';

        $name = '';
        $qGroup = '';

        //validacion del parametro periodos
        if(array_search($processOrderType->getId(),[2,3,4,8,9,10,12,18,20,21,22,26,23,27,28,29,30,32,33,34,36,37,38,39,41,42,44,45,46,48,50]) > -1){
           	if (!isset($params['periods'])) {
	            throw new Exception("Debe seleccionar un rango de fechas", 1);
	        }

            asort($params['periods']);
            $periods = implode(",",$params['periods']);      
              
	    }

        //validacion del parametro nombre del reporte
        if(array_search($processOrderType->getId(),[2,3,4,8,12,18,20,21,22,23,26,27,28,29,30,32,33,34,36,37,38,39,41,42,44,45,46,48,50]) > -1){
            if ($params['_name'] == '') {
                throw new Exception("Debe escribir el nombre del reporte.", 1);
            }
            $name = $params['_name'];   
        }
        
        //validacion del parametro localizacion
        if(array_search($processOrderType->getId(),[2,3,4,10,20,23,37,38,45,46]) > -1){
            
            if($params['type_select_location'] == 'specific' && !isset($params['location'])){
                
                throw new Exception("Debe elegir al menos un departamento o municipio valido", 1);
            }

            $location = $params['type_select_location'];
            if ($params['type_select_location'] == 'specific') {
                asort($params['location']);
                $location = implode(",",$params['location']);                    
            }
        }

        //validacion del parametro fuente
        if(array_search($processOrderType->getId(),[10,18,20,21,22,23,26,28,29,30,38,45,46]) > -1){
			
			if(!isset($params['type_source'])){
                throw new Exception("Debe elegir al menos un tipo de fuenta valida", 1);
            }            

            $source = $params['type_source'];
        }
        
        //validacion del parametro eps por ID  de la EPS
        if(array_search($processOrderType->getId(),[2,8,9,10,12,34,36,37]) > -1){
            
            if($params['type_select']=='guild' && !isset($params['guildIds'])){
                throw new Exception("Debe elegir al menos un gremio", 1);
            }

            if($params['type_select']=='epss' && !isset($params['_in'])){
                throw new Exception("Debe elegir al menos una EPSs", 1);
            }        	

        	switch($params['type_select']){
                case 'guild':
                    
                    $qb = $em->createQueryBuilder();
                    $queryBuilder = $em->getRepository('AppBundle:HealthPromotionEntity')
                        ->createQueryBuilder('h')
                        ->where($qb->expr()->in('h.guild',$params['guildIds']));
                    
                    $arrObjEpss = $queryBuilder->getQuery()->getResult();

                    foreach ($arrObjEpss as $objEps) {
                        $arrEps[] = $objEps->getId();
                    }
                    asort($arrEps);
                    $epss = implode(",",$arrEps);

                    break;
                case 'epss':
					asort($params['_in']);
                    $epss = implode(",",$params['_in']);                    
                    break;
     		}

     	}
            
        //validacion del parametro eps por codigo de la eps
        if(array_search($processOrderType->getId(),[18,20,21,22,23,26,28,29,30,32,33,38,39,41,42,44,45,46,48,50]) > -1){
            if($params['type_select']=='guild' && !isset($params['guildIds'])){
                throw new Exception("Debe elegir al menos un gremio", 1);
            }

            if($params['type_select']=='epss' && !isset($params['_in'])){
                throw new Exception("Debe elegir al menos una EPSs", 1);
            }           

            switch($params['type_select']){
                case 'guild':                    
                    $qb = $em->createQueryBuilder();
                    $queryBuilder = $em->getRepository('AppBundle:HealthPromotionEntity')
                        ->createQueryBuilder('h')
                        ->where($qb->expr()->in('h.guild',$params['guildIds']));
                    
                    $arrObjEpss = $queryBuilder->getQuery()->getResult();

                    foreach ($arrObjEpss as $objEps) {
                        $arrEps[] = $objEps->getCode();
                    }
                    asort($arrEps);
                    $epss = implode(",",$arrEps);

                    break;
                case 'epss':
                    foreach ($params['_in'] as $id) {
                        $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')->find($id);  
        
                        $arrEps[] = $healthPromotionEntity->getCode(); 
                    }
                    asort($arrEps);
                    $epss = implode(",",$arrEps);                    
                    break;
            }

        }

     	//Validacion por el parametro estado estado y regimen
        if(array_search($processOrderType->getId(),[2,3,4]) > -1){

            if(!isset($params['status'])){
                throw new Exception("Debe elegir al menos un estado valido", 1);
            }
            
            if(!isset($params['regimes'])){
                throw new Exception("Debe elegir al menos un regimen", 1);
            }

            asort($params['status']);
            $status = implode(",",$params['status']);                    

            asort($params['regimes']);
            $regimes = implode(",",$params['regimes']);                    
        }
		
		//Validacion por el parametro genero
        if(array_search($processOrderType->getId(),[2]) > -1){
        	if(!isset($params['genre'])){
                throw new Exception("Debe elegir al menos un genero", 1);
            }

            asort($params['genre']);
            $status = implode(",",$params['genre']);                    
        }		
        
        //Validacion por el parametro gremio
        if(array_search($processOrderType->getId(),[4]) > -1){
        	if(!isset($params['guildIds'])){
                throw new Exception("Debe elegir al menos un gremio", 1);
            }

            asort($params['guildIds']);
            $guilds = implode(",",$params['guildIds']);

        }
        
        //Validacion por el parametro Pagador 
        if(array_search($processOrderType->getId(),[8,9]) > -1){        	
            if($params['type_select_payer'] =='payers' && !isset($params['_pa'])){
                throw new Exception("Debe elegir al menos un pagador", 1);
            }

            $payers = $params['type_select_payer'];
           	if($params['type_select_payer'] =='payers'){
                asort($params['_pa']);
                $payers = implode(",",$params['_pa']);
            }                            
        }

        //Validacion por el parametro de agrupamiento
        if(array_search($processOrderType->getId(),[8,9,12]) > -1){
            if (isset($params['groupBy'])) {
           		asort($params['groupBy']);
        		$group = implode(",",$params['groupBy']);
        	}
        }

        //Validacion de parametro diganosticos
        if(array_search($processOrderType->getId(),[28]) > -1){
            if($params['type_diagnostic'] =='diagnostics' && !isset($params['diagnostics'])){
                throw new Exception("Debe elegir al menos un diagnostico", 1);
            }

            $diagnostics = $params['type_diagnostic'];
            if($params['type_diagnostic'] =='diagnostics'){
                asort($params['diagnostics']);
                $diagnostics = implode(',', $params['diagnostics']);
            }
        }   

        //Validacion por el parametro tipo de reporte para reporte upc no pos
        if(array_search($processOrderType->getId(),[8]) > -1){
            if(!isset($params['type_report'])){
                throw new Exception("Debe elegir al menos un tipo de cartera NO POS.", 1);
            }

            $typeReport = $params['type_report'];
        } 

        //Validacion por el parametro tipo de agrupamiento para reporte 1080 / 058 
        if(array_search($processOrderType->getId(),[34]) > -1){
            if(!isset($params['type_group'])){
                throw new Exception("Debe elegir al menos un tipo de agrupamiento.", 1);
            }

            $typeGroup = $params['type_group'];
        } 

        //Validacion de parametro diganosticos
        if(array_search($processOrderType->getId(),[29]) > -1){
            if($params['type_procedure'] =='procedures' && !isset($params['code_procedure'])){
                throw new Exception("Debe elegir al menos un procedimiento", 1);
            }

            $procedures = $params['type_procedure'];
            if($params['type_procedure'] =='procedures'){
                asort($params['code_procedure']);
                $procedures = implode(',', $params['code_procedure']);
            }
        }   

        //Validacion de parametro de grupos quinquenales
        if(array_search($processOrderType->getId(),[38]) > -1){
            
            if($params['type_select_qg'] =='q_group' && !isset($params['quinquennial_groups'])){
                throw new Exception("Debe elegir al un grupo quinquenal.", 1);
            }
            if($params['type_select_qg'] =='q_group'){
                asort($params['quinquennial_groups']);                           
                $qGroup = implode(",",$params['quinquennial_groups']);   
            }
        }

        $token = hash_hmac("md5",$processOrderType->getId().$location.$status.$regimes.$epss.$genre.$guilds.$periods.$source.$payers.$group.$diagnostics.$typeReport.$procedures.$qGroup.$typeGroup, $this->container->getParameter('secret'));

        $qb = $em->getRepository('AppBundle:ProcessOrder')
                ->createQueryBuilder('po')
                ->where("po.createToken = :token")
                ->andWhere("po.status != :status")
                ->setParameter('status',ProcessOrder::PROCESS_ORDER_STATUS_DELETED)
                ->setParameter('token',$token);
        
        $processOrder = $qb->getQuery()->getResult();

        $url = '';
        $homologation = '';
        if ($processOrder) {
            $homologation = $processOrder[0]->getId();
        }
        
        return array(
            'name' => $name,
            'hash' => $token,
            'homologation' => $homologation,
            );
    }
}