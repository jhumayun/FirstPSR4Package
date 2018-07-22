<?php
namespace AvanaTest\Acme;

/**
	A singleton Pattren Class
	Provides utility functions
**/
	
final class Utils{
	
	public static function instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Utils();
        }
        return $inst;
    }
	
	private function __construct()
    {
    }
	
	public static function getFileExtension($FilePath){
		$comp = explode('.',$FilePath);
		$ext = ucfirst(end($comp));
		return $ext;
	}
	
	public static function printMessage($message){
		echo "<pre>".print_r($message,1)."</pre>";
	}
	
	public static function validateExcelFile($FilePath){
		if (file_exists($FilePath)) {
			$ext = self::getFileExtension($FilePath);
			if('Xls'==$ext || 'Xlsx'==$ext){
				return true;
			}
			else{
				self::printMessage("The Extension '$ext' is invalid.");
				return false;
			}
		}
		else{ 
			self::printMessage("The File '$FilePath' does not exist.");
			return false;
		}
	}
}