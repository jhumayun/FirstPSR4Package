<?php
use AvanaTest\Acme\ExcelValidator as ExcelValidator;

namespace AvanaTest\Acme;

class Type_B extends ExcelValidator{
	public static $col_spec = array(
									'Field_A*',
									'#Field_B'
								);	
	
	public function __construct($file,$fileType){
		$this->_file = $file;
		$this->_fileType = $fileType;
	}
}