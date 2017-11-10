<?php 

namespace AppBundle\Services;

/**
* servicio para generar reportes en excel
*/
class ExcelHelper 
{

	public function addFiedlsdByArray($phpExcelObject,$sheet=0,$arrData)
	{
		$rowNumber=1;
        foreach ($arrData as $row) {
            $letter = 'A';
            foreach ($row as $data) {
                $phpExcelObject->setActiveSheetIndex($sheet)
                    ->setCellValue($letter.$rowNumber, $data);
                $letter++;
            }
            $rowNumber++;
        }

        return $phpExcelObject;
	}


}