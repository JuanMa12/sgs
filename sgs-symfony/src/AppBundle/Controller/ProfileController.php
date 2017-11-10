<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Profile;
use AppBundle\Entity\HealthPromotionEntity;
use AppBundle\Entity\ProfileHealthPromotion;
use Exception;


class ProfileController extends Controller
{
    /**
     * @Route("/admin/profile/list", name="profile_list")
     */
    public function formAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $healthPromotionEntities = $em->getRepository('AppBundle:HealthPromotionEntity')
                                    ->findBy(array('guild' => 1));

        return $this->render('profile/list.html.twig',array('healthPromotionEntities'=>$healthPromotionEntities));
    }

    /**
     * @Route("/admin/profile/grid", name="profile_grid")
     */
    public function gridAction(Request $request)
    {
        $colums = array(
            'id',
            'name',
            );
        
        $subquery = 'SELECT p.* FROM profiles AS p';

        $datatable = $this->get('data_table');
        $result = $datatable->listResult($request, $subquery, $colums);

        foreach ($result['result'] as $data) {
            $options = $this->renderView('profile/itemActions.html.twig',array('item' => $data));

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
     * @Route("/admin/profile/save/form", name="profile_save")
     */
    public function saveAction(Request $request)
    {
        try {
            
            $params = $request->request->all();            
            $em = $this->getDoctrine()->getManager();

            if (!isset($params['healthPromotionIds'])) {
                throw new Exception("Debe escoger al menos una EPS valida", 1);
                
            }
            if($params['_id'] != ''){
                
                $profile = $em->getRepository('AppBundle:Profile')
                              ->find($params['_id']);

                $message = 'Se edita el perfil de: '.$profile->getName();
            }else{

                $profile = new Profile();
                $em->persist($profile);

                $message = 'Se crea el perfil con el nombre de: '.$params['_name'];
            }

            $profile->setName($params['_name']);  
            $profile->setCreateReport($params['create_report']);  

            $em->flush();

            foreach ($profile->getProfilesHealthPromotion() as $value) {
                $em->remove($value);
                $em->flush();
            }

            foreach ($params['healthPromotionIds'] as $id) {
               $healthPromotionEntity = $em->getRepository('AppBundle:HealthPromotionEntity')
                                           ->find($id);

                $profileHealthPromotion = new ProfileHealthPromotion();
                $em->persist($profileHealthPromotion);

                $profileHealthPromotion->setProfile($profile);
                $profileHealthPromotion->setHealthPromotionEntity($healthPromotionEntity);

                $em->flush();
            }

            $this->get('log_activity_manager')->registerActivity($message);

            $output = array('success'=>true,'description' => $profile->getId());

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
     * @Route("/admin/profile/info", name="profile_get_info")
     */
    public function getInfoAction(Request $request)
    {
        try {
            $params = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            $profile = $em->getRepository('AppBundle:Profile')
                                ->find($params['_id']);

            $arrhHealthPromotionEntity= array();

            foreach ($profile->getProfilesHealthPromotion() as $value) {
                     
                $arrhHealthPromotionEntity[]= $value->getHealthPromotionEntity()->getId();
            }

            if(!$profile){
                throw new Exception("perfil no encontrada", 1);
            }
            
            $arrResult = array(
                'name' => $profile->getName(),
                'createReport' => $profile->getCreateReport(),
                'healthPromotionIds' => $arrhHealthPromotionEntity
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

