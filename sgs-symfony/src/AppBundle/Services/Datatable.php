<?php 

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

/**
* modelo para utilizacion del datatable
*/
class Datatable 
{

	protected $em;

	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
	}
	
	/**
	 * metodo para realizar consulta sobre una tabla usando el datatable
	 * @param  object $request   objeto de request
	 * @param  string $tableName nombre de la tabla de la base de datos
	 * @param  string $tableId   nombre del campo id de la tabla
	 * @param  array $columns   arreglo con columnas de la consulta
	 * @return array            arreglo con resultados y estructura de datos
	 */
	//public static function listResult(Request $httprequest,$subquery,$columns,$whereparam="",$sqlGroup="")
	public function listResult(Request $httprequest,$subquery,$columns,$whereparam = '',$sqlGroup = '')
	{
		$request = $httprequest->request->all();

		$secho = (int) $request["sEcho"];
		
		$dstar = (int) $request["iDisplayStart"];
		$dlength = (int) $request["iDisplayLength"];

		$sqlLimit = "";
		if (isset($dstar) && ($dlength != -1)) {
		    $sqlLimit  = " LIMIT $dstar, $dlength ";
		}

		//paginamos
		$sqltotal = "SELECT COUNT(*) as conteo FROM ({$subquery}) as sq";
        $stmt = $this->em->getConnection()->prepare($sqltotal);
        $stmt->execute();
        $resultotalb = $stmt->fetchAll();

                 
		$totalCount = $resultotalb[0]["conteo"];
                
        //filtramos
		$search = trim($request["sSearch"]);

		$sqlWhere = "";
		if (!empty($search)) {
			$arrSqlVariables = array();
		    $sqlWhere = " WHERE (";
		    for ($i = 0 ; $i < count($columns); $i++){

		    	$pos = strpos($whereparam ,$columns[$i]);

		    	if ($pos === false) {

			        $searcheable = (isset($request["bSearchable_".$i]))?$request["bSearchable_".$i]:'';
			        if ($searcheable == 'true') {
			            $sqlWhere.= $columns[$i]." LIKE :p".$i." OR ";
			            $arrSqlVariables['p'.$i] = "%".$search."%";
			        }
		    	}

		    }
		    $sqlWhere = substr_replace( $sqlWhere, "", -3 );
		    $sqlWhere .= ") ";
		}
		

		if(!empty($sqlWhere)){

			if (!empty($whereparam) && is_string($whereparam)) {
				$sqlWhere .= " AND ({$whereparam}) ";
			}
			
		}elseif($whereparam != '' && is_string($whereparam)){
			$sqlWhere .= " WHERE ({$whereparam}) ";
		}

		//ordenamos
		$isortCols = (int) $request["iSortingCols"];
		$sqlOrder = "";
		if ($isortCols > 0) {
		    $sqlOrder = " ORDER BY ";

		    for ($i=0 ; $i<$isortCols ; $i++){

		        $isortColumn = $request["iSortCol_".$i];
		        $sortColumn = $request["bSortable_".$isortColumn];
		        $isortDir = $request["sSortDir_".$i];

		        if ($sortColumn == 'true') {

		        	$sqlOrder .= $columns[$isortColumn].", ";
		        }

		    }

		    $sqlOrder = substr_replace($sqlOrder, "",-2);
		    $sqlOrder .= " ".strtoupper($isortDir);
		}

		$sqlColumns = "";
		foreach ($columns as $key => $value) {
			$sqlColumns .= "{$value}, ";
		}
		$sqlColumns = substr_replace($sqlColumns, "",-2);

		if($sqlGroup != ''){
			$sqlGroup = 'GROUP BY '.$sqlGroup;
			$sqlOrder ='';
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
		{$sqlColumns}
		FROM ({$subquery}) AS sq 
		{$sqlWhere}
		{$sqlOrder}
		{$sqlGroup}
		{$sqlLimit}
		";

		
		$stmt = $this->em->getConnection()->prepare($sql);

		/*
		Prevencion de sql injection
		 */
		if(!empty($search)){
			foreach ($arrSqlVariables as $key => $varieable) {
				$stmt->bindValue($key,$varieable);
			}
		}
        
      	$stmt->execute();
        $result = $stmt->fetchAll();

		//consultamos resultados encontrados
		$stmt = $this->em->getConnection()->prepare("SELECT FOUND_ROWS() as conteo");
        $stmt->execute();
        $resultotalb = $stmt->fetchAll();
		$totalCountFiltered = $resultotalb[0]["conteo"];

		//arreglo con datos filtrados par convertir en json
		$output = array(
		        "sEcho" => $secho,
		        "iTotalRecords" => $totalCount,
		        "iTotalDisplayRecords" => $totalCountFiltered,
		        "aaData" => array()
		);

		return array("output"=>$output,"result"=>$result);
	}
}