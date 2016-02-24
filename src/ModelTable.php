<?php

namespace JetFire\Db;


/**
 * Class ModelTable
 * @package JetFire\Db
 */
class ModelTable {

    /**
     * @var
     */
    public static $orm;
    /**
     * @var null
     */
    public static $instance = null;

    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    /**
     * @param null $table
     * @return ModelTable|null
     */
    public static function table ($table = null)
    {
        if (self::$instance === null)
            self::$instance = new self;
        (!is_null($table))
            ? self::$orm->setTable($table)
            : self::$orm->setTable(get_called_class());
        return self::$instance;
    }

    public static function __callStatic($name,$args){
        return self::$orm->callStatic($name,$args);
    }

    public function __call($name,$args){
        return self::$orm->callStatic($name,$args);
    }
} 