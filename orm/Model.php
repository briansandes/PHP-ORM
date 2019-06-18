<?php

class Model {
    public static function table() {
        return get_called_class()::$_table;
    }

    public static function find($params = null) {
        $params['table'] = self::table();
        return Query::select($params);
    }

    public static function find_one($params = null) {
        return self::find($params)[0];
    }
    
    public static function insert($data) {
        return Query::insert([
            'table' => self::table(),
            'data' => $data
        ]);
    }
    
    public static function update($data, $params) {
        if(!key_exists('where', $params)) {
            throw new Exception('Update without where.');
        }
        
        $params['table'] = self::table();

        return Query::update($data, $params);
    }
    
    public static function delete($params) {
        $params['table'] = self::table();

        if(!key_exists('where', $params)) {
            throw new Exception('Delete without where.');
        }
        return Query::delete($params);
    }
    
    public static function truncate() {
        
        return Query::truncate([
            'table' => self::table()
        ]);
    }
}