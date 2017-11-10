<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Diagnostic;
use Exception;


class DiagnosticController extends Controller
{
    /**
     * @Route("/admin/diagnostic/list", name="diagnostic_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('systemParameters/diagnosticList.html.twig');
    }

    /**
     * @Route("/admin/diagnostic/grid", name="diagnostic_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'id',
            'dCode',
            'capCode',
            'codeGroup',
            'description',
            'name'
            );
        
        $subquery = "SELECT 
                            gr.code as capCode,
                            d.id, d.code AS dCode, dg.code AS codeGroup,
                            d.description, dg.name FROM diagnostics AS d
                        INNER JOIN diagnostic_groups AS dg
                            ON d.diagnostic_group_id = dg.id
                        INNER JOIN diagnostic_groups AS gr on gr.id= dg.parent_id";

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                $data['capCode'],
                $data['dCode'],
                $data['codeGroup'].' - '.$data['name'],
                $data['description'],
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/diagnostic/save/form", name="diagnostic_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 

            $diagnosticGroup = $em->getRepository('AppBundle:DiagnosticGroup')
                                ->find($params['_group']);

            if(!$diagnosticGroup){
                throw new Exception("El grupo no es valido.", 1);
            }

            if($params['_id'] != ''){    
              
                $diagnostic = $em->getRepository('AppBundle:Diagnostic')
                                ->find($params['_id']);

                $logMessage= 'Se edita el diagnostico con el codigo: '.$diagnostic->getCode();
            
            }else{

                $diagnostic = new Diagnostic();
                $em->persist($diagnostic);
                
                $logMessage= 'Se crea el diagnostico con el codigo: '.$params['_code'];
            }

            if(strlen($params['_code']) != 4){
                throw new Exception("El codigo CIE-10 debe ser de 4 caracteres", 1);
            }

            $diagnostic->setCode($params['_code']); 
            $diagnostic->setDescription($params['_description']); 
            $diagnostic->setGenre($params['genre']); 
            $diagnostic->setMinAge($params['min_age']); 
            $diagnostic->setMaxAge($params['max_age']); 
            $diagnostic->setDiagnosticGroup($diagnosticGroup); 

            $this->get('log_activity_manager')->registerActivity($logMessage);

            $em->flush();

            $output = array('success'=>true,'description' => $diagnostic->getId());

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
     * @Route("/admin/diagnostic/get/info", name="diagnostic_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $diagnostic = $em->getRepository('AppBundle:Diagnostic')
                                ->find($params['_id']);

            if(!$diagnostic){
                throw new Exception("pagador no encontrada", 1);
            }
            
            $arrResult = array(
                'code' => $diagnostic->getCode(),
                'groupId' => $diagnostic->getDiagnosticGroup()->getId(),
                'groupName' => $diagnostic->getDiagnosticGroup()->getName(),
                'genre' => $diagnostic->getGenre(),
                'minAge' => $diagnostic->getMinAge(),
                'maxAge' => $diagnostic->getMaxAge(),
                'description' => $diagnostic->getDescription(),
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
     * @Route("/diagnostic/tree", name="diagnostic_tree")
     */
    public function getTreeAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            if($params['type'] == 'CIE 10 4 digitos'){

                $diagnostic = $em->getRepository('AppBundle:Diagnostic')
                                    ->find($params['id']);
                
                $arrResult = array(
                    "id" => $diagnostic->getId(),
                    "name" => $diagnostic->getCode()." - ".$diagnostic->getDescription()
                    );

            }else if($params['type'] == 'CIE 10 3 digitos'){

                $diagnosticGroup = $em->getRepository('AppBundle:DiagnosticGroup')
                                    ->find($params['id']);
                
                $arrResult = array(
                    "id" => $diagnosticGroup->getId(),
                    "name" => $diagnosticGroup->getCode()." - ".$diagnosticGroup->getName(),
                    "childs" => array()
                    );

                $cDiagnostics = $em->getRepository('AppBundle:Diagnostic')
                                    ->findByDiagnosticGroup($diagnosticGroup->getId());

                foreach ($cDiagnostics as $cDiagnostic) {
                    $arrResult["childs"][] = array(
                        'id' => $cDiagnostic->getId(), 
                        'name' => $cDiagnostic->getCode()." - ".$cDiagnostic->getDescription(),
                        );
                }

            }else{//capitulos
                $diagnosticGroup = $em->getRepository('AppBundle:DiagnosticGroup')
                                    ->find($params['id']);
                
                 $arrResult = array(
                    "id" => $diagnosticGroup->getId(),
                    "name" => $diagnosticGroup->getCode()." - ".$diagnosticGroup->getName(),
                    "childs" => array()
                    );

               
                foreach ($diagnosticGroup->getChilds() as $child) {
                    $arrChilds = array();

                    $diagnostics = $em->getRepository('AppBundle:Diagnostic')
                                    ->findByDiagnosticGroup($child);
                    
                    foreach ($diagnostics as $diagnostic) {
                        $arrChilds[] = array(
                            'id' => $diagnostic->getId(), 
                            'name' => $diagnostic->getCode()." - ".$diagnostic->getDescription(),
                            );
                    }

                    $arrResult["childs"][] = array(
                        "id" => $child->getId(),
                        "name" => $child->getCode()." - ".$child->getName(),
                        "childs" => $arrChilds
                        );
                }
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

