<?php

namespace pavel\connect;

use PDO;
use PDOException;

class Database
{
    private static $connection = null;

    static function connect()
    {
        try {
            if (self::$connection === null) {
                $dsn = 'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'];
                self::$connection = new PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
            }
            return self::$connection;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}