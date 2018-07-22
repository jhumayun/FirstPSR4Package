<?php
use AvanaTest\Acme\IProcessor as IProcessor;
use AvanaTest\Acme\ReaderFilter as ReaderFilter;

namespace AvanaTest\Acme;

class ExcelValidator implements IProcessor{
	protected $_file;
	protected $_fileType;
	protected $_worksheets_data;
		
	public function __construct(){
	}
	
	public function validate(){
		$this->process($this->_file, $this->_fileType);
	}
	
	public function process($file, $fileType){
		$Utils = Utils::instance();
		$Utils::printMessage("file: $file, fileType: $fileType<br/>");
		
		$refclass = new \ReflectionClass($this);
		$columnsSignature = $refclass->getStaticPropertyValue('col_spec');
		
		$this->analyseColumnSignature($columnsSignature,$file);
		
	}
	
	protected function analyseColumnSignature($columnsSignature,$file){
		$Utils = Utils::instance();
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($Utils::getFileExtension($file));
		$this->_worksheets_data = $reader->listWorksheetInfo($file);
		
		$signatureMap = array();
		foreach($this->_worksheets_data as $i=>$worksheet_data){
			$signatureMap[$worksheet_data['worksheetName']]=array(
				'isColumnSignaturePassed'=>true,
				'columns_meta' =>array()
			);
			$Utils::printMessage('Validating Sheet "'.$worksheet_data['worksheetName'].'"');
			$filterSubset = new ReaderFilter(1,1,range('A',$worksheet_data['lastColumnLetter']));
			$reader->setReadFilter($filterSubset);
			$reader->setLoadSheetsOnly($worksheet_data['worksheetName']);
			/**  Load only the rows and columns that match our filter to Spreadsheet  **/
			$spreadsheet = $reader->load($file);
			if(count($columnsSignature)==$worksheet_data['totalColumns']){
				foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
					$count = 0;
					foreach ($sheet->getCoordinates(true) as $coordinate) {
						$cell = $sheet->getCell($coordinate);
						$col = $cell->getColumn();
						$cellValue = $cell->getCalculatedValue();
						$isColumnSigMatchingSheetColumnSig = ($cellValue==$columnsSignature[$count])?true:false;
						if(!$isColumnSigMatchingSheetColumnSig){
							$signatureMap[$worksheet_data['worksheetName']]['isColumnSignaturePassed'] = false;
							$Utils::printMessage("Column '$col' header mismatched expected:".$columnsSignature[$count]." found:".$cellValue);
						}
						$signatureMap[$worksheet_data['worksheetName']]['columns_meta'][$col]=array(
							'value'=>$cellValue,
							'isColumnSigMatchingSheetColumnSig' => $isColumnSigMatchingSheetColumnSig,
							'validator' => $this->determineColumnValidator($cellValue)
						);
						$count++;
					}
					$this->validateData($signatureMap,$worksheet_data['worksheetName']);
				}
			}
			else{
				$Utils::printMessage('Number of columns not matching with "'.$this->_fileType.'" specification.');
			}
		}
	}
	
	protected function validateData($signatureMap,$worksheet){
		if(1 == $signatureMap[$worksheet]['isColumnSignaturePassed']){
			$Utils = Utils::instance();
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($Utils::getFileExtension($this->_file));
			$reader->setLoadSheetsOnly($worksheet);
			$spreadsheet = $reader->load($this->_file);
			
			$WSInfo = array();
			
			foreach($this->_worksheets_data as $i=>$worksheet_data){
				if($worksheet_data['worksheetName']==$worksheet){
					$WSInfo = $worksheet_data;
					break;
				}
			}
			$HTML = 	"<table border='1' align='center' width='100%'>";
			$HTML .= 		"<tr>";
			$HTML .= 			"<td>";
			$HTML .= 				"Row";
			$HTML .= 			"</td>";
			$HTML .= 			"<td>";
			$HTML .= 				"Errors";
			$HTML .= 			"</td>";
			$HTML .= 		"</tr>";
			
			for($rIdx=2;$rIdx<=($spreadsheet->getActiveSheet()->getHighestRow());$rIdx++){
				$rowErrors = array();
				foreach($signatureMap[$worksheet]['columns_meta'] as $cIdx=>$cMeta){
					$cellAddress = $cIdx.$rIdx;
					$cell = $spreadsheet->getActiveSheet()->getCell($cellAddress);
					$value = $cell->getFormattedValue();
					$res = $this->validateCell($cMeta['validator'],$value); 
					if($res!==true){
						$rowErrors[str_replace($cMeta['validator'],'',$cMeta['value'])] = $res; 
					}
				}
				if(!empty($rowErrors)){
					$HTML .= 	"<tr>";
					$HTML .= 		"<td>";
					$HTML .= 			$rIdx;
					$HTML .= 		"</td>";
					$HTML .= 		"<td>";
					foreach($rowErrors as $colName=>$message){
						$HTML .= 		"<p>";
						$HTML .= 			str_replace('%ColName%',$colName,$message);
						$HTML .= 		"</p>";
					}
					$HTML .= 		"</td>";
					$HTML .= 	"</tr>";
				}
			}
			$HTML .= 	"</table>";
			echo $HTML;
		}
	}
	
	protected function validateCell($validator,$value){
		$result = true;
		switch($validator){
			case '#':
				if($this->hasSpace($value)){
					return "%ColName% should not contain any space";
				}
			break;
			case '*':
				if($this->isEmpty($value)){
					return "Missing value in %ColName%";
				}
			break;
			default:
			break;
		}
		return $result;
	}
	
	private function isEmpty($value){
		if(null == $value || ''==$value){
			return true;
		}
		else{
			return false;
		}
	}
	
	private function hasSpace($value){
		$pos = strpos($value, ' ');
		if(false===$pos){
			return false;
		}
		else{
			return true;
		}
	}
	
	protected function determineColumnValidator($string){
		$st_arr = str_split($string);
		$start = $st_arr[0];
		$end = end($st_arr);
		
		$output = '';
		if($start=="#"){
			$output = "#";
		}
		if($end=="*"){
			$output = "*";
		}
		return $output;
	}
}