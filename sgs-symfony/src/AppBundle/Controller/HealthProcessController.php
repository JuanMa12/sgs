<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\HealthProcess;
use Exception;


class HealthProcessController extends Controller
{
    /**
     * @Route("/admin/health-process/list", name="health_procedures_list")
     */
    public function listAction(Request $request)
    {
        return $this->render('systemParameters/healthProceduresList.html.twig');
    }

    /**
     * @Route("/admin/health-process/grid", name="health_procedures_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'id',
            'code',
            'description',
            'coverage',
            'type'
            );
        
        $subquery = "SELECT 
            id,
            code,
            description,
            coverage,
            type
            FROM health_procesedures";

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                $data['code'],
                $data['description'],
                $data['type'],
                $data['coverage'],
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/health-process/save/form", name="health_procedures_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 

            if($params['_type'] == 'SUBCATEGORIA' and strlen($params['_code']) != 6){
                throw new Exception("El codigo CUPS debe ser de 6 caracteres", 1);
            }
            if($params['_type'] == 'CATEGORIA' and strlen($params['_code']) != 4){
                throw new Exception("El codigo CUPS debe ser de 4 caracteres", 1);
            }
            if($params['_type'] == 'SUBGRUPO' and strlen($params['_code']) != 3){
                throw new Exception("El codigo CUPS debe ser de 3 caracteres", 1);
            }
            if($params['_type'] == 'GRUPO' and strlen($params['_code']) != 2){
                throw new Exception("El codigo CUPS debe ser de 2 caracteres", 1);
            }
            
            $parentHealthProcess = null;
    
            if ($params['_type'] != 'GRUPO') {
                $parentHealthProcess = $em->getRepository('AppBundle:HealthProcess')
                                ->find($params['_parent']);
                
                if (!$parentHealthProcess) {
                    throw new Exception("Padre no valido.", 1);
                } 
            }

            if($params['_id'] != ''){    
              
                $healthProcess = $em->getRepository('AppBundle:HealthProcess')
                                ->find($params['_id']);

                $logMessage= 'Se edita el procedimeinto medico con el codigo: '.$healthProcess->getCode();
            
            }else{

                $healthProcess = $em->getRepository('AppBundle:HealthProcess')
                                ->findByCode($params['_code']);

                if ($healthProcess) {
                    throw new Exception("Codigo existente, por favor asignar un nuevo codigo.", 1);
                }

                $healthProcess = new HealthProcess();
                $em->persist($healthProcess);
                
                $logMessage= 'Se crea el procedimiento con el codigo: '.$params['_code'];
            }

            $healthProcess->setCode($params['_code']); 
            $healthProcess->setDescription($params['_description']);
            $healthProcess->setCoverage($params['_coverage']);
            $healthProcess->setType($params['_type']);
            $healthProcess->setGenre($params['genre']);
            $healthProcess->setAmbit($params['_ambit']);
            $healthProcess->setStay($params['_stay']);
            $healthProcess->setSection($params['_section']);
            $healthProcess->setChapter($params['_chapter']);

            $this->get('log_activity_manager')->registerActivity($logMessage);

            $em->flush();

            $output = array('success'=>true,'description' => $healthProcess->getId());

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
     * @Route("/admin/health-process/get/info", name="health_procedures_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $healthProcess = $em->getRepository('AppBundle:HealthProcess')
                                ->find($params['_id']);

            if(!$healthProcess){
                throw new Exception("Procedimiento no encontrada", 1);
            }
            
            $arrResult = array(
                'type' => $healthProcess->getType(),
                'code' => $healthProcess->getCode(),
                'description' => $healthProcess->getDescription(),
                'coverage' => $healthProcess->getCoverage(),
                'genre' => $healthProcess->getGenre(),
                'ambit' => $healthProcess->getAmbit(),
                'stay' => $healthProcess->getStay(),
                'chapter' => $healthProcess->getChapter(),
                'section' => $healthProcess->getSection(),
                'parentId' => ($healthProcess->getParent())?$healthProcess->getParent()->getId():'',
                'parentName' => ($healthProcess->getParent())?$healthProcess->getParent()->getCode().' - '.$healthProcess->getParent()->getDescription():'',
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
     * @Route("/health/procedures/tree", name="health_procedures_tree")
     */
    public function getTreeAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            if($params['type'] != 'SUBCATEGORIA'){

                $healthProcess = $em->getRepository('AppBundle:HealthProcess')
                                    ->find($params['id']);
                
                $arrResult = $this->getChildsProcedure($healthProcess);
                    
            }else{//4 digitos
                $healthProcess = $em->getRepository('AppBundle:HealthProcess')
                                    ->find($params['id']);
                
                $arrResult = array(
                    "id" => $healthProcess->getId(),
                    "name" => $healthProcess->getCode()." - ".$healthProcess->getDescription()
                    );
            }

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

    private function getChildsProcedure($procedure)
    {
        
        $arrChilds = array(
            "id" => $procedure->getId(),
            "name" => $procedure->getCode()." - ".$procedure->getDescription()
            ); 

        foreach ($procedure->getChilds() as $child) {
            $arrChilds['childs'][] = $this->getChildsProcedure($child);
        }

        return $arrChilds;
    }
  
}

