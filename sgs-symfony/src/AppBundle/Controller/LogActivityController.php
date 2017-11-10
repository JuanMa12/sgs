<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\LogActivity;
use Exception;


class LogActivityController extends Controller
{
    /**
     * @Route("/admin/log_activity/list", name="log_activity_list")
     */
    public function listAction(Request $request)
    {
        return $this->render('logActivity/list.html.twig');
    }

    /**
     * @Route("/admin/log_activity/grid", name="log_activity_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'date',
            'user',
            'description',
            );
        
        $subquery = 'SELECT 
                        u.name AS user,
                        la.date,
                        la.description
                    FROM
                        log_activities AS la
                            INNER JOIN
                        users AS u ON la.user_id = u.id';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            
            $result['output']['aaData'][] = array(
                date('d/m/Y',$data['date']),
                $data['user'],
                $data['description']
                );
        }

        $response = new Response(json_encode($result['output']));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
    * @Route("/admin/delete/log/activity", name="delete_log_activity")
    */
    public function deleteAction(Request $request)
    {
        try {

            $params = $request->request->all();   
            $em = $this->getDoctrine()->getManager();

            $queryBuilder = $em->getRepository('AppBundle:LogActivity')
                ->createQueryBuilder('la')
                ->where("la.date >= :dateIni")
                ->andWhere("la.date <= :dateEnd")
                ->setParameter('dateIni',strtotime($params['date_ini']))
                ->setParameter('dateEnd',strtotime($params['date_end']));


            $logActivities = $queryBuilder->getQuery()->getResult();

            if (!$logActivities) {
                throw new Exception("no se encuentran registros en el log para esas fechas", 1);
            }

            foreach ($logActivities as $logActivity) {
                $em->remove($logActivity);
                $em->flush();
            }

            $message = 'se elminan desde la fecha: '.$params['date_ini'].' hasta la fecha '.$params['date_end'];
            
            $this->get('log_activity_manager')->registerActivity($message);

            $output = array('success'=>true);

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

