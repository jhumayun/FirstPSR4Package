<?php
use AvanaTest\Acme\Utils as Utils;

namespace AvanaTest\Acme;

class ExcelValidatorFactory{
	public function __construct() {
    }
 
    public static function create($type,$FilePath) {             
        $className = __NAMESPACE__."\\".$type;
		$Utils = Utils::instance();
		if($Utils::validateExcelFile($FilePath)){
			// Assuming Class files are already loaded using autoload concept
			if(class_exists($className)) {
				return new $className($FilePath,$type);
			} else {
				$Utils::printMessage("Excel Type class '$className' not found."); die();
			}
		}
		else{
			$Utils::printMessage("File validation failed."); die();
		}
    }
}