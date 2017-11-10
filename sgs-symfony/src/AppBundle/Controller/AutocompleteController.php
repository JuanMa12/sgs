<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;


use AppBundle\Entity\Department;

class AutocompleteController extends Controller
{

	/**
     * @Route("/autocomplete/department", name="autocomplete_department")
     */
	public function departmentAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:Department')
							->createQueryBuilder('d')							
							->where("d.name LIKE :word OR d.code LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(10)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $department) {
				$arrResult[] = array(
					'value'=>$department->getId(),
					'label'=>$department->getCode().' -'.$department->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,"title"=>"Departamentos");

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
     * @Route("/autocomplete/health_promotion_entity", name="autocomplete_eps")
     */
	public function healthPromotionEntityAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:HealthPromotionEntity')
							->createQueryBuilder('d')							
							->where("d.name LIKE :word OR d.alias LIKE :word OR d.code LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(10)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $healthPromotionEntity) {
				$arrResult[] = array(
					'value'=>$healthPromotionEntity->getId(),
					'label'=>$healthPromotionEntity->getCode().' -'.$healthPromotionEntity->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,"title"=>"EPSs");

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
     * @Route("/autocomplete/municipality", name="autocomplete_municipality")
     */
	public function municipalityAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:Municipality')
							->createQueryBuilder('m')							
							->where("m.name LIKE :word OR m.code LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(10)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $municipality) {
				$arrResult[] = array(
					'value'=>$municipality->getId(),
					'label'=>$municipality->getCode().' -'.$municipality->getName().' - '.$municipality->getDepartmentId()->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,"title"=>"Municipios");

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
     * @Route("/autocomplete/payer", name="autocomplete_payer")
     */
	public function payerAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:Payer')
							->createQueryBuilder('p')							
							->where("p.name LIKE :word OR p.divipola LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(10)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $payer) {
				$arrResult[] = array(
					'value'=>$payer->getId(),
					'label'=>$payer->getDivipola().' -'.$payer->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult);

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
     * @Route("/autocomplete/diagnostics-f", name="autocomplete_diagnostics_f")
     */
	public function diagnosticsFAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:DiagnosticGroup')
							->createQueryBuilder('d')							
							->where("d.code LIKE :word OR d.name LIKE :word")
							->andWhere("d.parent is null")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(4)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $diagnosticGroup) {
				$arrResult[] = array(
					'value'=>$diagnosticGroup->getId(),
					'label'=>$diagnosticGroup->getCode().' - '.$diagnosticGroup->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,'title'=>'Capitulos');

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
     * @Route("/autocomplete/diagnostics-t", name="autocomplete_diagnostics_t")
     */
	public function diagnosticsTAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:Diagnostic')
							->createQueryBuilder('d')							
							->where("d.code LIKE :word OR d.description LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(4)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $diagnostic) {
				$arrResult[] = array(
					'value'=>$diagnostic->getId(),
					'label'=>$diagnostic->getCode()." - ".$diagnostic->getDescription()
					);				
			}

			$output = array('success'=>true,'description'=>$arrResult,'title'=>'CIE 10 4 digitos');

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
     * @Route("/autocomplete/health_process-f", name="autocomplete_health_process_f")
     */
	public function healthProcessFAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:HealthProcess')
						->createQueryBuilder('p')							
						->where("p.type = :type")
						->andWhere("p.code LIKE :word OR p.description LIKE :word")
						->setParameter('word','%'.$params['word'].'%')
						->setParameter('type','GRUPO')
						->setMaxResults(4)
						->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $healthProcess) {
				$arrResult[] = array(
					'value'=>$healthProcess->getId(),
					'label'=>$healthProcess->getCode().' - '.$healthProcess->getDescription()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,'title'=>'GRUPO');

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
     * @Route("/autocomplete/health_process-two", name="autocomplete_health_process_two")
     */
	public function healthProcessTwoAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:HealthProcess')
						->createQueryBuilder('p')							
						->where("p.type = :type")
						->andWhere("p.code LIKE :word OR p.description LIKE :word")
						->setParameter('word','%'.$params['word'].'%')
						->setParameter('type','SUBGRUPO')
						->setMaxResults(4)
						->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $healthProcess) {
				$arrResult[] = array(
					'value'=>$healthProcess->getId(),
					'label'=>$healthProcess->getCode().' - '.$healthProcess->getDescription()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,'title'=>'SUBGRUPO');

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
     * @Route("/autocomplete/health_process-t", name="autocomplete_health_process_t")
     */
	public function healthProcessTAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:HealthProcess')
						->createQueryBuilder('p')							
						->where("p.type = :type")
						->andWhere("p.code LIKE :word OR p.description LIKE :word")
						->setParameter('word','%'.$params['word'].'%')
						->setParameter('type','CATEGORIA')
						->setMaxResults(4)
						->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $healthProcess) {
				$arrResult[] = array(
					'value'=>$healthProcess->getId(),
					'label'=>$healthProcess->getCode().' - '.$healthProcess->getDescription()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,'title'=>'CATEGORIA');

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
     * @Route("/autocomplete/health_process-four", name="autocomplete_health_process_four")
     */
	public function healthProcessFourAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:HealthProcess')
							->createQueryBuilder('p')							
							->where("p.type = :type")
							->andWhere("p.code LIKE :word OR p.description LIKE :word")
							->setParameter('word','%'.$params['word'].'%')
							->setParameter('type','SUBCATEGORIA')
							->setMaxResults(4)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $healthProcess) {
				$arrResult[] = array(
					'value'=>$healthProcess->getId(),
					'label'=>$healthProcess->getCode()." - ".$healthProcess->getDescription()
					);				
			}

			$output = array('success'=>true,'description'=>$arrResult,'title'=>'SUBCATEGORIA');

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
     * @Route("/autocomplete/diagnostics/group", name="autocomplete_diagnostics_g")
     */
	public function diagnosticGroupAction(Request $request)
	{
		try {
			$params = $request->request->all();
			$em = $this->getDoctrine()->getManager();

			$query = $em->getRepository('AppBundle:DiagnosticGroup')
							->createQueryBuilder('d')							
							->where("d.code LIKE :word OR d.name LIKE :word")
							->andWhere("d.parent is not null")
							->setParameter('word','%'.$params['word'].'%')
							->setMaxResults(4)
							->getQuery();

			$records = $query->getResult();

			if(!$records){
				throw new Exception("Datos no encontrados", 1);
			}

			$arrResult = array();
			foreach ($records as $diagnosticGroup) {
				$arrResult[] = array(
					'value'=>$diagnosticGroup->getId(),
					'label'=>$diagnosticGroup->getCode().' - '.$diagnosticGroup->getName()
					);
			}
			
			$output = array('success'=>true,'description'=>$arrResult,'title'=>'CIE 10 3 digitos');

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
