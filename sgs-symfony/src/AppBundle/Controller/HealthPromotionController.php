<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\HealthPromotionEntity;
use AppBundle\Entity\Guild;

use Exception;


class HealthPromotionController extends Controller
{
    /**
     * @Route("/admin/health/promotion/entity/list", name="health_promotion_list")
     */
    public function formAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); 

        $guilds = $em->getRepository('AppBundle:Guild')
                    ->findAll();

        return $this->render('systemParameters/healthPromotionEntityList.html.twig',array('guilds' => $guilds));
    }

    /**
     * @Route("/admin/health/promotion/entity/grid", name="health_promotion_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'code',
            'nit',
            'name',
            'status',
            'id',
            );
        
        $subquery = 'SELECT h.* FROM health_promotion_entities AS h';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActionsEPSs.html.twig',array('item' => $data));

            switch ($data['status']) {
                case 0:
                    $status = 'Inactiva';
                    break;
                case 1:
                    $status = 'Activa';
                    break;
                
            }

            $result['output']['aaData'][] = array(
                $data['code'],
                $data['nit'],
                $data['name'],
                $status,
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/health/promotion/entity/save/form", name="health_promotion_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 

        
            if($params['_id'] != ''){    
                

                $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                ->find($params['_id']);

                $logMessage = 'Se Edita la EPS: '.$healthPromotionEntity->getName();
            
            }else{
                
                $healthPromotionEntity = new HealthPromotionEntity();
                $em->persist($healthPromotionEntity);

                $logMessage = 'Se crea la EPS: '.$params['_name'];
            }

            $guild = $em->getRepository('AppBundle:Guild')
                        ->find($params['_giel']);
            
            if ($guild) {    
                $healthPromotionEntity->setGuild($guild);
            }

            $healthPromotionEntity->setName($params['_name']);     
            $healthPromotionEntity->setNit($params['_nit']);     
            $healthPromotionEntity->setCode($params['_code']);
            $healthPromotionEntity->setCodeMobility($params['_code_new']);
            $healthPromotionEntity->setLegal($params['_legal']);
            $healthPromotionEntity->setAlias($params['_alias']);

            $em->flush();

            $this->get('log_activity_manager')->registerActivity($logMessage);

            $output = array('success'=>true,'description' => $healthPromotionEntity->getId());

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
     * @Route("/admin/health/promotion/entity/get/info", name="health_promotion_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                ->find($params['_id']);

            if(!$healthPromotionEntity){
                throw new Exception("EPS no encontrada", 1);
            }
            
            $arrResult = array(
                'name' => $healthPromotionEntity->getName(),
                'alias' => $healthPromotionEntity->getAlias(),
                'legal' => $healthPromotionEntity->getLegal(),
                'nit' => $healthPromotionEntity->getNit(),
                'gielId' => ($healthPromotionEntity->getGuild())?$healthPromotionEntity->getGuild()->getId():'',
                'code' => $healthPromotionEntity->getCode(),
                'codeNew' => $healthPromotionEntity->getCodeMobility()
                );

            $output = array('success'=>true,'description' => $arrResult);

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
    * @Route("/admin/health/promotion/changeStatus",name="health_promotion_changeStatus")
    */
    public function changeStatusAction(Request $request)
    {   
        try {

            $params = $request->request->all();

            $em = $this->getDoctrine()->getManager();
            
            $healthPromotionEntity = $this->getDoctrine()
                    ->getRepository('AppBundle:HealthPromotionEntity')
                    ->find($params['id']);

            if(!$healthPromotionEntity){
                throw new Exception("La eps no existe", 1);
            }
            
            $healthPromotionEntity->setStatus($params['status']);
            $em->flush();
        
            $this->get('log_activity_manager')->registerActivity('Se cambia estado de la EPS '.$healthPromotionEntity->getName().' a estado '.$params['status']);
            
            $output = array('success'=>true,'description' => $healthPromotionEntity->getId());
        
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

