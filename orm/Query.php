<?php

class Query {
    /* general query types */

    public static $queryTypes = [
        'select' => [
            'sql' => 'SELECT {$columns} FROM {$table} {$where} {$order_by} {$limit}',
            'params' => ['columns', 'table', 'where', 'order_by', 'limit']
        ],
        'insert' => [
            'sql' => 'INSERT INTO {$table} SET {$plain_columns}',
            'params' => ['plain_columns', 'table']
        ],
        'update' => [
            'sql' => 'UPDATE {$table} SET {$plain_columns} {$where} {$order_by} {$limit}',
            'params' => ['table', 'plain_columns', 'where', 'order_by', 'limit']
        ],
        'delete' => [
            'sql' => 'DELETE FROM {$table} {$where} {$order_by} {$limit}',
            'params' => ['columns', 'table', 'where', 'order_by', 'limit']
        ],
        'truncate' => [
            'sql' => 'TRUNCATE TABLE {$table}',
            'params' => ['table']
        ]
    ];

    /* general query parameters */
    public static $params = [
        'columns', 'table', 'where', 'order_by',
        'limit', 'values', 'columnsValues'
    ];
    

    /* param renderers */

    /* columns */
    public static function columns($params = null) {
        /* default value */
        $columns = '*';
        
        if ($params) {
            $columns = join(', ', $params);
        }

        return ['sql' => $columns];
    }

    /* $params [data, (optional) prefix] */
    /* returns a prepared statemment
     * with comma separated columns with or without a prefix on value placeholders */
    /* example name = :name, text = :text */

    public static function plain_columns($params) {
        $sql = '';
        if (key_exists('prefix', $params)) {
            $prefix = $params['prefix'];
        }

        foreach ($params['data'] as $column => $value) {
            $sql .= $column . ' = :' . $prefix . $column . ', ';
        }
        return ['sql' => trim($sql, ', '), 'data' => self::prepareData($params['data'], $prefix)];
    }

    /* table */

    public static function table($params) {
        return ['sql' => $params];
    }

    /* renders a where */

    public static function where($params) {
        return ['sql' => $params ? 'WHERE ' . $params : ''];
    }

    /* order_by */

    public static function order_by($params) {
        return ['sql' => $params ? 'ORDER BY ' . $params : ''];
    }

    public static function limit($params) {
        return ['sql' => $params ? 'LIMIT ' . $params : ''];
    }

    /* build query function */
    /* $queryType = A valid queryType defined on the queryTypes property */
    /* $params = set of (mandatory) params for a query to be built */
    /* returns Array [
     * 'sql' => plain SQL of the built query
     * 'data' => prepared data for the query to run
     * ] */

    public static function buildQuery($queryType, $params) {

        /* separates data from the params array */
        if (key_exists('data', $params)) {
            /* contains $data returned by any function which returns prepared statements */
            $data = $params['data'];
            unset($params['data']);
        } else {
            $data = [];
        }
        
        
        /* checks query type existance */
        if(!key_exists($queryType, self::$queryTypes)) {
            throw new Exception('Query type "'.$queryType.'" not declared in Query::$queryTypes.');
        }
        
        /* sets query template */
        $sql = self::$queryTypes[$queryType]['sql'];
        
        foreach (self::$queryTypes[$queryType]['params'] as $param) {
            /* gets the result from the Query::param_function */
            $renderedParam = self::$param($params[$param]);

            /* replaces param on the query template with the rendered param */
            $sql = str_replace('{$' . $param . '}', $renderedParam['sql'], $sql);

            /* merges data property if any has been returned */
            if (key_exists('data', $renderedParam)) {
                $data = array_merge($data, $renderedParam['data']);
            }
        }

        return [
            'sql' => trim($sql),
            'data' => $data
        ];
    }

    /* merely adds colons before keys of an array */
    /* a prefix before keys is optional */
    public static function prepareData($data, $prefix = null) {
        $preparedData = [];

        foreach ($data as $key => $value) {
            $preparedData[':' . $prefix . $key] = $value;
        }

        return $preparedData;
    }

    /* user query functions */


    /* SELECT */
    /* $params [columns, table, where, order_by, limit] */
    public static function select($params) {
        
        /* checks table parameter existance */
        if (!key_exists('table', $params)) {
            throw new Exception('Table not defined.');
        }

        /* renders query sql and data */
        $query = self::buildQuery('select', $params);

        /* prepares data if it has been provided */
        if ($query['data']) {
            $query['data'] = self::prepareData($query['data']);
        }

        $result = DB::query($query);
        $rows = $result->fetchAll(PDO::FETCH_OBJ);
        return $rows;
    }

    /* INSERT */
    /* $params [table, data] */
    public static function insert($params) {
        /* checks table existance */
        if (!key_exists('table', $params)) {
            throw new Exception('Table not defined.');
        }

        $query = self::buildQuery('insert', [
            'table' => $params['table'],
            'plain_columns' => [
                'data' => $params['data'],
                'prefix' => 'i_'
            ]
        ]);

        $result = DB::query($query);

        return (bool) $result === true ? DB::$_last_id : false;
    }

    /* UPDATE */
    /* $params [table, where, order_by, limit] */
    public static function update($data, $params) {
        /* checks table existance */
        if (!key_exists('table', $params)) {
            throw new Exception('Table not defined.');
        }

        /* sets up update parameters */
        $params['plain_columns'] = [
            'data' => $data,
            'prefix' => 'u_'
        ];


        $query = self::buildQuery('update', $params);
        $result = DB::query($query);

        return (bool)$result;
    }

    /* DELETE */
    /* $params [columns, table, where, order_by, limit] */
    public static function delete($params) {

        /* checks table parameter existance */
        if (!key_exists('table', $params)) {
            throw new Exception('Table not defined.');
        }

        /* renders query sql and data */
        $query = self::buildQuery('delete', $params);

        /* prepares data if it has been provided */
        if ($query['data']) {
            $query['data'] = self::prepareData($params['data']);
        }

        $result = DB::query($query);
        return (bool) $result;
    }
    
    public static function truncate($params) {
        /* checks table parameter existance */
        if (!key_exists('table', $params)) {
            throw new Exception('Table not defined.');
        }

        /* renders query sql and data */
        $query = self::buildQuery('truncate', $params);

        $result = DB::query($query);
        return (bool) $result;
    }
}