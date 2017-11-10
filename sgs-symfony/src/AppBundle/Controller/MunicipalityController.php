<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Municipality;
use Exception;


class MunicipalityController extends Controller
{
    /**
     * @Route("/admin/municipality/list", name="municipality_list")
     */
    public function formAction(Request $request)
    {
        return $this->render('systemParameters/municipalityList.html.twig');
    }

    /**
     * @Route("/admin/municipality/grid", name="municipality_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'code',
            'zone',
            'name',
            'deparment',
            'id'
            );
        
        $subquery = 'SELECT m.id,m.code,m.zone,m.name,d.name AS deparment FROM municipalities AS m INNER JOIN departments AS d ON d.id= m.department_id';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                $data['code'],
                $data['zone'],  
                $data['name'],
                $data['deparment'],
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/municipality/save/form", name="municipality_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $municipality = $em->getRepository('AppBundle:Municipality')
                             ->findOneBy(array('code' => $params['_code']));

            $department = $em->getRepository('AppBundle:Department')
                                ->find($params['_department']);

            if (!$department) {
                throw new Exception("Debe seleccionar un departamento", 1);
            }

            if (strlen($params['_code']) != 5) {
                throw new Exception("el codigo debe ser de cinco numeros", 1);
            }

            if($params['_id'] != ''){
                
                if ($municipality and $params['_id'] != $municipality->getId()) {
                    throw new Exception("el codigo del municipio ya existe", 1);
                }
                
                $municipality = $em->getRepository('AppBundle:Municipality')
                                ->find($params['_id']);

                $logMessage = 'Se edita el municipio con codigo: '.$municipality->getCode();

            }else{

                if ($municipality) {
                    throw new Exception("el codigo del municipio ya existe", 1);
                }

                $municipality = new Municipality();
                $em->persist($municipality);

                $logMessage = 'Se crea el municipio con codigo: '.$params['_code'];
            }

            $municipality->setName($params['_name']);            
            $municipality->setDepartmentId($department);
            $municipality->setCode($params['_code']);
            $municipality->setZone($params['_zone']);
            $municipality->setCategory($params['_category']);

            $em->flush();
            
            $this->get('log_activity_manager')->registerActivity($logMessage);

            $output = array('success'=>true,'description' => $municipality->getId());

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
     * @Route("/admin/municipality/info", name="municipality_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $municipality = $em->getRepository('AppBundle:Municipality')
                                ->find($params['_id']);

            if(!$municipality){
                throw new Exception("Municipio no encontrada", 1);
            }
            
            $arrResult = array(
                'name' => $municipality->getName(),
                'deoartmentId' => $municipality->getDepartmentId()->getId(),
                'departmentName' => $municipality->getDepartmentId()->getName(),
                'code' => $municipality->getCode(),
                'zone' => $municipality->getZone(),
                'category' => $municipality->getCategory()
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
     * @Route("/municipality/tree", name="municipality_get_tree")
     */
    public function getTreeAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            if($params['type']=="Departamentos"){
                $department = $em->getRepository("AppBundle:Department")
                    ->find($params['id']);

                $arrResult = array(
                    "id" => $department->getId(),
                    "name" => $department->getName(),
                    "childs" => array()
                    );

                $municipalities = $department->getMunicipalities();

                foreach ($municipalities as $key => $municipality) {
                    $arrResult["childs"][] = array(
                        'id' => $municipality->getId(), 
                        'name' => $municipality->getName()
                        );
                }
                
            }else{
                $municipality = $em->getRepository('AppBundle:Municipality')
                                    ->find($params['id']);

                $arrResult = array(
                    "id" => $municipality->getId(),
                    "name" => $municipality->getName(),
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
   
}

