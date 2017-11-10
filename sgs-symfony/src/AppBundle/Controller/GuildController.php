<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Guild;
use Exception;


class GuildController extends Controller
{
    /**
     * @Route("/admin/guild/list", name="guild_list")
     */
    public function formAction(Request $request)
    {
        return $this->render('systemParameters/guildList.html.twig');
    }

    /**
     * @Route("/admin/guild/grid", name="guild_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'id',
            'name',
            );
        
        $subquery = 'SELECT g.* FROM guilds AS g';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('systemParameters/itemActions.html.twig',array('item' => $data));

            $result['output']['aaData'][] = array(
                $data['id'],
                $data['name'],
                $options
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/admin/guild/save/form", name="guild_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager(); 


            if($params['_id'] != ''){    
              
                $guild = $em->getRepository('AppBundle:Guild')
                                ->find($params['_id']);

                $logMessage= 'Se edita el gremio con el nombre: '.$guild->getName();
            
            }else{

                $guild = new Guild();
                $em->persist($guild);
                
                $logMessage= 'Se crea el gremio con el nombre: '.$params['_name'];
            }

            $guild->setName($params['_name']); 

            $this->get('log_activity_manager')->registerActivity($logMessage);

            $em->flush();

            $output = array('success'=>true,'description' => $guild->getId());

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
     * @Route("/admin/guild/get/info", name="guild_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $guild = $em->getRepository('AppBundle:Guild')
                                ->find($params['_id']);

            if(!$guild){
                throw new Exception("gremio no encontrada", 1);
            }
            
            $arrResult = array(
                'name' => $guild->getName()
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

