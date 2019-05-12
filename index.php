<?php
/* config for error handling */
error_reporting(E_ALL ^ E_NOTICE);

function ExceptionHandler($Exception) {
    var_dump($Exception->getMessage() . ' on line '.$Exception->getLine().' '.$Exception->getFile());
}
//set_exception_handler('ExceptionHandler');


require 'orm/DB.php';
require 'orm/Query.php';

/* adds default connection */
DB::addConnection(['server' => 'localhost', 'user' => 'root', 'password' => '', 'name' => 'quiz']);