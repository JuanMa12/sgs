<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Exception;


class ReportController extends Controller
{
    /**
    * @Route("/project/data/dowland/report",name="project_data_dowland_report")
    */
    public function projectReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'name' =>'',
            'date' =>'',
            'dateInit' =>'',
            'dateEnd' =>'',
            'deparmentCode' =>'',
            'municipalityCode' =>'',
            'stage' =>'',
            'beneficiaryQuantity' =>'',
            'totalValue' =>'',
            'ownResources' =>'',
            'internationalCooperation' =>'',
            'nameCooperatingEntity' =>'',
            'otherResources' =>'',
            'ambit' =>'',
            'education' =>'',
            'incomeImprovement' =>'',
            'qualityLife' =>'',
            );

        $projectTitles = array();//Arreglo de titulos 
        array_push($projectTitles,'Empresa','Titulo del proyecto','Periodo de Corte','Fecha de Inicio','Fecha de Finalización','Código Departamento','Código municipio','Etapa','Numero de beneficiarios directos e indirectos','Valor Total del Proyecto','Recursos Propios','Cooperación Internacional','Nombre entidad cooperante','Otros Recursos','Ámbito','Líneas de Acción Educación','Líneas de Acción Mejoramiento de Ingresos','Líneas de Acción Mejoramiento de Calidad de Vida');
       

        $arrAmbit = array(
            "1"=>"Educación",
            "2"=>"Mejoramiento de Ingresos",
            "3"=>"Mejoramiento de Calidad de Vida"
        );
        $arrEducation = array(
            "1"=> "Formal",
            "2"=>"No formal"
        );
        $arrIncomeImprovement = array(
            "1"=> "Gestión de Proyectos Productivos",
            "2"=> "Ahorro y Crédito", 
            "3"=> "Empresarial",
            "4"=> "Otras"
        );

        $arrStage = array(
            "1"=> "Diseño",
            "2"=> "Operación (Ejecución)", 
            "3"=> "Terminado",
        );
        
        $arrQualityLife = array(
            "1"=> "Salud",
            "2"=>"Vivienda Digna",
            "3"=> "Seguridad Alimentaría",
            "4"=> "Recreación y Deporte",
            "5"=> "Ambiente y Saneamiento",
            "6"=> "Otros"
        );

        //Busqueda de proyectos
        $qb = $em->getRepository('AppBundle:Project')
                ->createQueryBuilder('p')
                ->innerJoin('p.projectsData','pd','p.id = pd.project_id');              

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("pd.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("pd.period <= :dateEnd");
            
        }
        
        $projects = $qb->getQuery()->getResult();
        $resultData = array();

        foreach ($projects as $project) {
            $data = $template;
            $data['enterprise'] = $project->getEnterprise()->getName();
            $data['name'] = $project->getName();

            foreach ($project->getProjectsData() as $projectData) {
        
                $data['date'] = date('Y-m-d',$projectData->getPeriod());
                $data['dateInit'] = date('Y-m-d',$projectData->getInitDate());
                $data['dateEnd'] = date('Y-m-d',$projectData->getEndDate());
                $data['deparmentCode'] = $projectData->getMunicipality()->getDepartmentId()->getCode();
                $data['municipalityCode'] = $projectData->getMunicipality()->getCode();
                $data['stage'] = $arrStage[$projectData->getStage()];
                $data['beneficiaryQuantity'] = $projectData->getBeneficiaryQuantity();
                $data['totalValue'] = $projectData->getTotalValue();
                $data['ownResources'] = $projectData->getOwnResources();
                $data['internationalCooperation'] = $projectData->getInternationalCooperation();
                $data['nameCooperatingEntity'] = $projectData->getNameCooperatingEntity();
                $data['otherResources'] = $projectData->getOtherResources();
                $data['ambit'] = $arrAmbit[$project->getAmbit()];
                if ($project->getEducation() != null) {
                    $data['education'] = $arrEducation[$project->getEducation()];
                }
                if ($project->getIncomeImprovement() != null) {
                    $data['incomeImprovement'] = $arrIncomeImprovement[$project->getIncomeImprovement()];
                }
                if ($project->getQualityLife() != null) {
                    $data['qualityLife'] = $arrQualityLife[$project->getQualityLife()];
                }
                $resultData[] = $data;
            }

        }

        $consolidate = array($projectTitles);
       
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'proyectos.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
    
    /**
    * @Route("/associate/dowland/report",name="associate_dowland_report")
    */
    public function associateReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'deparmentCode' =>'',
            'municipalityCode' =>'',
            'totalAssociate' =>'',
            'totalWomen' =>'',
            'associate_fse' =>'',
            'associate_fp' =>'',
            'associate_fs' =>'',
            'associate_ft' =>'',
            'associate_fu' =>'',
            'totalMan' =>'',
            'associate_mse' =>'',
            'associate_mp' =>'',
            'associate_ms' =>'',
            'associate_mt' =>'',
            'associate_mu' =>'',
            );

        $associateTitles = array();//Arreglo de titulos 
        array_push($associateTitles,'Empresa','Periodo de corte','Código Departamento','Código municipio','No. De Asociados ','Total Mujeres','No. De Asociadas sin escolaridad','No. De Asociadas con nivel primaria','No. De Asociadas con nivel secundaria','No. De Asociadas con nivel tecnico/tecnólogo','No. De Asociadas con nivel universitario','Total Hombres','No. De Asociados sin escolaridad','No. De Asociados con nivel primaria','No. De Asociados con nivel secundaria','No. De Asociados con nivel tecnico/tecnólogo','No. De Asociados con nivel universitario');
       
        //Busqueda de asociados
        $qb = $em->getRepository('AppBundle:Associate')
                ->createQueryBuilder('aso');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("aso.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("aso.period <= :dateEnd");
            
        }
        
        $associates = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($associates as $associate) {
            $enterpriseId = $associate->getEnterprise()->getId();
            $period = $associate->getPeriod();
            $municipality = $associate->getMunicipality()->getId();
            $keyRow =  $enterpriseId.$period.$municipality;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
            }

            $data[$keyRow]['enterprise'] = $associate->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$associate->getPeriod());
            $data[$keyRow]['deparmentCode'] = $associate->getMunicipality()->getDepartmentId()->getCode();
            $data[$keyRow]['municipalityCode'] = $associate->getMunicipality()->getCode();
            
            $data[$keyRow]['totalAssociate'] += $associate->getQuantity();
            if ($associate->getGenre() == 'F') {
                $data[$keyRow]['totalWomen'] += $associate->getQuantity();
            }else{
                $data[$keyRow]['totalMan'] += $associate->getQuantity();
            }

            switch ($associate->getEducation()) {
                case "Sin escolaridad":
                    if ($associate->getGenre() == 'F') {
                        $data[$keyRow]['associate_fse'] += $associate->getQuantity();
                    }else{
                        $data[$keyRow]['associate_mse'] += $associate->getQuantity();
                    }
                    break;
                case "Primaria":
                    if ($associate->getGenre() == 'F') {
                        $data[$keyRow]['associate_fp'] += $associate->getQuantity();
                    }else{
                        $data[$keyRow]['associate_mp'] += $associate->getQuantity();
                    }
                    break;
                case "Secundaria":
                    if ($associate->getGenre() == 'F') {
                        $data[$keyRow]['associate_fs'] += $associate->getQuantity();
                    }else{
                        $data[$keyRow]['associate_ms'] += $associate->getQuantity();
                    }
                    break;
                case "Tecnico/Tecnólogo":
                    if ($associate->getGenre() == 'F') {
                        $data[$keyRow]['associate_ft'] += $associate->getQuantity();
                    }else{
                        $data[$keyRow]['associate_mt'] += $associate->getQuantity();
                    }
                    break;
                default:
                    if ($associate->getGenre() == 'F') {
                        $data[$keyRow]['associate_fu'] += $associate->getQuantity();
                    }else{
                        $data[$keyRow]['associate_mu'] += $associate->getQuantity();
                    }
                    break;
            }

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($associateTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'asociados.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
    * @Route("/training/dowland/report",name="training_dowland_report")
    */
    public function trainingReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'theme' =>'',
            'intensity' =>'',
            'attendees_ldir' =>'',
            'attendees_ltec' =>'',
            'attendees_lo' =>'',
            'investment' =>'',
            );

        $trainigTitles = array();//Arreglo de titulos 
        array_push($trainigTitles,'Empresa','Periodo de corte','Tema de capacitacion','Intensidad de los Programas','Asistentes del nivel directivo','Asistentes del nivel tecnico','Asistentes del nivel operativo','Inversion de la capacitación');
       
        //Busqueda de capacitaciones
        $qb = $em->getRepository('AppBundle:Training')
                ->createQueryBuilder('t');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("t.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("t.period <= :dateEnd");
            
        }
        
        $trainings = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($trainings as $training) {
            $enterpriseId = $training->getEnterprise()->getId();
            $period = $training->getPeriod();
            $theme = $training->getTheme();
            $keyRow =  $enterpriseId.$period.$theme;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
            }

            $data[$keyRow]['enterprise'] = $training->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$training->getPeriod());
            $data[$keyRow]['theme'] = $training->getTheme();
            $data[$keyRow]['intensity'] = $training->getIntensity();
            $data[$keyRow]['investment'] = $training->getInvestment();            

            switch ($training->getLevelAttendees()) {
                case "Directivo":                    
                    $data[$keyRow]['attendees_ldir'] += $training->getQuantity();
                    break;
                case "Técnico":
                    $data[$keyRow]['attendees_ltec'] += $training->getQuantity();
                    break;
                default:
                    $data[$keyRow]['attendees_lo'] += $training->getQuantity();
                    break;
            }

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($trainigTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'capacitaciones.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
    * @Route("/human/resource/dowland/report",name="human_resource_dowland_report")
    */
    public function humanResourceReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'totalWomen' =>'',
            'management_fld' =>'',
            'management_pfld' =>'',
            'management_flt' =>'',
            'management_pflt' =>'',
            'management_flo' =>'',
            'management_pflo' =>'',
            'management_flp' =>'',
            'management_pflp' =>'',
            'totalMan' =>'',
            'management_mld' =>'',
            'management_pmld' =>'',
            'management_mlt' =>'',
            'management_pmlt' =>'',
            'management_mlo' =>'',
            'management_pmlo' =>'',
            'management_mlp' =>'',
            'management_pmlp' =>'',
            'linked' =>'',
            'un_linked' =>'',
            'preferment' =>'',
            );

        $hResourceTitles = array();//Arreglo de titulos 
        array_push($hResourceTitles,'Empresa','Periodo de corte','Empleadas Mujeres','Empleadas en el Nivel Directivo','Promedio de Edad Nivel Directivo','Empleadas en el Nivel Tecnico','Promedio de Edad Nivel Tecnico','Empleadas en el Nivel Operativo','Promedio de Edad Nivel Operativo','Empleadas Practicantes','Promedio de Edad Practicantes','Empleados Hombres','Empleados en el Nivel Directivo','Promedio de Edad Nivel Directivo','Empleados en el Nivel Tecnico','Promedio de Edad Nivel Tecnico','Empleados en el Nivel Operativo','Promedio de Edad Nivel Operativo','Empleados Practicantes','Promedio de Edad Practicantes','Vinculacion','Desvinculacion ','Ascensos');
       
        //Busqueda de capacitaciones
        $qb = $em->getRepository('AppBundle:HumanResource')
                ->createQueryBuilder('h');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("h.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("h.period <= :dateEnd");
            
        }
        
        $humanResources = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($humanResources as $humanResource) {
            $enterpriseId = $humanResource->getEnterprise()->getId();
            $period = $humanResource->getPeriod();

            $keyRow =  $enterpriseId.$period;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
            }

            $data[$keyRow]['enterprise'] = $humanResource->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$humanResource->getPeriod());
            $data[$keyRow]['linked'] += $humanResource->getLinked();

            $data[$keyRow]['un_linked'] += $humanResource->getUnlinked();
            $data[$keyRow]['preferment'] += $humanResource->getPreferment();
            if ($humanResource->getGenre() == 'F') {
                $data[$keyRow]['totalWomen'] += $humanResource->getQuantity();
            }else{
                $data[$keyRow]['totalMan'] += $humanResource->getQuantity();
            }
           
            switch ($humanResource->getManagementLevel()) { 
                case "Directivo":
                    if ($humanResource->getGenre() == 'F') {
                        $data[$keyRow]['management_fld'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pfld'] += $humanResource->getAverageAge();
                    }else{
                        $data[$keyRow]['management_mld'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pmld'] += $humanResource->getAverageAge();
                    }
                    break;
                case "Tecnico":
                    if ($humanResource->getGenre() == 'F') {
                        $data[$keyRow]['management_flt'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pflt'] += $humanResource->getAverageAge();
                    }else{
                        $data[$keyRow]['management_mlt'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pmlt'] += $humanResource->getAverageAge();
                    }
                    break;
                case "Operativo":
                    if ($humanResource->getGenre() == 'F') {
                        $data[$keyRow]['management_flo'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pflo'] += $humanResource->getAverageAge();
                    }else{
                        $data[$keyRow]['management_mlo'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pmlo'] += $humanResource->getAverageAge();
                    }
                    break;
                default:
                    if ($humanResource->getGenre() == 'F') {
                        $data[$keyRow]['management_flp'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pflp'] += $humanResource->getAverageAge();
                    }else{
                        $data[$keyRow]['management_mlp'] += $humanResource->getQuantity();
                        $data[$keyRow]['management_pmlp'] += $humanResource->getAverageAge();
                    }
                    break;
            }

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($hResourceTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'recurso_humano.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
    * @Route("/environment/dowland/report",name="environment_dowland_report")
    */
    public function environmentReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'pro_energy' =>'',
            'energy_saving' =>'',
            'pro_water' =>'',
            'water_saving' =>'',
            'pro_recycling' =>'',
            'recycling' =>'',
            );

        $environmentTitles = array();//Arreglo de titulos 
        array_push($environmentTitles,'Empresa','Periodo de corte','Programa de ahorro de energia','Resultado ahorro de energia','Programas de manejo del agua','Resultado ahorro del agua','Programas de reciclaje','Resultado del reciclaje');
       
        //Busqueda de capacitaciones
        $qb = $em->getRepository('AppBundle:Environment')
                ->createQueryBuilder('en');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("en.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("en.period <= :dateEnd");
            
        }
        
        $environments = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($environments as $environment) {
            $enterpriseId = $environment->getEnterprise()->getId();
            $period = $environment->getPeriod();
            
            $keyRow =  $enterpriseId.$period;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
                $data[$keyRow]['pro_energy'] = 'NO';
                $data[$keyRow]['pro_water'] = 'NO';
                $data[$keyRow]['pro_recycling'] = 'NO';
            }

            $data[$keyRow]['enterprise'] = $environment->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$environment->getPeriod());

            if ($environment->getEnergySaving() != null) {
                $data[$keyRow]['pro_energy'] = 'SI';
                $data[$keyRow]['energy_saving'] += $environment->getEnergySaving();
            }
            
            if ($environment->getSavingWater() != null) {
                $data[$keyRow]['pro_water'] = 'SI';
                $data[$keyRow]['water_saving'] += $environment->getSavingWater();
            }

            if ($environment->getRecycling() != null) {
                $data[$keyRow]['pro_recycling'] = 'SI';
                $data[$keyRow]['recycling'] += $environment->getRecycling();
            }

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($environmentTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'medio_ambiente.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
    * @Route("/institutional/participation/dowland/report",name="institutional_participation_dowland_report")
    */
    public function institutionalParticipationReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'deparmentCode' =>'',
            'municipalityCode' =>'',
            'user_quantity' =>'',
            );

        $instParticipationTitles = array();//Arreglo de titulos 
        array_push($instParticipationTitles,'Empresa','Periodo de corte','Código Departamento','Código municipio','No. Alianza de usuarios');
       
        //Busqueda de capacitaciones
        $qb = $em->getRepository('AppBundle:InstitutionalParticipation')
                ->createQueryBuilder('inp');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("inp.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("inp.period <= :dateEnd");
            
        }
        
        $institutionalsParticipation = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($institutionalsParticipation as $institutionalsP) {
            $enterpriseId = $institutionalsP->getEnterprise()->getId();
            $period = $institutionalsP->getPeriod();
            $municipality = $institutionalsP->getMunicipality()->getDepartmentId()->getId();
            
            $keyRow =  $enterpriseId.$period.$municipality;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
            }

            $data[$keyRow]['enterprise'] = $institutionalsP->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$institutionalsP->getPeriod());
            $data[$keyRow]['deparmentCode'] = $institutionalsP->getMunicipality()->getDepartmentId()->getCode();
            $data[$keyRow]['municipalityCode'] = $institutionalsP->getMunicipality()->getCode();
            $data[$keyRow]['user_quantity'] += $institutionalsP->getUsersQuantity();

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($instParticipationTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'participacion_institucional.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

     /**
    * @Route("/institutional/citizen/dowland/report",name="institutional_citizen_dowland_report")
    */
    public function institutionalCitizenReportAction(Request $request)
    
    {
        $em = $this->getDoctrine()->getManager();        
        $params = $request->query->all();
    
        //template de informacion
        $template = array(
            'enterprise' =>'',
            'period' =>'',
            'deparmentCode' =>'',
            'municipalityCode' =>'',
            'office_quantity' =>'',
            'suggestion_mailbox' =>'',
            );

        $citizenParticipationTitles = array();//Arreglo de titulos 
        array_push($citizenParticipationTitles,'Empresa','Periodo de corte','Código Departamento','Código municipio','N. Oficinas de atención al Usuario','Buzón de sugerencias');
       
        //Busqueda de capacitaciones
        $qb = $em->getRepository('AppBundle:CitizenParticipation')
                ->createQueryBuilder('inp');

        if(isset($params['date_ini'])){
            $qb->setParameter('dateIni',strtotime($params['date_ini']))
                ->andWhere("inp.period >= :dateIni");
        }

        if(isset($params['date_end'])){
            $qb->setParameter('dateEnd',strtotime($params['date_end']))
                ->andWhere("inp.period <= :dateEnd");
            
        }
        
        $citizensParticipation = $qb->getQuery()->getResult();
        $resultData = array();
        $data = array();
    
        foreach ($citizensParticipation as $citizenP) {
            $enterpriseId = $citizenP->getEnterprise()->getId();
            $period = $citizenP->getPeriod();
            $municipality = $citizenP->getMunicipality()->getDepartmentId()->getId();
            
            $keyRow =  $enterpriseId.$period.$municipality;

            if (!array_key_exists($keyRow, $data)) {
                $data[$keyRow] = $template;
            }

            $data[$keyRow]['enterprise'] = $citizenP->getEnterprise()->getName();
            $data[$keyRow]['period'] = date('Y-m-d',$citizenP->getPeriod());
            $data[$keyRow]['deparmentCode'] = $citizenP->getMunicipality()->getDepartmentId()->getCode();
            $data[$keyRow]['municipalityCode'] = $citizenP->getMunicipality()->getCode();
            $data[$keyRow]['office_quantity'] += $citizenP->getOfficesQuantity();
            $data[$keyRow]['suggestion_mailbox'] = $citizenP->getSuggestionsMailbox();

            $resultData[$keyRow] = $data[$keyRow];                           
        }
        
        $consolidate = array($citizenParticipationTitles);
        
        foreach ($resultData as $item) {
            $consolidate[] = $item;
        }
        

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("bwebcolombia");
        
        //Generamos el archivo excel de acuerdo a la matriz de informacion consolidada
        $phpExcelObject = $this->get('excelHelper')->addFiedlsdByArray($phpExcelObject,0,$consolidate);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // Insertando contenido
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // asignando nombre de archivo
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'participacion_ciudadana.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}

