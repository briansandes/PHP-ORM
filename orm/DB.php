<?php

class DB {
    public static $_last_id;
    
    public static $print = false;
    
    public static $_defaultParams = [
        'charset' => 'utf8',
        'driver' => 'mysql'
    ];
    
    /* connections */
    private static $defaultConnection;
    private static $connections = [];
    
    /* adds a connection */
    /* $params[server user password name] */
    /* $id string - optional, if an id hasnt been provided, sets connection as default */
    public static function addConnection($params, $id = null) {
        if(!$params) {
            throw new Exception('Undefined params.');
        }
        
        /* checks mandatory parameters existance */
        $undeclaredParams = [];
        foreach (['server', 'user', 'password', 'name'] as $param) {
            if(!key_exists($param, $params)) {
                $undeclaredParams[] = $param;
            }
        }
        /* throws exception if mandatory parameters havent been set */
        if(count($undeclaredParams) > 0) {
            throw new Exception('Mandatory parameters not parsed to ORMDB::addConnection: '.join(', ', $undeclaredParams).'');
        }
        
        $params = array_merge($params, self::$_defaultParams);
        
        $connection = self::connect($params);
        
        if($connection) {
            /* adds connection to static connections */
            if($id && is_string($id)) {
                self::$connections[$id] = $connection;
            } else {
                self::$defaultConnection = $connection;
            }
        }
        
        return $connection;
    }
    
    /* returns a connection object */
    /* $id optional */
    /* if an id hasnt been provided, returns defualtConnection */
    public static function getConnection($id = null) {
        if($id && is_string($id)) {
            if(key_exists($id, self::$connections)) {
                $connection = self::$connections[$id];
            } else {
                throw new Exception('Connection "'.$id.'" not declared.');
            }
        } else {
            $connection = self::$defaultConnection;
        }
        
        return $connection;
    }
    
    /* returns default connection */
    public static function getDefaultConnection() {
        return self::$defaultConnection;
    }
    
    /* returns a new connection */
    private static function connect($params){
        /* building connection string */
        $connectionString = $params['driver'].
                ':host='.$params['server'].(key_exists('port', $params) ? ';port='.$params['port'] : '').
                ';dbname='.$params['name'].';charset='.$params['charset'];
        
        return new PDO($connectionString, $params['user'], $params['password'], [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=''"
        ]);
    }
    
    
    /* runs a query */
    /* $params [sql, (optional) data, (optional) connection]
     */
    public static function query($params) {
        if(self::$print === true) {
            var_dump($params);
            die;
        }
        
        /* checks for existance of a sql query */
        if(!key_exists('sql', $params)) {
            throw new Exception('No SQL query declared.');
        }
        
        /* looks for a declared connection */
        /* if none has been provided, tries to pick self::defaultConnection */
        $connection = self::getConnection(key_exists('connection', $params) ? $params['connection'] : null);
        
        /* throws error if no connections at all have been found */
        if(!$connection) {
            throw new Exception('No DB connections declared.');
        }
        
        /* prepares query */
        $result = $connection->prepare($params['sql']);
        
        if($result) {
            $connection->beginTransaction();
            $result->execute($params['data']);
            self::$_last_id = ($connection->lastInsertId() ? $connection->lastInsertId() : null);
            $connection->commit();
        } else {
            throw new Exception('Error at: '.$params['sql']);
        }
        return $result;
    }
    
    /* var_dumps next executed query */
    public static function printQuery() {
        self::$print = true;
    }
}