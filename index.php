<?php
use AvanaTest\Acme\ExcelValidatorFactory as ExcelValidatorFactory;

require_once 'app/start.php';

define("FILE_PATH","./L2_sample/avana_exercise/Type_A/Type_A.xlsx");
define("FILE_TYPE","Type_A");

$Validator = ExcelValidatorFactory::create(FILE_TYPE,FILE_PATH);
$Validator->validate();