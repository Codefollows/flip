<?php

use lukafurlan\database\connector\ConnectorType;
use lukafurlan\database\DMLQuery\DMLQueryManager;

class Model {

	protected static $database;

    public static function getDb() {
	    if (!self::$database) {
            self::$database = new DMLQueryManager(ConnectorType::MYSQL);
        }
	    return self::$database;
    }

}