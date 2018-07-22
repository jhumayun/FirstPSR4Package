<?php
namespace AvanaTest\Acme;

interface IProcessor {
	/**
	* @param $file Path to excel file
	* @param $fileType The file type (Type_A, Type_B, et
	c)
	* @return mixed
	*/
	public function process($file, $fileType);
}