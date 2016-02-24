<?php

namespace JetFire\Db;


/**
 * Class Model
 * @package JetFire\Db
 */
class Model {

    /**
     * @var
     */
    public static $orm;
    public static $defaultOrm;
    /**
     * @var
     */
    public static $allOrm;
    /**
     * @var null
     */
    public static $instance = null;

    public static $provider = [];

    /**
     * @return Model|null
     */
    public static function getInstance ()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    public static function provide($provider = [],$default = null){
        self::$provider = $provider;
        reset($provider);
        self::$defaultOrm = (!is_null($default)) ? $default : key($provider);
    }

    public static function orm($name){
        if(!isset(self::$allOrm[$name]))
            self::$allOrm[$name] = call_user_func(self::$provider[$name]);
        self::$orm = self::$allOrm[$name];
        self::$orm->setTable(get_called_class());
        return self::getInstance();
    }


    public static function __callStatic($name,$args){
        if(is_null(self::$orm))
            self::orm(self::$defaultOrm);
        self::$orm->setTable(get_called_class());
        return self::$orm->callStatic($name,$args);
    }

    public function __call($name,$args){
        if(is_null(self::$orm))
            self::orm(self::$defaultOrm);
        self::$orm->setTable(get_called_class());
        return self::$orm->callStatic($name,$args);
    }

} 