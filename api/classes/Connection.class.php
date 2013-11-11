<?php
class Connection
{

	protected static $instance;

	protected function __construct() {}

  public static function getInstance()
  {
  	if(empty(self::$instance))
		{
			self::$instance = new PDO("mysql:host=".DB_HOST.';port=3306;dbname='.DB_NAME, DB_USER, DB_PASS);
    }

    return self::$instance;
  }
}