<?php
use AvanaTest\Acme\ExcelValidator as ExcelValidator;

namespace AvanaTest\Acme;

class Type_A extends ExcelValidator{
	public static $col_spec = array(
									'Field_A*',
									'#Field_B',
									'Field_C',
									'Field_D*',
									'Field_E*'
								);	
	
	public function __construct($file,$fileType){
		$this->_file = $file;
		$this->_fileType = $fileType;
	}
}