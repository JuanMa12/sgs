<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Medicine;
use Exception;


class MedicineController extends Controller
{
    /**
     * @Route("/admin/medicine/list", name="medicine_list")
     */
    public function formAction(Request $request)
    {
        return $this->render('systemParameters/medicineList.html.twig');
    }

    /**
     * @Route("/admin/medicine/grid", name="medicine_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'id',
            'product',
            'cum_code',
            'atc_code',
            'comercial_descripcion',
            );
        
        $subquery = 'SELECT m.* FROM medicines AS m';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);


        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                $data['id'],
                $data['product'],
                $data['cum_code'],
                $data['atc_code'],
                $data['comercial_descripcion'],
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/medicine/save/form", name="medicine_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 


            if($params['_id'] != ''){    
              
                $medicine = $em->getRepository('AppBundle:Medicine')
                                ->find($params['_id']);

                $logMessage= 'Se edita el medicamento con el codigo CUM: '.$medicine->getCumCode();
            
            }else{

                $medicine = new medicine();
                $em->persist($medicine);
                
                $logMessage= 'Se crea el medicamento con el codigo CUM: '.$params['_cumCode'];
            }
  
            $medicine->setCumCode($params["_cumCode"]);
            $medicine->setProduct($params["_product"]);
            $medicine->setComercialDescripcion($params["_commercialDescription"]);
            $medicine->setAtcCode($params["_atcCode"]);
            $medicine->setAtcDescription($params["_atcDescription"]);
            $medicine->setLaboratory($params["_laboratory"]);
            $medicine->setStatusCum($params["status_cum"]);
            $medicine->setUnity($params["unity"]);
            $medicine->setConcentration($params["_concentration"]);
            $medicine->setRouteAdministration($params["_route_administration"]);
            $medicine->setActivePrinciple($params["_activePrinciple"]);
            $medicine->setUnityMeasure($params["_unity_measure"]);
            $medicine->setReferenceUnit($params["_reference_unit"]);
            $medicine->setPharmaceuticalForm($params["_pharmaceutical_form"]);


            $this->get('log_activity_manager')->registerActivity($logMessage);

            $em->flush();

            $output = array('success'=>true,'description' => $medicine->getId());

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
     * @Route("/admin/medicine/get/info", name="medicine_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $medicine = $em->getRepository('AppBundle:Medicine')
                                ->find($params['_id']);

            if(!$medicine){
                throw new Exception("Medicamento no encontrada", 1);
            }
            
            $arrResult = array(
                "cumCode"               => $medicine->getCumCode(),
                "product"               => $medicine->getProduct(),
                "laboratory"          => $medicine->getLaboratory(),
                "commercialDescription" => $medicine->getComercialDescripcion(),
                "unity" => $medicine->getUnity(),
                "concentration"         => $medicine->getConcentration(),
                "statusCum"         => $medicine->getStatusCum(),
                "atcCode"               => $medicine->getAtcCode(),
                "atcDescription"        => $medicine->getAtcDescription(),
                "routeAdministration"       => $medicine->getRouteAdministration(),
                "activePrinciple"       => $medicine->getActivePrinciple(),
                "unityMeasure"          => $medicine->getUnityMeasure(),
                "referenceUnit"              => $medicine->getReferenceUnit(),
                "pharmaceuticalForm"         => $medicine->getPharmaceuticalForm(),
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
}

